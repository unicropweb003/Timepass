<?php

namespace App\Mcp\Tools\Categories;

use App\Models\Category;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('list_categories')]
#[Description('List all storefront categories with their id, slug, name, icon, sub-types and status. Use this first to get a category id/slug before updating or deleting.')]
#[IsReadOnly]
class ListCategories extends Tool
{
    public function handle(Request $request): Response
    {
        $categories = Category::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (Category $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'description' => $c->description,
                'icon' => $c->icon,
                'types' => $c->types ?: [],
                'is_active' => (bool) $c->is_active,
                'sort_order' => (int) $c->sort_order,
            ])->all();

        return Response::json([
            'count' => count($categories),
            'categories' => $categories,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
