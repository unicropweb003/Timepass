<?php

namespace Tests\Feature;

use App\Mcp\Servers\StoreServer;
use App\Mcp\Support\ImagePipeline;
use App\Mcp\Tools\Categories\CreateCategory;
use App\Mcp\Tools\Categories\UpdateCategory;
use App\Mcp\Tools\Products\CreateProduct;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\Setting;
use App\Providers\ShopDataServiceProvider;
use App\Support\ShopData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Page views must not hit the database for storefront data, yet any edit
 * (admin panel or MCP) must show on the next request.
 */
class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    private const PNG_B64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    private function countQueries(callable $callback): int
    {
        $count = 0;
        DB::listen(function () use (&$count) {
            $count++;
        });
        $callback();

        return $count;
    }

    private function seedShop(): void
    {
        Category::create(['name' => 'Skin Care', 'slug' => 'skin-care', 'types' => [], 'is_active' => true]);
        Product::create(['name' => 'Rose Toner', 'slug' => 'rose-toner', 'category' => 'skin-care', 'price' => 299]);
    }

    public function test_storefront_data_is_cached_and_refreshed_on_change(): void
    {
        $this->seedShop();

        $this->assertSame(['Rose Toner'], array_column(ShopData::get()['products'], 'name'));

        // Second read comes from the cache: no queries at all.
        $this->assertSame(0, $this->countQueries(fn () => ShopData::get()));

        // Edits clear the cache, so the very next read sees them.
        Product::where('slug', 'rose-toner')->first()->update(['price' => 199]);
        $this->assertSame(199, ShopData::get()['products'][0]['price']);

        Product::create(['name' => 'Aloe Gel', 'slug' => 'aloe-gel', 'category' => 'skin-care', 'price' => 149]);
        $this->assertCount(2, ShopData::get()['products']);

        Product::where('slug', 'aloe-gel')->first()->delete();
        $this->assertCount(1, ShopData::get()['products']);
    }

    public function test_hidden_items_are_excluded_and_settings_and_reviews_refresh(): void
    {
        $this->seedShop();
        Product::create(['name' => 'Hidden', 'slug' => 'hidden', 'category' => 'skin-care', 'price' => 1, 'is_active' => false]);
        Review::create(['name' => 'Asha', 'rating' => 5, 'text' => 'Great!', 'is_active' => true]);

        $data = ShopData::get();
        $this->assertSame(['Rose Toner'], array_column($data['products'], 'name'));
        $this->assertSame(['Asha'], array_column(ShopData::reviews(), 'name'));

        Setting::put('contact', ['phone' => '+91 1']);
        $this->assertSame('+91 1', ShopData::get()['settings']['contact']['phone']);

        Review::first()->update(['is_active' => false]);
        $this->assertSame([], ShopData::reviews());
    }

    public function test_provider_hydrates_config_from_cached_data(): void
    {
        $this->seedShop();

        (new ShopDataServiceProvider($this->app))->boot();

        $this->assertSame(['Rose Toner'], array_column(config('products'), 'name'));
        $this->assertSame(['skin-care'], array_column(config('shop.categories'), 'slug'));
    }

    public function test_bulk_category_slug_change_refreshes_cache(): void
    {
        StoreServer::tool(CreateCategory::class, ['name' => 'Old Name', 'image_source' => 'none'])->assertOk();
        StoreServer::tool(CreateProduct::class, ['name' => 'Item', 'price' => 5, 'category_name' => 'Old Name', 'image_source' => 'none'])->assertOk();
        ShopData::get(); // warm the cache

        StoreServer::tool(UpdateCategory::class, ['category' => 'Old Name', 'slug' => 'new-slug'])->assertOk();

        $this->assertSame('new-slug', ShopData::get()['products'][0]['category']);
    }

    public function test_media_is_stateless_and_served_from_disk_copy(): void
    {
        [$media] = ImagePipeline::store(base64_decode(self::PNG_B64));

        $first = $this->get('/media/'.$media->id)->assertOk()->assertHeader('Content-Type', $media->mime);
        $this->assertSame([], $first->headers->getCookies(), 'no session cookie for images');

        // Repeat requests are served from the disk copy without touching the database.
        $this->assertSame(0, $this->countQueries(fn () => $this->get('/media/'.$media->id)->assertOk()));

        // Deleting the image removes the copy too.
        $media->delete();
        $this->get('/media/'.$media->id)->assertNotFound();
    }

    public function test_timing_header_requires_the_token(): void
    {
        config(['services.mcp.token' => 'secret-token']);

        $this->get('/up')->assertHeaderMissing('Server-Timing');
        $this->get('/up', ['X-Timing' => 'wrong'])->assertHeaderMissing('Server-Timing');

        $res = $this->get('/up', ['X-Timing' => 'secret-token']);
        $this->assertStringContainsString('db;dur=', (string) $res->headers->get('Server-Timing'));
        $this->assertStringContainsString('session=', (string) $res->headers->get('X-Timing-Info'));
    }
}
