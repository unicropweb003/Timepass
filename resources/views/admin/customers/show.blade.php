@extends('layouts.admin', ['title' => $customer->name])

@section('content')
<a href="{{ route('admin.customers.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-800">
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
    Back to customers
</a>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 text-sm">
            <div class="flex items-center gap-3">
                <span class="flex h-12 w-12 items-center justify-center rounded-full bg-indigo-100 text-lg font-semibold text-indigo-700">{{ strtoupper(substr($customer->name, 0, 1)) }}</span>
                <div>
                    <p class="font-semibold text-gray-900">{{ $customer->name }}</p>
                    @if ($customer->is_blocked)<span class="text-xs font-medium text-red-600">Blocked</span>@else<span class="text-xs font-medium text-emerald-600">Active</span>@endif
                </div>
            </div>
            <dl class="mt-5 space-y-2">
                <div class="flex justify-between"><dt class="text-gray-500">Email</dt><dd class="font-medium text-gray-900">{{ $customer->email }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Phone</dt><dd class="font-medium text-gray-900">{{ $customer->phone ?: '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Joined</dt><dd class="font-medium text-gray-900">{{ $customer->created_at->format('d M Y') }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Orders</dt><dd class="font-medium text-gray-900">{{ $orders->total() }}</dd></div>
            </dl>
            <form method="POST" action="{{ route('admin.customers.block', $customer) }}" class="mt-5" onsubmit="return confirm('{{ $customer->is_blocked ? 'Unblock' : 'Block' }} this customer?')">
                @csrf @method('PATCH')
                <button class="w-full rounded-lg border px-4 py-2 text-sm font-medium {{ $customer->is_blocked ? 'border-emerald-200 text-emerald-700 hover:bg-emerald-50' : 'border-red-200 text-red-600 hover:bg-red-50' }}">
                    {{ $customer->is_blocked ? 'Unblock customer' : 'Block customer' }}
                </button>
            </form>
        </div>

        @if ($customer->addresses->count())
            <div class="rounded-2xl border border-gray-200 bg-white p-6 text-sm">
                <h2 class="font-semibold text-gray-900">Addresses</h2>
                @foreach ($customer->addresses as $a)
                    <p class="mt-3 text-gray-600">{{ $a->full_name }} · {{ $a->oneLine() }}</p>
                @endforeach
            </div>
        @endif
    </div>

    <div class="lg:col-span-2">
        <div class="rounded-2xl border border-gray-200 bg-white">
            <h2 class="border-b border-gray-100 px-6 py-4 font-semibold text-gray-900">Order history</h2>
            <div class="divide-y divide-gray-100">
                @forelse ($orders as $order)
                    <a href="{{ route('admin.orders.show', $order) }}" class="flex items-center justify-between px-6 py-3 hover:bg-gray-50">
                        <div>
                            <p class="font-medium text-gray-900">#{{ $order->order_number }}</p>
                            <p class="text-xs text-gray-500">{{ $order->created_at->format('d M Y') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-gray-900">₹{{ number_format($order->total) }}</p>
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium ring-1 {{ $order->statusClasses() }}">{{ $order->statusLabel() }}</span>
                        </div>
                    </a>
                @empty
                    <p class="px-6 py-10 text-center text-gray-500">No orders yet.</p>
                @endforelse
            </div>
            @if ($orders->hasPages())
                <div class="border-t border-gray-100 px-6 py-3">{{ $orders->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
