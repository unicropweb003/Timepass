<?php

namespace App\Mcp\Tools\Products;

use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('generate_product_image')]
#[Description('Generate an AI image for a product and set it as the product image shown on the website. By default the image is based on the product name and category; pass a custom "prompt" to control it. Identify the product by id (use list_products first). Omit "seed" to get a fresh random image each time.')]
class GenerateProductImage extends Tool
{
    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'id' => 'required|integer',
            'prompt' => 'nullable|string|max:300',
            'seed' => 'nullable|integer|min:0',
        ]);

        $product = Product::find($data['id']);
        if (! $product) {
            return Response::error("No product found with id {$data['id']}.");
        }

        // Build an image prompt from the product unless a custom one is given.
        $prompt = $data['prompt'] ?? trim(
            $product->name.', '
            .($product->category ? str_replace('-', ' ', (string) $product->category).' product, ' : '')
            .'professional studio product photography, clean white background, soft lighting, high detail, centered'
        );

        // Random related image each call unless a seed is provided (seed = stable image).
        $seed = $data['seed'] ?? random_int(1, 999999);

        // Pollinations.ai — free AI image generation, no API key required.
        $url = 'https://image.pollinations.ai/prompt/'.rawurlencode($prompt)
            .'?width=800&height=800&nologo=true&model=flux&seed='.$seed;

        $product->image = $url;
        $product->save();

        return Response::text(
            "Generated and set a new AI image for product #{$product->id} \"{$product->name}\". ".
            "It will show on the website (refresh to see it). Image URL: {$url}"
        );
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The product id to generate an image for (from list_products).')->required(),
            'prompt' => $schema->string()->description('Optional custom image description. If omitted, it is built from the product name and category.'),
            'seed' => $schema->integer()->description('Optional number for a reproducible image. Omit to get a new random image each time.'),
        ];
    }
}
