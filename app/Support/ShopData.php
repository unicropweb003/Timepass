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

    // Data version shared by all instances (see get()/flush()).
    private const VERSION_KEY = 'shop-data:version';

    // This instance's copy of that version, re-checked every VERSION_CHECK seconds.
    private const VERSION_SEEN = 'shop-data:version-seen';

    private const VERSION_CHECK = 15; // seconds

    /** Cached data, or null if the database is unavailable (callers keep config defaults). */
    public static function get(): ?array
    {
        try {
            $store = static::store();
            // Which version of the data is current. Re-read from the shared store at
            // most every VERSION_CHECK seconds, so with several instances behind a
            // load balancer an edit made on one shows on all within that time.
            $version = $store->remember(self::VERSION_SEEN, self::VERSION_CHECK, fn () => static::sharedVersion());

            return $store->remember(self::KEY.':'.$version, self::TTL, fn () => static::build());
        } catch (\Throwable $e) {
            return null; // DB unreachable or tables not migrated yet.
        }
    }

    public static function flush(): void
    {
        try {
            $store = static::store();
            $old = $store->get(self::VERSION_SEEN);
            if ($old !== null) {
                $store->forget(self::KEY.':'.$old);
            }

            // Publish a new version for the other instances, and switch to it here
            // right away so this instance shows the edit on its very next request.
            $version = (string) hrtime(true);
            try {
                static::shared()->forever(self::VERSION_KEY, $version);
            } catch (\Throwable $e) {
                // No shared store (e.g. cache table missing): this instance still refreshes.
            }
            $store->put(self::VERSION_SEEN, $version, self::VERSION_CHECK);
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

    /** A store every instance sees: the configured one if shared (Redis…), else the database cache table. */
    private static function shared(): Repository
    {
        $default = (string) config('cache.default');

        return Cache::store(in_array($default, ['file', 'array'], true) ? 'database' : $default);
    }

    private static function sharedVersion(): string
    {
        try {
            return (string) static::shared()->get(self::VERSION_KEY, '0');
        } catch (\Throwable $e) {
            return '0';
        }
    }
}
