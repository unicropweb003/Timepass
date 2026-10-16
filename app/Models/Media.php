<?php

namespace App\Models;

use App\Http\Controllers\MediaController;
use Illuminate\Database\Eloquent\Model;

/**
 * An image stored in the database (base64), served at /media/{id}.
 *
 * Rows created by POST /upload carry a short-lived upload_token until an
 * MCP tool attaches them to a record.
 */
class Media extends Model
{
    protected $fillable = ['mime', 'size', 'data', 'upload_token', 'expires_at'];

    protected $hidden = ['data'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime'];
    }

    /** Remove the on-disk copy served by MediaController when the image is deleted. */
    protected static function booted(): void
    {
        static::deleted(function (Media $media) {
            $path = MediaController::cachePath($media->id);
            @unlink($path);
            @unlink($path.'.mime');
        });
    }

    /** Relative path stored on records (asset()/url() turn it into a full URL). */
    public function path(): string
    {
        return 'media/'.$this->id;
    }

    /** The Media row behind a stored path like "media/12", if any. */
    public static function fromPath(?string $path): ?self
    {
        if ($path && preg_match('#^/?media/(\d+)$#', $path, $m)) {
            return static::find((int) $m[1]);
        }

        return null;
    }
}
