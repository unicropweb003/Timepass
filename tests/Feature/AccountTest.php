<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AccountDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_login(): void
    {
        foreach (['/account', '/account/orders', '/account/transactions', '/account/addresses', '/account/profile'] as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }

    public function test_account_pages_load_with_empty_states(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/account')->assertOk()->assertSee('No orders yet');
        $this->actingAs($user)->get('/account/orders')->assertOk()->assertSee('No orders yet');
        $this->actingAs($user)->get('/account/transactions')->assertOk()->assertSee('No transactions yet');
        $this->actingAs($user)->get('/account/addresses')->assertOk()->assertSee('No addresses yet');
        $this->actingAs($user)->get('/account/profile')->assertOk()->assertSee('Change password');
    }

    public function test_navbar_shows_account_menu_after_login(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/')->assertOk()
            ->assertSee(route('account.orders'))
            ->assertSee(route('account.transactions'))
            ->assertSee(route('account.addresses'))
            ->assertSee(route('account.profile'));
    }

    public function test_orders_transactions_and_delivery_details_show_with_demo_data(): void
    {
        $user = User::factory()->create();
        $this->seed(AccountDemoSeeder::class);

        $order = $user->orders()->where('status', 'shipped')->firstOrFail();

        $this->actingAs($user)->get('/account/orders')->assertOk()->assertSee($order->order_number)->assertSee('Shipped');
        $this->actingAs($user)->get(route('account.orders.show', $order->order_number))->assertOk()
            ->assertSee('Delivery status')
            ->assertSee('Out for delivery')
            ->assertSee('Vitamin C Brightening Serum')
            ->assertSee($order->tracking_number)
            ->assertSee('Ahmedabad');
        $this->actingAs($user)->get('/account/transactions')->assertOk()->assertSee('UPI');
        $this->actingAs($user)->get('/account')->assertOk()->assertSee('Total spent');
    }

    public function test_customers_cannot_see_other_customers_orders(): void
    {
        $owner = User::factory()->create();
        $this->seed(AccountDemoSeeder::class);
        $order = $owner->orders()->firstOrFail();

        $this->actingAs(User::factory()->create())
            ->get(route('account.orders.show', $order->order_number))
            ->assertNotFound();
    }

    public function test_addresses_can_be_added_made_default_and_removed(): void
    {
        $user = User::factory()->create();
        $address = [
            'label' => 'Home', 'full_name' => 'Test User', 'phone' => '9876543210',
            'line1' => '1 Main Road', 'city' => 'Surat', 'state' => 'Gujarat', 'pincode' => '395003',
        ];

        $this->actingAs($user)->post('/account/addresses', $address)->assertRedirect('/account/addresses');
        $this->actingAs($user)->post('/account/addresses', ['label' => 'Work', 'line1' => '2 Ring Road'] + $address);
        $this->assertTrue($user->addresses()->where('line1', '1 Main Road')->value('is_default'), 'First address becomes default');

        $work = $user->addresses()->where('label', 'Work')->first();
        $this->actingAs($user)->patch(route('account.addresses.default', $work));
        $this->assertTrue($work->fresh()->is_default);
        $this->assertSame(1, $user->addresses()->where('is_default', true)->count());

        $this->actingAs($user)->delete(route('account.addresses.destroy', $work));
        $this->assertSame(1, $user->addresses()->count());
        $this->assertTrue($user->addresses()->first()->is_default, 'Remaining address becomes default');

        $this->actingAs($user)->post('/account/addresses', ['pincode' => '12'] + $address)->assertSessionHasErrors('pincode');
    }

    public function test_other_customers_addresses_cannot_be_changed(): void
    {
        $owner = User::factory()->create();
        $address = $owner->addresses()->create([
            'label' => 'Home', 'full_name' => 'Owner', 'phone' => '9876543210',
            'line1' => '1 Main Road', 'city' => 'Surat', 'state' => 'Gujarat', 'pincode' => '395003',
        ]);

        $this->actingAs(User::factory()->create())->delete(route('account.addresses.destroy', $address))->assertNotFound();
        $this->assertNotNull($address->fresh());
    }

    public function test_profile_and_password_can_be_updated(): void
    {
        $user = User::factory()->create(['password' => 'oldpass123']);

        $this->actingAs($user)->put('/account/profile', ['name' => 'New Name', 'phone' => '9123456780', 'email' => 'new@example.com'])
            ->assertSessionHas('status', 'Profile updated.');
        $this->assertSame('new@example.com', $user->fresh()->email);

        $this->actingAs($user)->put('/account/password', ['current_password' => 'wrong', 'password' => 'newpass123', 'password_confirmation' => 'newpass123'])
            ->assertSessionHasErrorsIn('password', 'current_password');

        $this->actingAs($user)->put('/account/password', ['current_password' => 'oldpass123', 'password' => 'newpass123', 'password_confirmation' => 'newpass123'])
            ->assertSessionHas('status', 'Password changed.');
        $this->assertTrue(Hash::check('newpass123', $user->fresh()->password));
    }
}
