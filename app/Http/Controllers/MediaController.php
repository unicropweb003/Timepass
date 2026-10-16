<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serves images stored in the media table at /media/{id}.
 *
 * The first request decodes the image from the database and keeps a copy on
 * local disk; later requests are served from disk with no database query.
 * Media rows never change (a new image is a new row), so the copy can't go
 * stale; deleting a row removes its copy (see Media::booted()).
 */
class MediaController
{
    public static function cachePath(int $id): string
    {
        return storage_path('framework/cache/media/'.$id);
    }

    public function show(int $id): Response
    {
        $path = static::cachePath($id);

        if (is_file($path) && is_file($path.'.mime')) {
            $bytes = (string) file_get_contents($path);
            $mime = trim((string) file_get_contents($path.'.mime'));
        } else {
            $media = Media::find($id);
            abort_if($media === null, 404);

            $bytes = (string) base64_decode($media->data);
            $mime = $media->mime;

            // Write the mime file first and publish the bytes atomically, so a
            // concurrent request never sees a half-written copy.
            @mkdir(dirname($path), 0775, true);
            if (@file_put_contents($path.'.mime', $mime) !== false && @file_put_contents($path.'.tmp', $bytes) !== false) {
                @rename($path.'.tmp', $path);
            }
        }

        return response($bytes, 200, [
            'Content-Type' => $mime,
            'Content-Length' => (string) strlen($bytes),
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
