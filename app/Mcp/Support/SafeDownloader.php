<?php

namespace App\Mcp\Support;

use Illuminate\Support\Facades\Http;
use Psr\Http\Message\UriInterface;

/**
 * Downloads a remote image with SSRF protection:
 *  - https only (also for redirects)
 *  - host must resolve only to public IPs (no localhost / private / reserved)
 *  - at most 3 redirects, each re-checked
 *  - stops reading after the size limit
 */
class SafeDownloader
{
    public const MAX_BYTES = 5 * 1024 * 1024;

    /** @return array{0: ?string, 1: ?string} [bytes, error] */
    public static function fetch(string $url): array
    {
        $parts = parse_url($url);
        if (! $parts || strtolower($parts['scheme'] ?? '') !== 'https' || empty($parts['host'])) {
            return [null, 'image_url must be a full https:// link.'];
        }

        if ($error = static::hostProblem($parts['host'])) {
            return [null, $error];
        }

        try {
            $res = Http::timeout(30)
                ->connectTimeout(10)
                ->withHeaders(['User-Agent' => 'TimePass-MCP/1.0', 'Accept' => 'image/*'])
                ->withOptions([
                    'stream' => true,
                    'allow_redirects' => [
                        'max' => 3,
                        'protocols' => ['https'],
                        'on_redirect' => function ($request, $response, UriInterface $uri) {
                            if ($error = static::hostProblem($uri->getHost())) {
                                throw new \RuntimeException($error);
                            }
                        },
                    ],
                ])
                ->get($url);
        } catch (\Throwable $e) {
            $msg = str_contains($e->getMessage(), 'not allowed')
                ? 'A redirect pointed to an address that is not allowed (private or local network).'
                : 'Could not download the image (network error or timeout).';

            return [null, $msg];
        }

        if ($res->status() >= 400) {
            return [null, "Could not download the image (HTTP {$res->status()}). The link must be public and point directly to an image file."];
        }

        if ((int) $res->header('Content-Length') > self::MAX_BYTES) {
            return [null, 'The image is larger than 5 MB.'];
        }

        $body = $res->toPsrResponse()->getBody();
        $bytes = '';
        while (! $body->eof()) {
            $bytes .= $body->read(65536);
            if (strlen($bytes) > self::MAX_BYTES) {
                return [null, 'The image is larger than 5 MB.'];
            }
        }

        return [$bytes, null];
    }

    /** Null if the host is safe to fetch, otherwise an explanation. */
    public static function hostProblem(string $host): ?string
    {
        $host = trim($host, '[]');
        $lower = strtolower($host);

        if ($lower === 'localhost' || str_ends_with($lower, '.localhost') || str_ends_with($lower, '.internal')) {
            return 'That address is not allowed (local/internal host).';
        }

        $ips = filter_var($host, FILTER_VALIDATE_IP)
            ? [$host]
            : array_merge(
                array_column(@dns_get_record($host, DNS_A) ?: [], 'ip'),
                array_column(@dns_get_record($host, DNS_AAAA) ?: [], 'ipv6'),
            );

        if ($ips === []) {
            return "Could not resolve the image host \"{$host}\".";
        }

        foreach ($ips as $ip) {
            if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return 'That address is not allowed (private or reserved network).';
            }
        }

        return null;
    }
}
