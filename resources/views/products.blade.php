@php
    $typeName = $type ? $category['types'][$type] : null;
    $heading = $typeName ?? $category['name'] ?? $form['name'] ?? 'All products';
    $description = match (true) {
        (bool) $typeName => $typeName . ' from our ' . $category['name'] . ' range' . ($form ? ' · Form: ' . $form['name'] : '') . '.',
        (bool) $category => $category['description'] . '.',
        (bool) $form => $form['examples'] . '.',
        default => 'Browse cosmetics by category, product type or form.',
    };
    $hasPrice = $minPrice !== null || $maxPrice !== null;
    $hasFilters = $category || $form || $hasPrice;
    $rupees = fn ($n) => '₹' . number_format($n);
    $priceLabel = match (true) {
        $minPrice !== null && $maxPrice !== null => $rupees($minPrice) . ' - ' . $rupees($maxPrice),
        $minPrice !== null => 'From ' . $rupees($minPrice),
        $maxPrice !== null => 'Up to ' . $rupees($maxPrice),
        default => null,
    };

    // URL for this page with the current filters plus the given changes (null removes a filter).
    $filterUrl = fn (array $changes) => route('products', array_filter(array_merge([
        'category' => $category['slug'] ?? null,
        'type' => $type,
        'form' => $form['slug'] ?? null,
        'min_price' => $minPrice,
        'max_price' => $maxPrice,
    ], $changes), fn ($v) => $v !== null && $v !== ''));

    $sideLink = 'flex items-center gap-3 rounded-xl px-3 py-2 text-sm transition';
    $sideOn = 'bg-indigo-50 font-semibold text-indigo-700';
    $sideOff = 'text-gray-700 hover:bg-gray-50 hover:text-gray-900';
    $count = 'ml-auto rounded-full px-2 py-0.5 text-xs font-medium';
@endphp

@extends('layouts.app', ['title' => $heading . ($category && $typeName ? ' - ' . $category['name'] : ' - Products')])

@section('content')
<div class="mb-6">
    <nav class="flex flex-wrap items-center gap-1.5 text-sm text-gray-500" aria-label="Breadcrumb">
        <a href="{{ route('products') }}" class="hover:text-indigo-600">Products</a>
        @if ($category)
            <span aria-hidden="true">/</span>
            @if ($typeName)
                <a href="{{ route('products', ['category' => $category['slug']]) }}" class="hover:text-indigo-600">{{ $category['name'] }}</a>
                <span aria-hidden="true">/</span>
                <span class="text-gray-700">{{ $typeName }}</span>
            @else
                <span class="text-gray-700">{{ $category['name'] }}</span>
            @endif
        @endif
    </nav>
    <h1 class="mt-2 text-3xl font-bold text-gray-900">{{ $heading }}</h1>
    <p class="mt-2 text-gray-600">{{ $description }}</p>
</div>

<div class="grid gap-8 lg:grid-cols-[17rem_1fr]">
    {{-- Filters sidebar (collapsible on phones) --}}
    <div>
        <button type="button" id="filters-toggle" aria-controls="filters-panel" aria-expanded="false"
            class="flex w-full items-center justify-between rounded-xl bg-white px-4 py-3 text-sm font-semibold text-gray-800 ring-1 ring-gray-200 lg:hidden">
            <span class="flex items-center gap-2">
                <svg class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" /></svg>
                Filters
                @if ($hasFilters)<span class="rounded-full bg-indigo-600 px-2 py-0.5 text-xs text-white">{{ ($category ? 1 : 0) + ($type ? 1 : 0) + ($form ? 1 : 0) + ($hasPrice ? 1 : 0) }}</span>@endif
            </span>
            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" /></svg>
        </button>

        <aside id="filters-panel" class="mt-3 hidden space-y-6 rounded-2xl bg-white p-4 ring-1 ring-gray-200 lg:sticky lg:top-24 lg:mt-0 lg:block lg:max-h-[calc(100vh-7rem)] lg:overflow-y-auto lg:[scrollbar-width:thin]">
            <div>
                <h2 class="px-3 text-xs font-semibold uppercase tracking-wider text-gray-500">Categories</h2>
                <ul class="mt-2 space-y-0.5">
                    <li>
                        <a href="{{ $filterUrl(['category' => null, 'type' => null]) }}" class="{{ $sideLink }} {{ $category ? $sideOff : $sideOn }}">
                            <svg class="h-5 w-5 shrink-0 {{ $category ? 'text-gray-400' : 'text-indigo-600' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25a2.25 2.25 0 0 1-2.25-2.25v-2.25Z" /></svg>
                            All products
                            <span class="{{ $count }} {{ $category ? 'bg-gray-100 text-gray-500' : 'bg-indigo-100 text-indigo-700' }}">{{ $counts['all'] }}</span>
                        </a>
                    </li>
                    @foreach ($categories as $item)
                        @php $isCurrent = $category && $category['slug'] === $item['slug']; @endphp
                        <li>
                            <a href="{{ $filterUrl(['category' => $item['slug'], 'type' => null]) }}" @if ($isCurrent && ! $type) aria-current="page" @endif
                                class="{{ $sideLink }} {{ $isCurrent ? $sideOn : $sideOff }}">
                                <svg class="h-5 w-5 shrink-0 {{ $isCurrent ? 'text-indigo-600' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" /></svg>
                                <span class="min-w-0 leading-tight">{{ $item['name'] }}</span>
                                <span class="{{ $count }} {{ $isCurrent ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-500' }}">{{ $counts['categories'][$item['slug']] ?? 0 }}</span>
                            </a>
                            @if ($isCurrent)
                                <ul class="my-1 ml-6 space-y-0.5 border-l border-gray-200 pl-3">
                                    @foreach ($item['types'] as $slug => $name)
                                        <li>
                                            <a href="{{ $filterUrl(['type' => $slug]) }}" @if ($type === $slug) aria-current="page" @endif
                                                class="flex items-center rounded-lg px-2.5 py-1.5 text-sm transition {{ $type === $slug ? 'bg-indigo-600 font-semibold text-white' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                                {{ $name }}
                                                <span class="ml-auto text-xs {{ $type === $slug ? 'text-white/80' : 'text-gray-400' }}">{{ $counts['types'][$item['slug'] . '/' . $slug] ?? 0 }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="border-t border-gray-100 pt-5">
                <h2 class="px-3 text-xs font-semibold uppercase tracking-wider text-gray-500">Price</h2>
                <ul class="mt-2 space-y-0.5">
                    <li>
                        <a href="{{ $filterUrl(['min_price' => null, 'max_price' => null]) }}" class="{{ $sideLink }} {{ $hasPrice ? $sideOff : $sideOn }}">
                            <span class="h-4 w-4 shrink-0 rounded-full {{ $hasPrice ? 'border-2 border-gray-300' : 'border-[5px] border-indigo-600' }}"></span>
                            Any price
                        </a>
                    </li>
                    @foreach ($priceRanges as [$label, $min, $max, $rangeCount])
                        @php $isCurrent = $hasPrice && $minPrice === $min && $maxPrice === $max; @endphp
                        <li>
                            <a href="{{ $filterUrl(['min_price' => $min, 'max_price' => $max]) }}" @if ($isCurrent) aria-current="page" @endif
                                class="{{ $sideLink }} {{ $isCurrent ? $sideOn : $sideOff }}">
                                <span class="h-4 w-4 shrink-0 rounded-full {{ $isCurrent ? 'border-[5px] border-indigo-600' : 'border-2 border-gray-300' }}"></span>
                                {{ $label }}
                                <span class="{{ $count }} bg-gray-100 text-gray-500">{{ $rangeCount }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>

                {{-- Custom range: keeps the other filters as hidden fields --}}
                <form method="GET" action="{{ route('products') }}" class="mt-3 px-1">
                    @foreach (['category' => $category['slug'] ?? null, 'type' => $type, 'form' => $form['slug'] ?? null] as $key => $value)
                        @if ($value)<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
                    @endforeach
                    <div class="flex items-center gap-2">
                        <label class="relative flex-1">
                            <span class="sr-only">Minimum price</span>
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-400">₹</span>
                            <input type="number" name="min_price" min="0" step="1" inputmode="numeric" placeholder="Min" value="{{ $minPrice }}"
                                class="h-10 w-full rounded-lg border border-gray-200 bg-gray-50/70 pl-7 pr-2 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-500/15">
                        </label>
                        <span class="text-gray-400">-</span>
                        <label class="relative flex-1">
                            <span class="sr-only">Maximum price</span>
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-400">₹</span>
                            <input type="number" name="max_price" min="0" step="1" inputmode="numeric" placeholder="Max" value="{{ $maxPrice }}"
                                class="h-10 w-full rounded-lg border border-gray-200 bg-gray-50/70 pl-7 pr-2 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-500/15">
                        </label>
                    </div>
                    <button type="submit" class="mt-2 h-10 w-full rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 text-sm font-semibold text-white shadow-sm transition hover:shadow-md">Apply price</button>
                </form>
            </div>

            <div class="border-t border-gray-100 pt-5">
                <h2 class="px-3 text-xs font-semibold uppercase tracking-wider text-gray-500">Form</h2>
                <ul class="mt-2 space-y-0.5">
                    <li>
                        <a href="{{ $filterUrl(['form' => null]) }}" class="{{ $sideLink }} {{ $form ? $sideOff : $sideOn }}">
                            <span class="h-4 w-4 shrink-0 rounded-full {{ $form ? 'border-2 border-gray-300' : 'border-[5px] border-indigo-600' }}"></span>
                            Any form
                        </a>
                    </li>
                    @foreach ($forms as $item)
                        @php $isCurrent = $form && $form['slug'] === $item['slug']; @endphp
                        <li>
                            <a href="{{ $filterUrl(['form' => $item['slug']]) }}" title="{{ $item['examples'] }}" @if ($isCurrent) aria-current="page" @endif
                                class="{{ $sideLink }} {{ $isCurrent ? $sideOn : $sideOff }}">
                                <span class="h-4 w-4 shrink-0 rounded-full {{ $isCurrent ? 'border-[5px] border-indigo-600' : 'border-2 border-gray-300' }}"></span>
                                {{ $item['name'] }}
                                <span class="{{ $count }} bg-gray-100 text-gray-500">{{ $counts['forms'][$item['slug']] ?? 0 }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            @if ($hasFilters)
                <a href="{{ route('products') }}" class="block rounded-xl border border-gray-200 py-2 text-center text-sm font-semibold text-gray-700 transition hover:border-indigo-300 hover:text-indigo-700">Clear all filters</a>
            @endif
        </aside>
    </div>

    {{-- Products --}}
    <div class="min-w-0">
        <div class="flex flex-wrap items-center gap-2">
            <p class="mr-2 text-sm text-gray-500"><span class="font-semibold text-gray-900">{{ $products->count() }}</span> {{ \Illuminate\Support\Str::plural('product', $products->count()) }}</p>
            @foreach (array_filter([
                $category ? [$category['name'], $filterUrl(['category' => null, 'type' => null])] : null,
                $typeName ? [$typeName, $filterUrl(['type' => null])] : null,
                $form ? [$form['name'], $filterUrl(['form' => null])] : null,
                $priceLabel ? [$priceLabel, $filterUrl(['min_price' => null, 'max_price' => null])] : null,
            ]) as [$label, $removeUrl])
                <a href="{{ $removeUrl }}" class="inline-flex items-center gap-1 rounded-full bg-indigo-50 py-1 pl-3 pr-2 text-xs font-semibold text-indigo-700 ring-1 ring-indigo-200 transition hover:bg-indigo-100" aria-label="Remove filter {{ $label }}">
                    {{ $label }}
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </a>
            @endforeach
        </div>

        @if ($products->isNotEmpty())
            <div class="mt-5 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($products as $product)
                    @include('partials.product-card')
                @endforeach
            </div>
        @else
            <div class="mt-5 rounded-3xl border border-dashed border-gray-300 bg-white p-12 text-center sm:p-16">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 7.5 12 3l8.25 4.5M3.75 7.5 12 12m-8.25-4.5V16.5L12 21m0-9 8.25-4.5M12 12v9m8.25-13.5V16.5L12 21"/></svg>
                </div>
                <h2 class="mt-5 text-xl font-semibold text-gray-900">No products here yet</h2>
                <p class="mx-auto mt-2 max-w-md text-gray-500">Try another category or form, or clear the filters to see everything.</p>
                <a href="{{ route('products') }}" class="mt-6 inline-flex items-center rounded-full bg-white px-5 py-2.5 text-sm font-semibold text-indigo-600 ring-1 ring-indigo-200 transition hover:bg-indigo-50">Clear filters</a>
            </div>
        @endif
    </div>
</div>

<script>
    // Phones: the Filters button shows / hides the sidebar.
    (() => {
        const toggle = document.getElementById('filters-toggle');
        const panel = document.getElementById('filters-panel');
        toggle.addEventListener('click', () => {
            const open = panel.classList.toggle('hidden') === false;
            toggle.setAttribute('aria-expanded', String(open));
        });
    })();
</script>
@endsection
