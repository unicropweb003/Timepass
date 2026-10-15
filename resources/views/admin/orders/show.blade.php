@extends('layouts.admin', ['title' => 'Order #' . $order->order_number])

@section('content')
<a href="{{ route('admin.orders.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-800">
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
    Back to orders
</a>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    {{-- Items --}}
    <div class="space-y-6 lg:col-span-2">
        <div class="rounded-2xl border border-gray-200 bg-white">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                <h2 class="font-semibold text-gray-900">Items</h2>
                <span class="rounded-full px-2 py-0.5 text-xs font-medium ring-1 {{ $order->statusClasses() }}">{{ $order->statusLabel() }}</span>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach ($order->items as $item)
                    <div class="flex items-center gap-4 px-6 py-4">
                        @if ($item->image)
                            <img src="{{ asset($item->image) }}" alt="" class="h-14 w-14 rounded-lg object-cover">
                        @else
                            <span class="flex h-14 w-14 items-center justify-center rounded-lg bg-gray-100 text-gray-400">—</span>
                        @endif
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">{{ $item->product_name }}</p>
                            <p class="text-xs text-gray-500">{{ $item->category }} · Qty {{ $item->quantity }}</p>
                        </div>
                        <p class="font-semibold text-gray-900">₹{{ number_format($item->price * $item->quantity) }}</p>
                    </div>
                @endforeach
            </div>
            <div class="space-y-1 border-t border-gray-100 px-6 py-4 text-sm">
                <div class="flex justify-between text-gray-500"><span>Subtotal</span><span>₹{{ number_format($order->subtotal) }}</span></div>
                <div class="flex justify-between text-gray-500"><span>Shipping</span><span>₹{{ number_format($order->shipping) }}</span></div>
                @if ($order->discount > 0)<div class="flex justify-between text-gray-500"><span>Discount</span><span>−₹{{ number_format($order->discount) }}</span></div>@endif
                <div class="flex justify-between pt-1 text-base font-bold text-gray-900"><span>Total</span><span>₹{{ number_format($order->total) }}</span></div>
            </div>
        </div>

        @if ($order->transactions->count())
            <div class="rounded-2xl border border-gray-200 bg-white">
                <h2 class="border-b border-gray-100 px-6 py-4 font-semibold text-gray-900">Transactions</h2>
                <div class="divide-y divide-gray-100 text-sm">
                    @foreach ($order->transactions as $t)
                        <div class="flex items-center justify-between px-6 py-3">
                            <div><p class="font-medium text-gray-900">{{ $t->reference }}</p><p class="text-xs text-gray-500">{{ ucfirst($t->type) }} · {{ $t->method }}</p></div>
                            <div class="text-right"><p class="font-semibold">₹{{ number_format($t->amount) }}</p><p class="text-xs text-gray-500">{{ ucfirst($t->status) }}</p></div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Sidebar: status + customer + shipping --}}
    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-6">
            <h2 class="font-semibold text-gray-900">Update status</h2>
            <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="mt-4 space-y-3">
                @csrf @method('PATCH')
                <select name="status" class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/40">
                    @foreach ($steps as $key => $step)
                        <option value="{{ $key }}" @selected($order->status === $key)>{{ $step[0] }}</option>
                    @endforeach
                    <option value="cancelled" @selected($order->status === 'cancelled')>Cancelled</option>
                </select>
                <button class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">Save status</button>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 text-sm">
            <h2 class="font-semibold text-gray-900">Customer</h2>
            <p class="mt-3 font-medium text-gray-900">{{ $order->user->name ?? 'Guest' }}</p>
            <p class="text-gray-500">{{ $order->user->email ?? '—' }}</p>
            <p class="text-gray-500">{{ $order->user->phone ?? '' }}</p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 text-sm">
            <h2 class="font-semibold text-gray-900">Shipping</h2>
            <p class="mt-3 font-medium text-gray-900">{{ $order->shipping_name }}</p>
            <p class="text-gray-500">{{ $order->shipping_phone }}</p>
            <p class="mt-1 text-gray-500">{{ $order->shipping_address }}</p>
            @if ($order->courier)<p class="mt-2 text-gray-500">Courier: {{ $order->courier }} {{ $order->tracking_number ? '('.$order->tracking_number.')' : '' }}</p>@endif
            <p class="mt-1 text-gray-500">Payment: {{ $order->payment_method }}</p>
        </div>
    </div>
</div>
@endsection
