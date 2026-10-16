<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Servers\StoreServer;
use App\Mcp\Tools\About\UpdateAboutUs;
use App\Mcp\Tools\Categories\CreateCategory;
use App\Mcp\Tools\Categories\DeleteCategory;
use App\Mcp\Tools\Contact\UpdateContactInfo;
use App\Mcp\Tools\Products\CreateProduct;
use App\Mcp\Tools\Products\DeleteProduct;
use App\Mcp\Tools\Products\ListProducts;
use App\Mcp\Tools\Products\UpdateProduct;
use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StoreServerTest extends TestCase
{
    use RefreshDatabase;

    /** A valid 1x1 PNG. */
    private const PNG_B64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.mcp.token' => 'test-token',
            'services.gemini.key' => null, // use the (faked) free provider unless a test sets it
        ]);
    }

    private function png(): string
    {
        return base64_decode(self::PNG_B64);
    }

    public function test_create_product_with_ai_image_and_auto_created_category(): void
    {
        Http::fake(['image.pollinations.ai/*' => Http::response($this->png(), 200, ['Content-Type' => 'image/png'])]);

        StoreServer::tool(CreateProduct::class, [
            'name' => 'Blue Cotton Shirt',
            'price' => 799,
            'category_name' => "Men's Wear",
        ])->assertOk()->assertSee(['Created product', 'new category created', 'Image saved']);

        $category = Category::where('name', "Men's Wear")->first();
        $this->assertNotNull($category, 'category should be auto-created');

        $product = Product::where('name', 'Blue Cotton Shirt')->first();
        $this->assertSame($category->slug, $product->category);
        $this->assertSame(799, (int) $product->price);
        $this->assertMatchesRegularExpression('#^media/\d+$#', $product->image);
        $media = Media::fromPath($product->image);
        $this->assertNotNull($media);

        if (function_exists('imagewebp')) {
            $this->assertSame('image/webp', $media->mime, 'images are converted to WebP');
        }

        // The stored image is served by the site.
        $this->get('/'.$product->image)->assertOk()->assertHeader('Content-Type', $media->mime);
    }

    public function test_create_product_reuses_existing_category(): void
    {
        Category::create(['name' => 'Skin Care', 'slug' => 'skin-care', 'types' => [], 'is_active' => true]);

        StoreServer::tool(CreateProduct::class, [
            'name' => 'Rose Toner',
            'price' => 299,
            'category_name' => 'skin care',
            'image_source' => 'none',
        ])->assertOk()->assertDontSee('new category created');

        $this->assertSame(1, Category::count());
        $this->assertSame('skin-care', Product::first()->category);
        $this->assertNull(Product::first()->image);
    }

    public function test_create_product_with_url_image_rehosts_it(): void
    {
        Http::fake(['upload.wikimedia.org/*' => Http::response($this->png(), 200, ['Content-Type' => 'image/png'])]);

        StoreServer::tool(CreateProduct::class, [
            'name' => 'Linen Shirt',
            'price' => 999,
            'sale_price' => 799,
            'category_name' => 'Shirts',
            'image_url' => 'https://upload.wikimedia.org/test/shirt.png',
        ])->assertOk()->assertSee('downloaded from link');

        $product = Product::where('name', 'Linen Shirt')->first();
        $this->assertSame(799, (int) $product->price);  // selling price
        $this->assertSame(999, (int) $product->mrp);    // struck-through regular price
        $this->assertStringStartsWith('media/', $product->image); // re-hosted, not hotlinked
    }

    public function test_create_product_with_upload_id(): void
    {
        $res = $this->postJson('/upload', ['image_base64' => self::PNG_B64], ['Authorization' => 'Bearer test-token'])
            ->assertCreated()
            ->assertJsonStructure(['upload_id', 'expires_at']);

        StoreServer::tool(CreateProduct::class, [
            'name' => 'Denim Jacket',
            'price' => 1999,
            'category_name' => 'Jackets',
            'image_upload_id' => $res->json('upload_id'),
        ])->assertOk()->assertSee('Image attached from upload');

        $media = Media::fromPath(Product::where('name', 'Denim Jacket')->value('image'));
        $this->assertNotNull($media);
        $this->assertNull($media->upload_token, 'upload id is consumed once attached');
    }

    public function test_upload_requires_token(): void
    {
        $this->postJson('/upload', ['image_base64' => self::PNG_B64])->assertUnauthorized();
    }

    public function test_update_product_changes_fields_and_replaces_image(): void
    {
        Http::fake(['image.pollinations.ai/*' => Http::response($this->png(), 200)]);

        StoreServer::tool(CreateProduct::class, ['name' => 'Polo Tee', 'price' => 599, 'category_name' => 'Tees'])->assertOk();
        $product = Product::where('name', 'Polo Tee')->first();
        $oldMedia = Media::fromPath($product->image);

        StoreServer::tool(UpdateProduct::class, [
            'product' => 'polo-tee',            // by slug
            'sale_price' => 499,
            'stock' => 12,
            'image_source' => 'ai',
        ])->assertOk()->assertSee('Updated product');

        $product->refresh();
        $this->assertSame(499, (int) $product->price);
        $this->assertSame(599, (int) $product->mrp);
        $this->assertSame(12, $product->stock);
        $this->assertNotSame($oldMedia->path(), $product->image);
        $this->assertNull(Media::find($oldMedia->id), 'old image is deleted');
    }

    public function test_delete_requires_confirm_and_removes_image(): void
    {
        Http::fake(['image.pollinations.ai/*' => Http::response($this->png(), 200)]);
        StoreServer::tool(CreateProduct::class, ['name' => 'Old Cap', 'price' => 199, 'category_name' => 'Caps'])->assertOk();
        $product = Product::where('name', 'Old Cap')->first();
        $mediaId = Media::fromPath($product->image)->id;

        StoreServer::tool(DeleteProduct::class, ['product' => 'Old Cap'])->assertHasErrors(['confirm=true']);
        $this->assertNotNull(Product::find($product->id));

        StoreServer::tool(DeleteProduct::class, ['product' => (string) $product->id, 'confirm' => true])->assertOk();
        $this->assertNull(Product::find($product->id));
        $this->assertNull(Media::find($mediaId));
    }

    public function test_image_failure_still_saves_record(): void
    {
        Http::fake(['image.pollinations.ai/*' => Http::response(['error' => 'FREE_LIMIT'], 402)]);

        StoreServer::tool(CreateProduct::class, ['name' => 'Wool Scarf', 'price' => 499, 'category_name' => 'Winter'])
            ->assertOk()
            ->assertSee(['Image NOT saved', 'saved without an image']);

        $this->assertNotNull(Product::where('name', 'Wool Scarf')->first());
        $this->assertNull(Product::where('name', 'Wool Scarf')->value('image'));
    }

    public function test_url_image_blocks_private_addresses(): void
    {
        StoreServer::tool(CreateProduct::class, [
            'name' => 'Sneaky', 'price' => 1, 'category_name' => 'Test',
            'image_url' => 'https://127.0.0.1/internal.png',
        ])->assertOk()->assertSee('not allowed');

        StoreServer::tool(CreateProduct::class, [
            'name' => 'Sneaky 2', 'price' => 1, 'category_name' => 'Test',
            'image_url' => 'http://example.com/a.png',
        ])->assertOk()->assertSee('https');

        $this->assertSame(0, Media::count());
    }

    public function test_rejects_non_image_files(): void
    {
        Http::fake(['upload.wikimedia.org/*' => Http::response('<html>not an image</html>', 200, ['Content-Type' => 'image/png'])]);

        StoreServer::tool(CreateProduct::class, [
            'name' => 'Fake Image', 'price' => 1, 'category_name' => 'Test',
            'image_url' => 'https://upload.wikimedia.org/x.png',
        ])->assertOk()->assertSee('not a JPEG, PNG or WebP');
    }

    public function test_gemini_is_used_when_key_is_set(): void
    {
        config(['services.gemini.key' => 'fake-key']);

        // PNG padded past the minimum payload length the response parser expects.
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([
            'outputs' => [['type' => 'image', 'mime_type' => 'image/png', 'data' => base64_encode($this->png().str_repeat("\0", 400))]],
        ])]);

        StoreServer::tool(CreateProduct::class, ['name' => 'Gemini Shirt', 'price' => 10, 'category_name' => 'AI'])
            ->assertOk()->assertSee('Google Gemini');
    }

    public function test_list_products_filters_and_paginates(): void
    {
        foreach (['Red Shirt' => 300, 'Blue Shirt' => 700, 'Green Cap' => 200] as $name => $price) {
            StoreServer::tool(CreateProduct::class, ['name' => $name, 'price' => $price, 'category_name' => 'Clothes', 'image_source' => 'none'])->assertOk();
        }

        StoreServer::tool(ListProducts::class, ['search' => 'shirt', 'max_price' => 500])
            ->assertOk()->assertSee(['Found 1 product', 'Red Shirt'])->assertDontSee('Blue Shirt');
    }

    public function test_delete_category_moves_products(): void
    {
        StoreServer::tool(CreateCategory::class, ['name' => 'Old Range', 'image_source' => 'none'])->assertOk();
        StoreServer::tool(CreateCategory::class, ['name' => 'New Range', 'image_source' => 'none'])->assertOk();
        StoreServer::tool(CreateProduct::class, ['name' => 'Item', 'price' => 5, 'category_name' => 'Old Range', 'image_source' => 'none'])->assertOk();

        StoreServer::tool(DeleteCategory::class, ['category' => 'Old Range', 'confirm' => true])->assertHasErrors(['move_products_to']);

        StoreServer::tool(DeleteCategory::class, ['category' => 'Old Range', 'confirm' => true, 'move_products_to' => 'New Range'])->assertOk();
        $this->assertSame('new-range', Product::where('name', 'Item')->value('category'));
    }

    public function test_about_and_contact_updates(): void
    {
        StoreServer::tool(UpdateAboutUs::class, ['title' => 'Our Story', 'content' => 'Since 2020.'])->assertOk()->assertSee('Our Story');
        $this->assertSame('Our Story', Setting::get('about')['title']);

        StoreServer::tool(UpdateContactInfo::class, [
            'phone' => '+91 90000 00000',
            'social' => ['instagram' => 'https://instagram.com/timepass', 'x' => ''],
        ])->assertOk()->assertSee('+91 90000 00000');

        $this->assertSame('https://instagram.com/timepass', Setting::get('social')['instagram']);
        $this->assertSame('', Setting::get('social')['x']);
    }
}
