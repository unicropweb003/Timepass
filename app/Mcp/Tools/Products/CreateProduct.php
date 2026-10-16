<?php

namespace App\Mcp\Tools\Products;

use App\Mcp\Support\CategoryInput;
use App\Mcp\Support\Finder;
use App\Mcp\Support\ImageInput;
use App\Mcp\Support\ImagePipeline;
use App\Mcp\Support\Present;
use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('create_product')]
#[Description('Create a product on the website in ONE call. Give name, price and category_name — if the category does not exist it is created automatically. If no image is given, an AI product photo is generated and uploaded automatically (or pass image_url / image_upload_id / image_source="none"). Prices are whole rupees (INR).')]
class CreateProduct extends Tool
{
    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'price' => 'required|integer|min:0',
            'sale_price' => 'nullable|integer|min:0|lt:price',
            'stock' => 'nullable|integer|min:0',
            'category_name' => 'nullable|string|max:150',
            'category_id' => 'nullable|integer',
            'description' => 'nullable|string|max:5000',
            'details' => 'nullable|string|max:20000',
            'slug' => 'nullable|string|max:200',
            'type' => 'nullable|string|max:100',
            'form' => 'nullable|string|max:100',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'status' => 'nullable|in:active,inactive',
            'sort_order' => 'nullable|integer|min:0',
        ] + ImageInput::rules(), [
            'sale_price.lt' => 'sale_price must be lower than price (price is the regular price, sale_price the discounted one).',
        ]);

        [$category, $created, $error] = CategoryInput::resolve($data, true);
        if ($error) {
            return Response::error($error);
        }

        // The site shows "price" as the selling price and "mrp" struck through.
        $onSale = isset($data['sale_price']);

        // Save the record first so it exists even if the image step fails.
        $product = Product::create([
            'name' => $data['name'],
            'slug' => Finder::uniqueSlug(Product::class, $data['slug'] ?? $data['name']),
            'category' => $category->slug,
            'type' => $data['type'] ?? null,
            'form' => $data['form'] ?? null,
            'price' => $onSale ? $data['sale_price'] : $data['price'],
            'mrp' => $onSale ? $data['price'] : null,
            'stock' => $data['stock'] ?? null,
            'description' => $data['description'] ?? null,
            'details' => $data['details'] ?? null,
            'tags' => $data['tags'] ?? [],
            'is_active' => ($data['status'] ?? 'active') === 'active',
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        $image = ImagePipeline::resolve($data, 'ai', trim($product->name.'. '.($product->description ?? '')));
        if ($image['action'] === 'set') {
            $product->update(['image' => $image['path']]);
        }

        $summary = "Created product #{$product->id} \"{$product->name}\" in category \"{$category->name}\""
            .($created ? ' (new category created)' : '')
            .'. Price ₹'.($onSale ? "{$data['sale_price']} (was ₹{$data['price']})" : $data['price']).'.'
            .($image['note'] ? ' '.$image['note'] : '')
            .($image['action'] === 'failed' ? ' The product was saved without an image — retry with update_product (image_source "ai" or image_url).' : '');

        return Present::reply($summary, Present::product($product->fresh(), $category));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Product name, e.g. "Blue Cotton Shirt".')->required(),
            'price' => $schema->integer()->description('Regular price in whole rupees.')->required(),
            'sale_price' => $schema->integer()->description('Optional discounted price (must be lower than price). The site shows it with the regular price struck through.'),
            'stock' => $schema->integer()->description('Units in stock.'),
            'category_name' => $schema->string()->description('Category name (or slug). Created automatically if it does not exist.'),
            'category_id' => $schema->integer()->description('Existing category id (alternative to category_name).'),
            'description' => $schema->string()->description('Short description shown on the product card/page.'),
            'details' => $schema->string()->description('Longer details / specifications.'),
            'slug' => $schema->string()->description('URL slug; generated from the name if omitted (always kept unique).'),
            'type' => $schema->string()->description('Optional sub-type slug within the category.'),
            'form' => $schema->string()->description('Optional form slug.'),
            'tags' => $schema->array()->items($schema->string())->description('Tags. "best-seller" and "recommended" also feature the product on the home page.'),
            'status' => $schema->string()->enum(['active', 'inactive'])->description('active = visible on the site (default), inactive = hidden.'),
            'sort_order' => $schema->integer()->description('Lower numbers appear first.'),
        ] + ImageInput::schema($schema, true);
    }
}
