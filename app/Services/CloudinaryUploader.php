<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Uploads user images to Cloudinary and deletes them again.
 *
 * Files never touch the server's disk for good, so the app keeps working on
 * Render's ephemeral disk and on several instances behind a load balancer.
 * Credentials come only from config (CLOUDINARY_URL, see config/filesystems.php).
 */
class CloudinaryUploader
{
    /** Validation rules for an uploaded image: jpg, jpeg, png or webp, up to 5 MB. */
    public const RULES = ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'];

    public const MESSAGES = [
        'mimes' => 'The :attribute must be a JPG, JPEG, PNG or WebP image.',
        'max' => 'The :attribute may not be larger than 5 MB.',
        'image' => 'The :attribute must be an image.',
    ];

    /**
     * Validation rules for an uploaded product video: mp4, mov, webm or m4v, up to 40 MB
     * (Cloudinary's free plan resizes videos on the fly only up to that size).
     */
    public const VIDEO_RULES = ['file', 'mimes:mp4,mov,webm,m4v', 'max:40960'];

    public const VIDEO_MESSAGES = [
        'mimes' => 'The :attribute must be an MP4, MOV or WebM video.',
        'max' => 'The :attribute may not be larger than 40 MB. Use a shorter clip (5–20 seconds).',
        'file' => 'The :attribute failed to upload.',
    ];

    public function __construct(private ?Cloudinary $client = null) {}

    /**
     * Upload an image into "<CLOUDINARY_FOLDER>/<folder>".
     *
     * @return string the https secure_url to store in the database
     *
     * @throws RuntimeException when Cloudinary is not configured or the upload fails
     */
    public function upload(UploadedFile $file, string $folder): string
    {
        return $this->send($file->getRealPath(), $folder);
    }

    /**
     * Upload a video into "<CLOUDINARY_FOLDER>/<folder>". The SDK streams the
     * file and sends it in chunks when it is large, so it is never held in memory.
     *
     * @return string the https secure_url to store in the database
     *
     * @throws RuntimeException when Cloudinary is not configured or the upload fails
     */
    public function uploadVideo(UploadedFile $file, string $folder): string
    {
        return $this->send($file->getRealPath(), $folder, 'video');
    }

    /**
     * Upload image bytes that were already validated (e.g. an MCP/AI image) into
     * "<CLOUDINARY_FOLDER>/<folder>". Sent as a data URI: nothing touches the disk.
     *
     * @return string the https secure_url to store in the database
     *
     * @throws RuntimeException when Cloudinary is not configured or the upload fails
     */
    public function uploadBytes(string $bytes, string $mime, string $folder): string
    {
        return $this->send('data:'.$mime.';base64,'.base64_encode($bytes), $folder);
    }

    /** True when uploads can be attempted: credentials configured, or a client was given. */
    public function isConfigured(): bool
    {
        return $this->client !== null || filled(config('filesystems.disks.cloudinary.url'));
    }

    /**
     * @param  string  $file  a local file path or a data URI
     * @param  string  $type  Cloudinary resource type: image or video
     */
    private function send(string $file, string $folder, string $type = 'image'): string
    {
        $prefix = trim((string) config('filesystems.disks.cloudinary.folder'), '/');
        $folder = trim($prefix.'/'.trim($folder, '/'), '/');

        $result = $this->client()->uploadApi()->upload($file, [
            'folder' => $folder,
            'resource_type' => $type,
            'use_filename' => true,
            'unique_filename' => true,
            'overwrite' => false,
        ]);

        $url = $result['secure_url'] ?? null;
        if (! is_string($url) || ! str_starts_with($url, 'https://')) {
            throw new RuntimeException('Cloudinary did not return a secure_url.');
        }

        return $url;
    }

    /**
     * Delete the Cloudinary file behind a stored URL. Silently ignores null,
     * local paths and non-Cloudinary URLs; failures are logged, never thrown.
     */
    public function delete(?string $url): void
    {
        $target = static::parseUrl($url);
        if ($target === null) {
            return;
        }

        try {
            $this->client()->uploadApi()->destroy($target['public_id'], [
                'resource_type' => $target['resource_type'],
                'invalidate' => true,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Cloudinary delete failed', [
                'public_id' => $target['public_id'],
                'error' => static::safeMessage($e),
            ]);
        }
    }

    /** An exception message with any Cloudinary credentials removed, safe to log. */
    public static function safeMessage(\Throwable $e): string
    {
        $message = preg_replace('#cloudinary://\S+#i', 'cloudinary://[redacted]', $e->getMessage());

        // Also blank out the key and secret themselves wherever they appear.
        $url = (string) config('filesystems.disks.cloudinary.url');
        if (preg_match('#^cloudinary://([^:]+):([^@]+)@#', $url, $m)) {
            $message = str_replace([$m[1], $m[2]], '[redacted]', $message);
        }

        return class_basename($e).': '.$message;
    }

    /**
     * Resource type + public_id from a delivery URL such as
     * https://res.cloudinary.com/<cloud>/image/upload/v123/timepass/products/serum.jpg
     * (→ image, "timepass/products/serum"). Null for anything that is not one.
     *
     * @return array{resource_type: string, public_id: string}|null
     */
    public static function parseUrl(?string $url): ?array
    {
        if (! $url || ! str_starts_with($url, 'http')) {
            return null;
        }

        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');
        if ($host !== 'res.cloudinary.com' && ! str_ends_with($host, '.cloudinary.com')) {
            return null;
        }

        if (! preg_match('#^/[^/]+/(image|video|raw)/upload/(.+)$#', $parts['path'] ?? '', $m)) {
            return null;
        }
        [, $type, $rest] = $m;

        $segments = explode('/', $rest);
        $version = null;
        foreach ($segments as $i => $segment) {
            if (preg_match('/^v\d+$/', $segment)) {
                $version = $i;
                break;
            }
        }
        if ($version !== null) {
            $segments = array_slice($segments, $version + 1);
        } else {
            // No version: drop leading transformation segments like "c_fill,w_300".
            while (count($segments) > 1 && (str_contains($segments[0], ',') || preg_match('/^(a|ar|b|bo|c|co|dpr|e|f|fl|g|h|l|o|q|r|t|u|w|x|y|z)_[^_]+$/', $segments[0]))) {
                array_shift($segments);
            }
        }

        $publicId = rawurldecode(implode('/', $segments));
        if ($type !== 'raw') {
            $publicId = preg_replace('/\.[A-Za-z0-9]+$/', '', $publicId);
        }

        return $publicId === '' ? null : ['resource_type' => $type, 'public_id' => $publicId];
    }

    private function client(): Cloudinary
    {
        if ($this->client) {
            return $this->client;
        }

        $url = config('filesystems.disks.cloudinary.url');
        if (blank($url)) {
            throw new RuntimeException('Cloudinary is not configured: set CLOUDINARY_URL.');
        }

        return $this->client = new Cloudinary($url);
    }
}
