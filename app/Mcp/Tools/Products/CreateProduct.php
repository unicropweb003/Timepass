<?php

namespace App\Mcp\Tools\Products;

use App\Mcp\Support\ImagePath;
use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('create_product')]
#[Description('Create a new storefront product. Name, category (a category slug) and price (whole rupees) are required. The slug is generated from the name if not supplied.')]
class CreateProduct extends Tool
{
    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'category' => 'required|string|max:100',
            'price' => 'required|integer|min:0',
            'slug' => 'nullable|string|max:200',
            'type' => 'nullable|string|max:100',
            'form' => 'nullable|string|max:100',
            'mrp' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'details' => 'nullable|string',
            'image' => 'nullable|string|max:500',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($problem = ImagePath::problem($data['image'] ?? null)) {
            return Response::error($problem);
        }

        $base = Str::slug($data['slug'] ?? $data['name']);
        $slug = $base !== '' ? $base : 'product';
        $i = 1;
        while (Product::where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }

        $product = Product::create([
            'name' => $data['name'],
            'slug' => $slug,
            'category' => $data['category'],
            'type' => $data['type'] ?? null,
            'form' => $data['form'] ?? null,
            'price' => $data['price'],
            'mrp' => $data['mrp'] ?? null,
            'description' => $data['description'] ?? null,
            'details' => $data['details'] ?? null,
            'image' => $data['image'] ?? null,
            'tags' => $data['tags'] ?? [],
            'is_active' => $data['is_active'] ?? true,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return Response::text("Created product #{$product->id} \"{$product->name}\" (slug: {$product->slug}).");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Product name.')->required(),
            'category' => $schema->string()->description('Category slug this product belongs to.')->required(),
            'price' => $schema->integer()->description('Selling price in whole rupees (INR).')->required(),
            'slug' => $schema->string()->description('URL slug. Auto-generated from the name if omitted.'),
            'type' => $schema->string()->description('Optional sub-type slug within the category.'),
            'form' => $schema->string()->description('Optional form slug (e.g. a packaging/variant form).'),
            'mrp' => $schema->integer()->description('Original price in rupees, shown struck-through.'),
            'description' => $schema->string()->description('Short product description.'),
            'details' => $schema->string()->description('Longer product details / specifications.'),
            'image' => $schema->string()->description('Optional. A full public image URL. Never invent a file path; to upload a picture use set_product_image.'),
            'tags' => $schema->array()->items($schema->string())->description('Tags such as "best-seller" or "recommended".'),
            'is_active' => $schema->boolean()->description('Whether the product is visible on the site (default true).'),
            'sort_order' => $schema->integer()->description('Lower numbers appear first (default 0).'),
        ];
    }
}
