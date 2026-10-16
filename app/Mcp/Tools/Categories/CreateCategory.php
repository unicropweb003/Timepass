<?php

namespace App\Mcp\Tools\Categories;

use App\Mcp\Support\Finder;
use App\Mcp\Support\ImageInput;
use App\Mcp\Support\ImagePipeline;
use App\Mcp\Support\Present;
use App\Models\Category;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('create_category')]
#[Description('Create a product category. Name is required; slug is generated if omitted. Optionally set a parent category (id/slug/name). If no image is given, an AI category image is generated and uploaded automatically (pass image_source "none" to skip).')]
class CreateCategory extends Tool
{
    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'slug' => 'nullable|string|max:150',
            'description' => 'nullable|string|max:5000',
            'parent' => 'nullable|string|max:150',
            'icon' => 'nullable|string|max:2000',
            'types' => 'nullable|array',
            'status' => 'nullable|in:active,inactive',
            'sort_order' => 'nullable|integer|min:0',
        ] + ImageInput::rules());

        if (Finder::category($data['name']) && empty($data['slug'])) {
            $existing = Finder::category($data['name']);

            return Response::error("A category named \"{$existing->name}\" already exists (id {$existing->id}). Use update_category to change it.");
        }

        $parent = null;
        if (! empty($data['parent'])) {
            $parent = Finder::category($data['parent']);
            if (! $parent) {
                return Response::error("Parent category \"{$data['parent']}\" not found. Create it first or use list_categories.");
            }
        }

        $category = Category::create([
            'name' => $data['name'],
            'slug' => Finder::uniqueSlug(Category::class, $data['slug'] ?? $data['name']),
            'description' => $data['description'] ?? null,
            'parent_id' => $parent?->id,
            'icon' => $data['icon'] ?? null,
            'types' => $data['types'] ?? [],
            'is_active' => ($data['status'] ?? 'active') === 'active',
            'sort_order' => $data['sort_order'] ?? ((int) Category::max('sort_order') + 1),
        ]);

        $image = ImagePipeline::resolve($data, 'ai', trim($category->name.'. '.($category->description ?? '')), 'category');
        if ($image['action'] === 'set') {
            $category->update(['image' => $image['path']]);
        }

        $summary = "Created category #{$category->id} \"{$category->name}\" (slug {$category->slug})"
            .($parent ? " under \"{$parent->name}\"" : '').'.'
            .($image['note'] ? ' '.$image['note'] : '');

        return Present::reply($summary, Present::category($category->fresh()));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Category name, e.g. "Men\'s Wear".')->required(),
            'slug' => $schema->string()->description('URL slug; generated from the name if omitted.'),
            'description' => $schema->string()->description('Short description.'),
            'parent' => $schema->string()->description('Optional parent category (id, slug or name).'),
            'icon' => $schema->string()->description('Optional SVG icon path data used in menus.'),
            'types' => $schema->array()->description('Optional sub-types as an object map {"type-slug": "Type Name"}.'),
            'status' => $schema->string()->enum(['active', 'inactive'])->description('active (default) = visible on the site.'),
            'sort_order' => $schema->integer()->description('Lower numbers appear first.'),
        ] + ImageInput::schema($schema, true);
    }
}
