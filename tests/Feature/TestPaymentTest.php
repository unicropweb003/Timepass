<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

// Admin-only checkout without Razorpay, used until the Razorpay keys are set.
class TestPaymentTest extends TestCase
{
    use RefreshDatabase;

    private const ADDRESS = [
        'quantity' => 1, 'address_id' => '', 'shipping_name' => 'Probe Buyer',
        'shipping_phone' => '9876543210', 'line1' => '12 Test Street', 'city' => 'Surat',
        'state' => 'Gujarat', 'pincode' => '395001',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.razorpay' => ['key_id' => '', 'key_secret' => '', 'webhook_secret' => '']]);
        Http::preventStrayRequests();
        Product::create([
            'name' => 'Probe Serum', 'slug' => 'probe-serum', 'category' => 'skin-care',
            'price' => 299, 'stock' => 5, 'is_active' => true,
        ]);
    }

    public function test_admin_can_complete_an_order_with_a_test_payment(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get('/checkout/probe-serum')->assertOk()
            ->assertSee('test payment')
            ->assertSee('Continue to payment');

        $this->actingAs($admin)->post('/checkout/probe-serum', self::ADDRESS)->assertSessionHasNoErrors();
        $order = Order::sole();
        $this->assertSame('Test payment', $order->payment_method);
        $this->assertNull($order->gateway_order_id);

        $this->actingAs($admin)->get(route('checkout.payment', $order->order_number))->assertOk()
            ->assertSee('Complete test payment')
            ->assertDontSee('checkout.razorpay.com');

        $this->actingAs($admin)->post(route('checkout.test-pay', $order->order_number))
            ->assertRedirect(route('account.orders.show', $order->order_number));

        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('placed', $order->status);
        $this->assertNotNull($order->paid_at);
        $transaction = $order->transactions()->sole();
        $this->assertSame('Test', $transaction->method);
        $this->assertStringStartsWith('test_', $transaction->reference);
        $this->assertEquals(348, $transaction->amount);

        // Paying twice does not add a second transaction.
        $this->actingAs($admin)->post(route('checkout.test-pay', $order->order_number));
        $this->assertSame(1, $order->transactions()->count());
    }

    public function test_customers_still_cannot_check_out_without_razorpay(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)->get('/checkout/probe-serum')->assertOk()
            ->assertSee('Payment unavailable')
            ->assertDontSee('test payment');

        $this->actingAs($customer)->post('/checkout/probe-serum', self::ADDRESS)->assertSessionHasErrors('payment');
        $this->assertSame(0, Order::count());
    }

    public function test_customers_cannot_use_the_test_payment(): void
    {
        $customer = User::factory()->create();
        $order = $customer->orders()->create([
            'order_number' => 'TP-TEST-1', 'status' => 'pending_payment', 'subtotal' => 299,
            'shipping' => 49, 'discount' => 0, 'total' => 348, 'payment_method' => 'Test payment',
            'payment_status' => 'pending', 'shipping_name' => 'Probe Buyer',
            'shipping_phone' => '9876543210', 'shipping_address' => '12 Test Street, Surat',
        ]);

        $this->actingAs($customer)->get(route('checkout.payment', $order->order_number))->assertNotFound();
        $this->actingAs($customer)->post(route('checkout.test-pay', $order->order_number))->assertForbidden();
        $this->assertSame('pending', $order->fresh()->payment_status);
    }

    public function test_test_payment_turns_off_once_razorpay_is_configured(): void
    {
        config(['services.razorpay.key_id' => 'rzp_test_probe', 'services.razorpay.key_secret' => 'probe_secret']);
        $admin = User::factory()->create(['is_admin' => true]);
        $order = $admin->orders()->create([
            'order_number' => 'TP-TEST-2', 'status' => 'pending_payment', 'subtotal' => 299,
            'shipping' => 49, 'discount' => 0, 'total' => 348, 'payment_method' => 'Test payment',
            'payment_status' => 'pending', 'shipping_name' => 'Probe Buyer',
            'shipping_phone' => '9876543210', 'shipping_address' => '12 Test Street, Surat',
        ]);

        $this->actingAs($admin)->get('/checkout/probe-serum')->assertOk()->assertDontSee('test payment');
        $this->actingAs($admin)->post(route('checkout.test-pay', $order->order_number))->assertForbidden();
        $this->assertSame('pending', $order->fresh()->payment_status);
    }
}
