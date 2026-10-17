@extends('layouts.admin', ['title' => 'Wishlists'])

@section('content')
@php
    $catNames = collect(config('shop.categories', []))->pluck('name', 'slug');
    $max = max(1, $maxCount);
@endphp

<div class="mb-6">
    <h2 class="text-xl font-bold text-gray-900">Wishlists</h2>
    <p class="mt-1 text-sm text-gray-500">Which products customers have saved to their wishlist.</p>
</div>

{{-- Summary --}}
<div class="grid grid-cols-2 gap-5 sm:max-w-md">
    <div class="rounded-2xl border border-gray-200 bg-white p-5">
        <div class="flex items-center justify-between">
            <p class="text-sm font-medium text-gray-500">Total saves</p>
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-rose-50 text-rose-500">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
            </span>
        </div>
        <p class="mt-3 text-3xl font-black text-gray-900">{{ number_format($totalEntries) }}</p>
    </div>
    <div class="rounded-2xl border border-gray-200 bg-white p-5">
        <div class="flex items-center justify-between">
            <p class="text-sm font-medium text-gray-500">Customers saving</p>
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
            </span>
        </div>
        <p class="mt-3 text-3xl font-black text-gray-900">{{ number_format($uniqueCustomers) }}</p>
    </div>
</div>

{{-- Ranked list --}}
<div class="mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-white">
    <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
        <h3 class="font-semibold text-gray-900">Most wishlisted products</h3>
        <span class="text-xs text-gray-400">{{ $rows->total() }} products</span>
    </div>
    <div class="divide-y divide-gray-100">
        @forelse ($rows as $i => $row)
            @php $p = $row->product; @endphp
            <div class="flex items-center gap-4 px-6 py-4">
                <span class="w-6 shrink-0 text-center text-sm font-black text-gray-300">{{ $rows->firstItem() + $i }}</span>
                <span class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-gray-100 bg-gray-50">
                    <img src="{{ asset($p->image ?: 'images/no-image.svg') }}" onerror="this.onerror=null;this.src='{{ asset('images/no-image.svg') }}';" alt="" class="h-full w-full object-contain p-1">
                </span>
                <div class="min-w-0 flex-1">
                    <a href="{{ route('admin.products.edit', $p) }}" class="truncate font-semibold text-gray-900 hover:text-indigo-600">{{ $p->name }}</a>
                    <p class="text-xs text-gray-400">{{ $catNames[$p->category] ?? $p->category }}</p>
                    <div class="mt-2 h-1.5 w-full max-w-xs overflow-hidden rounded-full bg-gray-100">
                        <div class="h-full rounded-full bg-gradient-to-r from-rose-400 to-pink-500" style="width: {{ round($row->wish_count / $max * 100) }}%"></div>
                    </div>
                </div>
                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-rose-50 px-3 py-1 text-sm font-bold text-rose-600">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
                    {{ $row->wish_count }}
                </span>
            </div>
        @empty
            <div class="flex flex-col items-center px-6 py-16 text-center">
                <span class="flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
                </span>
                <p class="mt-4 font-semibold text-gray-700">No wishlist activity yet</p>
                <p class="mt-1 text-sm text-gray-500">When customers save products, they'll appear here ranked by popularity.</p>
            </div>
        @endforelse
    </div>
    @if ($rows->hasPages())
        <div class="border-t border-gray-100 px-6 py-3">{{ $rows->links() }}</div>
    @endif
</div>
@endsection
