<?php

namespace App\Mcp\Tools\Products;

use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[Name('delete_product')]
#[Description('Permanently delete a product by its id. This cannot be undone. Use list_products first to confirm the correct id.')]
#[IsDestructive]
class DeleteProduct extends Tool
{
    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'id' => 'required|integer',
        ]);

        $product = Product::find($data['id']);
        if (! $product) {
            return Response::error("No product found with id {$data['id']}.");
        }

        $name = $product->name;
        $product->delete();

        return Response::text("Deleted product #{$data['id']} \"{$name}\".");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The id of the product to delete (from list_products).')->required(),
        ];
    }
}
