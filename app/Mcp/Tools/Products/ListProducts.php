<?php

namespace App\Mcp\Tools\Products;

use App\Mcp\Support\Finder;
use App\Mcp\Support\Present;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('list_products')]
#[Description('List products with search, filters and pagination. Filter by category (id/slug/name), status, price range or stock. Use this to find a product before updating or deleting it.')]
#[IsReadOnly]
class ListProducts extends Tool
{
    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'search' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:150',
            'status' => 'nullable|in:active,inactive,all',
            'min_price' => 'nullable|integer|min:0',
            'max_price' => 'nullable|integer|min:0',
            'in_stock' => 'nullable|boolean',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $category = null;
        if (! empty($data['category'])) {
            $category = Finder::category($data['category']);
            if (! $category) {
                return Response::error("No category matches \"{$data['category']}\". Use list_categories.");
            }
        }

        $page = Product::query()
            ->when($data['search'] ?? null, fn ($q, $s) => $q->where(fn ($w) => $w
                ->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($s).'%'])
                ->orWhere('slug', 'like', '%'.$s.'%')
                ->orWhereRaw('LOWER(description) LIKE ?', ['%'.mb_strtolower($s).'%'])))
            ->when($category, fn ($q) => $q->where('category', $category->slug))
            ->when(($data['status'] ?? 'all') !== 'all', fn ($q) => $q->where('is_active', $data['status'] === 'active'))
            ->when(isset($data['min_price']), fn ($q) => $q->where('price', '>=', $data['min_price']))
            ->when(isset($data['max_price']), fn ($q) => $q->where('price', '<=', $data['max_price']))
            ->when(isset($data['in_stock']), fn ($q) => $data['in_stock']
                ? $q->where('stock', '>', 0)
                : $q->where(fn ($w) => $w->whereNull('stock')->orWhere('stock', 0)))
            ->orderBy('sort_order')->orderBy('id')
            ->paginate($data['per_page'] ?? 25, ['*'], 'page', $data['page'] ?? 1);

        $categories = Category::whereIn('slug', $page->pluck('category')->unique())->get()->keyBy('slug');
        $items = $page->getCollection()->map(fn (Product $p) => Present::product($p, $categories[$p->category] ?? null))->all();

        $summary = "Found {$page->total()} product(s); showing page {$page->currentPage()} of {$page->lastPage()}.";

        return Present::reply($summary, ['pagination' => Present::page($page), 'products' => $items]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Text to match in name, slug or description.'),
            'category' => $schema->string()->description('Category id, slug or name.'),
            'status' => $schema->string()->enum(['active', 'inactive', 'all'])->description('Default all.'),
            'min_price' => $schema->integer()->description('Minimum selling price (rupees).'),
            'max_price' => $schema->integer()->description('Maximum selling price (rupees).'),
            'in_stock' => $schema->boolean()->description('true = stock > 0, false = out of stock / not tracked.'),
            'page' => $schema->integer()->description('Page number (default 1).'),
            'per_page' => $schema->integer()->description('Items per page (default 25, max 100).'),
        ];
    }
}
