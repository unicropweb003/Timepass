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

#[Name('update_product')]
#[Description('Update a product. Identify it with "product" = its id, slug or exact name. Only the fields you pass change. To change the picture pass image_source "ai" (new AI photo), image_url, image_upload_id, or "none" to remove it; the old stored image is deleted. A new category_name is created if it does not exist.')]
class UpdateProduct extends Tool
{
    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'product' => 'required',
            'name' => 'sometimes|string|max:200',
            'price' => 'sometimes|integer|min:0',
            'sale_price' => 'nullable|integer|min:0',
            'stock' => 'nullable|integer|min:0',
            'category_name' => 'nullable|string|max:150',
            'category_id' => 'nullable|integer',
            'description' => 'nullable|string|max:5000',
            'details' => 'nullable|string|max:20000',
            'slug' => 'sometimes|string|max:200',
            'type' => 'nullable|string|max:100',
            'form' => 'nullable|string|max:100',
            'tags' => 'sometimes|array',
            'tags.*' => 'string|max:50',
            'status' => 'sometimes|in:active,inactive',
            'sort_order' => 'sometimes|integer|min:0',
        ] + ImageInput::rules());

        $product = Finder::product($data['product']);
        if (! $product) {
            return Response::error("No product matches \"{$data['product']}\". Use list_products to find its id or slug.");
        }

        $notes = [];
        $changes = [];

        foreach (['name', 'stock', 'description', 'details', 'type', 'form', 'tags', 'sort_order'] as $field) {
            if (array_key_exists($field, $data)) {
                $changes[$field] = $data[$field];
            }
        }

        if (array_key_exists('slug', $data)) {
            $changes['slug'] = Finder::uniqueSlug(Product::class, $data['slug'], $product->id);
        }

        if (array_key_exists('status', $data)) {
            $changes['is_active'] = $data['status'] === 'active';
        }

        // Price handling: "price" = regular, "sale_price" = discounted (null clears the sale).
        if (array_key_exists('price', $data) || array_key_exists('sale_price', $data)) {
            $current = Present::product($product);
            $regular = $data['price'] ?? $current['price'];
            $sale = array_key_exists('sale_price', $data) ? $data['sale_price'] : $current['sale_price'];

            if ($sale !== null && $sale >= $regular) {
                return Response::error('sale_price must be lower than price. Pass sale_price null to remove the discount.');
            }

            $changes['price'] = $sale ?? $regular;
            $changes['mrp'] = $sale !== null ? $regular : null;
        }

        [$category, $created, $error] = CategoryInput::resolve($data, false);
        if ($error) {
            return Response::error($error);
        }
        if ($category) {
            $changes['category'] = $category->slug;
            if ($created) {
                $notes[] = "Created new category \"{$category->name}\".";
            }
        }

        $product->fill($changes)->save();

        $image = ImagePipeline::resolve($data, 'keep', trim(($changes['name'] ?? $product->name).'. '.($product->description ?? '')));
        if (in_array($image['action'], ['set', 'remove'], true)) {
            $old = $product->image;
            $product->update(['image' => $image['path']]);
            ImagePipeline::deleteStored($old);
        }
        if ($image['note']) {
            $notes[] = $image['note'];
        }

        $summary = "Updated product #{$product->id} \"{$product->name}\"."
            .($notes ? ' '.implode(' ', $notes) : '')
            .($image['action'] === 'failed' ? ' Other changes were saved; the previous image was kept.' : '');

        return Present::reply($summary, Present::product($product->fresh()));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'product' => $schema->string()->description('Which product: its id, slug or exact name.')->required(),
            'name' => $schema->string()->description('New name.'),
            'price' => $schema->integer()->description('New regular price (whole rupees).'),
            'sale_price' => $schema->integer()->description('New discounted price; null removes the discount.'),
            'stock' => $schema->integer()->description('Units in stock.'),
            'category_name' => $schema->string()->description('Move to this category (created if it does not exist).'),
            'category_id' => $schema->integer()->description('Move to this existing category id.'),
            'description' => $schema->string()->description('Short description.'),
            'details' => $schema->string()->description('Longer details.'),
            'slug' => $schema->string()->description('New URL slug (kept unique).'),
            'type' => $schema->string()->description('Sub-type slug.'),
            'form' => $schema->string()->description('Form slug.'),
            'tags' => $schema->array()->items($schema->string())->description('Replaces all tags.'),
            'status' => $schema->string()->enum(['active', 'inactive'])->description('Show or hide on the site.'),
            'sort_order' => $schema->integer()->description('Lower numbers appear first.'),
        ] + ImageInput::schema($schema, false);
    }
}
