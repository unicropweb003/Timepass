<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

// Runs the whole Razorpay checkout flow against a faked Razorpay API.
class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.razorpay' => [
            'key_id' => 'rzp_test_probe',
            'key_secret' => 'probe_secret',
            'webhook_secret' => 'probe_webhook',
        ]]);
        Product::create([
            'name' => 'Probe Serum', 'slug' => 'probe-serum', 'category' => 'skin-care',
            'price' => 299, 'stock' => 5, 'is_active' => true,
        ]);
    }

    /** 299 rupees is below the free-shipping threshold, so total = 299 + 49 = 348 (34800 paise). */
    private function startOrder(User $user): Order
    {
        Http::fake([
            'api.razorpay.com/v1/orders' => Http::response([
                'id' => 'order_Probe123', 'entity' => 'order', 'amount' => 34800,
                'currency' => 'INR', 'receipt' => 'x', 'status' => 'created',
            ]),
            'api.razorpay.com/v1/payments/*' => Http::response([
                'id' => 'pay_Probe456', 'order_id' => 'order_Probe123', 'status' => 'captured',
                'currency' => 'INR', 'amount' => 34800, 'method' => 'upi',
            ]),
        ]);

        $this->actingAs($user)->get('/checkout/probe-serum')->assertOk()->assertSee('Continue to payment');

        $response = $this->actingAs($user)->post('/checkout/probe-serum', [
            'quantity' => 1, 'address_id' => '', 'shipping_name' => 'Probe Buyer',
            'shipping_phone' => '9876543210', 'line1' => '12 Test Street', 'city' => 'Surat',
            'state' => 'Gujarat', 'pincode' => '395001',
        ]);
        $response->assertSessionHasNoErrors();
        $order = Order::sole();
        $response->assertRedirect(route('checkout.payment', $order->order_number));

        return $order;
    }

    public function test_checkout_creates_order_opens_payment_and_confirms_payment(): void
    {
        $user = User::factory()->create();
        $order = $this->startOrder($user);

        $this->assertSame('pending_payment', $order->status);
        $this->assertSame('order_Probe123', $order->gateway_order_id);
        $this->assertSame('348.00', (string) $order->total);
        $this->assertSame(1, $order->items()->count());

        $this->actingAs($user)->get(route('checkout.payment', $order->order_number))
            ->assertOk()->assertSee('rzp_test_probe')->assertSee('order_Probe123')->assertSee('checkout.razorpay.com');

        $signature = hash_hmac('sha256', 'order_Probe123|pay_Probe456', 'probe_secret');
        $this->actingAs($user)->postJson(route('checkout.confirm', $order->order_number), [
            'razorpay_order_id' => 'order_Probe123',
            'razorpay_payment_id' => 'pay_Probe456',
            'razorpay_signature' => $signature,
        ])->assertOk()->assertJson(['status' => 'paid']);

        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('placed', $order->status);
        $this->assertSame(1, $order->transactions()->count());

        $this->actingAs($user)->get(route('account.orders.show', $order->order_number))->assertOk();
        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get(route('admin.orders.show', $order))->assertOk();
    }

    public function test_webhook_marks_a_pending_order_paid(): void
    {
        $order = $this->startOrder(User::factory()->create());

        $body = json_encode(['event' => 'payment.captured', 'payload' => ['payment' => ['entity' => [
            'id' => 'pay_Hook789', 'order_id' => 'order_Probe123', 'status' => 'captured',
            'currency' => 'INR', 'amount' => 34800, 'method' => 'card',
        ]]]]);

        $this->call('POST', '/payments/razorpay/webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_RAZORPAY_SIGNATURE' => hash_hmac('sha256', $body, 'probe_webhook'),
        ], $body)->assertOk()->assertJson(['status' => 'ok']);

        $this->assertSame('paid', $order->fresh()->payment_status);
    }

    public function test_wrong_signature_is_rejected(): void
    {
        $user = User::factory()->create();
        $order = $this->startOrder($user);

        $this->actingAs($user)->postJson(route('checkout.confirm', $order->order_number), [
            'razorpay_order_id' => 'order_Probe123',
            'razorpay_payment_id' => 'pay_Probe456',
            'razorpay_signature' => str_repeat('a', 64),
        ])->assertStatus(422);

        $this->assertSame('pending', $order->fresh()->payment_status);
    }
}
