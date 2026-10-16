<?php

namespace App\Mcp\Tools\Products;

use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('list_products')]
#[Description('List storefront products. Optionally filter by a search term (matches name or slug) or a category slug. Use this first to get a product id/slug before updating or deleting.')]
#[IsReadOnly]
class ListProducts extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'limit' => 'nullable|integer|min:1|max:200',
        ]);

        $products = Product::query()
            ->when($validated['search'] ?? null, fn ($q, $s) => $q->where(fn ($w) => $w->where('name', 'like', "%{$s}%")->orWhere('slug', 'like', "%{$s}%")))
            ->when($validated['category'] ?? null, fn ($q, $c) => $q->where('category', $c))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit($validated['limit'] ?? 100)
            ->get()
            ->map(fn (Product $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'category' => $p->category,
                'type' => $p->type,
                'form' => $p->form,
                'price' => (int) $p->price,
                'mrp' => $p->mrp !== null ? (int) $p->mrp : null,
                'is_active' => (bool) $p->is_active,
                'sort_order' => (int) $p->sort_order,
                'tags' => $p->tags ?: [],
                'image' => $p->image,
            ])->all();

        return Response::structured([
            'count' => count($products),
            'products' => $products,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Filter by product name or slug (partial match).'),
            'category' => $schema->string()->description('Filter by category slug.'),
            'limit' => $schema->integer()->description('Maximum number of products to return (default 100, max 200).'),
        ];
    }
}
