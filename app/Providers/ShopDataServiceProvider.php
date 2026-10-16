<?php

namespace App\Providers;

use App\Support\RequestTiming;
use App\Support\ShopData;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\ServiceProvider;

/**
 * Hydrates the config keys the front-end already reads
 * (shop.categories, shop.contact, shop.social, shop.about and products)
 * from the database, so admin/MCP edits show on the live site.
 *
 * The data comes from ShopData's cache, so a normal page view runs no
 * queries for it. If the database is unavailable, the original config
 * files are left in place — the site keeps working either way.
 */
class ShopDataServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Count queries + DB time for the token-protected X-Timing header.
        $this->app['events']->listen(QueryExecuted::class, function (QueryExecuted $query) {
            RequestTiming::$queries++;
            RequestTiming::$dbMs += $query->time;
        });
    }

    public function boot(): void
    {
        $data = ShopData::get();
        if (! $data) {
            return;
        }

        if ($data['categories'] !== null) {
            config(['shop.categories' => $data['categories']]);
        }
        if ($data['products'] !== null) {
            config(['products' => $data['products']]);
        }

        $settings = $data['settings'] ?? [];
        if (! empty($settings['contact']) && is_array($settings['contact'])) {
            config(['shop.contact' => array_merge(config('shop.contact', []), $settings['contact'])]);
        }
        if (! empty($settings['social']) && is_array($settings['social'])) {
            config(['shop.social' => array_merge(config('shop.social', []), $settings['social'])]);
        }
        if (! empty($settings['about']) && is_array($settings['about'])) {
            config(['shop.about' => $settings['about']]);
        }
    }
}
