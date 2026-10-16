@extends('layouts.app', ['title' => 'About Us · TimePass'])

@section('content')
@php
    $categories = config('shop.categories', []);
    $contact = config('shop.contact', []);
    $productCounts = collect(config('products', []))->countBy('category');

    // Editable via the MCP update_about_us tool; falls back to the default copy.
    $about = config('shop.about', []);
    $aboutTitle = $about['title'] ?? 'Beauty essentials for your everyday self-care';
    $aboutContent = $about['content'] ?? 'Your one-stop beauty store for skin care, hair care, makeup, fragrances and more — trusted products at honest prices, so self-care feels simple and joyful.';
    $aboutImage = $about['image'] ?? null;

    // A distinct colour per category (matches the product image tiles).
    $palette = [
        'skin-care'         => ['#6366f1', '#8b5cf6'],
        'hair-care'         => ['#f59e0b', '#d97706'],
        'makeup'            => ['#ec4899', '#db2777'],
        'nail-care'         => ['#a855f7', '#9333ea'],
        'hygiene-oral-care' => ['#06b6d4', '#0891b2'],
        'fragrances'        => ['#10b981', '#059669'],
        'body-care'         => ['#f43f5e', '#e11d48'],
    ];
    $grad = fn ($slug) => 'linear-gradient(135deg,' . ($palette[$slug][0] ?? '#6366f1') . ',' . ($palette[$slug][1] ?? '#8b5cf6') . ')';

    // Real, honest stats pulled from the live catalogue.
    $stats = [
        ['value' => count($categories), 'label' => 'Categories'],
        ['value' => count(config('products', [])) . '+', 'label' => 'Products'],
        ['value' => collect($categories)->sum(fn ($c) => count($c['types'] ?? [])), 'label' => 'Product types'],
        ['value' => count(config('shop.forms', [])), 'label' => 'Formulation forms'],
    ];

    $values = [
        ['Carefully curated', 'Every product is hand-picked across skin, hair, makeup and more — so you only see things worth buying.',
            'M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z'],
        ['Honest pricing', 'Clear prices with real savings shown up front. No hidden charges, no inflated MRPs.',
            'M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z'],
        ['Fast delivery', 'Quick dispatch with free delivery on orders above ₹499, delivered right to your door.',
            'M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12'],
        ['Easy 7-day returns', 'Changed your mind? Return or exchange within 7 days, hassle-free.',
            'M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99'],
        ['Secure checkout', 'Your account and payments are protected with encrypted, hashed credentials.',
            'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z'],
        ['Here to help', 'Real support when you need it — reach us any time during store hours.',
            'M2.25 12.76c0 1.6 1.123 2.994 2.707 3.227 1.068.157 2.148.279 3.238.364.466.037.893.281 1.153.671L12 21l2.652-3.978c.26-.39.687-.634 1.153-.67 1.09-.086 2.17-.208 3.238-.365 1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z'],
    ];
@endphp

<div class="space-y-12 sm:space-y-16">

    {{-- Hero (compact two-column) --}}
    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 px-6 py-8 text-white shadow-xl shadow-indigo-500/20 sm:px-10 sm:py-9">
        <div class="pointer-events-none absolute -right-16 -top-20 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 -left-10 h-72 w-72 rounded-full bg-pink-300/20 blur-3xl"></div>

        <div class="relative grid items-center gap-6 lg:grid-cols-[1.05fr_0.95fr] lg:gap-10">
            {{-- Copy --}}
            <div>
                <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3.5 py-1.5 text-xs font-semibold uppercase tracking-wider ring-1 ring-white/25 backdrop-blur">
                    <span aria-hidden="true">✨</span> About TimePass
                </span>
                <h1 class="mt-4 text-2xl font-black leading-[1.15] tracking-tight sm:text-3xl">
                    {{ $aboutTitle }}
                </h1>
                <p class="mt-3 max-w-xl whitespace-pre-line text-sm leading-relaxed text-white/90">{{ $aboutContent }}</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('products') }}"
                       class="inline-flex items-center gap-2 rounded-full bg-white px-6 py-3 text-sm font-bold text-indigo-700 shadow-lg transition hover:-translate-y-0.5 hover:shadow-xl">
                        Explore products
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </a>
                    <a href="{{ route('contact') }}"
                       class="inline-flex items-center gap-2 rounded-full bg-white/10 px-6 py-3 text-sm font-bold text-white ring-1 ring-white/30 backdrop-blur transition hover:bg-white/20">
                        Get in touch
                    </a>
                </div>
            </div>

            {{-- Category preview tiles (glass) --}}
            <div class="hidden grid-cols-2 gap-3 sm:grid">
                @foreach (array_slice($categories, 0, 4) as $i => $category)
                    <a href="{{ route('products', ['category' => $category['slug']]) }}"
                       class="group flex items-center gap-3 rounded-xl bg-white/10 p-3 ring-1 ring-white/20 backdrop-blur transition hover:-translate-y-0.5 hover:bg-white/20">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/20 text-white ring-1 ring-white/25">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $category['icon'] }}"/></svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-bold leading-tight text-white">{{ $category['name'] }}</span>
                            <span class="block text-xs text-white/70">{{ $productCounts[$category['slug']] ?? 0 }} products</span>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Optional About image (set via the MCP update_about_us tool) --}}
    @if ($aboutImage)
        <section class="overflow-hidden rounded-3xl shadow-xl ring-1 ring-gray-200">
            <img src="{{ asset($aboutImage) }}" alt="{{ $aboutTitle }}" class="h-64 w-full object-cover sm:h-80" loading="lazy"
                 onerror="this.closest('section').remove()">
        </section>
    @endif

    {{-- Stats --}}
    <section class="grid grid-cols-2 gap-3 sm:gap-5 lg:grid-cols-4">
        @foreach ($stats as $stat)
            <div class="rounded-2xl border border-gray-200 bg-white p-5 text-center shadow-sm transition hover:-translate-y-1 hover:shadow-lg hover:shadow-indigo-500/10">
                <p class="bg-gradient-to-r from-indigo-600 to-pink-500 bg-clip-text text-3xl font-black text-transparent sm:text-4xl">{{ $stat['value'] }}</p>
                <p class="mt-1 text-xs font-medium text-gray-500 sm:text-sm">{{ $stat['label'] }}</p>
            </div>
        @endforeach
    </section>

    {{-- Our story --}}
    <section class="grid items-center gap-10 lg:grid-cols-2 lg:gap-14">
        <div>
            <span class="text-sm font-bold uppercase tracking-wider text-indigo-600">Our story</span>
            <h2 class="mt-3 text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">Self-care, made simple</h2>
            <div class="mt-4 space-y-4 text-gray-600 leading-relaxed">
                <p>
                    TimePass started with a simple idea: finding good beauty products shouldn't be confusing or
                    expensive. Between endless brands and unclear pricing, everyday shopping had become a chore.
                </p>
                <p>
                    So we built a store that keeps things clear and honest — a tightly curated range across
                    <strong class="font-semibold text-gray-900">{{ count($categories) }} categories</strong>, real prices,
                    and a smooth experience from browsing to checkout.
                </p>
            </div>
            <a href="{{ route('products') }}" class="mt-6 inline-flex items-center gap-1.5 font-semibold text-indigo-600 transition hover:text-indigo-700">
                Browse the full range
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
            </a>
        </div>

        <div class="relative">
            <div class="overflow-hidden rounded-3xl shadow-xl shadow-indigo-500/10 ring-1 ring-gray-900/5">
                <img src="{{ asset('images/hero.jpg') }}" alt="A curated selection of beauty and personal-care products"
                     onerror="this.style.display='none';this.parentElement.classList.add('bg-gradient-to-br','from-indigo-500','via-purple-500','to-pink-500','aspect-[4/3]')"
                     class="h-full w-full object-cover">
            </div>
            <div class="absolute -bottom-5 -left-3 flex items-center gap-3 rounded-2xl bg-white p-4 shadow-xl ring-1 ring-gray-900/5 sm:-left-5">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-pink-500 text-white">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
                </span>
                <div>
                    <p class="text-sm font-bold text-gray-900">Made for you</p>
                    <p class="text-xs text-gray-500">Loved, everyday self-care</p>
                </div>
            </div>
        </div>
    </section>

    {{-- What we offer (colourful category cards) --}}
    <section>
        <div class="mx-auto max-w-2xl text-center">
            <span class="text-sm font-bold uppercase tracking-wider text-indigo-600">What we offer</span>
            <h2 class="mt-3 text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">Shop across every category</h2>
            <p class="mt-3 text-gray-600">From your morning routine to your night-time ritual — explore our full range.</p>
        </div>

        <div class="mt-9 grid grid-cols-2 gap-3 sm:gap-5 lg:grid-cols-4">
            @foreach ($categories as $category)
                <a href="{{ route('products', ['category' => $category['slug']]) }}"
                   class="group flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:-translate-y-1.5 hover:border-transparent hover:shadow-xl hover:shadow-indigo-500/15">
                    <span class="h-1.5 w-full" style="background-image: {{ $grad($category['slug']) }}"></span>
                    <div class="flex flex-1 flex-col p-4 sm:p-5">
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl text-white shadow-md transition group-hover:scale-110 sm:h-14 sm:w-14"
                              style="background-image: {{ $grad($category['slug']) }}">
                            <svg class="h-6 w-6 sm:h-7 sm:w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $category['icon'] }}"/></svg>
                        </span>
                        <h3 class="mt-4 font-bold text-gray-900">{{ $category['name'] }}</h3>
                        <p class="mt-1 flex-1 text-xs leading-relaxed text-gray-500 sm:text-sm">{{ $category['description'] }}</p>
                        <div class="mt-4 flex items-center justify-between">
                            <span class="rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-semibold text-gray-600">{{ $productCounts[$category['slug']] ?? 0 }} products</span>
                            <span class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600">
                                Shop
                                <svg class="h-3.5 w-3.5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke-width="2.4" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Why shop with us --}}
    <section class="rounded-3xl bg-gray-950 px-6 py-12 text-white sm:px-12 sm:py-14">
        <div class="mx-auto max-w-2xl text-center">
            <span class="text-sm font-bold uppercase tracking-wider text-indigo-400">Why TimePass</span>
            <h2 class="mt-3 text-2xl font-bold tracking-tight sm:text-3xl">Shopping you can feel good about</h2>
            <p class="mt-3 text-gray-400">We sweat the details so your experience stays effortless from start to finish.</p>
        </div>

        <div class="mt-10 grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-5 lg:grid-cols-3">
            @foreach ($values as [$title, $text, $icon])
                <div class="rounded-2xl bg-white/5 p-5 ring-1 ring-white/10 transition hover:-translate-y-1 hover:bg-white/[0.09]">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-pink-500 text-white shadow-lg shadow-indigo-500/30">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                    </span>
                    <h3 class="mt-4 font-bold text-white">{{ $title }}</h3>
                    <p class="mt-1.5 text-sm leading-relaxed text-gray-400">{{ $text }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Call to action --}}
    <section class="relative overflow-hidden rounded-3xl border border-indigo-100 bg-gradient-to-br from-indigo-50 via-white to-pink-50 px-6 py-12 text-center sm:px-12">
        <div class="pointer-events-none absolute -right-10 -top-10 h-48 w-48 rounded-full bg-indigo-200/40 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-12 -left-10 h-56 w-56 rounded-full bg-pink-200/40 blur-3xl"></div>
        <div class="relative mx-auto max-w-2xl">
            <h2 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">Ready to find your new favourites?</h2>
            <p class="mt-3 text-gray-600">Discover products picked just for you — or reach out and we'll help you choose.</p>
            <div class="mt-7 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('products') }}"
                   class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500 px-7 py-3 text-sm font-bold text-white shadow-lg shadow-purple-500/30 transition hover:-translate-y-0.5 hover:shadow-xl">
                    Start shopping
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                </a>
                <a href="{{ route('contact') }}"
                   class="inline-flex items-center gap-2 rounded-full border border-gray-300 bg-white px-7 py-3 text-sm font-bold text-gray-800 transition hover:border-indigo-300 hover:text-indigo-700">
                    Contact us
                </a>
            </div>
            @if (! empty($contact['email']) || ! empty($contact['phone']))
                <p class="mt-6 text-sm text-gray-500">
                    @if (! empty($contact['email']))
                        <a href="mailto:{{ $contact['email'] }}" class="font-medium text-indigo-600 hover:text-indigo-700">{{ $contact['email'] }}</a>
                    @endif
                    @if (! empty($contact['email']) && ! empty($contact['phone'])) <span class="mx-2 text-gray-300">•</span> @endif
                    @if (! empty($contact['phone']))
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $contact['phone']) }}" class="font-medium text-indigo-600 hover:text-indigo-700">{{ $contact['phone'] }}</a>
                    @endif
                </p>
            @endif
        </div>
    </section>

</div>
@endsection
