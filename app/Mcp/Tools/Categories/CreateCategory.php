<?php

namespace App\Mcp\Tools\Categories;

use App\Models\Category;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('create_category')]
#[Description('Create a new storefront category. Name is required; the slug is generated from the name if not supplied.')]
class CreateCategory extends Tool
{
    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'slug' => 'nullable|string|max:150',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:150',
            'types' => 'nullable|array',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $base = Str::slug($data['slug'] ?? $data['name']);
        $slug = $base !== '' ? $base : 'category';
        $i = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }

        $category = Category::create([
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'icon' => $data['icon'] ?? null,
            'types' => $data['types'] ?? [],
            'is_active' => $data['is_active'] ?? true,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return Response::text("Created category #{$category->id} \"{$category->name}\" (slug: {$category->slug}).");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Category name.')->required(),
            'slug' => $schema->string()->description('URL slug. Auto-generated from the name if omitted.'),
            'description' => $schema->string()->description('Short category description.'),
            'icon' => $schema->string()->description('Icon name or image path for the category.'),
            'types' => $schema->array()->description('Optional sub-types, each an object like {"slug":"...","name":"..."}.'),
            'is_active' => $schema->boolean()->description('Whether the category is visible (default true).'),
            'sort_order' => $schema->integer()->description('Lower numbers appear first (default 0).'),
        ];
    }
}
