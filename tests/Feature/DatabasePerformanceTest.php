<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\DashboardController;
use App\Mcp\Servers\StoreServer;
use App\Mcp\Tools\Categories\ListCategories;
use App\Mcp\Tools\Reviews\ListReviews;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\LazyLoadingViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Pagination, caching, indexes and N+1 protection.
 */
class DatabasePerformanceTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    /** A customer with $count orders, each with one item and one payment. */
    private function customerWithOrders(int $count, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);

        for ($i = 1; $i <= $count; $i++) {
            $order = $user->orders()->create([
                'order_number' => "TP-{$user->id}-{$i}",
                'status' => $i % 2 ? 'placed' : 'shipped',
                'subtotal' => 100, 'total' => 100, 'payment_method' => 'UPI',
                'shipping_name' => $user->name, 'shipping_phone' => '9999999999', 'shipping_address' => 'MG Road, Pune',
            ]);
            $order->items()->create(['product_name' => "Item {$i}", 'quantity' => 2, 'price' => 50]);
            $user->transactions()->create([
                'order_id' => $order->id, 'reference' => "TX-{$user->id}-{$i}",
                'method' => 'UPI', 'amount' => 100, 'status' => 'success',
            ]);
        }

        return $user;
    }

    /** A few rows in every admin list (several rows, so a lazy-loaded relation would throw). */
    private function seedAdminLists(): void
    {
        $this->customerWithOrders(2);
        $this->customerWithOrders(2);

        Category::create(['name' => 'Skin Care', 'slug' => 'skin-care', 'types' => [], 'is_active' => true]);
        Category::create(['name' => 'Hair Care', 'slug' => 'hair-care', 'types' => [], 'is_active' => true]);
        $a = Product::create(['name' => 'Toner', 'slug' => 'toner', 'category' => 'skin-care', 'price' => 99]);
        $b = Product::create(['name' => 'Shampoo', 'slug' => 'shampoo', 'category' => 'hair-care', 'price' => 199]);

        foreach (User::where('is_admin', false)->get() as $customer) {
            Wishlist::create(['user_id' => $customer->id, 'product_id' => $a->id]);
            Wishlist::create(['user_id' => $customer->id, 'product_id' => $b->id]);
        }

        Review::create(['name' => 'Anjali', 'rating' => 5, 'text' => 'Lovely serum.']);
        Review::create(['name' => 'Rohan', 'rating' => 4, 'text' => 'Great shampoo.']);
        ContactMessage::create(['name' => 'Priya', 'email' => 'priya@example.com', 'message' => 'Hello there']);
        ContactMessage::create(['name' => 'Arjun', 'email' => 'arjun@example.com', 'message' => 'Where is my order?']);
    }

    // ── pagination ───────────────────────────────────────────────────

    public function test_admin_lists_default_to_20_per_page_and_cap_per_page_at_50(): void
    {
        $this->seedAdminLists();
        $admin = $this->admin();
        $customer = User::where('is_admin', false)->first();

        $lists = [
            '/admin/products' => 'products',
            '/admin/categories' => 'categories',
            '/admin/orders' => 'orders',
            '/admin/transactions' => 'transactions',
            '/admin/customers' => 'customers',
            "/admin/customers/{$customer->id}" => 'orders',
            '/admin/messages' => 'messages',
            '/admin/reviews' => 'reviews',
            '/admin/wishlists' => 'rows',
        ];

        foreach ($lists as $url => $var) {
            $this->assertSame(20, $this->actingAs($admin)->get($url)->assertOk()->viewData($var)->perPage(), $url);
            $this->assertSame(5, $this->actingAs($admin)->get("{$url}?per_page=5")->assertOk()->viewData($var)->perPage(), $url);
            $this->assertSame(50, $this->actingAs($admin)->get("{$url}?per_page=500")->assertOk()->viewData($var)->perPage(), $url);
            $this->assertSame(20, $this->actingAs($admin)->get("{$url}?per_page=abc")->assertOk()->viewData($var)->perPage(), $url);
        }
    }

    public function test_customer_account_lists_are_paginated(): void
    {
        $customer = $this->customerWithOrders(25);

        $orders = $this->actingAs($customer)->get('/account/orders')->assertOk()->viewData('orders');
        $this->assertSame([20, 25, 20], [$orders->perPage(), $orders->total(), $orders->count()]);
        $this->assertCount(5, $this->actingAs($customer)->get('/account/orders?page=2')->viewData('orders'));
        $this->assertCount(25, $this->actingAs($customer)->get('/account/orders?per_page=50')->viewData('orders'));

        $transactions = $this->actingAs($customer)->get('/account/transactions')->assertOk()->viewData('transactions');
        $this->assertSame([20, 25], [$transactions->perPage(), $transactions->total()]);
    }

    public function test_customer_detail_shows_order_count_and_pages_through_orders(): void
    {
        $customer = $this->customerWithOrders(25);

        $orders = $this->actingAs($this->admin())->get("/admin/customers/{$customer->id}")->assertOk()->viewData('orders');

        $this->assertSame([25, 20], [$orders->total(), $orders->count()]);
    }

    public function test_wishlist_ranking_continues_across_pages(): void
    {
        $this->seedAdminLists(); // two products, wished by both customers
        $popular = Product::where('slug', 'toner')->first();
        Wishlist::create(['user_id' => $this->admin()->id, 'product_id' => $popular->id]);

        $page2 = $this->actingAs($this->admin())->get('/admin/wishlists?per_page=1&page=2')->assertOk();

        $this->assertSame(2, $page2->viewData('rows')->firstItem());
        $this->assertSame(3, $page2->viewData('maxCount')); // bars stay relative to the overall top product
        $this->assertSame('Shampoo', $page2->viewData('rows')->first()->product->name);
    }

    public function test_order_search_stays_inside_the_status_filter(): void
    {
        $this->customerWithOrders(4, ['name' => 'Asha Patel']); // 2 placed, 2 shipped

        $orders = $this->actingAs($this->admin())->get('/admin/orders?status=placed&q=Asha')->assertOk()->viewData('orders');

        $this->assertSame(2, $orders->total());
        $this->assertSame(['placed'], $orders->pluck('status')->unique()->values()->all());
    }

    // ── caching ──────────────────────────────────────────────────────

    public function test_dashboard_is_cached_and_cleared_when_its_data_changes(): void
    {
        $this->seedAdminLists();
        $admin = $this->admin();

        $first = $this->countQueries(fn () => $this->actingAs($admin)->get('/admin')->assertOk());
        $this->assertTrue(Cache::has(DashboardController::CACHE_KEY));
        $second = $this->countQueries(fn () => $this->actingAs($admin)->get('/admin')->assertOk());
        $this->assertLessThan($first - 8, $second, "first load: {$first} queries, cached load: {$second}");

        // Any change to data the dashboard shows clears it.
        Product::create(['name' => 'Serum', 'slug' => 'serum', 'category' => 'skin-care', 'price' => 499]);
        $this->assertFalse(Cache::has(DashboardController::CACHE_KEY));
        $this->assertSame(3, $this->actingAs($admin)->get('/admin')->viewData('stats')['products']);

        ContactMessage::first()->update(['read_at' => now()]);
        $this->assertFalse(Cache::has(DashboardController::CACHE_KEY));
        $this->assertSame(1, $this->actingAs($admin)->get('/admin')->viewData('stats')['unread_messages']);

        Order::first()->delete();
        $this->assertFalse(Cache::has(DashboardController::CACHE_KEY));
    }

    // ── indexes ──────────────────────────────────────────────────────

    public function test_index_migration_adds_indexes_is_rerunnable_and_reversible(): void
    {
        $migration = require database_path('migrations/2026_10_17_000002_add_performance_indexes.php');

        $expected = [
            'orders' => ['user_id', 'created_at'], 'order_items' => ['order_id'],
            'transactions' => ['order_id'], 'addresses' => ['user_id'], 'wishlists' => ['product_id'],
            'contact_messages' => ['read_at'], 'products' => ['category'], 'users' => ['created_at'],
        ];
        foreach ($expected as $table => $columns) {
            $this->assertTrue(Schema::hasIndex($table, $columns), "$table(".implode(',', $columns).')');
        }

        $migration->up(); // running again must not fail

        $migration->down();
        $this->assertFalse(Schema::hasIndex('orders', ['user_id', 'created_at']));
        $this->assertTrue(Schema::hasIndex('contact_messages', ['status'])); // older indexes untouched
        $this->assertTrue(Schema::hasIndex('products', ['slug']));

        $migration->up();
        $this->assertTrue(Schema::hasIndex('orders', ['user_id', 'created_at']));
    }

    public function test_index_migration_does_not_run_as_one_big_transaction(): void
    {
        // On Neon's pooler a single failed statement aborts the whole transaction;
        // committing each index on its own lets a re-run continue where it stopped.
        $migration = require database_path('migrations/2026_10_17_000002_add_performance_indexes.php');

        $this->assertFalse($migration->withinTransaction);
    }

    public function test_postgres_connection_avoids_named_prepared_statements(): void
    {
        if (! defined('PDO::PGSQL_ATTR_DISABLE_PREPARES')) {
            $this->markTestSkipped('pdo_pgsql is not installed.');
        }

        // Needed for Neon's pooled connection (PgBouncer): see config/database.php.
        $options = config('database.connections.pgsql.options');
        $this->assertTrue($options[\PDO::PGSQL_ATTR_DISABLE_PREPARES] ?? false);
    }

    // ── N+1 ──────────────────────────────────────────────────────────

    public function test_lazy_loading_throws_outside_production(): void
    {
        $this->assertTrue(Model::preventsLazyLoading());

        $this->customerWithOrders(2);
        $this->expectException(LazyLoadingViolationException::class);
        Order::all()->first()->user; // relation not eager-loaded inside a collection
    }

    public function test_mcp_list_categories_uses_the_same_number_of_queries_for_any_page_size(): void
    {
        $parent = Category::create(['name' => 'Care', 'slug' => 'care', 'types' => [], 'is_active' => true]);
        for ($i = 1; $i <= 8; $i++) {
            Category::create(['name' => "Range {$i}", 'slug' => "range-{$i}", 'types' => [], 'is_active' => true, 'parent_id' => $parent->id]);
            Product::create(['name' => "Product {$i}", 'slug' => "product-{$i}", 'category' => "range-{$i}", 'price' => 10]);
        }

        $small = $this->countQueries(fn () => StoreServer::tool(ListCategories::class, ['per_page' => 2])->assertOk());
        $large = $this->countQueries(fn () => StoreServer::tool(ListCategories::class, ['per_page' => 9])
            ->assertOk()->assertSee(['"parent_name": "Care"', '"product_count": 1']));

        $this->assertSame($small, $large);
    }

    public function test_mcp_list_reviews_is_paginated_and_keeps_its_keys(): void
    {
        for ($i = 1; $i <= 25; $i++) {
            Review::create(['name' => "Reviewer {$i}", 'rating' => 5, 'text' => 'Nice.']);
        }

        StoreServer::tool(ListReviews::class, ['per_page' => 10, 'page' => 3])
            ->assertOk()
            ->assertSee(['"count":5', '"reviews":[', '"pagination":{', '"page":3', '"total":25', '"has_more":false']);
    }

    private function countQueries(callable $callback): int
    {
        $count = 0;
        DB::listen(function () use (&$count) {
            $count++;
        });
        $callback();

        return $count;
    }
}
