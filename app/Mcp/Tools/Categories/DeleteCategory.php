<?php

namespace App\Mcp\Tools\Categories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[Name('delete_category')]
#[Description('Permanently delete a category by its id. This cannot be undone. Products in the category are not deleted but will no longer have a matching category. Use list_categories first.')]
#[IsDestructive]
class DeleteCategory extends Tool
{
    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'id' => 'required|integer',
        ]);

        $category = Category::find($data['id']);
        if (! $category) {
            return Response::error("No category found with id {$data['id']}.");
        }

        $productCount = Product::where('category', $category->slug)->count();
        $name = $category->name;
        $category->delete();

        $note = $productCount > 0
            ? " Note: {$productCount} product(s) still reference this category slug."
            : '';

        return Response::text("Deleted category #{$data['id']} \"{$name}\".".$note);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The id of the category to delete (from list_categories).')->required(),
        ];
    }
}
