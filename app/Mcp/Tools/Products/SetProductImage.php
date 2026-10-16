<?php

namespace App\Mcp\Tools\Products;

use App\Models\Media;
use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Http;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('set_product_image')]
#[Description('Upload an image for a product and show it on the website. Give either "image_url" (a public link to a JPG/PNG/WebP/GIF image — the server downloads it) or "image_base64" (raw base64 or a data: URI). The image is stored permanently in the database. Pass "remove": true to clear the image instead. Identify the product by id (use list_products first).')]
class SetProductImage extends Tool
{
    private const MAX_BYTES = 5 * 1024 * 1024; // 5 MB

    private const ALLOWED = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'id' => 'required|integer',
            'image_url' => 'nullable|string|max:2000',
            'image_base64' => 'nullable|string',
            'remove' => 'nullable|boolean',
        ]);

        $product = Product::find($data['id']);
        if (! $product) {
            return Response::error("No product found with id {$data['id']}.");
        }

        if (! empty($data['remove'])) {
            $product->image = null;
            $product->save();

            return Response::text("Removed the image from product #{$product->id} \"{$product->name}\".");
        }

        if (empty($data['image_url']) && empty($data['image_base64'])) {
            return Response::error('Provide "image_url" or "image_base64" (or "remove": true).');
        }

        [$bytes, $error] = ! empty($data['image_url'])
            ? $this->download($data['image_url'])
            : $this->decode($data['image_base64']);

        if ($error) {
            return Response::error($error);
        }

        if (strlen($bytes) > self::MAX_BYTES) {
            return Response::error('Image is larger than 5 MB. Use a smaller image.');
        }

        // Detect the real type from the file contents (don't trust headers).
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($bytes) ?: '';
        if (! in_array($mime, self::ALLOWED, true)) {
            return Response::error("That file is not a supported image (detected: {$mime}). Use JPG, PNG, WebP or GIF.");
        }

        $media = Media::create([
            'mime' => $mime,
            'size' => strlen($bytes),
            'data' => base64_encode($bytes),
        ]);

        $product->image = $media->path();
        $product->save();

        $url = url($media->path());

        return Response::text("Uploaded image for product #{$product->id} \"{$product->name}\" ({$mime}, ".round(strlen($bytes) / 1024)." KB). It now shows on the website. Image: {$url}");
    }

    /** @return array{0: string, 1: ?string} */
    private function download(string $url): array
    {
        if (! preg_match('#^https?://#i', $url)) {
            return ['', 'image_url must start with http:// or https://.'];
        }

        try {
            $res = Http::timeout(30)->withHeaders(['User-Agent' => 'TimePass-MCP/1.0'])->get($url);
        } catch (\Throwable $e) {
            return ['', 'Could not download the image: '.$e->getMessage()];
        }

        if (! $res->successful()) {
            return ['', "Could not download the image (HTTP {$res->status()}). Make sure the link is public and points directly to an image file."];
        }

        return [$res->body(), null];
    }

    /** @return array{0: string, 1: ?string} */
    private function decode(string $value): array
    {
        // Accept "data:image/png;base64,...." or raw base64.
        if (str_starts_with($value, 'data:')) {
            $value = substr($value, strpos($value, ',') + 1);
        }

        $bytes = base64_decode(preg_replace('/\s+/', '', $value), true);

        return $bytes === false || $bytes === ''
            ? ['', 'image_base64 is not valid base64 data.']
            : [$bytes, null];
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The product id (from list_products).')->required(),
            'image_url' => $schema->string()->description('Public URL of the image file to upload (JPG, PNG, WebP or GIF).'),
            'image_base64' => $schema->string()->description('Image as base64 or a data: URI, if you have the file contents instead of a link.'),
            'remove' => $schema->boolean()->description('Set true to remove the product image.'),
        ];
    }
}
