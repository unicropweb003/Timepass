@extends('layouts.app', ['title' => 'Home'])

@section('content')
{{-- Enable scroll-reveal only when JS runs, so content stays visible without it. --}}
<script>document.documentElement.classList.add('js-reveal');</script>

<style>
    @keyframes tp-float   { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-14px); } }
    @keyframes tp-float-2 { 0%,100% { transform: translateY(0) rotate(6deg); } 50% { transform: translateY(-18px) rotate(6deg); } }
    @keyframes tp-float-3 { 0%,100% { transform: translateY(0) rotate(-6deg); } 50% { transform: translateY(-10px) rotate(-6deg); } }
    @keyframes tp-spin-slow { to { transform: rotate(360deg); } }
    @keyframes tp-blob { 0%,100% { transform: translate(0,0) scale(1); } 33% { transform: translate(20px,-18px) scale(1.08); } 66% { transform: translate(-16px,12px) scale(.95); } }

    .tp-float   { animation: tp-float 6s ease-in-out infinite; }
    .tp-float-2 { animation: tp-float-2 7s ease-in-out infinite; }
    .tp-float-3 { animation: tp-float-3 5.5s ease-in-out infinite; }
    .tp-blob    { animation: tp-blob 14s ease-in-out infinite; }
    .tp-spin-slow { animation: tp-spin-slow 22s linear infinite; }

    /* Scroll reveal */
    .js-reveal [data-reveal] { opacity: 0; transform: translateY(26px); transition: opacity .7s ease, transform .7s cubic-bezier(.22,1,.36,1); }
    .js-reveal [data-reveal].in { opacity: 1; transform: none; }

    @media (prefers-reduced-motion: reduce) {
        .tp-float, .tp-float-2, .tp-float-3, .tp-blob, .tp-spin-slow { animation: none; }
        .js-reveal [data-reveal] { opacity: 1 !important; transform: none !important; transition: none; }
        [data-tilt-card] { transform: none !important; }
    }
</style>

@php
    // One colour per category (matches the product image tiles + About page).
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
    $categories = config('shop.categories', []);
    $productCounts = collect(config('products', []))->countBy('category');
@endphp

{{-- ============================ HERO (compact · classic) ============================ --}}
<section data-reveal class="relative mt-2 overflow-hidden rounded-[2rem] border border-gray-100 bg-[#f7f3ee] shadow-sm">
    <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-indigo-200/35 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-24 left-1/4 h-64 w-64 rounded-full bg-pink-200/35 blur-3xl"></div>

    <div class="relative grid items-center gap-8 px-6 py-10 sm:px-10 sm:py-12 lg:grid-cols-[1.08fr_0.92fr] lg:gap-10 lg:py-14 lg:pl-14">
        {{-- Copy --}}
        <div>
            <span class="inline-flex items-center gap-3 text-[11px] font-semibold uppercase tracking-[0.3em] text-indigo-600">
                <span class="h-px w-7 bg-indigo-400"></span> TimePass Beauty
            </span>
            <h1 class="font-display mt-6 text-[2.5rem] font-bold leading-[1.08] tracking-tight text-gray-900 sm:text-5xl lg:text-[3.5rem]">
                Beauty that feels<br>effortlessly <em class="not-italic text-indigo-600">you</em>.
            </h1>
            <p class="mt-5 max-w-md text-base leading-relaxed text-gray-600">
                A refined edit of skin care, hair care, makeup and fragrance — chosen with care, priced with honesty.
            </p>
            <div class="mt-8 flex flex-wrap items-center gap-x-6 gap-y-4">
                <a href="{{ route('products') }}"
                   class="group inline-flex h-12 items-center gap-2 rounded-full bg-gray-900 px-7 text-sm font-semibold text-white shadow-lg transition hover:-translate-y-0.5 hover:bg-gray-800">
                    Shop the collection
                    <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                </a>
                <a href="#offer" class="group inline-flex items-center gap-1.5 border-b border-gray-900 pb-0.5 text-sm font-semibold text-gray-900 transition hover:border-indigo-600 hover:text-indigo-600">
                    View today's offer
                    <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                </a>
            </div>
            <div class="mt-8 flex flex-wrap gap-x-6 gap-y-2 text-xs font-medium text-gray-500">
                <span class="inline-flex items-center gap-1.5"><span class="text-indigo-500">✦</span> Free delivery over ₹499</span>
                <span class="inline-flex items-center gap-1.5"><span class="text-indigo-500">✦</span> 7-day easy returns</span>
                <span class="inline-flex items-center gap-1.5"><span class="text-indigo-500">✦</span> 100% genuine</span>
            </div>
        </div>

        {{-- Classic arch-framed image (swap for a real product photo later) --}}
        <div class="relative mx-auto w-full max-w-xs sm:max-w-sm lg:max-w-none">
            <div class="relative mx-auto aspect-[4/5] w-full overflow-hidden rounded-t-[11rem] rounded-b-[2rem] shadow-xl ring-1 ring-black/5">
                <img src="{{ asset('images/promos/skincare-kit.jpg') }}" alt="A curated beauty selection"
                     onerror="this.style.display='none';this.parentElement.classList.add('bg-gradient-to-br','from-indigo-400','via-purple-400','to-pink-400')"
                     class="h-full w-full object-cover">
            </div>
            <span class="tp-float absolute -left-2 top-8 flex h-[4.5rem] w-[4.5rem] flex-col items-center justify-center rounded-full bg-white text-center shadow-xl ring-1 ring-black/5 sm:-left-4">
                <span class="font-display text-xl font-bold leading-none text-indigo-600">{{ $offer['discount'] }}</span>
                <span class="mt-0.5 text-[9px] font-bold uppercase tracking-widest text-gray-500">off</span>
            </span>
        </div>
    </div>
</section>

{{-- ============================ TRUST STRIP ============================ --}}
@php
    $trust = [
        ['Thoughtfully chosen', 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z'],
        ['Free delivery over ₹499', 'M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12'],
        ['Easy 7-day returns', 'M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99'],
        ['Secure checkout', 'M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z'],
    ];
@endphp
<section data-reveal class="mt-8 grid grid-cols-2 gap-3 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:gap-4 lg:grid-cols-4 lg:p-5">
    @foreach ($trust as $i => [$label, $icon])
        <div class="flex items-center gap-3" data-reveal style="transition-delay: {{ $i * 80 }}ms">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
            </span>
            <span class="text-xs font-semibold text-gray-700 sm:text-sm">{{ $label }}</span>
        </div>
    @endforeach
</section>

{{-- ============================ SHOP BY CATEGORY (circles) ============================ --}}
<section data-reveal class="mt-16">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-sm font-bold uppercase tracking-wider text-indigo-600">A look for every mood</p>
            <h2 class="font-display mt-1 text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">What's your vibe today?</h2>
        </div>
        <a href="{{ route('products') }}" class="group inline-flex items-center gap-1.5 border-b-2 border-gray-900 pb-0.5 text-sm font-bold text-gray-900">
            Shop all
            <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
        </a>
    </div>

    <div class="mt-10 grid grid-cols-3 gap-x-5 gap-y-9 sm:grid-cols-4 lg:grid-cols-7">
        @foreach ($categories as $i => $cat)
            @php
                [$c1, $c2] = $accent($cat['slug']);
                $n = $productCounts[$cat['slug']] ?? 0;
            @endphp
            <a href="{{ route('products', ['category' => $cat['slug']]) }}" data-reveal style="transition-delay: {{ $i * 70 }}ms"
               class="group flex flex-col items-center text-center">
                <span class="relative flex aspect-square w-full max-w-[9rem] items-center justify-center overflow-hidden rounded-full transition-transform duration-300 ease-out group-hover:-translate-y-1.5"
                      style="background-color: #ffffff; background-image: linear-gradient(150deg, {{ $c1 }}1a, {{ $c2 }}26); box-shadow: 0 8px 22px -14px rgba(17,24,39,0.22), inset 0 0 0 1px {{ $c1 }}1f;">
                    {{-- Gentle deepen on hover (stays soft, not loud) --}}
                    <span class="absolute inset-0 rounded-full opacity-0 transition-opacity duration-300 group-hover:opacity-100" style="background-image: linear-gradient(150deg, {{ $c1 }}36, {{ $c2 }}4a)"></span>
                    <svg class="relative h-12 w-12 transition-transform duration-300 group-hover:scale-105 sm:h-[3.25rem] sm:w-[3.25rem]"
                         style="color: {{ $c2 }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $cat['icon'] }}"/></svg>
                </span>
                <span class="font-display mt-4 text-sm font-semibold leading-tight text-gray-900 sm:text-[0.95rem]">{{ $cat['name'] }}</span>
                <span class="mt-1 text-xs font-medium text-gray-400">{{ $n }} {{ $n === 1 ? 'item' : 'items' }}</span>
            </a>
        @endforeach
    </div>
</section>

{{-- ============================ OFFER ============================ --}}
<section id="offer" data-reveal class="mt-16 scroll-mt-28">
    <div class="grid overflow-hidden rounded-3xl bg-white shadow-xl shadow-indigo-500/5 ring-1 ring-gray-200 lg:grid-cols-5">
        <div class="relative flex flex-col justify-center overflow-hidden bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 p-6 text-white sm:p-8 lg:col-span-2 lg:p-10">
            <div class="pointer-events-none absolute -right-12 -top-12 h-40 w-40 rounded-full bg-white/10"></div>
            <div class="pointer-events-none absolute -bottom-16 -left-12 h-44 w-44 rounded-full bg-white/10"></div>
            <div class="relative">
                <span class="inline-flex items-center gap-2 rounded-full bg-white/20 px-3 py-1 text-[11px] font-semibold uppercase tracking-wider ring-1 ring-white/30">
                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-yellow-300"></span>
                    {{ $offer['badge'] }}
                </span>
                <p class="mt-5 text-sm font-semibold text-white/90">{{ $offer['title'] }}</p>
                <p class="mt-1 flex items-end gap-2 leading-none">
                    <span class="text-6xl font-black tracking-tight">{{ $offer['discount'] }}</span>
                    <span class="pb-1.5 text-2xl font-bold">OFF</span>
                </p>
                <p class="mt-2 text-sm text-white/80">On everything in the store</p>
                <div class="mt-6 flex flex-wrap items-center gap-2">
                    <span class="rounded-lg border border-dashed border-white/60 bg-white/10 px-4 py-2 font-mono text-base font-bold tracking-widest">{{ $offer['code'] }}</span>
                    <button type="button" data-copy-code="{{ $offer['code'] }}"
                        class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-purple-700 shadow-sm transition hover:bg-purple-50">Copy code</button>
                </div>
                <p class="mt-6 text-[11px] font-semibold uppercase tracking-wider text-white/70">Offer ends in</p>
                <div id="offer-countdown" data-ends-at="{{ \Illuminate\Support\Carbon::parse($offer['ends_at'])->toIso8601String() }}" class="mt-2 grid max-w-[18rem] grid-cols-4 gap-2 text-center">
                    @foreach (['days' => 'Days', 'hours' => 'Hours', 'minutes' => 'Mins', 'seconds' => 'Secs'] as $unit => $label)
                        <div class="rounded-lg bg-white/15 py-2 ring-1 ring-white/20">
                            <span data-unit="{{ $unit }}" class="block text-xl font-bold leading-tight tabular-nums">00</span>
                            <span class="text-[10px] font-medium uppercase tracking-wide text-white/70">{{ $label }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="flex flex-col justify-center p-6 sm:p-8 lg:col-span-3 lg:p-10">
            <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">About this offer</p>
            <h2 class="font-display mt-2 text-2xl font-bold text-gray-900 sm:text-3xl">{{ $offer['title'] }}: {{ $offer['discount'] }} off everything</h2>
            <p class="mt-3 text-gray-600">{{ $offer['description'] }}</p>
            <ul class="mt-6 grid gap-x-6 gap-y-3 sm:grid-cols-2">
                @foreach ($offer['benefits'] as $benefit)
                    <li class="flex items-start gap-2.5 text-sm text-gray-700">
                        <span class="mt-px flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        </span>
                        {{ $benefit }}
                    </li>
                @endforeach
            </ul>
            <div class="mt-6 flex flex-wrap items-center gap-x-5 gap-y-3">
                <a href="{{ route('products') }}"
                    class="inline-flex h-11 items-center gap-2 rounded-full bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500 px-6 text-sm font-semibold text-white shadow-lg shadow-purple-500/25 transition hover:-translate-y-0.5 hover:shadow-xl">
                    Shop the sale
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                </a>
                <p class="text-sm text-gray-500">Use code <span class="font-mono font-bold text-gray-900">{{ $offer['code'] }}</span> at checkout</p>
            </div>
        </div>
    </div>
</section>

{{-- ============================ BEST SELLERS (premium cards) ============================ --}}
@if (! empty($bestSellers['products']))
<section data-reveal class="mt-20">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-sm font-bold uppercase tracking-wider text-indigo-600">The ones you come back for</p>
            <h2 class="font-display mt-1 text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">Best sellers. For good reason.</h2>
        </div>
        <a href="{{ $bestSellers['link'] ?? route('products') }}" class="group inline-flex items-center gap-1.5 border-b-2 border-gray-900 pb-0.5 text-sm font-bold text-gray-900">
            Meet the favourites
            <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
        </a>
    </div>
    <div class="mt-8 grid grid-cols-2 gap-4 sm:gap-5 lg:grid-cols-4">
        @foreach (array_slice($bestSellers['products'], 0, 4) as $i => $product)
            <div data-reveal style="transition-delay: {{ $i * 80 }}ms">
                @include('partials.product-card-premium', ['product' => $product, 'accent' => $accent($product['category_slug'] ?? '')])
            </div>
        @endforeach
    </div>
</section>
@endif

{{-- ============================ BANNER SLIDER ============================ --}}
<section class="group/slider relative mt-20" data-banner-slider data-reveal aria-roledescription="carousel" aria-label="Featured collections">
    @if (count($wideBanners) > 1)
        @foreach (['prev' => ['Previous banner', 'left-3', 'M15.75 19.5 8.25 12l7.5-7.5'], 'next' => ['Next banner', 'right-3', 'm8.25 4.5 7.5 7.5-7.5 7.5']] as $dir => [$label, $pos, $path])
            <button type="button" data-slider-{{ $dir }} aria-label="{{ $label }}"
                class="absolute {{ $pos }} top-[calc(50%-1.25rem)] z-20 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/90 text-gray-800 shadow-lg ring-1 ring-gray-900/10 backdrop-blur transition hover:scale-105 hover:bg-white sm:opacity-0 sm:group-hover/slider:opacity-100 sm:focus-visible:opacity-100">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2.4" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" /></svg>
            </button>
        @endforeach
    @endif
    <div class="grid cursor-grab touch-pan-y select-none active:cursor-grabbing" data-slider-track>
        @foreach ($wideBanners as $banner)
            <div data-slide class="col-start-1 row-start-1 transition-opacity duration-700 {{ $loop->first ? 'opacity-100' : 'pointer-events-none opacity-0' }}"
                @unless ($loop->first) inert @endunless>
                @include('partials.banner', ['banner' => $banner, 'wide' => true])
            </div>
        @endforeach
    </div>
    <div class="mt-4 flex justify-center gap-2">
        @foreach ($wideBanners as $banner)
            <button type="button" data-dot="{{ $loop->index }}" aria-label="Show banner {{ $loop->iteration }}"
                class="h-2.5 rounded-full transition-all {{ $loop->first ? 'w-8 bg-indigo-600' : 'w-2.5 bg-gray-300 hover:bg-gray-400' }}"></button>
        @endforeach
    </div>
</section>

{{-- ============================ BRAND STORY ============================ --}}
<section data-reveal class="mt-20 grid items-center gap-10 lg:grid-cols-2 lg:gap-16">
    <div class="order-2 lg:order-1">
        <p class="text-sm font-bold uppercase tracking-wider text-indigo-600">Good skin. Better days.</p>
        <h2 class="font-display mt-3 text-3xl font-bold leading-tight tracking-tight text-gray-900 sm:text-4xl">Everyday beauty.<br>Extraordinary you.</h2>
        <p class="mt-5 max-w-lg text-gray-600 leading-relaxed">
            From your morning routine to your night-time ritual, there's a TimePass pick for every little moment.
            Discover a world of care, colour and feel-good favourites — made simple, honest and affordable.
        </p>
        <a href="{{ route('about') }}"
           class="mt-7 inline-flex h-12 items-center gap-2 rounded-full bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500 px-7 text-sm font-bold text-white shadow-lg shadow-purple-500/30 transition hover:-translate-y-0.5 hover:shadow-xl">
            Get to know us
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.4" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
        </a>
        <p class="mt-3 text-sm text-gray-400">A little self-care goes a long way.</p>
    </div>

    <div class="relative order-1 lg:order-2">
        <div class="overflow-hidden rounded-[2rem] shadow-2xl shadow-indigo-500/10 ring-1 ring-gray-900/5">
            <img src="{{ asset('images/hero.jpg') }}" alt="A curated selection of beauty and personal-care products"
                 onerror="this.style.display='none';this.parentElement.classList.add('bg-gradient-to-br','from-indigo-500','via-purple-500','to-pink-500','aspect-[4/3]')"
                 class="h-full w-full object-cover">
        </div>
        <span class="tp-float absolute -bottom-6 -left-4 flex h-24 w-24 items-center justify-center rounded-full bg-yellow-300 text-center text-[11px] font-black uppercase leading-tight text-gray-900 shadow-xl sm:-left-6 sm:h-28 sm:w-28">Glow<br>more,<br>stress less</span>
    </div>
</section>

{{-- ============================ NEW ARRIVALS (premium cards) ============================ --}}
@if (! empty($recommended['products']))
<section data-reveal class="mt-20">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-sm font-bold uppercase tracking-wider text-indigo-600">Just in</p>
            <h2 class="font-display mt-1 text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">New arrivals.</h2>
        </div>
        <a href="{{ $recommended['link'] ?? route('products') }}" class="group inline-flex items-center gap-1.5 border-b-2 border-gray-900 pb-0.5 text-sm font-bold text-gray-900">
            See what's new
            <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
        </a>
    </div>
    <div class="mt-8 grid grid-cols-2 gap-4 sm:gap-5 lg:grid-cols-4">
        @foreach (array_slice($recommended['products'], 0, 4) as $i => $product)
            <div data-reveal style="transition-delay: {{ $i * 80 }}ms">
                @include('partials.product-card-premium', ['product' => $product, 'accent' => $accent($product['category_slug'] ?? '')])
            </div>
        @endforeach
    </div>
</section>
@endif

{{-- ============================ PROMO TILES ============================ --}}
<section id="promos" data-reveal class="mt-20 scroll-mt-28" aria-label="Featured products">
    <div class="grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4">
        @foreach ($promos as $i => $promo)
            <a href="{{ route('products', array_filter(['category' => $promo['category'], 'type' => $promo['type'] ?? null])) }}" data-reveal style="transition-delay: {{ $i * 70 }}ms"
                class="group relative block aspect-[8/11] overflow-hidden rounded-2xl bg-gray-100 shadow-sm ring-1 ring-gray-900/5 transition duration-300 hover:-translate-y-1.5 hover:shadow-xl hover:shadow-indigo-500/15">
                <img src="{{ asset($promo['image']) }}"
                    onerror="this.onerror=null;this.src='{{ asset('images/no-image.svg') }}';"
                    alt="{{ $promo['alt'] }}" loading="lazy"
                    class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-110">
                <div class="pointer-events-none absolute inset-x-0 top-0 h-3/5"
                    style="background: linear-gradient(to bottom, {{ $promo['tint'] }}cc 0%, {{ $promo['tint'] }}80 30%, {{ $promo['tint'] }}00 55%)"></div>
                <div class="relative max-w-[64%] p-3 sm:p-5">
                    <p class="text-[10px] font-bold uppercase tracking-[0.15em] sm:text-[11px]" style="color: {{ $promo['accent'] }}">{{ $promo['eyebrow'] }}</p>
                    <h3 class="mt-1 text-sm/tight font-black text-gray-900 sm:text-xl/tight lg:text-lg/tight xl:text-xl/tight">{{ $promo['title'] }}</h3>
                    <p class="mt-1.5 hidden text-xs leading-snug text-gray-700 sm:block lg:hidden xl:block">{{ $promo['tagline'] }}</p>
                </div>
                <span class="absolute bottom-2.5 right-2.5 inline-flex items-center gap-1 rounded-full bg-white/90 px-2.5 py-1 text-[10px] font-semibold text-gray-900 shadow-sm ring-1 ring-gray-900/5 backdrop-blur transition group-hover:bg-white sm:bottom-4 sm:right-4 sm:px-3 sm:py-1.5 sm:text-xs">
                    Shop now
                    <svg class="h-3 w-3 transition-transform group-hover:translate-x-0.5 sm:h-3.5 sm:w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                </span>
            </a>
        @endforeach
    </div>
</section>

{{-- ============================ REVIEWS ============================ --}}
<section data-reveal class="mt-20" aria-labelledby="reviews-title">
    <div class="text-center">
        <h2 id="reviews-title" class="font-display text-3xl font-bold text-gray-900 sm:text-4xl">What our customers say</h2>
        <p class="mt-2 text-gray-500">Honest words from people who shop with TimePass.</p>
    </div>
    <div class="mx-auto mt-8 grid max-w-4xl gap-6 md:grid-cols-2">
        @foreach ($reviews as $i => $review)
            <figure data-reveal style="transition-delay: {{ $i * 90 }}ms" class="flex flex-col items-center rounded-2xl bg-white px-8 py-8 text-center shadow-xl shadow-gray-200/70 ring-1 ring-gray-100 transition hover:-translate-y-1">
                <span class="flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 via-purple-600 to-pink-500 text-3xl font-bold text-white shadow-lg ring-4 ring-white">
                    {{ strtoupper(substr($review['name'], 0, 1)) }}
                </span>
                <figcaption class="mt-4">
                    <p class="text-lg font-bold text-gray-900">{{ $review['name'] }}</p>
                    <p class="mt-0.5 text-sm font-semibold text-indigo-600">{{ $review['tagline'] }}</p>
                </figcaption>
                <div class="mt-3 flex gap-1" role="img" aria-label="Rated {{ $review['rating'] }} out of 5">
                    @for ($i2 = 1; $i2 <= 5; $i2++)
                        <svg class="h-5 w-5 {{ $i2 <= $review['rating'] ? 'text-amber-400' : 'text-gray-200' }}" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 0 0 .95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 0 0-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 0 0-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 0 0-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 0 0 .951-.69l1.07-3.292Z"/></svg>
                    @endfor
                </div>
                <blockquote class="mt-4 leading-relaxed text-gray-600">"{{ $review['text'] }}"</blockquote>
            </figure>
        @endforeach
    </div>
</section>

{{-- ============================ FINAL CTA ============================ --}}
<section data-reveal class="relative mt-20 overflow-hidden rounded-[2rem] bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 px-6 py-14 text-center text-white shadow-2xl shadow-purple-500/25 sm:px-12 sm:py-16">
    <div class="tp-blob pointer-events-none absolute -right-16 -top-16 h-60 w-60 rounded-full bg-white/10 blur-2xl"></div>
    <div class="tp-blob pointer-events-none absolute -bottom-20 -left-12 h-64 w-64 rounded-full bg-pink-300/20 blur-2xl" style="animation-delay:-6s"></div>
    <div class="relative mx-auto max-w-2xl">
        <h2 class="font-display text-4xl font-bold tracking-tight sm:text-5xl">Ready to glow?</h2>
        <p class="mt-4 text-white/90">Treat yourself today — your new favourites are just a tap away.</p>
        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
            <a href="{{ route('products') }}" class="nav-shimmer inline-flex h-12 items-center gap-2 rounded-full bg-white px-7 text-sm font-bold text-indigo-700 shadow-lg transition hover:-translate-y-0.5 hover:shadow-xl">
                Start shopping
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.4" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
            </a>
            <a href="{{ route('contact') }}" class="inline-flex h-12 items-center rounded-full bg-white/10 px-6 text-sm font-bold text-white ring-1 ring-white/30 backdrop-blur transition hover:bg-white/20">Contact us</a>
        </div>
    </div>
</section>

{{-- ============================ SCRIPTS ============================ --}}
<script>
    (function () {
        const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        // Copy coupon code.
        document.querySelectorAll('[data-copy-code]').forEach((btn) => {
            btn.addEventListener('click', async () => {
                try { await navigator.clipboard.writeText(btn.dataset.copyCode); btn.textContent = 'Copied!'; }
                catch (e) { btn.textContent = btn.dataset.copyCode; }
                setTimeout(() => (btn.textContent = 'Copy code'), 2000);
            });
        });

        // Offer countdown.
        (function () {
            const box = document.getElementById('offer-countdown');
            if (!box) return;
            const end = new Date(box.dataset.endsAt).getTime();
            const set = (u, v) => (box.querySelector('[data-unit="' + u + '"]').textContent = String(v).padStart(2, '0'));
            const tick = () => {
                const left = Math.max(0, end - Date.now());
                set('days', Math.floor(left / 86400000));
                set('hours', Math.floor(left / 3600000) % 24);
                set('minutes', Math.floor(left / 60000) % 60);
                set('seconds', Math.floor(left / 1000) % 60);
                if (left === 0) clearInterval(timer);
            };
            const timer = setInterval(tick, 1000); tick();
        })();

        // Scroll reveal.
        if ('IntersectionObserver' in window) {
            const io = new IntersectionObserver((entries) => {
                entries.forEach((en) => { if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); } });
            }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
            document.querySelectorAll('[data-reveal]').forEach((el) => io.observe(el));
        } else {
            document.querySelectorAll('[data-reveal]').forEach((el) => el.classList.add('in'));
        }

        // 3D tilt on cards.
        if (!reduce && window.matchMedia('(pointer: fine)').matches) {
            document.querySelectorAll('[data-tilt]').forEach((wrap) => {
                const card = wrap.querySelector('[data-tilt-card]') || wrap;
                const MAX = 9;
                wrap.addEventListener('pointermove', (e) => {
                    const r = wrap.getBoundingClientRect();
                    const px = (e.clientX - r.left) / r.width - 0.5;
                    const py = (e.clientY - r.top) / r.height - 0.5;
                    card.style.transform = 'rotateY(' + (px * MAX) + 'deg) rotateX(' + (-py * MAX) + 'deg)';
                });
                wrap.addEventListener('pointerleave', () => { card.style.transform = ''; });
            });

            // Subtle parallax on the hero collage.
            document.querySelectorAll('[data-parallax]').forEach((scene) => {
                const layers = scene.querySelectorAll('[data-depth]');
                scene.addEventListener('pointermove', (e) => {
                    const r = scene.getBoundingClientRect();
                    const px = (e.clientX - r.left) / r.width - 0.5;
                    const py = (e.clientY - r.top) / r.height - 0.5;
                    layers.forEach((l) => {
                        const d = Number(l.dataset.depth) || 0;
                        l.style.transform = 'translate(' + (px * d) + 'px,' + (py * d) + 'px)';
                    });
                });
                scene.addEventListener('pointerleave', () => layers.forEach((l) => (l.style.transform = '')));
            });
        }

        // (Wishlist hearts are handled globally in the layout, saved to the account.)

        // Banner slider (dots, arrows, drag, autoplay).
        document.querySelectorAll('[data-banner-slider]').forEach((slider) => {
            const slides = slider.querySelectorAll('[data-slide]');
            const dots = slider.querySelectorAll('[data-dot]');
            let current = 0;
            const show = (index) => {
                current = (index + slides.length) % slides.length;
                slides.forEach((slide, i) => {
                    const on = i === current;
                    slide.classList.toggle('opacity-100', on);
                    slide.classList.toggle('opacity-0', !on);
                    slide.classList.toggle('pointer-events-none', !on);
                    slide.inert = !on;
                });
                dots.forEach((dot, i) => {
                    const on = i === current;
                    dot.classList.toggle('w-8', on);
                    dot.classList.toggle('bg-indigo-600', on);
                    dot.classList.toggle('w-2.5', !on);
                    dot.classList.toggle('bg-gray-300', !on);
                });
            };
            const autoplay = slides.length > 1 && !reduce;
            let timer = null;
            const stop = () => { clearInterval(timer); timer = null; };
            const start = () => { stop(); if (autoplay) timer = setInterval(() => show(current + 1), 5000); };
            const go = (index) => { show(index); start(); };
            dots.forEach((dot) => dot.addEventListener('click', () => go(Number(dot.dataset.dot))));
            slider.querySelector('[data-slider-prev]')?.addEventListener('click', () => go(current - 1));
            slider.querySelector('[data-slider-next]')?.addEventListener('click', () => go(current + 1));
            const track = slider.querySelector('[data-slider-track]');
            let startX = null, dragged = false;
            track.addEventListener('pointerdown', (e) => { startX = e.clientX; dragged = false; });
            track.addEventListener('pointermove', (e) => { if (startX !== null && Math.abs(e.clientX - startX) > 10) dragged = true; });
            track.addEventListener('pointerup', (e) => {
                if (startX === null) return;
                const dx = e.clientX - startX; startX = null;
                if (Math.abs(dx) > 50) go(current + (dx < 0 ? 1 : -1));
            });
            track.addEventListener('pointercancel', () => { startX = null; });
            track.addEventListener('click', (e) => { if (dragged) { e.preventDefault(); dragged = false; } }, true);
            track.addEventListener('dragstart', (e) => e.preventDefault());
            slider.addEventListener('keydown', (e) => { if (e.key === 'ArrowLeft') go(current - 1); if (e.key === 'ArrowRight') go(current + 1); });
            slider.addEventListener('mouseenter', stop);
            slider.addEventListener('mouseleave', start);
            start();
        });
    })();
</script>
@endsection
