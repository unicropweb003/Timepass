<?php

namespace Tests\Feature;

use App\Mcp\Servers\StoreServer;
use App\Mcp\Tools\Products\CreateProduct;
use App\Mcp\Tools\Products\UpdateProduct;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\CloudinaryUploader;
use App\Support\VideoUrl;
use Cloudinary\Api\ApiResponse;
use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Cloudinary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * Product videos: admin upload / link / removal, the MCP video_url field, and
 * the "Watch & shop" reels on the home page. Cloudinary is mocked.
 */
class ProductVideoTest extends TestCase
{
    use RefreshDatabase;

    private const VIDEO = 'https://res.cloudinary.com/demo-cloud/video/upload/v1712345678/timepass/products/videos/serum_ab12.mp4';

    private MockInterface $api;

    protected function setUp(): void
    {
        parent::setUp();

        config(['filesystems.disks.cloudinary.folder' => 'timepass', 'services.mcp.token' => 'test-token']);

        $this->api = Mockery::mock(UploadApi::class);
        $client = Mockery::mock(Cloudinary::class);
        $client->shouldReceive('uploadApi')->andReturn($this->api);

        $this->app->instance(CloudinaryUploader::class, new CloudinaryUploader($client));
    }

    private function admin(): User
    {
        Category::create(['name' => 'Skin Care', 'slug' => 'skin-care', 'types' => [], 'is_active' => true]);

        return User::factory()->create(['is_admin' => true]);
    }

    private function clip(string $name = 'serum.mp4', int $kb = 900, string $mime = 'video/mp4'): UploadedFile
    {
        return UploadedFile::fake()->create($name, $kb, $mime);
    }

    private function product(array $attributes = []): Product
    {
        return Product::create($attributes + ['name' => 'Glow Serum', 'slug' => 'glow-serum', 'category' => 'skin-care', 'price' => 499]);
    }

    // ── URLs ─────────────────────────────────────────────────────────

    public function test_cloudinary_videos_get_a_light_mp4_first_and_a_poster_frame(): void
    {
        $this->assertSame([
            'https://res.cloudinary.com/demo-cloud/video/upload/q_auto,vc_auto,w_720,c_limit/v1712345678/timepass/products/videos/serum_ab12.mp4',
            self::VIDEO,
        ], VideoUrl::sources(self::VIDEO));
        $this->assertSame(
            'https://res.cloudinary.com/demo-cloud/video/upload/so_10p,w_540,c_limit,q_auto/v1712345678/timepass/products/videos/serum_ab12.jpg',
            VideoUrl::poster(self::VIDEO),
        );

        // iPhone MOV uploads are delivered as MP4.
        $this->assertStringEndsWith('/v1/a/clip.mp4', VideoUrl::sources('https://res.cloudinary.com/x/video/upload/v1/a/clip.mov')[0]);

        // Other links are used as they are, without a poster.
        $this->assertSame(['https://cdn.example.com/a.mp4'], VideoUrl::sources('https://cdn.example.com/a.mp4'));
        $this->assertNull(VideoUrl::poster('https://cdn.example.com/a.mp4'));
        $this->assertSame([], VideoUrl::sources(null));
        $this->assertSame([], VideoUrl::sources(''));
    }

    public function test_only_direct_video_file_links_are_accepted(): void
    {
        foreach (['https://cdn.example.com/a.mp4', 'https://x.com/b/c.WEBM', 'https://x.com/c.mov?token=1', self::VIDEO] as $ok) {
            $this->assertMatchesRegularExpression(VideoUrl::LINK_PATTERN, $ok);
        }
        foreach (['http://x.com/a.mp4', 'https://www.youtube.com/watch?v=abc', 'https://instagram.com/reel/x/', 'https://x.com/a.mp4.html', 'javascript:alert(1)//a.mp4'] as $bad) {
            $this->assertDoesNotMatchRegularExpression(VideoUrl::LINK_PATTERN, $bad);
        }
    }

    // ── home page ────────────────────────────────────────────────────

    public function test_home_shows_video_reels_first_then_photo_reels_above_the_offer(): void
    {
        $sample = ['category' => 'skin-care', 'price' => 499, 'mrp' => 699, 'image' => null, 'tags' => ['best-seller']];
        config(['products' => [
            ['name' => 'Photo One'] + $sample,
            ['name' => 'Video Serum', 'video' => self::VIDEO] + $sample,
            ['name' => 'Photo Two'] + $sample,
        ]]);

        $html = $this->get('/')->assertOk()->assertSee('Watch &amp; shop.', false)->getContent();

        $this->assertStringContainsString('<video data-reel-video', $html);
        $this->assertStringContainsString(e(VideoUrl::sources(self::VIDEO)[0]), $html);
        $this->assertStringContainsString('data-reel-open="0"', $html);
        $this->assertStringContainsString('aria-label="Watch the Video Serum video"', $html);
        $this->assertStringContainsString('<dialog data-reel-dialog', $html);

        // Video reel first, then photo reels; all of it before the offer.
        $row = strpos($html, 'data-reels-track');
        $video = strpos($html, 'Watch the Video Serum video', $row);
        $this->assertLessThan(strpos($html, 'Photo One', $row), $video);
        $this->assertLessThan(strpos($html, 'id="offer"'), strpos($html, 'Photo Two', $row));
    }

    public function test_home_without_videos_shows_moving_photo_reels_and_no_player(): void
    {
        config(['products' => [
            ['name' => 'Plain Toner', 'category' => 'skin-care', 'price' => 299, 'image' => null, 'tags' => []],
        ]]);

        $html = $this->get('/')->assertOk()->assertSee('Watch &amp; shop.', false)->assertSee('Plain Toner')->getContent();

        $this->assertStringContainsString('tp-reel-kb', $html);
        $this->assertStringNotContainsString('<video data-reel-video', $html);
        $this->assertStringNotContainsString('<dialog data-reel-dialog', $html);
    }

    // ── admin ────────────────────────────────────────────────────────

    public function test_admin_uploads_replaces_and_deletes_a_video_on_cloudinary(): void
    {
        $admin = $this->admin();
        $new = 'https://res.cloudinary.com/demo-cloud/video/upload/v2/timepass/products/videos/new_9.mp4';
        $this->api->shouldReceive('upload')->twice()
            ->withArgs(fn ($path, $o) => is_file($path) && $o['resource_type'] === 'video' && $o['folder'] === 'timepass/products/videos')
            ->andReturn(new ApiResponse(['secure_url' => self::VIDEO], []), new ApiResponse(['secure_url' => $new], []));

        $this->actingAs($admin)->post('/admin/products', [
            'name' => 'Glow Serum', 'category' => 'skin-care', 'price' => 499, 'video' => $this->clip(),
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.products.index'));
        $product = Product::where('name', 'Glow Serum')->sole();
        $this->assertSame(self::VIDEO, $product->video);

        // Replace: the old video is deleted (as a video) once the new one is saved.
        $this->api->shouldReceive('destroy')->once()
            ->with('timepass/products/videos/serum_ab12', Mockery::on(fn ($o) => $o['resource_type'] === 'video'))
            ->andReturn(new ApiResponse(['result' => 'ok'], []));
        $this->actingAs($admin)->put("/admin/products/{$product->id}", [
            'name' => 'Glow Serum', 'category' => 'skin-care', 'price' => 499, 'video' => $this->clip('new.mov', 900, 'video/quicktime'),
        ])->assertSessionHasNoErrors();
        $this->assertSame($new, $product->fresh()->video);

        // Deleting the product removes its video too.
        $this->api->shouldReceive('destroy')->once()->with('timepass/products/videos/new_9', Mockery::any())
            ->andReturn(new ApiResponse(['result' => 'ok'], []));
        $this->actingAs($admin)->delete("/admin/products/{$product->id}")->assertRedirect();
        $this->assertNull(Product::find($product->id));
    }

    public function test_editing_without_video_fields_keeps_the_video_and_remove_deletes_it(): void
    {
        $admin = $this->admin();
        $product = $this->product(['video' => self::VIDEO]);
        $base = ['name' => 'Glow Serum', 'category' => 'skin-care', 'price' => 449];

        $this->api->shouldNotReceive('upload');
        $this->actingAs($admin)->put("/admin/products/{$product->id}", $base)->assertSessionHasNoErrors();
        $this->assertSame(self::VIDEO, $product->fresh()->video);

        $this->api->shouldReceive('destroy')->once()->with('timepass/products/videos/serum_ab12', Mockery::any())
            ->andReturn(new ApiResponse(['result' => 'ok'], []));
        $this->actingAs($admin)->put("/admin/products/{$product->id}", $base + ['remove_video' => '1'])->assertSessionHasNoErrors();
        $this->assertNull($product->fresh()->video);
    }

    public function test_admin_can_paste_a_direct_video_link_but_not_a_page_link(): void
    {
        $admin = $this->admin();
        $product = $this->product();
        $base = ['name' => 'Glow Serum', 'category' => 'skin-care', 'price' => 499];

        $this->actingAs($admin)->put("/admin/products/{$product->id}", $base + ['video_url' => 'https://www.youtube.com/watch?v=abc'])
            ->assertSessionHasErrors(['video_url' => VideoUrl::LINK_MESSAGE]);
        $this->assertNull($product->fresh()->video);

        $this->api->shouldNotReceive('destroy'); // nothing to delete: the old value was empty
        $this->actingAs($admin)->put("/admin/products/{$product->id}", $base + ['video_url' => 'https://cdn.example.com/serum.mp4'])
            ->assertSessionHasNoErrors();
        $this->assertSame('https://cdn.example.com/serum.mp4', $product->fresh()->video);
    }

    public function test_video_uploads_are_validated(): void
    {
        $admin = $this->admin();
        $this->api->shouldNotReceive('upload');
        $base = ['name' => 'Bad', 'category' => 'skin-care', 'price' => 1];

        $this->actingAs($admin)->post('/admin/products', $base + ['video' => UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf')])
            ->assertSessionHasErrors(['video' => 'The video must be an MP4, MOV or WebM video.']);
        $this->actingAs($admin)->post('/admin/products', $base + ['video' => $this->clip('huge.mp4', 41 * 1024)])
            ->assertSessionHasErrors('video');
        $this->assertSame(0, Product::count());
    }

    public function test_a_failed_video_upload_keeps_the_record_and_cleans_up_the_new_image(): void
    {
        $admin = $this->admin();
        $image = 'https://res.cloudinary.com/demo-cloud/image/upload/v1/timepass/products/glow.png';
        $png = UploadedFile::fake()->createWithContent('glow.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='));

        $this->api->shouldReceive('upload')->with(Mockery::any(), Mockery::on(fn ($o) => $o['resource_type'] === 'image'))
            ->andReturn(new ApiResponse(['secure_url' => $image], []));
        $this->api->shouldReceive('upload')->with(Mockery::any(), Mockery::on(fn ($o) => $o['resource_type'] === 'video'))
            ->andThrow(new \Exception('timeout'));
        $this->api->shouldReceive('destroy')->once()->with('timepass/products/glow', Mockery::any())
            ->andReturn(new ApiResponse(['result' => 'ok'], []));

        $this->actingAs($admin)->post('/admin/products', [
            'name' => 'Glow Serum', 'category' => 'skin-care', 'price' => 499, 'image' => $png, 'video' => $this->clip(),
        ])->assertSessionHasErrors(['video' => 'Could not upload the video. Please try again.']);

        $this->assertSame(0, Product::count());
    }

    // ── MCP ──────────────────────────────────────────────────────────

    public function test_mcp_sets_replaces_and_clears_the_video_link(): void
    {
        Category::create(['name' => 'Skin Care', 'slug' => 'skin-care', 'types' => [], 'is_active' => true]);

        StoreServer::tool(CreateProduct::class, [
            'name' => 'Clip Serum', 'price' => 499, 'category_name' => 'Skin Care', 'image_source' => 'none', 'video_url' => self::VIDEO,
        ])->assertOk()->assertSee('"video_url": "'.self::VIDEO.'"');
        $product = Product::where('name', 'Clip Serum')->sole();
        $this->assertSame(self::VIDEO, $product->video);

        StoreServer::tool(UpdateProduct::class, ['product' => 'Clip Serum', 'video_url' => 'https://www.youtube.com/watch?v=x'])
            ->assertHasErrors([VideoUrl::LINK_MESSAGE]);

        // Replacing the Cloudinary upload deletes it; other changes leave the video alone.
        $this->api->shouldReceive('destroy')->once()->with('timepass/products/videos/serum_ab12', Mockery::on(fn ($o) => $o['resource_type'] === 'video'))
            ->andReturn(new ApiResponse(['result' => 'ok'], []));
        StoreServer::tool(UpdateProduct::class, ['product' => 'Clip Serum', 'video_url' => 'https://cdn.example.com/b.mp4'])->assertOk();
        StoreServer::tool(UpdateProduct::class, ['product' => 'Clip Serum', 'price' => 449])->assertOk();
        $this->assertSame('https://cdn.example.com/b.mp4', $product->fresh()->video);

        StoreServer::tool(UpdateProduct::class, ['product' => 'Clip Serum', 'video_url' => null])->assertOk();
        $this->assertNull($product->fresh()->video);
    }
}
