<?php

namespace App\Mcp\Tools\Categories;

use App\Mcp\Support\Finder;
use App\Mcp\Support\ImagePipeline;
use App\Models\Category;
use App\Models\Product;
use App\Support\ShopData;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[Name('delete_category')]
#[Description('Permanently delete a category and its stored image. Identify it with "category" = id, slug or exact name. You MUST pass confirm=true. If the category still has products, pass move_products_to (another category) or the delete is refused — products are never deleted by this tool.')]
#[IsDestructive]
class DeleteCategory extends Tool
{
    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'category' => 'required',
            'confirm' => 'nullable',
            'move_products_to' => 'nullable|string|max:150',
        ]);

        $category = Finder::category($data['category']);
        if (! $category) {
            return Response::error("No category matches \"{$data['category']}\". Use list_categories.");
        }

        $productCount = Product::where('category', $category->slug)->count();

        if (! filter_var($data['confirm'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return Response::error("Not deleted. Category #{$category->id} \"{$category->name}\" has {$productCount} product(s). Call delete_category again with confirm=true"
                .($productCount ? ' and move_products_to (another category id/slug/name).' : '.'));
        }

        $target = null;
        if ($productCount > 0) {
            if (empty($data['move_products_to'])) {
                return Response::error("Not deleted: \"{$category->name}\" still has {$productCount} product(s). Pass move_products_to with another category so they stay visible on the site.");
            }
            $target = Finder::category($data['move_products_to']);
            if (! $target || $target->id === $category->id) {
                return Response::error("move_products_to \"{$data['move_products_to']}\" is not a different existing category.");
            }
            Product::where('category', $category->slug)->update(['category' => $target->slug]);
        }

        // Sub-categories become top-level instead of pointing at a missing parent.
        $children = Category::where('parent_id', $category->id)->update(['parent_id' => null]);

        $label = "#{$category->id} \"{$category->name}\"";
        ImagePipeline::deleteStored($category->image);
        $category->delete();
        ShopData::flush(); // the bulk product/child updates above skip model events

        return Response::text("Deleted category {$label} and its stored image."
            .($target ? " Moved {$productCount} product(s) to \"{$target->name}\"." : '')
            .($children ? " {$children} sub-categor".($children === 1 ? 'y is' : 'ies are').' now top-level.' : ''));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'category' => $schema->string()->description('Which category: id, slug or exact name.')->required(),
            'confirm' => $schema->boolean()->description('Must be true to actually delete.')->required(),
            'move_products_to' => $schema->string()->description('Required if the category has products: category (id/slug/name) to move them to.'),
        ];
    }
}
