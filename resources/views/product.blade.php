@extends('layouts.app', ['seo' => \App\Support\Seo::product($product)])

@section('content')
<script>document.documentElement.classList.add('pd-js');</script>
<style>
    @keyframes pd-float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-14px); } }
    @keyframes pd-glow  { 0%,100% { opacity: .45; } 50% { opacity: .8; } }
    @keyframes pd-spin  { to { transform: rotate(360deg); } }
    .pd-float { animation: pd-float 5s ease-in-out infinite; }
    .pd-spin  { animation: pd-spin 20s linear infinite; }
    .pd-glow  { animation: pd-glow 4s ease-in-out infinite; }
    [data-tilt-card] { transition: transform .25s cubic-bezier(.22,1,.36,1); will-change: transform; }

    .pd-js .pd-reveal { opacity: 0; transform: translateY(26px); transition: opacity .7s ease, transform .7s cubic-bezier(.22,1,.36,1); }
    .pd-js .pd-reveal.in { opacity: 1; transform: none; }

    @media (prefers-reduced-motion: reduce) {
        .pd-float, .pd-spin, .pd-glow { animation: none; }
        [data-tilt-card] { transform: none !important; }
        .pd-js .pd-reveal { opacity: 1 !important; transform: none !important; }
    }
</style>

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
    [$c1, $c2] = $accent($product['category_slug'] ?? '');
    $grad = 'linear-gradient(135deg,' . $c1 . ',' . $c2 . ')';
    $hasDiscount = ! empty($product['mrp']) && $product['mrp'] > $product['price'];
    $off = $hasDiscount ? round(100 - $product['price'] / $product['mrp'] * 100) : 0;
    $formName = collect(config('shop.forms', []))->firstWhere('slug', $product['form_slug'] ?? null)['name'] ?? null;
    $canBuy = isset($product['id']) && (! isset($product['stock']) || (int) $product['stock'] > 0);
    $buyUrl = $canBuy ? route('checkout.show', $product['slug']) : null;
@endphp

{{-- Breadcrumb --}}
<nav class="flex flex-wrap items-center gap-2 text-sm text-gray-500" aria-label="Breadcrumb">
    <a href="{{ route('home') }}" class="transition hover:text-gray-900">Home</a>
    <span class="text-gray-300">/</span>
    <a href="{{ route('products', ['category' => $product['category_slug']]) }}" class="transition hover:text-gray-900">{{ $product['category'] }}</a>
    <span class="text-gray-300">/</span>
    <span class="font-medium text-gray-800">{{ $product['name'] }}</span>
</nav>

<div class="mt-6 grid gap-8 lg:grid-cols-2 lg:gap-12">
    {{-- 3D product showcase --}}
    <div data-tilt-scene class="relative [perspective:1400px]">
        <div data-tilt-card class="relative overflow-hidden rounded-[2rem] p-8 shadow-xl shadow-indigo-500/10 [transform-style:preserve-3d] sm:p-12"
             style="background-image: radial-gradient(120% 120% at 30% 18%, #ffffff 0%, {{ $c1 }}1f 48%, {{ $c2 }}3d 100%)">
            {{-- pulsing glow behind the product --}}
            <div class="pd-glow pointer-events-none absolute left-1/2 top-1/2 h-64 w-64 -translate-x-1/2 -translate-y-1/2 rounded-full blur-3xl" style="background-image: {{ $grad }}" data-depth="8"></div>

            {{-- floating sparkle accents --}}
            <span class="pointer-events-none absolute left-7 top-8 text-white/70" data-depth="34">
                <svg class="pd-float h-7 w-7" style="animation-delay:-1s" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l1.8 5.2L19 9l-5.2 1.8L12 16l-1.8-5.2L5 9l5.2-1.8L12 2z"/></svg>
            </span>
            <span class="pointer-events-none absolute bottom-10 right-9 text-white/60" data-depth="26">
                <svg class="pd-float h-5 w-5" style="animation-delay:-2.5s" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l1.8 5.2L19 9l-5.2 1.8L12 16l-1.8-5.2L5 9l5.2-1.8L12 2z"/></svg>
            </span>

            {{-- discount badge, popped forward --}}
            @if ($hasDiscount)
                <span class="absolute left-6 top-6 z-10 rounded-full px-3 py-1.5 text-xs font-black text-white shadow-lg [transform:translateZ(70px)]" style="background-image: {{ $grad }}">{{ $off }}% OFF</span>
            @endif

            {{-- product image lifted in 3D, floating, with a ground shadow --}}
            <div class="relative flex items-center justify-center [transform:translateZ(55px)]" data-depth="14">
                <img src="{{ asset($product['image'] ?: 'images/no-image.svg') }}"
                     onerror="this.onerror=null;this.src='{{ asset('images/no-image.svg') }}';"
                     alt="{{ $product['name'] }}"
                     class="pd-float aspect-square w-full max-w-md object-contain drop-shadow-2xl">
            </div>
            <div class="pointer-events-none absolute bottom-10 left-1/2 h-5 w-2/3 -translate-x-1/2 rounded-[50%] bg-black/20 blur-xl"></div>
        </div>
    </div>

    {{-- Info --}}
    <div class="pd-reveal">
        <div class="flex items-start justify-between gap-4">
            <p class="text-xs font-bold uppercase tracking-[0.2em]" style="color: {{ $c2 }}">{{ $product['category'] }} · Made for your everyday</p>
            <button type="button" data-wish="{{ $product['id'] ?? '' }}" aria-label="Save to favourites"
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-400 shadow-sm transition hover:scale-105 hover:text-rose-500">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
            </button>
        </div>

        <h1 class="font-display mt-3 text-3xl font-medium tracking-tight text-gray-900 sm:text-4xl">{{ $product['name'] }}</h1>

        @if (isset($product['id']) && ! empty($product['reviews']))
            <div class="mt-3 flex items-center gap-2">
                <div class="flex gap-0.5">
                    @for ($i = 1; $i <= 5; $i++)
                        <svg class="h-5 w-5 {{ $i <= round($product['rating']) ? 'text-amber-400' : 'text-gray-200' }}" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 0 0 .95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 0 0-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 0 0-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 0 0-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 0 0 .951-.69l1.07-3.292Z"/></svg>
                    @endfor
                </div>
                <span class="text-sm text-gray-500">{{ number_format($product['rating'], 1) }} ({{ $product['reviews'] ?? 0 }} reviews)</span>
            </div>
        @endif

        <p class="mt-4 text-gray-600 leading-relaxed">{{ $product['description'] }}</p>

        {{-- Price panel --}}
        <div class="mt-6 rounded-2xl border border-gray-100 bg-gray-50/70 p-5">
            <div class="flex flex-wrap items-baseline gap-3">
                <span class="text-4xl font-black tracking-tight text-gray-900">₹{{ number_format($product['price']) }}</span>
                @if ($hasDiscount)
                    <span class="text-lg text-gray-400 line-through">₹{{ number_format($product['mrp']) }}</span>
                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-sm font-bold text-emerald-600">Save {{ $off }}%</span>
                @endif
            </div>
            <p class="mt-1 text-xs text-gray-400">Inclusive of all taxes. Shipping calculated at checkout.</p>
            @if ($canBuy)
                <p class="mt-3 inline-flex items-center gap-2 text-sm font-semibold text-emerald-600">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span> In stock · ready to ship
                </p>
            @else
                <p class="mt-3 inline-flex items-center gap-2 text-sm font-semibold text-amber-700">
                    <span class="h-2 w-2 rounded-full bg-amber-500"></span> Online purchase unavailable
                </p>
            @endif
        </div>

        {{-- Buy --}}
        <div class="mt-5">
            @if ($canBuy)
            <a href="{{ $buyUrl }}"
               class="nav-shimmer group inline-flex w-full items-center justify-center gap-2 rounded-xl px-6 py-4 text-sm font-bold uppercase tracking-wider text-white shadow-lg transition hover:-translate-y-0.5 hover:shadow-xl" style="background-image: {{ $grad }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                Buy now
                <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
            </a>
            @else
                <span class="inline-flex w-full items-center justify-center rounded-xl border border-gray-200 bg-gray-100 px-6 py-4 text-sm font-bold uppercase tracking-wider text-gray-500">Online purchase unavailable</span>
            @endif
        </div>

        <div class="mt-3 grid gap-3 sm:grid-cols-2">
            <a href="{{ route('contact', ['product' => $product['name']]) }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 px-5 py-3 text-sm font-semibold text-gray-700 transition hover:border-indigo-300 hover:text-indigo-700">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 0 1 .778-.332 48.294 48.294 0 0 0 5.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z"/></svg>
                Ask about this product
            </a>
            <a href="{{ route('contact', ['product' => $product['name'] . ' (bulk / gifting)']) }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 px-5 py-3 text-sm font-semibold text-gray-700 transition hover:border-indigo-300 hover:text-indigo-700">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
                Bulk / gifting inquiry
            </a>
        </div>

        <div class="mt-6 flex flex-wrap gap-x-6 gap-y-2 border-t border-gray-100 pt-5 text-sm text-gray-600">
            <span class="inline-flex items-center gap-2">
                <svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25"/></svg>
                Free shipping above ₹499
            </span>
            <span class="inline-flex items-center gap-2">
                <svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
                Secure checkout
            </span>
        </div>
    </div>
</div>

{{-- Product details (expandable) --}}
@php
    $specs = array_values(array_filter([
        ['Category', $product['category'] ?? null, 'M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z'],
        ['Type', $product['type'] ?? null, 'M6.429 9.75 2.25 12l4.179 2.25m0-4.5 5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0 4.179 2.25L12 21.75 2.25 14.25l4.179-2.25m11.142 0-5.571 3-5.571-3'],
        ['Form', $formName, 'M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23-.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5'],
        ['Price', '₹' . number_format($product['price']), 'M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
    ], fn ($s) => ! empty($s[1])));

    // Keep the admin's plain-text details intact while giving headings, lists and steps proper structure.
    $detailText = trim((string) (($product['details'] ?? null) ?: ($product['description'] ?: 'No description available for this product yet.')));
    $detailSections = [];
    $section = ['title' => 'Overview', 'blocks' => []];
    $block = null;
    $flushBlock = function () use (&$block, &$section): void {
        if ($block !== null) {
            $section['blocks'][] = $block;
            $block = null;
        }
    };
    $flushSection = function () use (&$section, &$detailSections, $flushBlock): void {
        $flushBlock();
        if ($section['blocks'] !== []) {
            $detailSections[] = $section;
        }
    };

    foreach (preg_split('/\R/u', $detailText) ?: [] as $rawLine) {
        $line = trim($rawLine);
        if ($line === '') {
            $flushBlock();
            continue;
        }

        if (preg_match('/^[\p{Lu}\d][\p{Lu}\p{N}\s&\/(),+:.\-]{2,80}$/u', $line)) {
            $flushSection();
            $section = ['title' => rtrim($line, ':'), 'blocks' => []];
            continue;
        }

        if (preg_match('/^[•\-*]\s+(.+)$/u', $line, $match)) {
            $kind = 'bullet';
            $value = $match[1];
        } elseif (preg_match('/^(\d+)[.)]\s+(.+)$/u', $line, $match)) {
            $kind = 'step';
            $value = ['number' => $match[1], 'text' => $match[2]];
        } else {
            $kind = 'paragraph';
            $value = $line;
        }

        if ($block === null || $block['kind'] !== $kind) {
            $flushBlock();
            $block = ['kind' => $kind, 'items' => []];
        }
        $block['items'][] = $value;
    }
    $flushSection();
@endphp
<section class="pd-reveal mt-12">
    <details open class="group overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
        <summary class="flex cursor-pointer items-center gap-4 px-6 py-5 [&::-webkit-details-marker]:hidden sm:px-8">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-white shadow-md" style="background-image: {{ $grad }}">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/></svg>
            </span>
            <span class="min-w-0 flex-1">
                <span class="font-display block text-lg font-medium text-gray-900 sm:text-xl">Product details</span>
                <span class="block text-xs text-gray-500">Benefits, ingredients, directions and specifications</span>
            </span>
            <svg class="h-6 w-6 shrink-0 text-gray-400 transition-transform duration-300 group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/></svg>
        </summary>
        <div class="border-t border-gray-100 bg-gray-50/40 px-5 py-6 sm:px-8 sm:py-8">
            <div class="grid gap-4 md:grid-cols-2 md:gap-5">
                @foreach ($detailSections as $detailSection)
                    @php
                        $hasSteps = collect($detailSection['blocks'])->contains(fn ($detailBlock) => $detailBlock['kind'] === 'step');
                        $isWide = $detailSection['title'] === 'Overview' || $hasSteps;
                    @endphp
                    <section class="min-w-0 rounded-2xl border border-gray-200/80 bg-white p-5 shadow-sm sm:p-6 {{ $isWide ? 'md:col-span-2' : '' }}">
                        <h3 class="font-display text-lg font-medium leading-snug text-gray-900 sm:text-xl">{{ $detailSection['title'] }}</h3>
                        <div class="mt-3 h-0.5 w-10 rounded-full" style="background-color: {{ $c1 }}"></div>
                        <div class="mt-4 space-y-4 text-[15px] leading-7 text-gray-700">
                            @foreach ($detailSection['blocks'] as $detailBlock)
                                @if ($detailBlock['kind'] === 'bullet')
                                    <ul class="space-y-2.5">
                                        @foreach ($detailBlock['items'] as $item)
                                            <li class="flex items-start gap-3"><span aria-hidden="true" class="mt-[11px] h-1.5 w-1.5 shrink-0 rounded-full" style="background-color: {{ $c1 }}"></span><span class="min-w-0">{{ $item }}</span></li>
                                        @endforeach
                                    </ul>
                                @elseif ($detailBlock['kind'] === 'step')
                                    <ol class="space-y-3">
                                        @foreach ($detailBlock['items'] as $item)
                                            <li class="flex items-start gap-3"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold" style="background-color: {{ $c1 }}1a; color: {{ $c2 }}">{{ $item['number'] }}</span><span class="min-w-0">{{ $item['text'] }}</span></li>
                                        @endforeach
                                    </ol>
                                @else
                                    <p>{{ implode(' ', $detailBlock['items']) }}</p>
                                @endif
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>

            {{-- Specifications: modern strip with soft icons + subtle dividers --}}
            <h3 class="mt-8 font-display text-lg font-medium text-gray-900 sm:text-xl">Specifications</h3>
            <div class="mt-3 grid grid-cols-2 divide-x divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-100 bg-white sm:grid-cols-4 sm:divide-y-0">
                @foreach ($specs as [$label, $value, $icon])
                    <div class="flex flex-col items-center gap-2.5 p-5 text-center transition hover:bg-gray-50/70">
                        <span class="flex h-11 w-11 items-center justify-center rounded-full" style="background-color: {{ $c1 }}1a; color: {{ $c2 }}">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                        </span>
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">{{ $label }}</span>
                        <span class="font-bold text-gray-900">{{ $value }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </details>
</section>

{{-- Related --}}
@if ($related->isNotEmpty())
    <section class="pd-reveal mt-16">
        <h2 class="font-display text-2xl font-medium tracking-tight text-gray-900 sm:text-3xl">You may also like</h2>
        <div class="mt-6 grid grid-cols-2 gap-4 sm:gap-5 lg:grid-cols-4">
            @foreach ($related as $rel)
                @include('partials.product-card-premium', ['product' => $rel, 'accent' => $accent($rel['category_slug'] ?? '')])
            @endforeach
        </div>
    </section>
@endif

<script>
    (function () {
        const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        // 3D tilt + parallax on the product showcase.
        if (!reduce && window.matchMedia('(pointer: fine)').matches) {
            document.querySelectorAll('[data-tilt-scene]').forEach((scene) => {
                const card = scene.querySelector('[data-tilt-card]');
                const layers = scene.querySelectorAll('[data-depth]');
                const MAX = 12;
                scene.addEventListener('pointermove', (e) => {
                    const r = scene.getBoundingClientRect();
                    const px = (e.clientX - r.left) / r.width - 0.5;
                    const py = (e.clientY - r.top) / r.height - 0.5;
                    if (card) card.style.transform = 'rotateY(' + (px * MAX) + 'deg) rotateX(' + (-py * MAX) + 'deg)';
                    layers.forEach((l) => {
                        const d = Number(l.dataset.depth) || 0;
                        l.style.transform = (l.style.transform.replace(/translate\([^)]*\)/, '') + ' translate(' + (px * d) + 'px,' + (py * d) + 'px)').trim();
                    });
                });
                scene.addEventListener('pointerleave', () => {
                    if (card) card.style.transform = '';
                    layers.forEach((l) => { l.style.transform = l.style.transform.replace(/translate\([^)]*\)/, '').trim(); });
                });
            });
        }

        // Scroll reveal.
        if ('IntersectionObserver' in window) {
            const io = new IntersectionObserver((entries) => {
                entries.forEach((en) => { if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); } });
            }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
            document.querySelectorAll('.pd-reveal').forEach((el) => io.observe(el));
        } else {
            document.querySelectorAll('.pd-reveal').forEach((el) => el.classList.add('in'));
        }

        // (Wishlist hearts are handled globally in the layout, saved to the account.)
    })();
</script>
@endsection
