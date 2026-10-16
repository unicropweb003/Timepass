<?php

namespace App\Mcp\Support;

use Illuminate\Support\Facades\Http;

/**
 * Generates an image from a text prompt.
 *
 * Provider order:
 *  1. Google Gemini image model (when GEMINI_API_KEY is set) — tries the
 *     Interactions API first, then the classic generateContent endpoint.
 *  2. Pollinations (free fallback, ~1 image / 45 s, small watermark).
 *
 * Returns ['bytes' => string, 'provider' => string] or ['error' => string].
 */
class ImageGenerator
{
    public static function generate(string $prompt): array
    {
        if ($key = config('services.gemini.key')) {
            $result = static::gemini($prompt, $key);
            if (isset($result['bytes'])) {
                return $result;
            }
            $geminiError = $result['error'];

            // Fall through to Pollinations so the user still gets an image.
            $fallback = static::pollinations($prompt);

            return isset($fallback['bytes'])
                ? $fallback + ['warning' => "Gemini failed ({$geminiError}); used the free fallback."]
                : ['error' => "Gemini failed ({$geminiError}) and the free fallback failed ({$fallback['error']})."];
        }

        return static::pollinations($prompt);
    }

    private static function gemini(string $prompt, string $key): array
    {
        $model = config('services.gemini.image_model', 'gemini-3.1-flash-lite-image');
        $base = 'https://generativelanguage.googleapis.com/v1beta';
        $http = Http::timeout(120)->withHeaders(['x-goog-api-key' => $key]);

        $attempts = [
            // Current Interactions API.
            fn () => $http->post("{$base}/interactions", [
                'model' => $model,
                'input' => [['type' => 'text', 'text' => $prompt]],
            ]),
            // Classic endpoint.
            fn () => $http->post("{$base}/models/{$model}:generateContent", [
                'contents' => [['parts' => [['text' => $prompt]]]],
                'generationConfig' => ['responseModalities' => ['TEXT', 'IMAGE']],
            ]),
        ];

        $lastError = 'no response';
        foreach ($attempts as $attempt) {
            try {
                $res = $attempt();
            } catch (\Throwable $e) {
                $lastError = 'timeout or network error';

                continue;
            }

            if (! $res->successful()) {
                $lastError = 'HTTP '.$res->status().': '.static::shortError($res->json());

                continue;
            }

            $b64 = static::findBase64Image($res->json() ?? []);
            if ($b64 !== null && ($bytes = base64_decode($b64, true)) !== false) {
                return ['bytes' => $bytes, 'provider' => "Google Gemini ({$model})"];
            }

            $lastError = 'response contained no image';
        }

        return ['error' => $lastError];
    }

    private static function pollinations(string $prompt): array
    {
        $url = 'https://image.pollinations.ai/prompt/'.rawurlencode($prompt)
            .'?width=1024&height=1024&nologo=true&model=flux&seed='.random_int(1, 999999);

        $http = Http::timeout(120)->withHeaders(['User-Agent' => 'TimePass-MCP/1.0']);
        if ($token = config('services.pollinations.token')) {
            $http = $http->withToken($token);
        }

        try {
            $res = $http->get($url);
        } catch (\Throwable $e) {
            return ['error' => 'the free image service did not respond in time'];
        }

        if (in_array($res->status(), [402, 429], true)) {
            return ['error' => 'the free image service is busy (about 1 image every 45 seconds) — wait a minute and retry'];
        }

        if (! $res->successful()) {
            return ['error' => 'the free image service returned HTTP '.$res->status()];
        }

        return ['bytes' => $res->body(), 'provider' => 'Pollinations (free)'];
    }

    /** Walk any JSON shape and return the first base64 image payload. */
    private static function findBase64Image(array $node): ?string
    {
        $mime = $node['mimeType'] ?? $node['mime_type'] ?? null;
        if (isset($node['data']) && is_string($node['data']) && strlen($node['data']) > 200
            && ($mime === null || str_starts_with((string) $mime, 'image/'))) {
            return $node['data'];
        }

        foreach ($node as $child) {
            if (is_array($child) && ($found = static::findBase64Image($child)) !== null) {
                return $found;
            }
        }

        return null;
    }

    private static function shortError(mixed $json): string
    {
        $msg = is_array($json) ? ($json['error']['message'] ?? json_encode($json)) : 'unknown error';

        return mb_substr((string) $msg, 0, 160);
    }
}
