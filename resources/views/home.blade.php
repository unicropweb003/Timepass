@extends('layouts.app', ['title' => 'Home'])

@section('content')
{{-- Full-width hero banner: headline on the left, product photo collage on the right --}}
<section class="relative left-1/2 -mt-8 w-screen -translate-x-1/2 overflow-hidden bg-gradient-to-br from-indigo-50 via-white to-pink-50">
    <div class="pointer-events-none absolute -left-24 top-10 h-80 w-80 rounded-full bg-indigo-300/30 blur-3xl"></div>
    <div class="pointer-events-none absolute -right-24 bottom-0 h-96 w-96 rounded-full bg-pink-300/30 blur-3xl"></div>

    <div class="relative mx-auto grid max-w-7xl items-center gap-12 px-6 pb-16 pt-12 sm:px-10 lg:min-h-[calc(100vh-7rem)] lg:grid-cols-2 lg:py-16">
        <div>
            @auth
                <p class="text-sm font-semibold text-indigo-600">Welcome back, {{ auth()->user()->name }} 👋</p>
            @else
                <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 text-xs font-semibold text-indigo-700 shadow-sm ring-1 ring-indigo-100">
                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-pink-500"></span>
                    New season, up to {{ $offer['discount'] }} off
                </span>
            @endauth
            <h1 class="mt-4 text-4xl font-black leading-[1.1] tracking-tight text-gray-900 sm:text-6xl">
                Beauty that fits your
                <span class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500 bg-clip-text text-transparent">everyday</span>
            </h1>
            <p class="mt-5 max-w-lg text-lg text-gray-600">Skin care, hair care, makeup, fragrances and more, all in one place. Find what suits you and shop with ease.</p>

            <div class="mt-8 flex flex-wrap items-center gap-3">
                <a href="{{ route('products') }}"
                    class="nav-animated-gradient inline-flex h-12 items-center gap-2 rounded-full bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500 px-7 font-semibold text-white shadow-lg shadow-purple-500/30 transition hover:-translate-y-0.5 hover:shadow-xl">
                    Shop now
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                </a>
                <a href="#offer" class="inline-flex h-12 items-center rounded-full bg-white px-6 font-semibold text-gray-800 shadow-sm ring-1 ring-gray-200 transition hover:ring-indigo-300 hover:text-indigo-700">See today's offer</a>
            </div>

            {{-- Quick category links --}}
            <div class="mt-10 flex flex-wrap gap-2">
                @foreach (array_slice(config('shop.categories', []), 0, 4) as $category)
                    <a href="{{ route('products', ['category' => $category['slug']]) }}"
                        class="inline-flex items-center gap-1.5 rounded-full bg-white/80 px-3 py-1.5 text-sm font-medium text-gray-700 ring-1 ring-gray-200 backdrop-blur transition hover:text-indigo-700 hover:ring-indigo-300">
                        <svg class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $category['icon'] }}"/></svg>
                        {{ $category['name'] }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Photo collage --}}
        <div class="relative mx-auto h-[360px] w-full max-w-md sm:h-[460px] lg:max-w-none" aria-hidden="true">
            <img src="{{ asset('images/promos/toothpaste.jpg') }}" alt=""
                class="absolute left-0 top-12 w-[38%] -rotate-6 rounded-3xl object-cover shadow-2xl ring-4 ring-white">
            <img src="{{ asset('images/promos/keratin-hair-care.jpg') }}" alt=""
                class="absolute right-0 top-12 w-[38%] rotate-6 rounded-3xl object-cover shadow-2xl ring-4 ring-white">
            <img src="{{ asset('images/promos/skincare-kit.jpg') }}" alt=""
                class="absolute left-1/2 top-0 z-10 w-[46%] -translate-x-1/2 rounded-3xl object-cover shadow-2xl shadow-indigo-500/20 ring-4 ring-white">
        </div>
    </div>
</section>

{{-- Current offer: one card, gradient panel on the left and details on the right --}}
<section id="offer" class="mt-12 scroll-mt-28">
    <div class="grid overflow-hidden rounded-3xl bg-white shadow-xl shadow-indigo-500/5 ring-1 ring-gray-200 lg:grid-cols-5">
        {{-- Offer panel --}}
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

                {{-- Coupon code --}}
                <div class="mt-6 flex flex-wrap items-center gap-2">
                    <span class="rounded-lg border border-dashed border-white/60 bg-white/10 px-4 py-2 font-mono text-base font-bold tracking-widest">{{ $offer['code'] }}</span>
                    <button type="button" data-copy-code="{{ $offer['code'] }}"
                        class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-purple-700 shadow-sm transition hover:bg-purple-50">
                        Copy code
                    </button>
                </div>

                {{-- Countdown --}}
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

        {{-- Offer details --}}
        <div class="flex flex-col justify-center p-6 sm:p-8 lg:col-span-3 lg:p-10">
            <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">About this offer</p>
            <h2 class="mt-2 text-2xl font-bold text-gray-900 sm:text-3xl">{{ $offer['title'] }}: {{ $offer['discount'] }} off everything</h2>
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

            {{-- Terms: 1px gaps over a grey background draw the dividers --}}
            <dl class="mt-6 grid grid-cols-2 gap-px overflow-hidden rounded-xl bg-gray-200 ring-1 ring-gray-200 sm:grid-cols-4 lg:grid-cols-2 xl:grid-cols-4">
                @foreach ($offer['terms'] as $label => $value)
                    <div class="bg-white px-4 py-3">
                        <dt class="text-[11px] font-medium uppercase tracking-wider text-gray-500">{{ $label }}</dt>
                        <dd class="mt-0.5 text-sm font-semibold text-gray-900">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>

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

<script>
    // Copy coupon code to clipboard.
    document.querySelectorAll('[data-copy-code]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(btn.dataset.copyCode);
                btn.textContent = 'Copied!';
            } catch (e) {
                btn.textContent = btn.dataset.copyCode;
            }
            setTimeout(() => (btn.textContent = 'Copy code'), 2000);
        });
    });

    // Live countdown to the offer end date.
    (() => {
        const box = document.getElementById('offer-countdown');
        if (!box) return;
        const end = new Date(box.dataset.endsAt).getTime();
        const set = (unit, value) => (box.querySelector(`[data-unit="${unit}"]`).textContent = String(value).padStart(2, '0'));
        const tick = () => {
            const left = Math.max(0, end - Date.now());
            set('days', Math.floor(left / 86400000));
            set('hours', Math.floor(left / 3600000) % 24);
            set('minutes', Math.floor(left / 60000) % 60);
            set('seconds', Math.floor(left / 1000) % 60);
            if (left === 0) clearInterval(timer);
        };
        const timer = setInterval(tick, 1000);
        tick();
    })();
</script>

{{-- Product carousels (same card design, see partials/product-carousel.blade.php) --}}
{{-- Full-width banner slider: slides share one grid cell and fade; dots + auto-play --}}
<section class="group/slider relative mt-12" data-banner-slider aria-roledescription="carousel" aria-label="Featured collections">
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

@include('partials.product-carousel', $bestSellers)

{{-- Two banners side by side --}}
<section class="mt-16 grid gap-6 lg:grid-cols-2" aria-label="Collections">
    @foreach ($dualBanners as $banner)
        @include('partials.banner', ['banner' => $banner])
    @endforeach
</section>

@include('partials.product-carousel', $recommended)

{{-- Promo banners (static, no slider): product photo with text over its empty top area --}}
<section id="promos" class="mt-12 scroll-mt-28" aria-label="Featured products">
    <div class="grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4">
        @foreach ($promos as $promo)
            <a href="{{ route('products', array_filter(['category' => $promo['category'], 'type' => $promo['type'] ?? null])) }}"
                class="group relative block aspect-[8/11] overflow-hidden rounded-xl bg-gray-100 shadow-sm ring-1 ring-gray-900/5 transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-indigo-500/15 sm:rounded-2xl">
                <img src="{{ asset($promo['image']) }}"
                    onerror="this.onerror=null;this.src='{{ asset('images/no-image.svg') }}';"
                    alt="{{ $promo['alt'] }}" loading="lazy"
                    class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]">

                {{-- Soft wash in the photo's own backdrop colour keeps the text readable --}}
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


<script>
    // Banner slider: dots switch slides; auto-advances every 5s, pausing while hovered.
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
        const autoplay = slides.length > 1 && !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        let timer = null;
        const stop = () => { clearInterval(timer); timer = null; };
        const start = () => { stop(); if (autoplay) timer = setInterval(() => show(current + 1), 5000); };
        const go = (index) => { show(index); start(); };
        dots.forEach((dot) => dot.addEventListener('click', () => go(Number(dot.dataset.dot))));
        slider.querySelector('[data-slider-prev]')?.addEventListener('click', () => go(current - 1));
        slider.querySelector('[data-slider-next]')?.addEventListener('click', () => go(current + 1));

        // Drag with the mouse or swipe on touch screens to change slides.
        const track = slider.querySelector('[data-slider-track]');
        let startX = null;
        let dragged = false;
        track.addEventListener('pointerdown', (e) => { startX = e.clientX; dragged = false; });
        track.addEventListener('pointermove', (e) => { if (startX !== null && Math.abs(e.clientX - startX) > 10) dragged = true; });
        track.addEventListener('pointerup', (e) => {
            if (startX === null) return;
            const dx = e.clientX - startX;
            startX = null;
            if (Math.abs(dx) > 50) go(current + (dx < 0 ? 1 : -1));
        });
        track.addEventListener('pointercancel', () => { startX = null; });
        // A drag shouldn't also open the banner link, and images/links shouldn't be dragged out.
        track.addEventListener('click', (e) => { if (dragged) { e.preventDefault(); dragged = false; } }, true);
        track.addEventListener('dragstart', (e) => e.preventDefault());

        // Keyboard: left/right arrows while the slider has focus.
        slider.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') go(current - 1);
            if (e.key === 'ArrowRight') go(current + 1);
        });

        slider.addEventListener('mouseenter', stop);
        slider.addEventListener('mouseleave', start);
        start();
    });

    // Product carousel: arrow buttons scroll one card at a time.
    document.querySelectorAll('[data-carousel]').forEach((carousel) => {
        const track = carousel.querySelector('[data-carousel-track]');
        const prev = carousel.querySelector('[data-carousel-prev]');
        const next = carousel.querySelector('[data-carousel-next]');
        const step = () => carousel.querySelector('[data-carousel-item]')?.offsetWidth || track.clientWidth;
        const update = () => {
            prev.disabled = track.scrollLeft <= 2;
            next.disabled = track.scrollLeft + track.clientWidth >= track.scrollWidth - 2;
        };
        prev.addEventListener('click', () => track.scrollBy({ left: -step(), behavior: 'smooth' }));
        next.addEventListener('click', () => track.scrollBy({ left: step(), behavior: 'smooth' }));
        track.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update);
        update();
    });
</script>

{{-- Customer reviews --}}
<section class="mt-16" aria-labelledby="reviews-title">
    <div class="text-center">
        <h2 id="reviews-title" class="text-2xl font-bold text-gray-900 sm:text-3xl">What our customers say</h2>
        <p class="mt-2 text-gray-500">Honest words from people who shop with TimePass.</p>
    </div>
    <div class="mx-auto mt-8 grid max-w-4xl gap-6 md:grid-cols-2">
        @foreach ($reviews as $review)
            <figure class="flex flex-col items-center rounded-2xl bg-white px-8 py-8 text-center shadow-xl shadow-gray-200/70 ring-1 ring-gray-100 transition hover:-translate-y-1">
                <span class="flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 via-purple-600 to-pink-500 text-3xl font-bold text-white shadow-lg ring-4 ring-white">
                    {{ strtoupper(substr($review['name'], 0, 1)) }}
                </span>
                <figcaption class="mt-4">
                    <p class="text-lg font-bold text-gray-900">{{ $review['name'] }}</p>
                    <p class="mt-0.5 text-sm font-semibold text-indigo-600">{{ $review['tagline'] }}</p>
                </figcaption>
                <div class="mt-3 flex gap-1" role="img" aria-label="Rated {{ $review['rating'] }} out of 5">
                    @for ($i = 1; $i <= 5; $i++)
                        <svg class="h-5 w-5 {{ $i <= $review['rating'] ? 'text-amber-400' : 'text-gray-200' }}" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 0 0 .95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 0 0-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 0 0-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 0 0-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 0 0 .951-.69l1.07-3.292Z"/></svg>
                    @endfor
                </div>
                <blockquote class="mt-4 leading-relaxed text-gray-600">"{{ $review['text'] }}"</blockquote>
            </figure>
        @endforeach
    </div>
</section>

{{-- Highlights --}}
{{-- Shopper benefits. SAMPLE promises: make sure each one matches the store's real policies. --}}
@php
    $benefits = [
        ['Genuine products', 'Sourced from trusted brands and authorised sellers.', 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z'],
        ['Free delivery', 'On all orders above ₹499, right to your door.', 'M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12'],
        ['Easy 7-day returns', 'Changed your mind? Return or exchange within 7 days.', 'M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99'],
        ['Secure payments', 'Pay safely with UPI, cards, net banking or cash on delivery.', 'M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z'],
    ];
@endphp
<section class="mt-16 overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 p-8 text-white shadow-xl shadow-purple-500/20 sm:p-10">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-white/70">Why shop with us</p>
            <h2 class="mt-1 text-2xl font-bold sm:text-3xl">Shopping made easy at TimePass</h2>
        </div>
        <a href="{{ route('products') }}" class="inline-flex items-center gap-1.5 rounded-full bg-white px-5 py-2.5 text-sm font-semibold text-purple-700 shadow-sm transition hover:bg-purple-50">
            Start shopping
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
        </a>
    </div>
    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($benefits as [$title, $text, $icon])
            <div class="rounded-2xl bg-white/10 p-5 ring-1 ring-white/20 backdrop-blur transition hover:bg-white/15">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-white text-purple-600 shadow-sm">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                </span>
                <h3 class="mt-4 font-semibold">{{ $title }}</h3>
                <p class="mt-1 text-sm text-white/80">{{ $text }}</p>
            </div>
        @endforeach
    </div>
</section>
@endsection
