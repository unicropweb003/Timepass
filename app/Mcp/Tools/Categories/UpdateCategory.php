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

#[Name('update_category')]
#[Description('Update an existing category. Identify it by id (use list_categories first). Only the fields you provide are changed.')]
class UpdateCategory extends Tool
{
    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'id' => 'required|integer',
            'name' => 'sometimes|string|max:150',
            'slug' => 'sometimes|string|max:150',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:150',
            'types' => 'sometimes|array',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer|min:0',
        ]);

        $category = Category::find($data['id']);
        if (! $category) {
            return Response::error("No category found with id {$data['id']}.");
        }

        if (array_key_exists('slug', $data)) {
            $base = Str::slug($data['slug']);
            $slug = $base !== '' ? $base : $category->slug;
            $i = 1;
            while (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
                $slug = $base.'-'.(++$i);
            }
            $data['slug'] = $slug;
        }

        unset($data['id']);
        $category->fill($data)->save();

        return Response::text("Updated category #{$category->id} \"{$category->name}\".");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The category id to update (from list_categories).')->required(),
            'name' => $schema->string()->description('New category name.'),
            'slug' => $schema->string()->description('New URL slug (kept unique automatically).'),
            'description' => $schema->string()->description('New description.'),
            'icon' => $schema->string()->description('Icon name or image path.'),
            'types' => $schema->array()->description('Replaces the sub-types list; each an object like {"slug":"...","name":"..."}.'),
            'is_active' => $schema->boolean()->description('Show or hide the category.'),
            'sort_order' => $schema->integer()->description('Lower numbers appear first.'),
        ];
    }
}
