{{-- One order in a list. Expects: $order (with items loaded or items_count). --}}
@php
    $itemCount = $order->items_count ?? $order->items->sum('quantity');
    $names = $order->relationLoaded('items') ? $order->items->pluck('product_name') : collect();
@endphp
<a href="{{ route('account.orders.show', $order->order_number) }}"
    class="group block rounded-2xl bg-white p-5 ring-1 ring-gray-200 transition hover:shadow-lg hover:shadow-indigo-500/5 hover:ring-indigo-200">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <p class="font-semibold text-gray-900">Order #{{ $order->order_number }}</p>
            <p class="mt-0.5 text-xs text-gray-500">{{ $order->status === 'pending_payment' ? 'Started on' : 'Placed on' }} {{ $order->created_at->format('d M Y') }} · {{ $itemCount }} {{ \Illuminate\Support\Str::plural('item', $itemCount) }}</p>
        </div>
        <span class="rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $order->statusClasses() }}">{{ $order->statusLabel() }}</span>
    </div>
    @if ($names->isNotEmpty())
        <p class="mt-3 line-clamp-1 text-sm text-gray-600">{{ $names->implode(', ') }}</p>
    @endif
    <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-4 text-sm">
        <span class="text-gray-500">
            @if ($order->status === 'delivered')
                Delivered {{ $order->delivered_at?->format('d M Y') }}
            @elseif ($order->status === 'cancelled')
                Cancelled
            @elseif ($order->expected_delivery)
                Arriving by {{ $order->expected_delivery->format('d M Y') }}
            @endif
        </span>
        <span class="flex items-center gap-4">
            <span class="font-bold text-gray-900">₹{{ number_format($order->total, 2) }}</span>
            <span class="inline-flex items-center gap-1 font-semibold text-indigo-600">
                Details
                <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
            </span>
        </span>
    </div>
</a>
