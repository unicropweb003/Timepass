<?php

namespace App\Support;

/**
 * Full URL for a stored image value.
 *
 * New uploads are stored as full Cloudinary https URLs and are returned as is.
 * Older rows hold local paths ("images/products/x.jpg", "storage/…", "media/12"),
 * which keep their old behaviour: asset() turns them into a site URL.
 */
class ImageUrl
{
    public static function resolve(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $value)) {
            return $value;
        }

        return asset(ltrim($value, '/'));
    }
}
