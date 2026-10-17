<?php

namespace App\Mcp\Support;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Laravel\Mcp\Response;

/** Consistent record shapes and replies for MCP tools. */
class Present
{
    /** Short readable summary followed by the saved record as JSON. */
    public static function reply(string $summary, array $record): Response
    {
        return Response::text($summary."\n\n".json_encode($record, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    public static function imageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return preg_match('#^https?://#i', $path) ? $path : url(ltrim($path, '/'));
    }

    public static function product(Product $p, ?Category $category = null): array
    {
        $category ??= Category::where('slug', $p->category)->first();
        $onSale = $p->mrp !== null && (int) $p->mrp > (int) $p->price;

        return [
            'id' => $p->id,
            'name' => $p->name,
            'slug' => $p->slug,
            'description' => $p->description,
            'details' => $p->details,
            // Site stores selling price in "price" and the struck-through original in "mrp".
            'price' => $onSale ? (int) $p->mrp : (int) $p->price,
            'sale_price' => $onSale ? (int) $p->price : null,
            'stock' => $p->stock,
            'category_id' => $category?->id,
            'category_name' => $category?->name,
            'category_slug' => $p->category,
            'type' => $p->type,
            'form' => $p->form,
            'tags' => $p->tags ?: [],
            'status' => $p->is_active ? 'active' : 'inactive',
            'image_url' => static::imageUrl($p->image),
            'video_url' => $p->video,
            'page_url' => route('product.show', ['product' => $p->slug]),
            'created_at' => $p->created_at?->toIso8601String(),
            'updated_at' => $p->updated_at?->toIso8601String(),
        ];
    }

    /** $productCount: pass it when listing many categories (one grouped query instead of one per category). */
    public static function category(Category $c, ?int $productCount = null): array
    {
        return [
            'id' => $c->id,
            'name' => $c->name,
            'slug' => $c->slug,
            'description' => $c->description,
            'parent_id' => $c->parent_id,
            'parent_name' => $c->parent_id ? $c->parent?->name : null,
            'image_url' => static::imageUrl($c->image),
            'icon' => $c->icon,
            'types' => $c->types ?: [],
            'status' => $c->is_active ? 'active' : 'inactive',
            'sort_order' => (int) $c->sort_order,
            'product_count' => $productCount ?? Product::where('category', $c->slug)->count(),
            'page_url' => route('products', ['category' => $c->slug]),
            'created_at' => $c->created_at?->toIso8601String(),
            'updated_at' => $c->updated_at?->toIso8601String(),
        ];
    }

    /** Pagination block for list tools. */
    public static function page(LengthAwarePaginator $p): array
    {
        return [
            'page' => $p->currentPage(),
            'per_page' => $p->perPage(),
            'total' => $p->total(),
            'last_page' => $p->lastPage(),
            'has_more' => $p->hasMorePages(),
        ];
    }
}
