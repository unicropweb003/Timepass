<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Transaction;
use App\Services\RazorpayGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class CheckoutController extends Controller
{
    private const SHIPPING_FEE_PAISE = 4900;

    private const FREE_SHIPPING_FROM_PAISE = 49900;

    private const TEST_PAYMENT_METHOD = 'Test payment';

    public function show(Request $request, string $product, RazorpayGateway $gateway)
    {
        $product = $this->purchasableProduct($product);

        return view('checkout', [
            'product' => $product,
            'addresses' => $request->user()->addresses()->orderByDesc('is_default')->latest()->get(),
            'gatewayReady' => $gateway->configured(),
            'testPayment' => $this->testPaymentAllowed($request, $gateway),
            'shippingFee' => self::SHIPPING_FEE_PAISE / 100,
            'freeShippingThreshold' => self::FREE_SHIPPING_FROM_PAISE / 100,
        ]);
    }

    public function start(Request $request, string $product, RazorpayGateway $gateway)
    {
        $product = $this->purchasableProduct($product);
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:10'],
            'address_id' => ['nullable', 'integer'],
            'shipping_name' => ['required_without:address_id', 'nullable', 'string', 'max:255'],
            'shipping_phone' => ['required_without:address_id', 'nullable', 'regex:/^[0-9]{10,15}$/'],
            'line1' => ['required_without:address_id', 'nullable', 'string', 'max:255'],
            'line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required_without:address_id', 'nullable', 'string', 'max:100'],
            'state' => ['required_without:address_id', 'nullable', 'string', 'max:100'],
            'pincode' => ['required_without:address_id', 'nullable', 'regex:/^[0-9]{6}$/'],
        ]);

        $quantity = (int) $data['quantity'];
        if ($product->stock !== null && $quantity > $product->stock) {
            throw ValidationException::withMessages(['quantity' => 'Only '.$product->stock.' units are currently available.']);
        }

        if (isset($data['address_id'])) {
            $address = $request->user()->addresses()->findOrFail($data['address_id']);
            $shippingName = $address->full_name;
            $shippingPhone = $address->phone;
            $shippingAddress = $address->oneLine();
        } else {
            $shippingName = $data['shipping_name'];
            $shippingPhone = $data['shipping_phone'];
            $shippingAddress = collect([
                $data['line1'], $data['line2'] ?? null, $data['city'],
                $data['state'].' - '.$data['pincode'],
            ])->filter()->implode(', ');
        }

        $testPayment = $this->testPaymentAllowed($request, $gateway);
        if (! $gateway->configured() && ! $testPayment) {
            return back()->withInput()->withErrors(['payment' => 'Online payment is being set up. Please try again later.']);
        }

        // Prices and shipping are calculated here from the active database product.
        // Browser-supplied prices are never accepted.
        $subtotalPaise = ((int) $product->price) * $quantity * 100;
        $shippingPaise = $subtotalPaise >= self::FREE_SHIPPING_FROM_PAISE ? 0 : self::SHIPPING_FEE_PAISE;
        $totalPaise = $subtotalPaise + $shippingPaise;
        if ($subtotalPaise <= 0 || $totalPaise > 9_999_999_999) {
            throw ValidationException::withMessages(['quantity' => 'This order cannot be checked out online.']);
        }

        $orderNumber = 'TP-'.now()->format('ymd').'-'.Str::upper(Str::random(10));
        $gatewayOrder = null;
        if (! $testPayment) {
            try {
                $gatewayOrder = $gateway->createOrder($totalPaise, $orderNumber);
            } catch (Throwable $e) {
                report($e);

                return back()->withInput()->withErrors(['payment' => 'The payment gateway is unavailable right now. Please try again shortly.']);
            }
        }

        // Recheck price and stock after the external API call. If either changed,
        // this gateway order is never exposed to the customer and cannot be paid.
        $order = DB::transaction(function () use (
            $request, $product, $quantity, $subtotalPaise, $shippingPaise, $totalPaise,
            $shippingName, $shippingPhone, $shippingAddress, $gatewayOrder, $orderNumber, $testPayment
        ) {
            $current = Product::query()->whereKey($product->id)->lockForUpdate()->firstOrFail();
            if (! $current->is_active
                || ($current->stock !== null && $current->stock < $quantity)
                || ((int) $current->price) * $quantity * 100 !== $subtotalPaise) {
                throw ValidationException::withMessages(['quantity' => 'Product availability or price changed. Please review and try again.']);
            }

            $order = $request->user()->orders()->create([
                'order_number' => $orderNumber,
                'status' => 'pending_payment',
                'subtotal' => $subtotalPaise / 100,
                'shipping' => $shippingPaise / 100,
                'discount' => 0,
                'total' => $totalPaise / 100,
                'payment_method' => $testPayment ? self::TEST_PAYMENT_METHOD : 'Razorpay',
                'payment_status' => 'pending',
                'gateway_order_id' => $gatewayOrder['id'] ?? null,
                'shipping_name' => $shippingName,
                'shipping_phone' => $shippingPhone,
                'shipping_address' => $shippingAddress,
            ]);
            $order->items()->create([
                'product_name' => $current->name,
                'category' => $current->category,
                'type' => $current->type,
                'image' => $current->image,
                'quantity' => $quantity,
                'price' => $current->price,
            ]);

            return $order;
        });

        return redirect()->route('checkout.payment', $order->order_number);
    }

    public function payment(Request $request, string $orderNumber, RazorpayGateway $gateway)
    {
        $order = $request->user()->orders()->with('items')->where('order_number', $orderNumber)->firstOrFail();
        if ($order->payment_status === 'paid') {
            return redirect()->route('account.orders.show', $order->order_number);
        }
        abort_unless($order->status === 'pending_payment', 404);

        if ($order->payment_method === self::TEST_PAYMENT_METHOD) {
            abort_unless($this->testPaymentAllowed($request, $gateway), 404);

            return view('checkout.payment', ['order' => $order, 'testPayment' => true]);
        }

        abort_unless($order->gateway_order_id, 404);
        abort_unless($gateway->configured(), 503);

        $keyId = $gateway->keyId();
        $checkoutPayload = [
            'order_id' => $order->gateway_order_id,
            'amount' => (int) round((float) $order->total * 100),
            'currency' => 'INR',
            'name' => 'TimePass',
            'description' => 'Order '.$order->order_number,
            'prefill' => [
                'name' => $order->shipping_name,
                'email' => $request->user()->email,
                'contact' => $order->shipping_phone,
            ],
            'theme' => ['color' => '#7c3aed'],
        ];

        return view('checkout.payment', compact('order', 'keyId', 'checkoutPayload') + ['testPayment' => false]);
    }

    /**
     * Marks an admin's test order as paid without Razorpay. No money is taken;
     * the order and its transaction are labelled as a test.
     */
    public function testPay(Request $request, string $orderNumber, RazorpayGateway $gateway)
    {
        abort_unless($this->testPaymentAllowed($request, $gateway), 403);
        $order = $request->user()->orders()->where('order_number', $orderNumber)->firstOrFail();

        DB::transaction(function () use ($order) {
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($locked->payment_status === 'paid') {
                return;
            }
            abort_unless($locked->status === 'pending_payment' && $locked->payment_method === self::TEST_PAYMENT_METHOD, 404);

            $locked->transactions()->create([
                'user_id' => $locked->user_id,
                'reference' => 'test_'.Str::lower(Str::random(20)),
                'type' => 'payment',
                'method' => 'Test',
                'amount' => $locked->total,
                'status' => 'success',
            ]);
            $locked->update([
                'payment_status' => 'paid',
                'paid_at' => now(),
                'status' => 'placed',
            ]);
        });

        return redirect()->route('account.orders.show', $order->order_number)
            ->with('status', 'Test payment completed. No money was taken.');
    }

    public function confirm(Request $request, string $orderNumber, RazorpayGateway $gateway): JsonResponse
    {
        $order = $request->user()->orders()->where('order_number', $orderNumber)->firstOrFail();
        $data = $request->validate([
            'razorpay_order_id' => ['required', 'string', 'max:50'],
            'razorpay_payment_id' => ['required', 'string', 'max:40'],
            'razorpay_signature' => ['required', 'string', 'regex:/^[a-f0-9]{64}$/i'],
        ]);

        if ($order->gateway_order_id !== $data['razorpay_order_id']
            || ! $gateway->validCheckoutSignature(
                $order->gateway_order_id, $data['razorpay_payment_id'], $data['razorpay_signature']
            )) {
            return response()->json(['message' => 'Payment verification failed.'], 422);
        }

        try {
            $payment = $gateway->fetchPayment($data['razorpay_payment_id']);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Payment is being verified. Please check your order shortly.'], 503);
        }

        if (($payment['status'] ?? null) === 'captured') {
            if (! $this->recordCapturedPayment($order, $payment)) {
                return response()->json(['message' => 'Payment details did not match this order.'], 422);
            }

            return response()->json([
                'status' => 'paid',
                'redirect' => route('account.orders.show', $order->order_number),
            ]);
        }

        return response()->json([
            'status' => 'pending',
            'redirect' => route('account.orders.show', $order->order_number),
        ], 202);
    }

    public function webhook(Request $request, RazorpayGateway $gateway): JsonResponse
    {
        $body = $request->getContent();
        $signature = (string) $request->header('X-Razorpay-Signature', '');
        if (! $gateway->validWebhookSignature($body, $signature)) {
            return response()->json(['message' => 'Invalid signature.'], 400);
        }

        $event = json_decode($body, true);
        if (! is_array($event)) {
            return response()->json(['message' => 'Invalid payload.'], 400);
        }
        if (($event['event'] ?? null) !== 'payment.captured') {
            return response()->json(['status' => 'ignored']);
        }

        $payment = data_get($event, 'payload.payment.entity');
        if (! is_array($payment) || ! is_string($payment['order_id'] ?? null)) {
            return response()->json(['message' => 'Invalid payment payload.'], 400);
        }

        $order = Order::query()->where('gateway_order_id', $payment['order_id'])->first();
        if (! $order) {
            return response()->json(['status' => 'unknown order']);
        }
        if (! $this->recordCapturedPayment($order, $payment)) {
            return response()->json(['message' => 'Payment details did not match the order.'], 422);
        }

        return response()->json(['status' => 'ok']);
    }

    /** Until the Razorpay keys are set, admins (and only admins) can check out with a test payment. */
    private function testPaymentAllowed(Request $request, RazorpayGateway $gateway): bool
    {
        $user = $request->user();

        return ! $gateway->configured() && $user->is_admin && ! $user->is_blocked;
    }

    private function purchasableProduct(string $slug): Product
    {
        $product = Product::query()->where('slug', $slug)->where('is_active', true)->firstOrFail();
        abort_if($product->stock !== null && $product->stock < 1, 404);

        return $product;
    }

    private function recordCapturedPayment(Order $order, array $payment): bool
    {
        $paymentId = $payment['id'] ?? null;
        if (! is_string($paymentId) || ! preg_match('/\Apay_[A-Za-z0-9]+\z/', $paymentId)
            || strlen($paymentId) > 40
            || ($payment['order_id'] ?? null) !== $order->gateway_order_id
            || ($payment['status'] ?? null) !== 'captured'
            || ($payment['currency'] ?? null) !== 'INR'
            || ($payment['amount'] ?? null) !== (int) round((float) $order->total * 100)) {
            return false;
        }

        DB::transaction(function () use ($order, $payment, $paymentId) {
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($locked->payment_status === 'paid') {
                return;
            }
            if ($locked->status !== 'pending_payment' || $locked->gateway_order_id !== $payment['order_id']) {
                throw new RuntimeException('A captured payment cannot be applied to this order.');
            }

            $existing = Transaction::query()->where('reference', $paymentId)->first();
            if ($existing && $existing->order_id !== $locked->id) {
                throw new RuntimeException('Payment reference is already assigned to another order.');
            }
            if (! $existing) {
                $locked->transactions()->create([
                    'user_id' => $locked->user_id,
                    'reference' => $paymentId,
                    'type' => 'payment',
                    'method' => Str::limit(ucfirst((string) ($payment['method'] ?? 'Razorpay')), 30, ''),
                    'amount' => $locked->total,
                    'status' => 'success',
                ]);
            }
            $locked->update([
                'payment_status' => 'paid',
                'paid_at' => now(),
                'status' => 'placed',
            ]);
        });

        return true;
    }
}
