<?php

namespace App\Mcp\Support;

/**
 * Guards product image values set through MCP so an AI can't save a made-up
 * file path (which would show a broken image on the site).
 */
class ImagePath
{
    /** Returns an error message if the value can't be displayed, or null if it's fine. */
    public static function problem(?string $image): ?string
    {
        if ($image === null || $image === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $image) || str_starts_with($image, 'media/')) {
            return null;
        }

        if (is_file(public_path(ltrim($image, '/')))) {
            return null;
        }

        return "The image \"{$image}\" does not exist on the server, so it would show as a broken image. "
            .'Do not invent file paths. To add a picture, use the set_product_image tool with a public image URL '
            .'(a direct link to a .jpg/.png/.webp file), or leave "image" empty.';
    }
}
