<?php

namespace Tests\Feature;

use App\Mcp\Servers\StoreServer;
use App\Mcp\Tools\Categories\CreateCategory;
use App\Mcp\Tools\Products\CreateProduct;
use App\Mcp\Tools\Products\DeleteProduct;
use App\Mcp\Tools\Products\UpdateProduct;
use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\CloudinaryUploader;
use Cloudinary\Api\ApiResponse;
use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Cloudinary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * MCP images (AI, link, base64, upload id) go to Cloudinary; the media table is
 * the fallback and old /media/{id} images keep working. Cloudinary is mocked:
 * no network calls, no credentials.
 */
class McpCloudinaryImagesTest extends TestCase
{
    use RefreshDatabase;

    private const PNG_B64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    private MockInterface $api;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.mcp.token' => 'test-token',
            'services.gemini.key' => null, // the (faked) free AI provider
            'filesystems.disks.cloudinary.folder' => 'timepass',
        ]);
        Http::fake([
            'image.pollinations.ai/*' => Http::response(base64_decode(self::PNG_B64), 200, ['Content-Type' => 'image/png']),
            'upload.wikimedia.org/*' => Http::response(base64_decode(self::PNG_B64), 200, ['Content-Type' => 'image/png']),
        ]);

        $this->api = Mockery::mock(UploadApi::class);
        $client = Mockery::mock(Cloudinary::class);
        $client->shouldReceive('uploadApi')->andReturn($this->api);
        $this->app->instance(CloudinaryUploader::class, new CloudinaryUploader($client));
    }

    private static function url(string $folder, string $name): string
    {
        return "https://res.cloudinary.com/demo/image/upload/v17/timepass/{$folder}/{$name}.webp";
    }

    /** Expect one upload of image bytes (a data URI) into $folder, answering with $url. */
    private function expectUpload(string $folder, string $url): void
    {
        $this->api->shouldReceive('upload')->once()
            ->withArgs(fn ($file, $options) => str_starts_with($file, 'data:image/') && $options['folder'] === $folder)
            ->andReturn(new ApiResponse(['secure_url' => $url], []));
    }

    private function mediaProduct(string $name): Product
    {
        $media = Media::create(['mime' => 'image/png', 'size' => 68, 'data' => self::PNG_B64]);

        return Product::create(['name' => $name, 'slug' => str($name)->slug(), 'category' => 'skin-care', 'price' => 99, 'image' => $media->path()]);
    }

    // ── new images ───────────────────────────────────────────────────

    public function test_ai_product_image_goes_to_cloudinary_not_the_database(): void
    {
        $url = self::url('products', 'rose_serum');
        $this->expectUpload('timepass/products', $url);

        StoreServer::tool(CreateProduct::class, ['name' => 'Rose Serum', 'price' => 499, 'category_name' => 'Skin Care'])
            ->assertOk()->assertSee(['Image saved', 'stored on Cloudinary', $url]);

        $this->assertSame($url, Product::where('name', 'Rose Serum')->sole()->image);
        $this->assertSame(0, Media::count());
    }

    public function test_category_images_use_the_categories_folder(): void
    {
        $url = self::url('categories', 'hair_care');
        $this->expectUpload('timepass/categories', $url);

        StoreServer::tool(CreateCategory::class, ['name' => 'Hair Care'])->assertOk();

        $this->assertSame($url, Category::where('name', 'Hair Care')->sole()->image);
    }

    public function test_link_and_base64_images_go_to_cloudinary(): void
    {
        $this->expectUpload('timepass/products', self::url('products', 'from_link'));
        StoreServer::tool(CreateProduct::class, ['name' => 'Link Item', 'price' => 10, 'category_name' => 'Misc', 'image_url' => 'https://upload.wikimedia.org/a.png'])->assertOk();

        $this->expectUpload('timepass/products', self::url('products', 'from_base64'));
        StoreServer::tool(CreateProduct::class, ['name' => 'Base64 Item', 'price' => 10, 'category_name' => 'Misc', 'image_base64' => self::PNG_B64])->assertOk();

        $this->assertSame(self::url('products', 'from_link'), Product::where('name', 'Link Item')->sole()->image);
        $this->assertSame(self::url('products', 'from_base64'), Product::where('name', 'Base64 Item')->sole()->image);
        $this->assertSame(0, Media::count());
    }

    public function test_upload_id_is_moved_to_cloudinary_and_its_staging_row_removed(): void
    {
        $uploadId = $this->postJson('/upload', ['image_base64' => self::PNG_B64], ['Authorization' => 'Bearer test-token'])
            ->assertCreated()->json('upload_id');
        $this->assertSame(1, Media::count()); // staged for 15 minutes, as before

        $url = self::url('products', 'uploaded');
        $this->expectUpload('timepass/products', $url);
        StoreServer::tool(CreateProduct::class, ['name' => 'Uploaded Item', 'price' => 10, 'category_name' => 'Misc', 'image_upload_id' => $uploadId])
            ->assertOk()->assertSee('Image attached from upload');

        $this->assertSame($url, Product::where('name', 'Uploaded Item')->sole()->image);
        $this->assertSame(0, Media::count());
    }

    public function test_a_failed_cloudinary_upload_falls_back_to_the_database_and_logs_no_secrets(): void
    {
        config(['filesystems.disks.cloudinary.url' => 'cloudinary://111222333:topsecretvalue@demo']);
        $this->api->shouldReceive('upload')->once()->andThrow(new \Exception('Invalid api_key 111222333 / topsecretvalue'));
        Log::spy();

        StoreServer::tool(CreateProduct::class, ['name' => 'Fallback Item', 'price' => 10, 'category_name' => 'Misc'])
            ->assertOk()->assertSee('stored in the database because the Cloudinary upload failed');

        $this->assertMatchesRegularExpression('#^media/\d+$#', Product::where('name', 'Fallback Item')->sole()->image);
        $this->assertSame(1, Media::count());
        Log::shouldHaveReceived('warning')->once()->withArgs(fn ($message, $context) => str_contains($message, 'Cloudinary upload failed')
            && ! str_contains($context['error'], '111222333') && ! str_contains($context['error'], 'topsecretvalue'));
    }

    public function test_without_cloudinary_credentials_images_stay_in_the_database(): void
    {
        config(['filesystems.disks.cloudinary.url' => '']);
        $this->app->instance(CloudinaryUploader::class, new CloudinaryUploader); // no client, not configured
        $this->api->shouldNotReceive('upload');

        StoreServer::tool(CreateProduct::class, ['name' => 'Local Item', 'price' => 10, 'category_name' => 'Misc'])
            ->assertOk()->assertSee('stored in the database');

        $this->assertMatchesRegularExpression('#^media/\d+$#', Product::where('name', 'Local Item')->sole()->image);
    }

    // ── replace / delete ─────────────────────────────────────────────

    public function test_replacing_and_deleting_remove_the_old_cloudinary_file(): void
    {
        $old = self::url('products', 'old_shot');
        $new = self::url('products', 'new_shot');
        Product::create(['name' => 'Toner', 'slug' => 'toner', 'category' => 'skin-care', 'price' => 99, 'image' => $old]);

        $this->expectUpload('timepass/products', $new);
        $this->api->shouldReceive('destroy')->once()->with('timepass/products/old_shot', Mockery::any())->andReturn(new ApiResponse(['result' => 'ok'], []));
        StoreServer::tool(UpdateProduct::class, ['product' => 'toner', 'image_source' => 'ai'])->assertOk();
        $this->assertSame($new, Product::where('slug', 'toner')->sole()->image);

        $this->api->shouldReceive('destroy')->once()->with('timepass/products/new_shot', Mockery::any())->andReturn(new ApiResponse(['result' => 'ok'], []));
        StoreServer::tool(DeleteProduct::class, ['product' => 'toner', 'confirm' => true])->assertOk();
        $this->assertNull(Product::where('slug', 'toner')->first());
    }

    public function test_replacing_an_old_media_image_deletes_its_row_and_the_old_url_keeps_working_until_then(): void
    {
        $product = $this->mediaProduct('Old Serum');
        $this->get('/'.$product->image)->assertOk()->assertHeader('Content-Type', 'image/png'); // old URL still served

        $this->expectUpload('timepass/products', self::url('products', 'fresh'));
        $this->api->shouldNotReceive('destroy'); // a media/{id} path is not a Cloudinary file
        StoreServer::tool(UpdateProduct::class, ['product' => (string) $product->id, 'image_source' => 'ai'])->assertOk();

        $this->assertSame(0, Media::count());
        $this->assertSame(self::url('products', 'fresh'), $product->fresh()->image);
    }

    // ── php artisan media:to-cloudinary ──────────────────────────────

    public function test_move_command_dry_run_changes_nothing(): void
    {
        $product = $this->mediaProduct('Dry Serum');
        $this->api->shouldNotReceive('upload');

        $this->artisan('media:to-cloudinary', ['--dry-run' => true])
            ->expectsOutputToContain('would move')
            ->expectsOutputToContain('Nothing was changed')
            ->assertSuccessful();

        $this->assertSame($product->image, $product->fresh()->image);
    }

    public function test_move_command_moves_every_reference_keeps_media_rows_and_can_run_again(): void
    {
        $product = $this->mediaProduct('Moving Serum');
        $productMediaId = (int) str($product->image)->after('media/')->toString();

        $categoryMedia = Media::create(['mime' => 'image/png', 'size' => 68, 'data' => self::PNG_B64]);
        $category = Category::create(['name' => 'Skin Care', 'slug' => 'skin-care', 'types' => [], 'is_active' => true, 'image' => $categoryMedia->path()]);

        $aboutMedia = Media::create(['mime' => 'image/png', 'size' => 68, 'data' => self::PNG_B64]);
        Setting::put('about', ['title' => 'About us', 'content' => 'Hello', 'image' => $aboutMedia->path()]);

        $order = User::factory()->create()->orders()->create([
            'order_number' => 'TP-1', 'status' => 'placed', 'subtotal' => 99, 'total' => 99, 'payment_method' => 'UPI',
            'shipping_name' => 'A', 'shipping_phone' => '9999999999', 'shipping_address' => 'Pune',
        ]);
        $item = $order->items()->create(['product_name' => 'Moving Serum', 'quantity' => 1, 'price' => 99, 'image' => $product->image]);

        $this->expectUpload('timepass/products', self::url('products', 'moved_product'));     // shared by product + order item: uploaded once
        $this->expectUpload('timepass/categories', self::url('categories', 'moved_category'));
        $this->expectUpload('timepass/about', self::url('about', 'moved_about'));

        $this->artisan('media:to-cloudinary')->expectsOutputToContain('Moved 3 image(s)')->assertSuccessful();

        $this->assertSame(self::url('products', 'moved_product'), $product->fresh()->image);
        $this->assertSame(self::url('products', 'moved_product'), $item->fresh()->image);
        $this->assertSame(self::url('categories', 'moved_category'), $category->fresh()->image);
        $this->assertSame(self::url('about', 'moved_about'), Setting::get('about')['image']);

        // Rows kept: old links still work.
        $this->assertSame(3, Media::count());
        $this->get("/media/{$productMediaId}")->assertOk();

        // Nothing left to move the second time (no further uploads expected).
        $this->artisan('media:to-cloudinary')->expectsOutputToContain('Nothing to move')->assertSuccessful();
    }

    public function test_move_command_needs_cloudinary_unless_dry_run(): void
    {
        config(['filesystems.disks.cloudinary.url' => '']);
        $this->app->instance(CloudinaryUploader::class, new CloudinaryUploader);
        $product = $this->mediaProduct('Waiting Serum');

        $this->artisan('media:to-cloudinary')->expectsOutputToContain('not configured')->assertFailed();

        $this->assertSame($product->image, $product->fresh()->image);
    }

    public function test_move_command_continues_after_a_failed_upload_and_honours_the_limit(): void
    {
        config(['filesystems.disks.cloudinary.url' => 'cloudinary://111222333:topsecretvalue@demo']);
        $first = $this->mediaProduct('First Serum');
        $second = $this->mediaProduct('Second Serum');
        $third = $this->mediaProduct('Third Serum');

        $this->api->shouldReceive('upload')->twice()->andReturnUsing(
            fn () => throw new \Exception('Invalid api_key 111222333 / topsecretvalue'),
            fn () => new ApiResponse(['secure_url' => self::url('products', 'second')], []),
        );

        $this->artisan('media:to-cloudinary', ['--limit' => 2])
            ->expectsOutputToContain('FAILED')
            ->doesntExpectOutputToContain('topsecretvalue')
            ->expectsOutputToContain('1 more image(s) left')
            ->assertFailed();

        $this->assertSame($first->image, $first->fresh()->image);                // failed: untouched
        $this->assertSame(self::url('products', 'second'), $second->fresh()->image);
        $this->assertSame($third->image, $third->fresh()->image);                // beyond the limit
    }
}
