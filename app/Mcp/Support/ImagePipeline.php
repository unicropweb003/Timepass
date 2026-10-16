<?php

namespace App\Mcp\Support;

use App\Models\Media;

/**
 * Turns an image request (AI prompt, URL, upload id, base64, local path)
 * into a stored, optimised image and returns its path ("media/12").
 *
 * All images are validated by magic bytes (jpeg/png/webp only), resized to
 * at most 1200px wide and converted to WebP when GD supports it, then saved
 * in the database so they survive redeploys.
 *
 * Never throws: failures come back as ['path' => null, 'note' => why].
 */
class ImagePipeline
{
    public const MAX_WIDTH = 1200;

    /**
     * @param  array  $input  validated tool input (image_* keys)
     * @param  string  $default  source to use when image_source is absent ("ai" on create, "keep" on update)
     * @param  string  $subject  what to draw when no prompt is given (name + description)
     * @param  string  $kind  "product" | "category" | "about"
     * @return array{action: string, path: ?string, note: string}
     *                                                            action: keep (no change) | set | remove | failed
     */
    public static function resolve(array $input, string $default, string $subject, string $kind = 'product'): array
    {
        $source = $input['image_source'] ?? null;

        // Infer the source from what was supplied, so "image_url" alone just works.
        if ($source === null) {
            $source = match (true) {
                ! empty($input['image_url']) => 'url',
                ! empty($input['image_upload_id']) || ! empty($input['image_base64']) || ! empty($input['local_path']) => 'upload',
                ! empty($input['image_prompt']) => 'ai',
                default => $default,
            };
        }

        if ($source === 'keep') {
            return ['action' => 'keep', 'path' => null, 'note' => ''];
        }

        if ($source === 'none') {
            return ['action' => 'remove', 'path' => null, 'note' => 'Image removed.'];
        }

        [$bytes, $error, $how] = match ($source) {
            'ai' => static::fromAi($input['image_prompt'] ?? null, $subject, $kind),
            'url' => static::fromUrl($input['image_url'] ?? null),
            'upload' => static::fromUpload($input),
            default => [null, "Unknown image_source \"{$source}\". Use ai, url, upload or none.", ''],
        };

        // An upload id refers to an already-processed Media row.
        if ($bytes instanceof Media) {
            return ['action' => 'set', 'path' => $bytes->path(), 'note' => "Image attached from upload ({$how})."];
        }

        if ($error) {
            return ['action' => 'failed', 'path' => null, 'note' => "Image NOT saved: {$error}"];
        }

        [$media, $error] = static::store($bytes);
        if ($error) {
            return ['action' => 'failed', 'path' => null, 'note' => "Image NOT saved: {$error}"];
        }

        return ['action' => 'set', 'path' => $media->path(), 'note' => "Image saved ({$how}, {$media->mime}, ".round($media->size / 1024).' KB).'];
    }

    /** Validate + optimise + persist raw bytes. @return array{0: ?Media, 1: ?string} */
    public static function store(string $bytes, bool $asUpload = false): array
    {
        if (strlen($bytes) > SafeDownloader::MAX_BYTES) {
            return [null, 'the image is larger than 5 MB'];
        }

        $type = static::detectType($bytes);
        if ($type === null) {
            return [null, 'the file is not a JPEG, PNG or WebP image'];
        }

        [$bytes, $type] = static::optimise($bytes, $type);

        $media = Media::create([
            'mime' => $type,
            'size' => strlen($bytes),
            'data' => base64_encode($bytes),
            'upload_token' => $asUpload ? bin2hex(random_bytes(16)) : null,
            'expires_at' => $asUpload ? now()->addMinutes(15) : null,
        ]);

        return [$media, null];
    }

    /** Delete the stored file behind a record's old image path (no-op for links/paths). */
    public static function deleteStored(?string $path): void
    {
        Media::fromPath($path)?->delete();
    }

    /** Detect type from magic bytes (not the extension or headers). */
    public static function detectType(string $bytes): ?string
    {
        return match (true) {
            str_starts_with($bytes, "\xFF\xD8\xFF") => 'image/jpeg',
            str_starts_with($bytes, "\x89PNG\r\n\x1A\n") => 'image/png',
            strlen($bytes) > 12 && substr($bytes, 0, 4) === 'RIFF' && substr($bytes, 8, 4) === 'WEBP' => 'image/webp',
            default => null,
        };
    }

    // ── sources ─────────────────────────────────────────────────────────

    private static function fromAi(?string $prompt, string $subject, string $kind): array
    {
        $what = trim($prompt ?: $subject);
        $full = match ($kind) {
            'category' => "Clean, modern banner image representing the product category \"{$what}\" for an online beauty store. Arrange a few typical products of this category neatly on a soft pastel background, studio lighting. No people, no faces, no hands, no text.",
            'about' => "Warm, professional lifestyle image for an online store's About Us page: {$what}. Bright, inviting, minimal, no text, no logos.",
            default => "Professional e-commerce product packshot of {$what}. Only the product itself (bottle, jar, tube, box or garment) centered, isolated on a plain white background, soft studio lighting, sharp focus, high detail. No people, no faces, no hands, no text overlay.",
        };

        $result = ImageGenerator::generate($full);
        if (isset($result['error'])) {
            return [null, $result['error'], ''];
        }

        $how = 'AI: '.$result['provider'].(isset($result['warning']) ? ' — '.$result['warning'] : '');

        return [$result['bytes'], null, $how];
    }

    private static function fromUrl(?string $url): array
    {
        if (! $url) {
            return [null, 'image_source is "url" but no image_url was given', ''];
        }

        [$bytes, $error] = SafeDownloader::fetch($url);

        return [$bytes, $error, 'downloaded from link'];
    }

    private static function fromUpload(array $input): array
    {
        if (! empty($input['image_upload_id'])) {
            $media = Media::where('upload_token', $input['image_upload_id'])
                ->where('expires_at', '>', now())
                ->first();

            if (! $media) {
                return [null, 'image_upload_id is unknown or expired (upload ids last 15 minutes) — upload again via POST /upload', ''];
            }

            $media->forceFill(['upload_token' => null, 'expires_at' => null])->save();

            return [$media, null, 'upload id'];
        }

        if (! empty($input['image_base64'])) {
            $value = $input['image_base64'];
            if (str_starts_with($value, 'data:')) {
                $value = substr($value, strpos($value, ',') + 1);
            }
            $bytes = base64_decode(preg_replace('/\s+/', '', $value), true);

            return $bytes === false || $bytes === ''
                ? [null, 'image_base64 is not valid base64 data', '']
                : [$bytes, null, 'base64'.(! empty($input['image_filename']) ? ' '.$input['image_filename'] : '')];
        }

        if (! empty($input['local_path'])) {
            if (! app()->runningInConsole()) {
                return [null, 'local_path only works when the MCP server runs locally (stdio). Use image_url or image_upload_id instead', ''];
            }
            $path = $input['local_path'];
            if (! is_file($path) || ! is_readable($path)) {
                return [null, "local file not found: {$path}", ''];
            }
            if (filesize($path) > SafeDownloader::MAX_BYTES) {
                return [null, 'the image is larger than 5 MB', ''];
            }

            return [file_get_contents($path), null, 'local file'];
        }

        return [null, 'image_source is "upload" but no image_upload_id, image_base64 or local_path was given', ''];
    }

    // ── processing ──────────────────────────────────────────────────────

    /** Resize to MAX_WIDTH and convert to WebP when GD supports it. */
    private static function optimise(string $bytes, string $type): array
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagewebp')) {
            return [$bytes, $type]; // GD/WebP unavailable: keep the validated original.
        }

        $img = @imagecreatefromstring($bytes);
        if ($img === false) {
            return [$bytes, $type];
        }

        $w = imagesx($img);
        $h = imagesy($img);
        if ($w > self::MAX_WIDTH) {
            $nh = (int) round($h * self::MAX_WIDTH / $w);
            $resized = imagecreatetruecolor(self::MAX_WIDTH, $nh);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagecopyresampled($resized, $img, 0, 0, 0, 0, self::MAX_WIDTH, $nh, $w, $h);
            imagedestroy($img);
            $img = $resized;
        } else {
            imagepalettetotruecolor($img);
            imagealphablending($img, false);
            imagesavealpha($img, true);
        }

        ob_start();
        $ok = imagewebp($img, null, 82);
        $webp = ob_get_clean();
        imagedestroy($img);

        return $ok && $webp ? [$webp, 'image/webp'] : [$bytes, $type];
    }
}
