@extends('layouts.admin', ['title' => 'Categories'])

@section('content')
@php
    // One colour per category (matches the storefront + product images).
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
    $defaultIcon = 'M6 6.878V6a2.25 2.25 0 0 1 2.25-2.25h7.5A2.25 2.25 0 0 1 18 6v.878m-12 0c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 0 0 4.5 9v.878m13.5-3A2.25 2.25 0 0 1 19.5 9v.878m0 0a2.246 2.246 0 0 0-.75-.128H5.25c-.263 0-.515.045-.75.128m15 0A2.25 2.25 0 0 1 21 12v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6c0-.98.626-1.813 1.5-2.122';
@endphp

{{-- Header --}}
<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div>
        <h2 class="text-xl font-bold text-gray-900">All categories</h2>
        <p class="mt-1 text-sm text-gray-500">{{ $categories->count() }} {{ \Illuminate\Support\Str::plural('category', $categories->count()) }} · organise your storefront</p>
    </div>
    <a href="{{ route('admin.categories.create') }}"
       class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-indigo-500/30 transition hover:-translate-y-0.5 hover:bg-indigo-500">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Add category
    </a>
</div>

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
    @forelse ($categories as $cat)
        @php
            [$c1, $c2] = $accent($cat->slug);
            $grad = 'linear-gradient(135deg,' . $c1 . ',' . $c2 . ')';
            $typeCount = count($cat->types ?? []);
            $prodCount = $counts[$cat->slug] ?? 0;
        @endphp
        <div class="group flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-gray-300/40">
            {{-- Colour accent strip --}}
            <span class="block h-1.5 w-full" style="background-image: {{ $grad }}"></span>

            <div class="flex flex-1 flex-col p-5">
                <div class="flex items-start justify-between">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl text-white shadow-md transition group-hover:scale-105" style="background-image: {{ $grad }}">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $cat->icon ?: $defaultIcon }}"/></svg>
                    </span>
                    @if ($cat->is_active)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Active
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-500 ring-1 ring-gray-200">
                            <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span> Hidden
                        </span>
                    @endif
                </div>

                <h3 class="mt-4 text-lg font-bold text-gray-900">{{ $cat->name }}</h3>
                <p class="mt-1 line-clamp-2 flex-1 text-sm text-gray-500">{{ $cat->description ?: 'No description' }}</p>

                {{-- Meta pills --}}
                <div class="mt-4 flex flex-wrap gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-600 ring-1 ring-gray-200/70">
                        <svg class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6.878V6a2.25 2.25 0 0 1 2.25-2.25h7.5A2.25 2.25 0 0 1 18 6v.878m-12 0c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 0 0 4.5 9v.878"/></svg>
                        {{ $typeCount }} {{ \Illuminate\Support\Str::plural('type', $typeCount) }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-semibold" style="color: {{ $c2 }}; background-color: {{ $c1 }}14;">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
                        {{ $prodCount }} {{ \Illuminate\Support\Str::plural('product', $prodCount) }}
                    </span>
                </div>

                {{-- Actions --}}
                <div class="mt-5 flex gap-2 border-t border-gray-100 pt-4">
                    <a href="{{ route('admin.categories.edit', $cat) }}" class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-gray-300 px-3 py-2 text-xs font-semibold text-gray-700 transition hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/></svg>
                        Edit
                    </a>
                    <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}" onsubmit="return confirm('Delete this category? Products in it will remain but become uncategorised.')" class="flex-1">
                        @csrf @method('DELETE')
                        <button class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-red-200 px-3 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-50">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-full flex flex-col items-center rounded-2xl border border-dashed border-gray-300 bg-white py-16 text-center">
            <span class="flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 7.125C2.25 6.504 2.754 6 3.375 6h6c.621 0 1.125.504 1.125 1.125v3.75c0 .621-.504 1.125-1.125 1.125h-6a1.125 1.125 0 0 1-1.125-1.125v-3.75Z"/></svg>
            </span>
            <p class="mt-4 font-semibold text-gray-700">No categories yet</p>
            <p class="mt-1 text-sm text-gray-500">Create your first category to start organising products.</p>
            <a href="{{ route('admin.categories.create') }}" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Add category
            </a>
        </div>
    @endforelse
</div>
@endsection
