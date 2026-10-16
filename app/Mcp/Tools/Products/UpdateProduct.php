<?php

namespace App\Mcp\Tools\Products;

use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('update_product')]
#[Description('Update an existing product. Identify it by id (use list_products first). Only the fields you provide are changed.')]
class UpdateProduct extends Tool
{
    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'id' => 'required|integer',
            'name' => 'sometimes|string|max:200',
            'category' => 'sometimes|string|max:100',
            'price' => 'sometimes|integer|min:0',
            'slug' => 'sometimes|string|max:200',
            'type' => 'nullable|string|max:100',
            'form' => 'nullable|string|max:100',
            'mrp' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'details' => 'nullable|string',
            'image' => 'nullable|string|max:500',
            'tags' => 'sometimes|array',
            'tags.*' => 'string|max:50',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer|min:0',
        ]);

        $product = Product::find($data['id']);
        if (! $product) {
            return Response::error("No product found with id {$data['id']}.");
        }

        if (array_key_exists('slug', $data)) {
            $base = Str::slug($data['slug']);
            $slug = $base !== '' ? $base : $product->slug;
            $i = 1;
            while (Product::where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                $slug = $base.'-'.(++$i);
            }
            $data['slug'] = $slug;
        }

        unset($data['id']);
        $product->fill($data)->save();

        return Response::text("Updated product #{$product->id} \"{$product->name}\".");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The product id to update (from list_products).')->required(),
            'name' => $schema->string()->description('New product name.'),
            'category' => $schema->string()->description('New category slug.'),
            'price' => $schema->integer()->description('New selling price in whole rupees.'),
            'slug' => $schema->string()->description('New URL slug (kept unique automatically).'),
            'type' => $schema->string()->description('Sub-type slug within the category.'),
            'form' => $schema->string()->description('Form slug.'),
            'mrp' => $schema->integer()->description('Original price in rupees (struck-through).'),
            'description' => $schema->string()->description('Short description.'),
            'details' => $schema->string()->description('Longer details / specifications.'),
            'image' => $schema->string()->description('Image path or URL.'),
            'tags' => $schema->array()->items($schema->string())->description('Replaces the full list of tags.'),
            'is_active' => $schema->boolean()->description('Show or hide the product on the site.'),
            'sort_order' => $schema->integer()->description('Lower numbers appear first.'),
        ];
    }
}
