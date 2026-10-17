<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Providers\AppServiceProvider;
use App\Support\ShopData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * The site must work as several identical instances behind a load balancer:
 * nothing may depend on one instance's local disk or memory.
 */
class LoadBalancingTest extends TestCase
{
    use RefreshDatabase;

    private function seedShop(): void
    {
        Category::create(['name' => 'Skin Care', 'slug' => 'skin-care', 'types' => [], 'is_active' => true]);
        Product::create(['name' => 'Rose Toner', 'slug' => 'rose-toner', 'category' => 'skin-care', 'price' => 299]);
    }

    public function test_an_edit_on_another_instance_shows_here_within_the_version_check(): void
    {
        $this->seedShop();
        $this->assertSame(299, ShopData::get()['products'][0]['price']);

        // Another instance edits the product and publishes a new data version in
        // the shared store. This instance's own cache is untouched.
        DB::table('products')->where('slug', 'rose-toner')->update(['price' => 199]);
        Cache::store('database')->forever('shop-data:version', 'from-instance-b');

        // Until this instance re-checks the version, it serves its cached copyâ€¦
        $this->assertSame(299, ShopData::get()['products'][0]['price']);

        // â€¦and within 15 seconds it picks up the new version.
        $this->travel(16)->seconds();
        $this->assertSame(199, ShopData::get()['products'][0]['price']);
    }

    public function test_a_local_edit_publishes_a_new_shared_version(): void
    {
        $this->seedShop();
        ShopData::get();
        $before = Cache::store('database')->get('shop-data:version');

        Product::where('slug', 'rose-toner')->first()->update(['price' => 149]);

        $this->assertNotSame($before, Cache::store('database')->get('shop-data:version'));
        $this->assertSame(149, ShopData::get()['products'][0]['price']); // this instance: immediately
    }

    public function test_links_use_the_public_address_when_forced(): void
    {
        config(['app.url' => 'https://shop.example.com', 'app.force_url' => true]);
        (new AppServiceProvider($this->app))->boot();

        $this->assertSame('https://shop.example.com/products', route('products'));

        URL::forceRootUrl(null);
    }
}
