@extends('layouts.admin', ['title' => 'Dashboard'])

@section('content')
@php
    $maxTrend = max(1, $days->max('count'));
    $cards = [
        ['Total orders', number_format($stats['orders']), 'admin.orders.index', 'bg-blue-50 text-blue-600'],
        ['Revenue', '₹' . number_format($stats['revenue']), 'admin.transactions.index', 'bg-emerald-50 text-emerald-600'],
        ['Products', number_format($stats['products']), 'admin.products.index', 'bg-indigo-50 text-indigo-600'],
        ['Customers', number_format($stats['customers']), 'admin.customers.index', 'bg-purple-50 text-purple-600'],
    ];
@endphp

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
    @foreach ($cards as [$label, $value, $route, $color])
        <a href="{{ route($route) }}" class="rounded-2xl border border-gray-200 bg-white p-5 transition hover:shadow-md">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-500">{{ $label }}</p>
                <span class="flex h-9 w-9 items-center justify-center rounded-lg {{ $color }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/></svg>
                </span>
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $value }}</p>
        </a>
    @endforeach
</div>

<div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
    {{-- Orders trend --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 lg:col-span-2">
        <h2 class="font-semibold text-gray-900">Orders · last 7 days</h2>
        <div class="mt-6 flex h-48 items-end justify-between gap-3">
            @foreach ($days as $d)
                <div class="flex flex-1 flex-col items-center gap-2">
                    <span class="text-xs font-medium text-gray-500">{{ $d['count'] }}</span>
                    <div class="w-full rounded-t-lg bg-indigo-500" style="height: {{ max(4, ($d['count'] / $maxTrend) * 150) }}px"></div>
                    <span class="text-xs text-gray-400">{{ $d['label'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Quick links / alerts --}}
    <div class="space-y-4">
        <a href="{{ route('admin.messages.index') }}" class="flex items-center justify-between rounded-2xl border border-gray-200 bg-white p-5 hover:shadow-md">
            <div>
                <p class="text-sm text-gray-500">Unread messages</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ $stats['unread_messages'] }}</p>
            </div>
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
            </span>
        </a>
        <a href="{{ route('admin.categories.index') }}" class="flex items-center justify-between rounded-2xl border border-gray-200 bg-white p-5 hover:shadow-md">
            <div>
                <p class="text-sm text-gray-500">Categories</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ $stats['categories'] }}</p>
            </div>
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6.878V6a2.25 2.25 0 0 1 2.25-2.25h7.5A2.25 2.25 0 0 1 18 6v.878m-12 0c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 0 0 4.5 9v.878m13.5-3A2.25 2.25 0 0 1 19.5 9v.878m0 0a2.246 2.246 0 0 0-.75-.128H5.25c-.263 0-.515.045-.75.128m15 0A2.25 2.25 0 0 1 21 12v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6c0-.98.626-1.813 1.5-2.122"/></svg>
            </span>
        </a>
        <a href="{{ route('admin.products.create') }}" class="flex items-center justify-center gap-2 rounded-2xl bg-indigo-600 p-4 font-semibold text-white hover:bg-indigo-500">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Add new product
        </a>
    </div>
</div>

<div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
    {{-- Recent orders --}}
    <div class="rounded-2xl border border-gray-200 bg-white">
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
            <h2 class="font-semibold text-gray-900">Recent orders</h2>
            <a href="{{ route('admin.orders.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">View all</a>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse ($recentOrders as $order)
                <a href="{{ route('admin.orders.show', $order) }}" class="flex items-center justify-between px-6 py-3 hover:bg-gray-50">
                    <div>
                        <p class="font-medium text-gray-900">#{{ $order->order_number }}</p>
                        <p class="text-xs text-gray-500">{{ $order->user->name ?? 'Guest' }} · {{ $order->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-gray-900">₹{{ number_format($order->total) }}</p>
                        <span class="inline-block rounded-full px-2 py-0.5 text-xs font-medium ring-1 {{ $order->statusClasses() }}">{{ $order->statusLabel() }}</span>
                    </div>
                </a>
            @empty
                <p class="px-6 py-8 text-center text-sm text-gray-500">No orders yet.</p>
            @endforelse
        </div>
    </div>

    {{-- Recent messages --}}
    <div class="rounded-2xl border border-gray-200 bg-white">
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
            <h2 class="font-semibold text-gray-900">Recent messages</h2>
            <a href="{{ route('admin.messages.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">View all</a>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse ($recentMessages as $msg)
                <a href="{{ route('admin.messages.show', $msg) }}" class="block px-6 py-3 hover:bg-gray-50">
                    <div class="flex items-center justify-between">
                        <p class="font-medium text-gray-900">{{ $msg->name }}</p>
                        @unless ($msg->read_at)<span class="h-2 w-2 rounded-full bg-indigo-500"></span>@endunless
                    </div>
                    <p class="truncate text-xs text-gray-500">{{ $msg->message }}</p>
                </a>
            @empty
                <p class="px-6 py-8 text-center text-sm text-gray-500">No messages yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
