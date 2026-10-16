<?php

namespace App\Mcp\Tools\Products;

use App\Mcp\Support\Finder;
use App\Mcp\Support\Present;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('get_product')]
#[Description('Get one product with all its fields. Identify it with "product" = id, slug or exact name.')]
#[IsReadOnly]
class GetProduct extends Tool
{
    public function handle(Request $request): Response
    {
        $data = $request->validate(['product' => 'required']);

        $product = Finder::product($data['product']);
        if (! $product) {
            return Response::error("No product matches \"{$data['product']}\". Use list_products with a search term.");
        }

        return Present::reply("Product #{$product->id} \"{$product->name}\".", Present::product($product));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'product' => $schema->string()->description('Product id, slug or exact name.')->required(),
        ];
    }
}
