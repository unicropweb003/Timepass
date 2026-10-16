<?php

namespace App\Mcp\Tools\Categories;

use App\Mcp\Support\Finder;
use App\Mcp\Support\Present;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('get_category')]
#[Description('Get one category with all its fields and product count. Identify it with "category" = id, slug or exact name.')]
#[IsReadOnly]
class GetCategory extends Tool
{
    public function handle(Request $request): Response
    {
        $data = $request->validate(['category' => 'required']);

        $category = Finder::category($data['category']);
        if (! $category) {
            return Response::error("No category matches \"{$data['category']}\". Use list_categories.");
        }

        return Present::reply("Category #{$category->id} \"{$category->name}\".", Present::category($category));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'category' => $schema->string()->description('Category id, slug or exact name.')->required(),
        ];
    }
}
