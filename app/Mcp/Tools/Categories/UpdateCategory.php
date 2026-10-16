<?php

namespace App\Mcp\Tools\Categories;

use App\Mcp\Support\Finder;
use App\Mcp\Support\ImageInput;
use App\Mcp\Support\ImagePipeline;
use App\Mcp\Support\Present;
use App\Models\Category;
use App\Models\Product;
use App\Support\ShopData;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('update_category')]
#[Description('Update a category. Identify it with "category" = id, slug or exact name. Only passed fields change. Changing the slug also moves its products. Pass image_source "ai", image_url, image_upload_id or "none" to change the image (the old stored image is deleted). Pass parent "none" to make it top-level.')]
class UpdateCategory extends Tool
{
    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'category' => 'required',
            'name' => 'sometimes|string|max:150',
            'slug' => 'sometimes|string|max:150',
            'description' => 'nullable|string|max:5000',
            'parent' => 'nullable|string|max:150',
            'icon' => 'nullable|string|max:2000',
            'types' => 'sometimes|array',
            'status' => 'sometimes|in:active,inactive',
            'sort_order' => 'sometimes|integer|min:0',
        ] + ImageInput::rules());

        $category = Finder::category($data['category']);
        if (! $category) {
            return Response::error("No category matches \"{$data['category']}\". Use list_categories.");
        }

        $changes = [];
        foreach (['name', 'description', 'icon', 'types', 'sort_order'] as $field) {
            if (array_key_exists($field, $data)) {
                $changes[$field] = $data[$field];
            }
        }
        if (array_key_exists('status', $data)) {
            $changes['is_active'] = $data['status'] === 'active';
        }

        if (array_key_exists('parent', $data)) {
            if ($data['parent'] === null || in_array(strtolower((string) $data['parent']), ['none', 'null', ''], true)) {
                $changes['parent_id'] = null;
            } else {
                $parent = Finder::category($data['parent']);
                if (! $parent) {
                    return Response::error("Parent category \"{$data['parent']}\" not found.");
                }
                if ($parent->id === $category->id) {
                    return Response::error('A category cannot be its own parent.');
                }
                $changes['parent_id'] = $parent->id;
            }
        }

        $notes = [];
        $oldSlug = $category->slug;
        if (array_key_exists('slug', $data)) {
            $changes['slug'] = Finder::uniqueSlug(Category::class, $data['slug'], $category->id);
        }

        $category->fill($changes)->save();

        if ($category->slug !== $oldSlug) {
            $moved = Product::where('category', $oldSlug)->update(['category' => $category->slug]);
            ShopData::flush(); // bulk update skips model events
            $notes[] = "Slug changed to {$category->slug}; {$moved} product(s) moved with it.";
        }

        $image = ImagePipeline::resolve($data, 'keep', trim($category->name.'. '.($category->description ?? '')), 'category');
        if (in_array($image['action'], ['set', 'remove'], true)) {
            $old = $category->image;
            $category->update(['image' => $image['path']]);
            ImagePipeline::deleteStored($old);
        }
        if ($image['note']) {
            $notes[] = $image['note'];
        }

        return Present::reply(
            "Updated category #{$category->id} \"{$category->name}\".".($notes ? ' '.implode(' ', $notes) : ''),
            Present::category($category->fresh())
        );
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'category' => $schema->string()->description('Which category: id, slug or exact name.')->required(),
            'name' => $schema->string()->description('New name.'),
            'slug' => $schema->string()->description('New slug (products follow automatically).'),
            'description' => $schema->string()->description('New description.'),
            'parent' => $schema->string()->description('New parent (id/slug/name), or "none" for top-level.'),
            'icon' => $schema->string()->description('SVG icon path data.'),
            'types' => $schema->array()->description('Replaces the sub-types map {"type-slug": "Type Name"}.'),
            'status' => $schema->string()->enum(['active', 'inactive'])->description('Show or hide.'),
            'sort_order' => $schema->integer()->description('Lower numbers appear first.'),
        ] + ImageInput::schema($schema, false);
    }
}
