@extends('layouts.app', ['title' => 'Pay for order '.$order->order_number])

@section('content')
<div class="mx-auto max-w-5xl pb-14">
    <nav class="mb-6 flex items-center gap-2 text-sm text-gray-500" aria-label="Breadcrumb">
        <a href="{{ route('products') }}" class="hover:text-indigo-700">Products</a>
        <span aria-hidden="true">/</span>
        <span class="font-medium text-gray-900">Payment</span>
    </nav>

    <div class="mb-8">
        <p class="text-xs font-bold uppercase tracking-[0.22em] text-indigo-600">Order {{ $order->order_number }}</p>
        <h1 class="font-display mt-2 text-4xl font-semibold tracking-tight text-gray-950 sm:text-5xl">Ready to pay?</h1>
        <p class="mt-3 max-w-2xl text-gray-600">Check your delivery and total, then open the secure Razorpay payment window. Your order is confirmed after the payment is verified.</p>
    </div>

    @if ($errors->any())
        <div role="alert" class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif
    @if (session('status'))
        <p role="status" class="mb-6 rounded-2xl border border-indigo-200 bg-indigo-50 p-4 text-sm text-indigo-800">{{ session('status') }}</p>
    @endif

    <div class="grid items-start gap-8 lg:grid-cols-[minmax(0,1fr)_21rem]">
        <div class="space-y-6">
            <section class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm sm:p-7" aria-labelledby="items-heading">
                <div class="flex items-center gap-4">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-600 text-sm font-bold text-white">2</span>
                    <div>
                        <h2 id="items-heading" class="font-display text-2xl font-semibold text-gray-950">Your order</h2>
                        <p class="mt-1 text-sm text-gray-500">Review the items and amount due.</p>
                    </div>
                </div>
                <ul class="mt-6 divide-y divide-gray-100 border-t border-gray-100">
                    @foreach ($order->items as $item)
                        <li class="flex items-center gap-4 py-4">
                            <img src="{{ $item->image ? \App\Support\ImageUrl::resolve($item->image) : asset('images/no-image.svg') }}" alt="" class="h-16 w-16 shrink-0 rounded-xl border border-gray-100 bg-gray-50 object-contain p-1.5">
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-gray-900">{{ $item->product_name }}</p>
                                <p class="mt-1 text-sm text-gray-500">Quantity {{ $item->quantity }} × ₹{{ number_format($item->price, 2) }}</p>
                            </div>
                            <p class="shrink-0 font-semibold text-gray-900">₹{{ number_format($item->price * $item->quantity, 2) }}</p>
                        </li>
                    @endforeach
                </ul>
            </section>

            <section class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm sm:p-7" aria-labelledby="address-heading">
                <h2 id="address-heading" class="font-display text-2xl font-semibold text-gray-950">Delivery details</h2>
                <p class="mt-4 font-semibold text-gray-900">{{ $order->shipping_name }}</p>
                <p class="mt-1 text-sm leading-relaxed text-gray-600">{{ $order->shipping_address }}</p>
                <p class="mt-1 text-sm text-gray-500">Phone: {{ $order->shipping_phone }}</p>
            </section>
        </div>

        <aside class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm lg:sticky lg:top-28 sm:p-6" aria-labelledby="payment-heading">
            <h2 id="payment-heading" class="font-display text-2xl font-semibold text-gray-950">Payment summary</h2>
            <dl class="mt-5 space-y-3 text-sm">
                <div class="flex justify-between text-gray-600"><dt>Items subtotal</dt><dd>₹{{ number_format($order->subtotal, 2) }}</dd></div>
                <div class="flex justify-between text-gray-600"><dt>Shipping</dt><dd>{{ (float) $order->shipping === 0.0 ? 'Free' : '₹'.number_format($order->shipping, 2) }}</dd></div>
                @if ((float) $order->discount > 0)
                    <div class="flex justify-between text-emerald-700"><dt>Discount</dt><dd>−₹{{ number_format($order->discount, 2) }}</dd></div>
                @endif
                <div class="flex justify-between border-t border-gray-100 pt-4 text-lg font-bold text-gray-950"><dt>Total to pay</dt><dd>₹{{ number_format($order->total, 2) }}</dd></div>
            </dl>

            <button type="button" id="pay-button" disabled class="mt-6 flex w-full cursor-not-allowed items-center justify-center rounded-xl bg-gray-200 px-5 py-3.5 text-sm font-bold text-gray-500 transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Loading secure payment…</button>
            <button type="button" id="retry-verification" hidden class="mt-3 w-full rounded-xl border border-indigo-300 px-5 py-3 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-50">Retry payment verification</button>
            <p id="payment-status" role="status" aria-live="polite" class="mt-4 text-sm leading-relaxed text-gray-600">Pay using UPI, cards, net banking, or other options offered by Razorpay.</p>
            <a href="{{ route('account.orders.show', $order->order_number) }}" class="mt-5 block text-center text-sm font-semibold text-indigo-700 hover:text-indigo-800">View order status</a>
            <p class="mt-4 border-t border-gray-100 pt-4 text-xs leading-relaxed text-gray-500">Your payment details are entered in Razorpay's secure window. We never ask for your card details on this page.</p>
        </aside>
    </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
(() => {
    const payButton = document.getElementById('pay-button');
    const retryButton = document.getElementById('retry-verification');
    const status = document.getElementById('payment-status');
    const checkoutPayload = @json($checkoutPayload);
    const keyId = @json($keyId);
    const confirmUrl = @json(route('checkout.confirm', $order->order_number));
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    let completedPayment = null;
    let verifying = false;

    function showStatus(message, failed = false) {
        status.textContent = message;
        status.className = 'mt-4 text-sm leading-relaxed ' + (failed ? 'text-rose-700' : 'text-gray-600');
    }

    async function verifyPayment() {
        if (!completedPayment || verifying) return;
        verifying = true;
        retryButton.hidden = true;
        payButton.disabled = true;
        payButton.textContent = 'Verifying payment…';
        showStatus('Verifying your payment with TimePass. Please keep this page open.');
        try {
            const response = await fetch(confirmUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(completedPayment),
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'The payment could not be verified yet.');
            if (data.redirect) {
                window.location.assign(data.redirect);
                return;
            }
            showStatus('Payment verification is still pending. Please check your order status.');
        } catch (error) {
            showStatus('Your payment was submitted, but verification could not finish here. Retry verification or check your order status before making another payment.', true);
            retryButton.hidden = false;
        } finally {
            verifying = false;
            payButton.textContent = 'Payment submitted';
        }
    }

    if (typeof window.Razorpay !== 'function') {
        showStatus('The secure payment window could not load. Please refresh this page and try again.', true);
        payButton.textContent = 'Payment unavailable';
        return;
    }

    const checkout = new Razorpay({
        ...checkoutPayload,
        key: keyId,
        handler: (response) => {
            completedPayment = {
                razorpay_payment_id: response.razorpay_payment_id,
                razorpay_order_id: response.razorpay_order_id,
                razorpay_signature: response.razorpay_signature,
            };
            verifyPayment();
        },
        modal: {
            ondismiss: () => {
                if (!completedPayment) showStatus('Payment window closed. You can open it again when you are ready.');
            },
        },
        theme: { color: '#4f46e5' },
    });

    checkout.on('payment.failed', (response) => {
        const reason = response.error?.description || 'Payment was not completed.';
        showStatus(reason + ' You can try again.', true);
    });

    payButton.disabled = false;
    payButton.className = 'mt-6 flex w-full items-center justify-center rounded-xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-purple-500/20 transition hover:shadow-xl focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600';
    payButton.textContent = 'Pay ₹{{ number_format($order->total, 2) }} securely';
    payButton.addEventListener('click', () => { if (!completedPayment) checkout.open(); });
    retryButton.addEventListener('click', verifyPayment);
})();
</script>
@endsection
