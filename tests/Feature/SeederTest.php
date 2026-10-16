<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * The deploy runs `db:seed` on every start. Seeding must fill an empty
 * database but never undo changes made later in the admin panel or via MCP.
 */
class SeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeding_again_never_overwrites_edits(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertGreaterThan(0, Category::count());
        $this->assertGreaterThan(0, Product::count());
        $this->assertGreaterThan(0, Review::count());

        // Simulate edits made after the first deploy.
        $edited = Product::orderBy('id')->first();
        $edited->update(['price' => 1, 'image' => 'media/999', 'is_active' => false]);

        $deleted = Product::orderBy('id')->skip(1)->first();
        $deletedSlug = $deleted->slug;
        $deleted->delete();

        $hiddenReview = Review::orderBy('id')->first();
        $hiddenReview->update(['is_active' => false]);
        Review::orderBy('id')->skip(1)->first()?->delete();
        $reviewCount = Review::count();

        $admin = User::where('email', 'unicropweb003@gmail.com')->firstOrFail();
        $admin->update(['password' => 'NewSecret#2026']);

        // Next deploy / cold start.
        $this->seed(DatabaseSeeder::class);

        $edited->refresh();
        $this->assertSame(1, (int) $edited->price, 'edited price is kept');
        $this->assertSame('media/999', $edited->image, 'edited image is kept');
        $this->assertFalse($edited->is_active, 'hidden product stays hidden');
        $this->assertFalse(Product::where('slug', $deletedSlug)->exists(), 'deleted product is not re-created');

        $this->assertFalse($hiddenReview->fresh()->is_active, 'hidden review stays hidden');
        $this->assertSame($reviewCount, Review::count(), 'deleted review is not re-created');

        $admin->refresh();
        $this->assertTrue(Hash::check('NewSecret#2026', $admin->password), 'admin password is not reset');
        $this->assertTrue($admin->is_admin);
    }
}
