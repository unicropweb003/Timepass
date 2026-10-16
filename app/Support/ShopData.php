<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\Setting;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

/**
 * Storefront data (categories, products, settings, reviews) loaded from the
 * database once and cached, instead of querying on every page view.
 *
 * The cache is cleared whenever a Product, Category, Setting or Review is
 * saved or deleted (see each model's booted() hook), so admin-panel and MCP
 * edits show on the very next request. The TTL also picks up changes made
 * outside the app (e.g. SQL in the Neon console) within a few minutes.
 */
class ShopData
{
    private const KEY = 'shop-data:v1';

    private const TTL = 300; // seconds

    /** Cached data, or null if the database is unavailable (callers keep config defaults). */
    public static function get(): ?array
    {
        try {
            return static::store()->remember(self::KEY, self::TTL, fn () => static::build());
        } catch (\Throwable $e) {
            return null; // DB unreachable or tables not migrated yet.
        }
    }

    public static function flush(): void
    {
        try {
            static::store()->forget(self::KEY);
        } catch (\Throwable $e) {
            // Cache unavailable — nothing to clear.
        }
    }

    /** Active reviews for the home page ([] = none in the database). */
    public static function reviews(): array
    {
        return static::get()['reviews'] ?? [];
    }

    private static function build(): array
    {
        // One query per table; null means "table empty → keep the config/sample defaults".
        $categories = Category::query()->orderBy('sort_order')->orderBy('name')->get();
        $products = Product::query()->orderBy('sort_order')->orderBy('id')->get();

        return [
            'categories' => $categories->isEmpty() ? null
                : $categories->where('is_active', true)->values()->map->toConfigArray()->all(),
            'products' => $products->isEmpty() ? null
                : $products->where('is_active', true)->values()->map->toConfigArray()->all(),
            'settings' => Setting::query()->get()
                ->mapWithKeys(fn (Setting $s) => [$s->key => json_decode((string) $s->value, true)])
                ->all(),
            'reviews' => Review::query()->where('is_active', true)->orderBy('sort_order')->orderBy('id')
                ->get(['name', 'tagline', 'rating', 'text'])
                ->map(fn (Review $r) => [
                    'name' => $r->name,
                    'tagline' => $r->tagline,
                    'rating' => (int) $r->rating,
                    'text' => $r->text,
                ])->all(),
        ];
    }

    private static function store(): Repository
    {
        $default = (string) config('cache.default');

        // A database cache would cost a DB round trip per request — exactly what
        // this class avoids — so use the container's local file cache instead.
        return Cache::store(in_array($default, ['database', 'dynamodb'], true) ? 'file' : $default);
    }
}
