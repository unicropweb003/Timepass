<?php

namespace App\Mcp\Support;

use Illuminate\Contracts\JsonSchema\JsonSchema;

/** Shared image_* input fields for create/update tools. */
class ImageInput
{
    public static function rules(): array
    {
        return [
            'image_source' => 'nullable|in:ai,url,upload,none,keep',
            'image_prompt' => 'nullable|string|max:500',
            'image_url' => 'nullable|string|max:2000',
            'image_base64' => 'nullable|string',
            'image_filename' => 'nullable|string|max:200',
            'image_upload_id' => 'nullable|string|max:64',
            'local_path' => 'nullable|string|max:1000',
        ];
    }

    public static function schema(JsonSchema $schema, bool $forCreate): array
    {
        $default = $forCreate
            ? 'Default "ai": if you give no image, an AI picture is generated automatically.'
            : 'Default "keep": the current image is unchanged unless you pass an image field.';

        return [
            'image_source' => $schema->string()->enum(['ai', 'url', 'upload', 'none', 'keep'])
                ->description("Where the image comes from: ai (generate), url (download image_url), upload (image_upload_id / image_base64 / local_path), none (no image / remove it), keep. {$default}"),
            'image_prompt' => $schema->string()->description('Optional description of the picture to generate. If omitted it is built from the name and description.'),
            'image_url' => $schema->string()->description('Public https link straight to a JPEG/PNG/WebP file (max 5 MB). It is downloaded and re-hosted on the site.'),
            'image_upload_id' => $schema->string()->description('Id returned by POST /upload (valid 15 minutes).'),
            'image_base64' => $schema->string()->description('Small image as base64 or a data: URI.'),
            'image_filename' => $schema->string()->description('Optional original filename for image_base64.'),
            'local_path' => $schema->string()->description('Path to an image on this machine — only when the server runs locally over stdio.'),
        ];
    }
}
