<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

/**
 * Hydrates the config keys the front-end already reads
 * (shop.categories, shop.contact, shop.social and products)
 * from the database, so admin edits show on the live site.
 *
 * If the tables aren't migrated/seeded yet, the original config
 * files are left in place — the site keeps working either way.
 */
class ShopDataServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // During migrations/initial install the tables may not exist yet.
        try {
            $this->hydrateCategories();
            $this->hydrateProducts();
            $this->hydrateSettings();
        } catch (\Throwable $e) {
            // Fail silently: fall back to config file defaults.
        }
    }

    private function hydrateCategories(): void
    {
        if (! Schema::hasTable('categories') || ! Category::query()->exists()) {
            return;
        }

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map->toConfigArray()
            ->all();

        config(['shop.categories' => $categories]);
    }

    private function hydrateProducts(): void
    {
        if (! Schema::hasTable('products') || ! Product::query()->exists()) {
            return;
        }

        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map->toConfigArray()
            ->all();

        config(['products' => $products]);
    }

    private function hydrateSettings(): void
    {
        $settings = Setting::allSafely();

        if (! empty($settings['contact']) && is_array($settings['contact'])) {
            config(['shop.contact' => array_merge(config('shop.contact', []), $settings['contact'])]);
        }
        if (! empty($settings['social']) && is_array($settings['social'])) {
            config(['shop.social' => array_merge(config('shop.social', []), $settings['social'])]);
        }
    }
}
