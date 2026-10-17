<?php

namespace App\Mcp\Tools\Categories;

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

#[Name('list_categories')]
#[Description('List product categories with search, filters and pagination (includes product counts and parent category). Use this to get a category id/slug.')]
#[IsReadOnly]
class ListCategories extends Tool
{
    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'search' => 'nullable|string|max:100',
            'parent' => 'nullable|string|max:150',
            'top_level_only' => 'nullable|boolean',
            'status' => 'nullable|in:active,inactive,all',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $parent = null;
        if (! empty($data['parent'])) {
            $parent = Finder::category($data['parent']);
            if (! $parent) {
                return Response::error("No category matches \"{$data['parent']}\".");
            }
        }

        $page = Category::query()
            ->with('parent:id,name')
            ->when($data['search'] ?? null, fn ($q, $s) => $q->where(fn ($w) => $w
                ->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($s).'%'])
                ->orWhere('slug', 'like', '%'.$s.'%')))
            ->when($parent, fn ($q) => $q->where('parent_id', $parent->id))
            ->when(! empty($data['top_level_only']), fn ($q) => $q->whereNull('parent_id'))
            ->when(($data['status'] ?? 'all') !== 'all', fn ($q) => $q->where('is_active', $data['status'] === 'active'))
            ->orderBy('sort_order')->orderBy('name')
            ->paginate($data['per_page'] ?? 50, ['*'], 'page', $data['page'] ?? 1);

        // One grouped query for all product counts on this page (was one query per category).
        $counts = Product::whereIn('category', $page->getCollection()->pluck('slug'))
            ->selectRaw('category, COUNT(*) as c')->groupBy('category')->pluck('c', 'category');

        $items = $page->getCollection()->map(fn (Category $c) => Present::category($c, (int) ($counts[$c->slug] ?? 0)))->all();

        return Present::reply(
            "Found {$page->total()} categor".($page->total() === 1 ? 'y' : 'ies')."; showing page {$page->currentPage()} of {$page->lastPage()}.",
            ['pagination' => Present::page($page), 'categories' => $items]
        );
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Text to match in name or slug.'),
            'parent' => $schema->string()->description('Only sub-categories of this category (id/slug/name).'),
            'top_level_only' => $schema->boolean()->description('Only categories without a parent.'),
            'status' => $schema->string()->enum(['active', 'inactive', 'all'])->description('Default all.'),
            'page' => $schema->integer()->description('Page number (default 1).'),
            'per_page' => $schema->integer()->description('Items per page (default 50, max 100).'),
        ];
    }
}
