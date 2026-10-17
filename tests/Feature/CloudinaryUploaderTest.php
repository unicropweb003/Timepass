<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\CloudinaryUploader;
use App\Support\ImageUrl;
use Cloudinary\Api\ApiResponse;
use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Cloudinary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Mockery;
use Mockery\MockInterface;
use RuntimeException;
use Tests\TestCase;

/**
 * Cloudinary uploads, with a mocked client: no real API calls, no credentials.
 */
class CloudinaryUploaderTest extends TestCase
{
    use RefreshDatabase;

    private const PNG_B64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    private const URL = 'https://res.cloudinary.com/demo-cloud/image/upload/v1712345678/timepass/products/serum_ab12cd.png';

    private MockInterface $api;

    protected function setUp(): void
    {
        parent::setUp();

        config(['filesystems.disks.cloudinary.folder' => 'timepass']);

        $this->api = Mockery::mock(UploadApi::class);
        $client = Mockery::mock(Cloudinary::class);
        $client->shouldReceive('uploadApi')->andReturn($this->api);

        $this->app->instance(CloudinaryUploader::class, new CloudinaryUploader($client));
    }

    private function png(string $name = 'serum.png'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, base64_decode(self::PNG_B64));
    }

    private function uploader(): CloudinaryUploader
    {
        return $this->app->make(CloudinaryUploader::class);
    }

    // ── service ──────────────────────────────────────────────────────

    public function test_upload_sends_the_file_to_the_folder_and_returns_the_secure_url(): void
    {
        $this->api->shouldReceive('upload')->once()
            ->withArgs(fn ($path, $options) => is_file($path)
                && $options['folder'] === 'timepass/products'
                && $options['resource_type'] === 'image')
            ->andReturn(new ApiResponse(['secure_url' => self::URL, 'public_id' => 'timepass/products/serum_ab12cd'], []));

        $this->assertSame(self::URL, $this->uploader()->upload($this->png(), 'products'));
    }

    public function test_upload_fails_loudly_without_a_secure_url(): void
    {
        $this->api->shouldReceive('upload')->andReturn(new ApiResponse(['url' => 'http://insecure.example/x.png'], []));

        $this->expectException(RuntimeException::class);
        $this->uploader()->upload($this->png(), 'products');
    }

    public function test_upload_without_credentials_says_so(): void
    {
        config(['filesystems.disks.cloudinary.url' => null]);

        $this->expectExceptionMessage('Cloudinary is not configured');
        (new CloudinaryUploader)->upload($this->png(), 'products');
    }

    public function test_delete_destroys_by_public_id(): void
    {
        $this->api->shouldReceive('destroy')->once()
            ->with('timepass/products/serum_ab12cd', Mockery::on(fn ($o) => $o['resource_type'] === 'image'))
            ->andReturn(new ApiResponse(['result' => 'ok'], []));

        $this->uploader()->delete(self::URL);
    }

    public function test_delete_ignores_null_local_paths_and_other_hosts(): void
    {
        $this->api->shouldNotReceive('destroy');

        foreach ([null, '', 'images/products/a.jpg', 'storage/a.jpg', 'media/12', 'https://example.com/a.jpg'] as $value) {
            $this->uploader()->delete($value);
        }
        $this->addToAssertionCount(1);
    }

    public function test_delete_failures_are_logged_never_thrown_and_secrets_are_redacted(): void
    {
        config(['filesystems.disks.cloudinary.url' => 'cloudinary://111222333:topsecretvalue@demo-cloud']);
        $this->api->shouldReceive('destroy')->andThrow(new \Exception('Invalid api_key 111222333 / topsecretvalue'));

        Log::spy();
        $this->uploader()->delete(self::URL);

        Log::shouldHaveReceived('warning')->once()->withArgs(function ($message, $context) {
            return $message === 'Cloudinary delete failed'
                && ! str_contains($context['error'], '111222333')
                && ! str_contains($context['error'], 'topsecretvalue');
        });
    }

    public function test_public_id_is_parsed_from_common_url_shapes(): void
    {
        $cases = [
            self::URL => ['image', 'timepass/products/serum_ab12cd'],
            'https://res.cloudinary.com/demo/image/upload/c_fill,w_300/v1/my_folder/a_b.webp' => ['image', 'my_folder/a_b'],
            'https://res.cloudinary.com/demo/image/upload/my_folder/photo.jpg' => ['image', 'my_folder/photo'],
            'https://res.cloudinary.com/demo/image/upload/w_200/sample.jpg' => ['image', 'sample'],
            'https://res.cloudinary.com/demo/raw/upload/v5/docs/file.pdf' => ['raw', 'docs/file.pdf'],
        ];

        foreach ($cases as $url => [$type, $id]) {
            $this->assertSame(['resource_type' => $type, 'public_id' => $id], CloudinaryUploader::parseUrl($url), $url);
        }
    }

    public function test_image_url_keeps_old_paths_working(): void
    {
        $this->assertSame(self::URL, ImageUrl::resolve(self::URL));
        $this->assertSame(asset('images/products/a.jpg'), ImageUrl::resolve('images/products/a.jpg'));
        $this->assertSame(asset('media/12'), ImageUrl::resolve('/media/12'));
        $this->assertNull(ImageUrl::resolve(null));
        $this->assertSame(self::URL, (new Product(['image' => self::URL]))->image_url);
    }

    // ── admin product upload ─────────────────────────────────────────

    private function admin(): User
    {
        Category::create(['name' => 'Skin Care', 'slug' => 'skin-care', 'types' => [], 'is_active' => true]);

        return User::factory()->create(['is_admin' => true]);
    }

    public function test_admin_upload_stores_the_cloudinary_url_and_replacing_deletes_the_old_one(): void
    {
        $admin = $this->admin();
        $new = 'https://res.cloudinary.com/demo-cloud/image/upload/v2/timepass/products/new_1.png';
        $this->api->shouldReceive('upload')->twice()->andReturn(
            new ApiResponse(['secure_url' => self::URL], []),
            new ApiResponse(['secure_url' => $new], []),
        );

        $this->actingAs($admin)->post('/admin/products', [
            'name' => 'Glow Serum', 'category' => 'skin-care', 'price' => 499, 'image' => $this->png(),
        ])->assertRedirect(route('admin.products.index'));

        $product = Product::where('name', 'Glow Serum')->sole();
        $this->assertSame(self::URL, $product->image);

        // Replace: the old file is deleted only after the new URL is saved.
        $this->api->shouldReceive('destroy')->once()->with('timepass/products/serum_ab12cd', Mockery::any())
            ->andReturn(new ApiResponse(['result' => 'ok'], []));
        $this->actingAs($admin)->put("/admin/products/{$product->id}", [
            'name' => 'Glow Serum', 'category' => 'skin-care', 'price' => 499, 'image' => $this->png('new.png'),
        ])->assertRedirect();
        $this->assertSame($new, $product->fresh()->image);

        // Delete the product: its image goes too.
        $this->api->shouldReceive('destroy')->once()->with('timepass/products/new_1', Mockery::any())
            ->andReturn(new ApiResponse(['result' => 'ok'], []));
        $this->actingAs($admin)->delete("/admin/products/{$product->id}")->assertRedirect();
        $this->assertNull(Product::find($product->id));
    }

    public function test_editing_without_a_new_file_keeps_the_image(): void
    {
        $admin = $this->admin();
        $product = Product::create(['name' => 'Toner', 'slug' => 'toner', 'category' => 'skin-care', 'price' => 99, 'image' => 'images/products/toner.jpg']);
        $this->api->shouldNotReceive('upload');
        $this->api->shouldNotReceive('destroy');

        $this->actingAs($admin)->put("/admin/products/{$product->id}", ['name' => 'Toner', 'category' => 'skin-care', 'price' => 89]);

        $this->assertSame('images/products/toner.jpg', $product->fresh()->image);
    }

    public function test_uploads_are_validated(): void
    {
        $admin = $this->admin();
        $this->api->shouldNotReceive('upload');
        $base = ['name' => 'Bad', 'category' => 'skin-care', 'price' => 1];

        $this->actingAs($admin)->from('/admin/products/create')
            ->post('/admin/products', $base + ['image' => UploadedFile::fake()->create('a.gif', 10, 'image/gif')])
            ->assertSessionHasErrors(['image' => 'The image must be a JPG, JPEG, PNG or WebP image.']);

        $this->actingAs($admin)->from('/admin/products/create')
            ->post('/admin/products', $base + ['image' => UploadedFile::fake()->create('big.png', 6000, 'image/png')])
            ->assertSessionHasErrors('image');

        $this->assertSame(0, Product::where('name', 'Bad')->count());
    }

    public function test_a_failed_upload_is_a_clear_validation_error(): void
    {
        $admin = $this->admin();
        $this->api->shouldReceive('upload')->andThrow(new \Exception('network down'));

        $this->actingAs($admin)->from('/admin/products/create')->post('/admin/products', [
            'name' => 'Glow Serum', 'category' => 'skin-care', 'price' => 499, 'image' => $this->png(),
        ])->assertSessionHasErrors(['image' => 'Could not upload the image. Please try again.']);

        $this->assertSame(0, Product::count());
    }
}
