<?php

namespace App\Mcp\Tools\Products;

use App\Mcp\Support\Finder;
use App\Mcp\Support\ImagePipeline;
use App\Services\CloudinaryUploader;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[Name('delete_product')]
#[Description('Permanently delete a product and its stored image. Identify it with "product" = id, slug or exact name. You MUST pass confirm=true; ask the user first if they did not clearly ask to delete it.')]
#[IsDestructive]
class DeleteProduct extends Tool
{
    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'product' => 'required',
            'confirm' => 'nullable',
        ]);

        $product = Finder::product($data['product']);
        if (! $product) {
            return Response::error("No product matches \"{$data['product']}\". Use list_products to find it.");
        }

        if (! filter_var($data['confirm'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return Response::error("Not deleted. To permanently delete product #{$product->id} \"{$product->name}\", call delete_product again with confirm=true.");
        }

        $label = "#{$product->id} \"{$product->name}\"";
        ImagePipeline::deleteStored($product->image);
        app(CloudinaryUploader::class)->delete($product->video); // only removes Cloudinary uploads
        $product->delete();

        return Response::text("Deleted product {$label} and its stored image.");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'product' => $schema->string()->description('Which product: id, slug or exact name.')->required(),
            'confirm' => $schema->boolean()->description('Must be true to actually delete.')->required(),
        ];
    }
}
