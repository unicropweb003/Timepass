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

#[Name('generate_product_image')]
#[Description('Create an AI product photo for a product and upload it to the website (stored on the site, shown immediately). The picture is based on the product name and category, or on a custom "prompt". Identify the product by id (use list_products first). The free image service allows about one image every 45 seconds; if it is busy, wait a minute and call again.')]
class GenerateProductImage extends Tool
{
    private const ALLOWED = ['image/jpeg', 'image/png', 'image/webp'];

    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'id' => 'required|integer',
            'prompt' => 'nullable|string|max:300',
        ]);

        $product = Product::find($data['id']);
        if (! $product) {
            return Response::error("No product found with id {$data['id']}.");
        }

        $subject = $data['prompt'] ?? trim($product->name.' '.str_replace('-', ' ', (string) $product->category));

        // Ask for a clean e-commerce packshot of the product itself — never people.
        $prompt = "Professional e-commerce product packshot of {$subject}. "
            .'Only the product container (bottle, jar, tube or box) with a simple label, '
            .'centered, isolated on a plain white background, soft studio lighting, sharp focus. '
            .'No people, no face, no hands, no models, no text overlay.';

        $url = 'https://image.pollinations.ai/prompt/'.rawurlencode($prompt)
            .'?width=800&height=800&nologo=true&model=flux&seed='.random_int(1, 999999);

        $http = Http::timeout(120)->withHeaders(['User-Agent' => 'TimePass-MCP/1.0']);
        if ($token = config('services.pollinations.token')) {
            $http = $http->withToken($token);
        }

        try {
            $res = $http->get($url);
        } catch (\Throwable $e) {
            return Response::error('The image service did not respond in time. Please try again in a minute.');
        }

        if ($res->status() === 402 || $res->status() === 429) {
            return Response::error('The free image service is busy (limit: about 1 image every 45 seconds). Wait a minute, then call generate_product_image again.');
        }

        if (! $res->successful()) {
            return Response::error("The image service returned an error (HTTP {$res->status()}). Please try again later.");
        }

        $bytes = $res->body();
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($bytes) ?: '';
        if (! in_array($mime, self::ALLOWED, true)) {
            return Response::error('The image service did not return an image. Please try again in a minute.');
        }

        $media = Media::create([
            'mime' => $mime,
            'size' => strlen($bytes),
            'data' => base64_encode($bytes),
        ]);

        $product->image = $media->path();
        $product->save();

        return Response::text(
            "Generated an AI product photo for #{$product->id} \"{$product->name}\" and uploaded it to the website. "
            .'Image: '.url($media->path()).' — if you do not like it, call generate_product_image again for a new one, '
            .'or set_product_image with "remove": true to clear it.'
        );
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The product id (from list_products).')->required(),
            'prompt' => $schema->string()->description('Optional: describe the product to draw, e.g. "amber glass serum bottle with dropper". Defaults to the product name and category.'),
        ];
    }
}
