@extends('layouts.app', ['title' => 'Secure checkout'])

@section('content')
@php
    $defaultAddress = $addresses->firstWhere('is_default', true) ?? $addresses->first();
    $selectedAddress = old('address_id', $defaultAddress?->id ?? '');
    $useNewAddress = (string) $selectedAddress === '';
    $maxQuantity = $product->stock === null ? 10 : min(10, max(1, (int) $product->stock));
@endphp

<div class="mx-auto max-w-6xl pb-14">
    <nav class="mb-6 flex items-center gap-2 text-sm text-gray-500" aria-label="Breadcrumb">
        <a href="{{ route('products') }}" class="hover:text-indigo-700">Products</a>
        <span aria-hidden="true">/</span>
        <a href="{{ route('product.show', $product->slug) }}" class="hover:text-indigo-700">{{ $product->name }}</a>
        <span aria-hidden="true">/</span>
        <span class="font-medium text-gray-900">Checkout</span>
    </nav>

    <div class="mb-8">
        <p class="text-xs font-bold uppercase tracking-[0.22em] text-indigo-600">TimePass checkout</p>
        <h1 class="font-display mt-2 text-4xl font-semibold tracking-tight text-gray-950 sm:text-5xl">Almost yours.</h1>
        <p class="mt-3 max-w-2xl text-gray-600">Choose where we should send your order. You will review the final amount before opening the secure payment window.</p>
    </div>

    @if ($errors->any())
        <div role="alert" class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
            <p class="font-semibold">Please check the details below.</p>
            <ul class="mt-2 list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="checkout-form" method="POST" action="{{ route('checkout.start', $product->slug) }}" class="grid items-start gap-8 lg:grid-cols-[minmax(0,1fr)_21rem]">
        @csrf
        <div class="space-y-6">
            <section class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm sm:p-7" aria-labelledby="delivery-heading">
                <div class="flex items-start gap-4">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-sm font-bold text-white">1</span>
                    <div>
                        <h2 id="delivery-heading" class="font-display text-2xl font-semibold text-gray-950">Delivery address</h2>
                        <p class="mt-1 text-sm text-gray-500">Select a saved address or enter a new one for this order.</p>
                    </div>
                </div>

                @if ($addresses->isNotEmpty())
                    <div class="mt-6 grid gap-3 sm:grid-cols-2">
                        @foreach ($addresses as $address)
                            <label class="cursor-pointer">
                                <input type="radio" name="address_id" value="{{ $address->id }}" class="peer sr-only" @checked((string) $selectedAddress === (string) $address->id)>
                                <span class="block h-full rounded-2xl border border-gray-200 p-4 transition hover:border-indigo-300 peer-checked:border-indigo-600 peer-checked:bg-indigo-50/60 peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-indigo-600">
                                    <span class="flex items-center justify-between gap-2">
                                        <span class="font-semibold text-gray-900">{{ $address->full_name }}</span>
                                        <span class="rounded-full bg-white px-2 py-0.5 text-[11px] font-semibold text-indigo-700 ring-1 ring-indigo-100">{{ $address->label }}</span>
                                    </span>
                                    <span class="mt-2 block text-sm leading-relaxed text-gray-600">{{ $address->oneLine() }}</span>
                                    <span class="mt-2 block text-sm text-gray-500">{{ $address->phone }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                @endif

                <label class="mt-4 flex cursor-pointer items-center gap-3 rounded-2xl border border-gray-200 p-4 transition hover:border-indigo-300 has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/60">
                    <input type="radio" name="address_id" value="" class="h-4 w-4 accent-indigo-600" @checked($useNewAddress)>
                    <span class="text-sm font-semibold text-gray-900">Use a new delivery address</span>
                </label>

                <fieldset id="new-address-fields" class="mt-5 grid gap-4 border-t border-gray-100 pt-5 sm:grid-cols-2" @disabled(! $useNewAddress)>
                    <div>
                        <label for="shipping_name" class="mb-1.5 block text-sm font-medium text-gray-700">Full name</label>
                        <input id="shipping_name" name="shipping_name" value="{{ old('shipping_name', auth()->user()->name) }}" autocomplete="name" maxlength="255" placeholder="Recipient's full name" class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10">
                    </div>
                    <div>
                        <label for="shipping_phone" class="mb-1.5 block text-sm font-medium text-gray-700">Phone number</label>
                        <input id="shipping_phone" name="shipping_phone" type="tel" value="{{ old('shipping_phone', auth()->user()->phone) }}" autocomplete="tel" inputmode="numeric" maxlength="15" placeholder="10 to 15 digits" class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="line1" class="mb-1.5 block text-sm font-medium text-gray-700">House no., building and street</label>
                        <input id="line1" name="line1" value="{{ old('line1') }}" autocomplete="address-line1" maxlength="255" class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="line2" class="mb-1.5 block text-sm font-medium text-gray-700">Area or landmark <span class="font-normal text-gray-400">(optional)</span></label>
                        <input id="line2" name="line2" value="{{ old('line2') }}" autocomplete="address-line2" maxlength="255" class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10">
                    </div>
                    <div>
                        <label for="city" class="mb-1.5 block text-sm font-medium text-gray-700">City</label>
                        <input id="city" name="city" value="{{ old('city') }}" autocomplete="address-level2" maxlength="100" class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10">
                    </div>
                    <div>
                        <label for="state" class="mb-1.5 block text-sm font-medium text-gray-700">State</label>
                        <input id="state" name="state" value="{{ old('state') }}" autocomplete="address-level1" maxlength="100" class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10">
                    </div>
                    <div>
                        <label for="pincode" class="mb-1.5 block text-sm font-medium text-gray-700">Pincode</label>
                        <input id="pincode" name="pincode" value="{{ old('pincode') }}" autocomplete="postal-code" inputmode="numeric" maxlength="6" class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10">
                    </div>
                </fieldset>
            </section>
        </div>

        <aside class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm lg:sticky lg:top-28 sm:p-6" aria-labelledby="summary-heading">
            <h2 id="summary-heading" class="font-display text-2xl font-semibold text-gray-950">Order summary</h2>
            <div class="mt-5 flex items-center gap-4 border-b border-gray-100 pb-5">
                <img src="{{ $product->image_url ?: asset('images/no-image.svg') }}" alt="{{ $product->name }}" class="h-20 w-20 shrink-0 rounded-xl border border-gray-100 bg-gray-50 object-contain p-2">
                <div class="min-w-0">
                    <p class="font-semibold leading-snug text-gray-900">{{ $product->name }}</p>
                    <p class="mt-1 text-sm text-gray-500">₹{{ number_format($product->price) }} each</p>
                </div>
            </div>
            <div class="mt-5 flex items-center justify-between gap-3">
                <label for="quantity" class="text-sm font-medium text-gray-700">Quantity</label>
                <select id="quantity" name="quantity" class="rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10">
                    @for ($quantity = 1; $quantity <= $maxQuantity; $quantity++)
                        <option value="{{ $quantity }}" @selected((int) old('quantity', 1) === $quantity)>{{ $quantity }}</option>
                    @endfor
                </select>
            </div>
            <div class="mt-5 space-y-3 border-t border-gray-100 pt-5 text-sm">
                <div class="flex justify-between text-gray-600"><span>Items subtotal</span><span id="items-subtotal">₹{{ number_format($product->price) }}</span></div>
                <div class="flex justify-between text-gray-600"><span>Shipping</span><span id="shipping-amount"></span></div>
                <div class="flex justify-between border-t border-gray-100 pt-3 text-base font-bold text-gray-950"><span>Total</span><span id="order-total"></span></div>
            </div>
            <p class="mt-5 rounded-xl bg-indigo-50 px-4 py-3 text-xs leading-relaxed text-indigo-800">Delivery is free from ₹{{ number_format($freeShippingThreshold) }}. Below that, shipping is ₹{{ number_format($shippingFee) }}. Your final amount is confirmed on the next step.</p>
            @if ($gatewayReady)
                <button type="submit" class="mt-5 flex w-full items-center justify-center rounded-xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-purple-500/20 transition hover:shadow-xl focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Continue to payment <span aria-hidden="true" class="ml-2">→</span></button>
            @else
                <p role="status" class="mt-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">Online payment setup is pending. Checkout will be available once it is connected.</p>
                <button type="button" disabled class="mt-4 w-full cursor-not-allowed rounded-xl bg-gray-200 px-5 py-3.5 text-sm font-bold text-gray-500">Payment unavailable</button>
            @endif
            <p class="mt-3 text-center text-xs text-gray-500">Secure payment is handled by Razorpay. TimePass does not store your card details.</p>
        </aside>
    </form>
</div>

<script>
(() => {
    const addressChoices = document.querySelectorAll('input[name="address_id"]');
    const newAddressFields = document.getElementById('new-address-fields');
    const requiredFields = ['shipping_name', 'shipping_phone', 'line1', 'city', 'state', 'pincode'];
    function updateAddressFields() {
        const selected = document.querySelector('input[name="address_id"]:checked');
        const useNew = !selected || selected.value === '';
        newAddressFields.disabled = !useNew;
        requiredFields.forEach((name) => { document.getElementById(name).required = useNew; });
    }
    addressChoices.forEach((choice) => choice.addEventListener('change', updateAddressFields));
    updateAddressFields();

    const quantity = document.getElementById('quantity');
    const subtotal = document.getElementById('items-subtotal');
    const shippingAmount = document.getElementById('shipping-amount');
    const orderTotal = document.getElementById('order-total');
    const unitPrice = @json((int) $product->price);
    const shippingFee = @json((int) $shippingFee);
    const freeShippingThreshold = @json((int) $freeShippingThreshold);
    const formatRupees = new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 });
    quantity.addEventListener('change', () => {
        const itemsTotal = Number(quantity.value) * unitPrice;
        const shipping = itemsTotal >= freeShippingThreshold ? 0 : shippingFee;
        subtotal.textContent = formatRupees.format(itemsTotal);
        shippingAmount.textContent = shipping === 0 ? 'Free' : formatRupees.format(shipping);
        orderTotal.textContent = formatRupees.format(itemsTotal + shipping);
    });
    quantity.dispatchEvent(new Event('change'));
})();
</script>
@endsection
