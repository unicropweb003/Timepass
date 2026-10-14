@extends('layouts.account', ['title' => 'Order #' . $order->order_number])

@php
    $steps = \App\Models\Order::STEPS;
    $currentIndex = array_search($order->status, array_keys($steps), true);
    $money = fn ($amount) => '₹' . number_format($amount, 2);
@endphp

@section('account')
<a href="{{ route('account.orders') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-gray-500 hover:text-indigo-600">
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
    All orders
</a>

<div class="mt-3 flex flex-wrap items-start justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Order #{{ $order->order_number }}</h1>
        <p class="mt-1 text-sm text-gray-500">Placed on {{ $order->created_at->format('d M Y, h:i A') }}</p>
    </div>
    <span class="rounded-full px-3 py-1 text-sm font-semibold ring-1 {{ $order->statusClasses() }}">{{ $order->statusLabel() }}</span>
</div>

{{-- Delivery tracking --}}
<div class="mt-6 rounded-2xl bg-white p-6 ring-1 ring-gray-200">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <h2 class="font-semibold text-gray-900">Delivery status</h2>
        @if ($order->status === 'delivered')
            <p class="text-sm font-medium text-emerald-700">Delivered on {{ $order->delivered_at?->format('d M Y') }}</p>
        @elseif ($order->status !== 'cancelled' && $order->expected_delivery)
            <p class="text-sm text-gray-600">Expected by <span class="font-semibold text-gray-900">{{ $order->expected_delivery->format('d M Y') }}</span></p>
        @endif
    </div>

    @if ($order->status === 'cancelled')
        <p class="mt-4 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700 ring-1 ring-red-100">
            This order was cancelled{{ $order->cancelled_at ? ' on ' . $order->cancelled_at->format('d M Y') : '' }}. Any payment made will be refunded to the original payment method.
        </p>
    @else
        <ol class="mt-6 grid gap-6 sm:grid-cols-5 sm:gap-2">
            @foreach ($steps as $key => [$label, $column])
                @php
                    $index = $loop->index;
                    $done = $currentIndex !== false && $index <= $currentIndex;
                    $time = $order->{$column};
                @endphp
                <li class="relative flex gap-3 sm:flex-col sm:items-center sm:text-center">
                    @unless ($loop->last)
                        <span class="absolute left-[15px] top-8 h-[calc(100%-0.5rem)] w-0.5 sm:left-[calc(50%+1.25rem)] sm:top-[15px] sm:h-0.5 sm:w-[calc(100%-2.5rem)] {{ $currentIndex !== false && $index < $currentIndex ? 'bg-indigo-500' : 'bg-gray-200' }}"></span>
                    @endunless
                    <span class="relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $done ? 'bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-md shadow-indigo-500/30' : 'bg-gray-100 text-gray-400 ring-1 ring-gray-200' }}">
                        @if ($done)
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        @else
                            <span class="text-xs font-bold">{{ $index + 1 }}</span>
                        @endif
                    </span>
                    <span>
                        <span class="block text-sm font-semibold {{ $done ? 'text-gray-900' : 'text-gray-400' }}">{{ $label }}</span>
                        @if ($done && $time)
                            <span class="block text-xs text-gray-500">{{ $time->format('d M, h:i A') }}</span>
                        @endif
                    </span>
                </li>
            @endforeach
        </ol>
    @endif

    @if ($order->courier || $order->tracking_number)
        <div class="mt-6 flex flex-wrap gap-x-8 gap-y-2 border-t border-gray-100 pt-4 text-sm">
            @if ($order->courier)
                <p><span class="text-gray-500">Courier:</span> <span class="font-medium text-gray-900">{{ $order->courier }}</span></p>
            @endif
            @if ($order->tracking_number)
                <p><span class="text-gray-500">Tracking number:</span> <span class="font-mono font-medium text-gray-900">{{ $order->tracking_number }}</span></p>
            @endif
        </div>
    @endif
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-[1fr_20rem]">
    {{-- Items --}}
    <div class="rounded-2xl bg-white ring-1 ring-gray-200">
        <h2 class="border-b border-gray-100 px-6 py-4 font-semibold text-gray-900">Items in this order</h2>
        <ul class="divide-y divide-gray-100">
            @foreach ($order->items as $item)
                <li class="flex items-center gap-4 px-6 py-4">
                    <img src="{{ asset($item->image ?: 'images/no-image.svg') }}" alt="" class="h-16 w-16 shrink-0 rounded-xl bg-gray-50 object-cover ring-1 ring-gray-100">
                    <div class="min-w-0 flex-1">
                        <p class="font-medium text-gray-900">{{ $item->product_name }}</p>
                        <p class="text-xs text-gray-500">{{ collect([$item->category, $item->type])->filter()->implode(' · ') }}</p>
                        <p class="mt-1 text-sm text-gray-600">Qty {{ $item->quantity }} × {{ $money($item->price) }}</p>
                    </div>
                    <p class="font-semibold text-gray-900">{{ $money($item->price * $item->quantity) }}</p>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="space-y-6">
        {{-- Delivery details --}}
        <div class="rounded-2xl bg-white p-5 ring-1 ring-gray-200">
            <h2 class="font-semibold text-gray-900">Delivery details</h2>
            <p class="mt-3 text-sm font-medium text-gray-900">{{ $order->shipping_name }}</p>
            <p class="mt-1 text-sm text-gray-600">{{ $order->shipping_address }}</p>
            <p class="mt-1 text-sm text-gray-500">Phone: {{ $order->shipping_phone }}</p>
        </div>

        {{-- Payment summary --}}
        <div class="rounded-2xl bg-white p-5 ring-1 ring-gray-200">
            <h2 class="font-semibold text-gray-900">Payment summary</h2>
            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Subtotal</dt><dd class="text-gray-900">{{ $money($order->subtotal) }}</dd></div>
                @if ($order->discount > 0)
                    <div class="flex justify-between"><dt class="text-gray-500">Discount</dt><dd class="text-emerald-600">-{{ $money($order->discount) }}</dd></div>
                @endif
                <div class="flex justify-between"><dt class="text-gray-500">Shipping</dt><dd class="text-gray-900">{{ $order->shipping > 0 ? $money($order->shipping) : 'Free' }}</dd></div>
                <div class="flex justify-between border-t border-gray-100 pt-2 font-semibold"><dt class="text-gray-900">Total</dt><dd class="text-gray-900">{{ $money($order->total) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Paid via</dt><dd class="text-gray-900">{{ $order->payment_method }}</dd></div>
            </dl>
            @if ($order->transactions->isNotEmpty())
                <a href="{{ route('account.transactions') }}" class="mt-4 inline-flex text-sm font-semibold text-indigo-600 hover:text-indigo-700">View transactions &rarr;</a>
            @endif
        </div>
    </div>
</div>
@endsection
