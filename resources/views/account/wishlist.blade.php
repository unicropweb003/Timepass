@extends('layouts.account', ['title' => 'My Wishlist'])

@section('account')
@php
    $palette = [
        'skin-care'         => ['#6366f1', '#8b5cf6'],
        'hair-care'         => ['#f59e0b', '#d97706'],
        'makeup'            => ['#ec4899', '#db2777'],
        'nail-care'         => ['#a855f7', '#9333ea'],
        'hygiene-oral-care' => ['#06b6d4', '#0891b2'],
        'fragrances'        => ['#10b981', '#059669'],
        'body-care'         => ['#f43f5e', '#e11d48'],
    ];
    $accent = fn ($slug) => $palette[$slug] ?? ['#6366f1', '#8b5cf6'];
@endphp

<div class="flex flex-wrap items-end justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">My Wishlist</h1>
        <p class="mt-1 text-gray-500">{{ $products->count() }} saved {{ \Illuminate\Support\Str::plural('product', $products->count()) }} · tap the heart to remove one.</p>
    </div>
    <a href="{{ route('products') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">Browse products &rarr;</a>
</div>

@if ($products->isNotEmpty())
    <div class="mt-6 grid grid-cols-2 gap-4 sm:gap-5 lg:grid-cols-3">
        @foreach ($products as $p)
            @include('partials.product-card-premium', ['product' => $p, 'accent' => $accent($p['category_slug'] ?? '')])
        @endforeach
    </div>
@else
    @include('account.partials.empty', [
        'icon' => 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z',
        'title' => 'Your wishlist is empty',
        'text' => 'Tap the heart on any product to save it here for later.',
        'action' => ['Browse products', route('products')],
    ])
@endif
@endsection
