<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'TimePass Store' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;0,800;1,500&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <style>
        .font-display { font-family: 'Playfair Display', Georgia, 'Times New Roman', serif; }
        @keyframes nav-gradient { 0%, 100% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } }
        @keyframes nav-shimmer { from { transform: translateX(-120%) skewX(-20deg); } to { transform: translateX(220%) skewX(-20deg); } }
        @keyframes nav-glow { 0%, 100% { opacity: .55; transform: scale(1); } 50% { opacity: .9; transform: scale(1.15); } }
        @keyframes nav-item-in { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: none; } }

        .nav-animated-gradient { background-size: 200% 200%; animation: nav-gradient 8s ease infinite; }
        .nav-logo-glow { animation: nav-glow 3s ease-in-out infinite; }
        .nav-shimmer { position: relative; overflow: hidden; isolation: isolate; }
        .nav-shimmer::after {
            content: ''; position: absolute; inset: 0; width: 40%; z-index: -1;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,.45), transparent);
            transform: translateX(-120%) skewX(-20deg);
        }
        .nav-shimmer:hover::after { animation: nav-shimmer .9s ease; }

        #mobile-menu { display: grid; grid-template-rows: 0fr; transition: grid-template-rows .35s ease, opacity .25s ease; opacity: 0; }
        #mobile-menu[data-open="true"] { grid-template-rows: 1fr; opacity: 1; }
        #mobile-menu[data-open="true"] [data-stagger] { animation: nav-item-in .35s ease both; }

        @media (prefers-reduced-motion: reduce) {
            .nav-animated-gradient, .nav-logo-glow, .nav-shimmer:hover::after,
            #mobile-menu[data-open="true"] [data-stagger] { animation: none; }
            #mobile-menu, [data-nav-indicator] { transition: none !important; }
        }
    </style>
</head>
<body class="min-h-screen overflow-x-hidden bg-gray-50 text-gray-900 antialiased">
    @php
        $navLinks = [
            'home' => ['Home', 'm2.25 12 8.954-8.955a1.126 1.126 0 0 1 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25'],
            'products' => ['Products', 'M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z'],
            'about' => ['About Us','m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z'],
            'contact' => ['Contact', 'M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75'],
        ];
        $categories = config('shop.categories', []);
        $forms = config('shop.forms', []);
        $activeCategory = request()->routeIs('products') ? request('category') : null;
        // Category whose types the mega menu shows first: the one being browsed, else the first.
        $megaActive = collect($categories)->contains('slug', $activeCategory) ? $activeCategory : ($categories[0]['slug'] ?? null);
        // "My Account" links in the user menu (desktop dropdown and mobile menu).
        $accountMenu = [
            ['account.index', 'My Account', 'M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z'],
            ['account.wishlist', 'My Wishlist', 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z'],
            ['account.orders', 'My Orders', 'M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z'],
            ['account.transactions', 'Transactions', 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z'],
            ['account.addresses', 'Delivery Addresses', 'M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z'],
            ['account.profile', 'Edit Profile', 'm16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10'],
        ];
        $initial = auth()->check() ? strtoupper(substr(auth()->user()->name, 0, 1)) : '';
        $searchIcon = 'm21 21-4.3-4.3m1.8-5.2a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z';
    @endphp

    {{-- Announcement strip --}}
    <div class="nav-animated-gradient bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500 text-white">
        <div class="mx-auto flex max-w-7xl items-center justify-center gap-2 px-4 py-2 text-center text-xs font-medium sm:text-sm">
            <span aria-hidden="true">✨</span>
            <span>New arrivals every week. Don't miss out!</span>
            <a href="{{ route('products') }}" class="group inline-flex items-center gap-1 font-semibold underline decoration-white/40 underline-offset-4 hover:decoration-white">
                Shop now
                <span class="transition-transform group-hover:translate-x-0.5" aria-hidden="true">→</span>
            </a>
        </div>
    </div>

    {{-- Navigation bar (floating glass) --}}
    <header id="site-header" class="sticky top-0 z-40 px-3 pt-3 transition-transform duration-300 ease-out sm:px-6">
        <div id="nav-shell"
            class="relative mx-auto max-w-7xl rounded-2xl bg-white/70 shadow-lg shadow-indigo-500/5 ring-1 ring-gray-900/5 backdrop-blur-xl transition-all duration-300">
            {{-- Gradient hairline on top edge --}}
            <div class="pointer-events-none absolute inset-x-6 top-0 h-px bg-gradient-to-r from-transparent via-indigo-500/60 to-transparent"></div>

            <nav class="flex h-16 items-center gap-3 px-3 sm:px-4" aria-label="Main">
                {{-- Logo --}}
                <a href="{{ route('home') }}" class="group flex shrink-0 items-center gap-2.5">
                    <span class="relative flex h-10 w-10 items-center justify-center">
                        <span class="nav-logo-glow absolute inset-0 rounded-xl bg-gradient-to-br from-indigo-500 to-pink-500 blur-md"></span>
                        <span class="relative flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 via-purple-600 to-pink-500 text-lg font-black text-white transition duration-300 group-hover:rotate-[8deg] group-hover:scale-110">T</span>
                    </span>
                    <span class="text-xl font-black tracking-tight text-gray-900">
                        Time<span class="bg-gradient-to-r from-indigo-600 to-pink-500 bg-clip-text text-transparent">Pass</span>
                    </span>
                </a>

                {{-- Shop by Category (desktop) --}}
                <div class="relative ml-4 hidden shrink-0 lg:block" data-dropdown>
                    <button type="button" data-dropdown-toggle aria-expanded="false" aria-haspopup="true"
                        class="flex h-10 items-center gap-2 whitespace-nowrap rounded-full bg-gradient-to-r from-indigo-600 to-purple-600 pl-3.5 pr-3 text-sm font-semibold text-white shadow-md shadow-indigo-500/25 transition hover:shadow-lg hover:shadow-indigo-500/35">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                        </svg>
                        <span><span class="hidden xl:inline">Shop by </span>Category</span>
                        <svg class="h-4 w-4 opacity-80 transition-transform duration-200" data-dropdown-chevron fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                        </svg>
                    </button>
                    {{-- Mega menu: categories on the left, the hovered category's types + forms on the right --}}
                    <div data-dropdown-menu
                        class="invisible absolute left-0 mt-3 w-[46rem] origin-top-left -translate-y-1 scale-95 overflow-hidden rounded-2xl bg-white opacity-0 shadow-2xl shadow-indigo-500/20 ring-1 ring-gray-900/5 transition duration-200">
                        <div class="flex" data-mega>
                            <ul class="w-64 shrink-0 space-y-0.5 border-r border-gray-100 bg-gray-50/70 p-2">
                                @foreach ($categories as $category)
                                    <li>
                                        <a href="{{ route('products', ['category' => $category['slug']]) }}" data-mega-tab="{{ $category['slug'] }}"
                                            @if ($megaActive === $category['slug']) data-active @endif
                                            @if ($activeCategory === $category['slug']) aria-current="page" @endif
                                            class="group flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-semibold text-gray-700 transition hover:bg-white data-[active]:bg-white data-[active]:text-indigo-700 data-[active]:shadow-sm data-[active]:ring-1 data-[active]:ring-gray-900/5">
                                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 transition group-data-[active]:bg-gradient-to-br group-data-[active]:from-indigo-500 group-data-[active]:to-pink-500 group-data-[active]:text-white">
                                                <svg class="h-[1.125rem] w-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $category['icon'] }}" />
                                                </svg>
                                            </span>
                                            <span class="min-w-0 flex-1 leading-tight">{{ $category['name'] }}</span>
                                            <svg class="h-4 w-4 shrink-0 text-gray-300 transition group-data-[active]:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="flex min-w-0 flex-1 flex-col p-5">
                                @foreach ($categories as $category)
                                    <div data-mega-panel="{{ $category['slug'] }}" @class(['hidden' => $megaActive !== $category['slug']])>
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="min-w-0">
                                                <p class="font-semibold text-gray-900">{{ $category['name'] }}</p>
                                                <p class="mt-0.5 text-xs text-gray-500">{{ $category['description'] }}</p>
                                            </div>
                                            <a href="{{ route('products', ['category' => $category['slug']]) }}" class="shrink-0 whitespace-nowrap text-xs font-semibold text-indigo-600 hover:text-indigo-700">Shop all &rarr;</a>
                                        </div>
                                        <ul class="mt-3 grid grid-cols-2 gap-x-3">
                                            @foreach ($category['types'] as $typeSlug => $typeName)
                                                <li>
                                                    <a href="{{ route('products', ['category' => $category['slug'], 'type' => $typeSlug]) }}"
                                                        class="block rounded-lg px-2.5 py-1.5 text-sm text-gray-600 transition hover:bg-indigo-50 hover:text-indigo-700">{{ $typeName }}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endforeach

                                <div class="mt-auto border-t border-gray-100 pt-4">
                                    <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-500">Shop by form</p>
                                    <div class="mt-2 flex flex-wrap gap-1.5">
                                        @foreach ($forms as $form)
                                            <a href="{{ route('products', ['form' => $form['slug']]) }}" title="{{ $form['examples'] }}"
                                                class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 transition hover:bg-indigo-50 hover:text-indigo-700">{{ $form['name'] }}</a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('products') }}" class="flex items-center justify-between border-t border-gray-100 bg-gray-50 px-5 py-3 text-sm font-semibold text-indigo-600 transition hover:bg-indigo-50">
                            View all products
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                        </a>
                    </div>
                </div>

                {{-- Primary links with sliding highlight (desktop) --}}
                <ul id="nav-links" class="relative ml-2 hidden shrink-0 items-center rounded-full bg-gray-900/[0.04] p-1 ring-1 ring-inset ring-gray-900/5 lg:flex">
                    <span data-nav-indicator aria-hidden="true"
                        class="absolute left-0 top-1 h-[calc(100%-0.5rem)] rounded-full bg-white opacity-0 shadow-md shadow-indigo-500/10 ring-1 ring-gray-900/5 transition-all duration-300 ease-out"></span>
                    @foreach ($navLinks as $route => [$label, $icon])
                        @php $isActive = request()->routeIs($route); @endphp
                        <li class="relative">
                            <a href="{{ route($route) }}" data-nav-link @if ($isActive) aria-current="page" data-active @endif
                                class="group flex items-center gap-1.5 whitespace-nowrap rounded-full px-3 py-1.5 text-sm font-semibold transition-colors {{ $isActive ? 'text-indigo-600' : 'text-gray-600 hover:text-gray-900' }}">
                                <svg class="hidden h-4 w-4 transition-transform 2xl:block group-hover:-translate-y-0.5 {{ $isActive ? 'text-indigo-500' : 'text-gray-400 group-hover:text-indigo-500' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" />
                                </svg>
                                {{ $label }}
                            </a>
                        </li>
                    @endforeach
                </ul>

                <div class="flex min-w-0 flex-1 items-center justify-end gap-2">
                    {{-- Search (desktop): flexes to fill the leftover space so the buttons never get squeezed --}}
                    <form action="{{ route('search') }}" method="GET" role="search"
                        class="hidden min-w-[10rem] max-w-[15rem] flex-1 transition-[max-width] duration-300 focus-within:max-w-[18rem] xl:block">
                        <label class="group relative block">
                            <span class="sr-only">Search</span>
                            <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 transition group-focus-within:text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $searchIcon }}" />
                            </svg>
                            <input id="nav-search" type="search" name="q" value="{{ request('q') }}" placeholder="Search products..."
                                class="h-10 w-full rounded-full border border-gray-200/80 bg-white/80 pl-10 pr-4 text-sm placeholder-gray-400 transition focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-500/15">
                        </label>
                    </form>

                    {{-- Auth (desktop) --}}
                    <div class="hidden shrink-0 items-center gap-2 text-sm lg:flex">
                        @auth
                            <div class="relative" data-dropdown>
                                <button type="button" data-dropdown-toggle aria-expanded="false" aria-haspopup="true"
                                    class="flex items-center gap-2 rounded-full bg-white py-1 pl-1 pr-3 shadow-sm ring-1 ring-gray-900/10 transition hover:shadow-md hover:ring-indigo-300">
                                    <span class="relative flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-pink-500 text-sm font-bold text-white">
                                        {{ $initial }}
                                        <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-white bg-emerald-400"></span>
                                    </span>
                                    <span class="max-w-[8rem] truncate font-semibold text-gray-700">{{ auth()->user()->name }}</span>
                                    <svg class="h-4 w-4 text-gray-400 transition-transform duration-200" data-dropdown-chevron fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                                    </svg>
                                </button>
                                <div data-dropdown-menu
                                    class="invisible absolute right-0 mt-3 w-64 origin-top-right -translate-y-1 scale-95 overflow-hidden rounded-2xl bg-white opacity-0 shadow-2xl shadow-indigo-500/20 ring-1 ring-gray-900/5 transition duration-200">
                                    <div class="bg-gradient-to-br from-indigo-500 via-purple-600 to-pink-500 px-4 py-4 text-white">
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-11 w-11 items-center justify-center rounded-full bg-white/20 text-lg font-bold ring-2 ring-white/40">{{ $initial }}</span>
                                            <div class="min-w-0">
                                                <p class="truncate font-bold">{{ auth()->user()->name }}</p>
                                                <p class="truncate text-xs text-white/80">{{ auth()->user()->email }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-1.5">
                                        @foreach ($accountMenu as [$route, $label, $icon])
                                            <a href="{{ route($route) }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 font-medium text-gray-700 transition hover:bg-indigo-50 hover:text-indigo-700">
                                                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" /></svg>
                                                {{ $label }}
                                            </a>
                                        @endforeach
                                        <div class="my-1 border-t border-gray-100"></div>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2.5 text-left font-semibold text-red-600 transition hover:bg-red-50">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                                                </svg>
                                                Logout
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @else
                            <a href="{{ route('login') }}" class="inline-flex h-10 items-center whitespace-nowrap rounded-full px-3.5 font-semibold text-gray-700 transition hover:bg-gray-900/5 hover:text-gray-900">Login</a>
                            <a href="{{ route('register') }}"
                                class="nav-shimmer nav-animated-gradient inline-flex h-10 items-center gap-1.5 whitespace-nowrap rounded-full bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500 px-4 font-semibold text-white shadow-lg shadow-purple-500/30 transition hover:-translate-y-0.5 hover:shadow-xl hover:shadow-purple-500/40">
                                Get started
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                </svg>
                            </a>
                        @endauth
                    </div>

                    {{-- Mobile menu button --}}
                    <button type="button" id="mobile-menu-toggle" aria-controls="mobile-menu" aria-expanded="false"
                        class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white text-gray-700 shadow-sm ring-1 ring-gray-900/10 transition hover:ring-indigo-300 lg:hidden">
                        <span class="sr-only">Open menu</span>
                        <span class="relative block h-4 w-5" aria-hidden="true">
                            <span data-bar="1" class="absolute left-0 top-0 h-0.5 w-5 rounded-full bg-current transition duration-300"></span>
                            <span data-bar="2" class="absolute left-0 top-[7px] h-0.5 w-5 rounded-full bg-current transition duration-300"></span>
                            <span data-bar="3" class="absolute left-0 top-[14px] h-0.5 w-5 rounded-full bg-current transition duration-300"></span>
                        </span>
                    </button>
                </div>
            </nav>

            {{-- Mobile menu panel --}}
            <div id="mobile-menu" data-open="false" inert class="lg:hidden">
                <div class="overflow-hidden">
                    <div class="max-h-[calc(100dvh-6rem)] space-y-4 overflow-y-auto overscroll-contain border-t border-gray-900/5 px-3 pb-4 pt-4 sm:px-4">
                        <form action="{{ route('search') }}" method="GET" role="search" data-stagger>
                            <label class="relative block">
                                <span class="sr-only">Search</span>
                                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $searchIcon }}" />
                                </svg>
                                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search products..."
                                    class="w-full rounded-xl border border-gray-200 bg-white py-3 pl-10 pr-4 text-sm placeholder-gray-400 focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-500/15">
                            </label>
                        </form>

                        <ul class="grid grid-cols-2 gap-2">
                            @foreach ($navLinks as $route => [$label, $icon])
                                @php $isActive = request()->routeIs($route); @endphp
                                <li data-stagger style="animation-delay: {{ 60 + $loop->index * 50 }}ms">
                                    <a href="{{ route($route) }}" @if ($isActive) aria-current="page" @endif
                                        class="flex items-center gap-3 rounded-xl p-3 text-sm font-semibold transition {{ $isActive ? 'bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-lg shadow-indigo-500/30' : 'bg-white text-gray-700 ring-1 ring-gray-900/5 hover:ring-indigo-300' }}">
                                        <span class="flex h-9 w-9 items-center justify-center rounded-lg {{ $isActive ? 'bg-white/20' : 'bg-indigo-50 text-indigo-600' }}">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" />
                                            </svg>
                                        </span>
                                        {{ $label }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        {{-- Shop by Category (mobile): tap a category to see its types --}}
                        <div data-stagger style="animation-delay: 260ms">
                            <p class="px-1 text-xs font-semibold uppercase tracking-wider text-gray-500">Shop by Category</p>
                            <div class="mt-2 space-y-2">
                                @foreach ($categories as $category)
                                    @php $isCurrent = $activeCategory === $category['slug']; @endphp
                                    <details class="group rounded-xl bg-white ring-1 {{ $isCurrent ? 'ring-indigo-200' : 'ring-gray-900/5' }}" @if ($isCurrent) open @endif>
                                        <summary class="flex cursor-pointer list-none items-center gap-2.5 p-2.5 text-sm font-medium text-gray-700 [&::-webkit-details-marker]:hidden">
                                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                                                <svg class="h-[1.125rem] w-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $category['icon'] }}" />
                                                </svg>
                                            </span>
                                            <span class="flex-1">{{ $category['name'] }}</span>
                                            <svg class="h-4 w-4 shrink-0 text-gray-400 transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" /></svg>
                                        </summary>
                                        <div class="flex flex-wrap gap-1.5 px-2.5 pb-3">
                                            <a href="{{ route('products', ['category' => $category['slug']]) }}"
                                                class="rounded-full bg-indigo-600 px-3 py-1 text-xs font-semibold text-white">All {{ $category['name'] }}</a>
                                            @foreach ($category['types'] as $typeSlug => $typeName)
                                                <a href="{{ route('products', ['category' => $category['slug'], 'type' => $typeSlug]) }}"
                                                    class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 transition hover:bg-indigo-50 hover:text-indigo-700">{{ $typeName }}</a>
                                            @endforeach
                                        </div>
                                    </details>
                                @endforeach
                            </div>

                            <p class="mt-4 px-1 text-xs font-semibold uppercase tracking-wider text-gray-500">Shop by Form</p>
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                @foreach ($forms as $form)
                                    <a href="{{ route('products', ['form' => $form['slug']]) }}"
                                        class="rounded-full bg-white px-3 py-1.5 text-xs font-medium text-gray-700 ring-1 ring-gray-900/5 transition hover:ring-indigo-300">{{ $form['name'] }}</a>
                                @endforeach
                            </div>
                        </div>

                        <div data-stagger style="animation-delay: 320ms">
                            @auth
                                <div class="flex items-center gap-3 rounded-xl bg-gradient-to-br from-indigo-50 to-pink-50 p-3 ring-1 ring-indigo-100">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-pink-500 font-bold text-white">{{ $initial }}</span>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate font-semibold text-gray-900">{{ auth()->user()->name }}</p>
                                        <p class="truncate text-xs text-gray-500">{{ auth()->user()->email }}</p>
                                    </div>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="rounded-lg bg-white px-3 py-2 text-sm font-semibold text-red-600 shadow-sm ring-1 ring-red-100 transition hover:bg-red-50">Logout</button>
                                    </form>
                                </div>
                                <div class="mt-2 grid grid-cols-2 gap-2">
                                    @foreach ($accountMenu as [$route, $label, $icon])
                                        <a href="{{ route($route) }}" class="flex items-center gap-2 rounded-xl bg-white p-2.5 text-sm font-medium text-gray-700 ring-1 ring-gray-900/5 transition hover:ring-indigo-300">
                                            <svg class="h-4 w-4 shrink-0 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" /></svg>
                                            <span class="truncate">{{ $label }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <div class="grid grid-cols-2 gap-2">
                                    <a href="{{ route('login') }}" class="rounded-xl bg-white py-3 text-center text-sm font-semibold text-gray-700 ring-1 ring-gray-900/10 transition hover:ring-indigo-300">Login</a>
                                    <a href="{{ route('register') }}" class="nav-shimmer nav-animated-gradient rounded-xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500 py-3 text-center text-sm font-semibold text-white shadow-lg shadow-purple-500/30">Get started</a>
                                </div>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
        @yield('content')
    </main>

    @php
        $contact = config('shop.contact', []);
        // Social icons: inner SVG markup per network (24x24 viewBox, stroke icons).
        $socialIcons = [
            'instagram' => ['Instagram', '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="0.6" fill="currentColor"/>'],
            'facebook' => ['Facebook', '<path d="M14 8h2.5V4.5H14A4 4 0 0 0 10 8.5V11H7.5v3.5H10V21h3.5v-6.5H16l.5-3.5h-3V9a1 1 0 0 1 1-1Z"/>'],
            'youtube' => ['YouTube', '<rect x="2.5" y="5.5" width="19" height="13" rx="4"/><path d="m10 9.5 4.5 2.5-4.5 2.5v-5Z" fill="currentColor"/>'],
            'x' => ['X (Twitter)', '<path d="M4 4l16 16M20 4 4 20"/>'],
        ];
        $footerColumns = [
            'Shop by Category' => collect($categories)->map(fn ($c) => [$c['name'], route('products', ['category' => $c['slug']])])->all(),
            'My Account' => [
                ['My Account', route('account.index')],
                ['My Orders', route('account.orders')],
                ['Transactions', route('account.transactions')],
                ['Delivery Addresses', route('account.addresses')],
                ['Edit Profile', route('account.profile')],
            ],
            'Help & Information' => [
                ['About Us', route('about')],
                ['Contact Us', route('contact')],
                ['All Products', route('products')],
                ['Search', route('search')],
            ],
        ];
    @endphp
    <footer class="mt-20 bg-gray-950 text-gray-400">
        <div class="h-1 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500"></div>

        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 md:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr_1fr_1.3fr]">
            {{-- Brand + follow us --}}
            <div>
                <a href="{{ route('home') }}" class="flex w-fit items-center gap-2.5">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 via-purple-600 to-pink-500 text-lg font-black text-white">T</span>
                    <span class="text-xl font-black tracking-tight text-white">Time<span class="bg-gradient-to-r from-indigo-400 to-pink-400 bg-clip-text text-transparent">Pass</span></span>
                </a>
                <p class="mt-4 max-w-xs text-sm leading-relaxed">Skin care, hair care, makeup, fragrances and more, all in one place.</p>
                <p class="mt-6 text-xs font-semibold uppercase tracking-wider text-gray-300">Follow us</p>
                <div class="mt-3 flex gap-2">
                    @foreach (config('shop.social', []) as $network => $url)
                        @if ($url && isset($socialIcons[$network]))
                            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $socialIcons[$network][0] }}"
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-white/5 text-gray-300 ring-1 ring-white/10 transition hover:-translate-y-0.5 hover:bg-gradient-to-br hover:from-indigo-500 hover:to-pink-500 hover:text-white hover:ring-transparent">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">{!! $socialIcons[$network][1] !!}</svg>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- Link columns --}}
            @foreach ($footerColumns as $heading => $links)
                <div>
                    <p class="text-sm font-semibold text-white">{{ $heading }}</p>
                    <ul class="mt-4 space-y-2.5 text-sm">
                        @foreach ($links as [$label, $url])
                            <li><a href="{{ $url }}" class="transition hover:text-white">{{ $label }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endforeach

            {{-- Contact --}}
            <div>
                <p class="text-sm font-semibold text-white">Get in touch</p>
                <ul class="mt-4 space-y-3 text-sm">
                    @foreach ([
                        ['email', 'mailto:' . ($contact['email'] ?? ''), 'M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75'],
                        ['phone', 'tel:' . preg_replace('/[^0-9+]/', '', $contact['phone'] ?? ''), 'M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z'],
                        ['address', null, 'M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z'],
                        ['hours', null, 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
                    ] as [$key, $href, $icon])
                        @if (! empty($contact[$key]))
                            <li class="flex gap-3">
                                <svg class="mt-0.5 h-5 w-5 shrink-0 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" /></svg>
                                @if ($href)
                                    <a href="{{ $href }}" class="break-all transition hover:text-white">{{ $contact[$key] }}</a>
                                @else
                                    <span>{{ $contact[$key] }}</span>
                                @endif
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Bottom bar --}}
        <div class="border-t border-white/10">
            <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-3 px-4 py-5 text-xs sm:flex-row sm:px-6">
                <p>&copy; {{ date('Y') }} TimePass Store. All rights reserved.</p>
                <a href="#" onclick="window.scrollTo({ top: 0, behavior: 'smooth' }); return false;" class="inline-flex items-center gap-1.5 font-semibold text-gray-300 transition hover:text-white">
                    Back to top
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" /></svg>
                </a>
            </div>
        </div>
    </footer>

    <script>
        (function () {
            const shell = document.getElementById('nav-shell');
            const toggle = document.getElementById('mobile-menu-toggle');
            const menu = document.getElementById('mobile-menu');

            // Navbar turns more solid once the page is scrolled.
            const onScroll = () => {
                const scrolled = window.scrollY > 8;
                shell.classList.toggle('bg-white/70', !scrolled);
                shell.classList.toggle('bg-white/90', scrolled);
                shell.classList.toggle('shadow-xl', scrolled);
                shell.classList.toggle('shadow-lg', !scrolled);
            };
            onScroll();
            window.addEventListener('scroll', onScroll, { passive: true });

            // Hide the navbar while scrolling down, bring it back when scrolling up.
            // It stays visible near the top and while a menu or dropdown is open.
            const header = document.getElementById('site-header');
            const setHidden = (hidden) => {
                header.style.transform = hidden ? 'translateY(calc(-100% - 1.5rem))' : '';
            };
            let lastY = window.scrollY;
            window.addEventListener('scroll', () => {
                const y = window.scrollY;
                if (Math.abs(y - lastY) < 8) return; // ignore tiny movements
                const menuOpen = header.querySelector('[aria-expanded="true"]');
                setHidden(y > lastY && y > header.offsetHeight && !menuOpen);
                lastY = y;
            }, { passive: true });
            // Keyboard users tabbing into the navbar should always see it.
            header.addEventListener('focusin', () => setHidden(false));

            // Sliding highlight behind the desktop links.
            const linkList = document.getElementById('nav-links');
            const indicator = linkList.querySelector('[data-nav-indicator]');
            const links = linkList.querySelectorAll('[data-nav-link]');
            const activeLink = linkList.querySelector('[data-active]');
            const moveTo = (el) => {
                if (!el) { indicator.style.opacity = '0'; return; }
                indicator.style.width = el.offsetWidth + 'px';
                indicator.style.transform = `translateX(${el.parentElement.offsetLeft}px)`;
                indicator.style.opacity = '1';
            };
            links.forEach((link) => link.addEventListener('mouseenter', () => moveTo(link)));
            linkList.addEventListener('mouseleave', () => moveTo(activeLink));
            requestAnimationFrame(() => moveTo(activeLink));
            window.addEventListener('resize', () => moveTo(activeLink));

            // Mobile menu with animated hamburger.
            const bars = toggle.querySelectorAll('[data-bar]');
            const setMenu = (open) => {
                menu.dataset.open = String(open);
                menu.inert = !open;
                toggle.setAttribute('aria-expanded', String(open));
                toggle.querySelector('.sr-only').textContent = open ? 'Close menu' : 'Open menu';
                bars[0].style.transform = open ? 'translateY(7px) rotate(45deg)' : '';
                bars[1].style.opacity = open ? '0' : '';
                bars[2].style.transform = open ? 'translateY(-7px) rotate(-45deg)' : '';
            };
            toggle.addEventListener('click', () => setMenu(menu.dataset.open !== 'true'));
            window.matchMedia('(min-width: 1024px)').addEventListener('change', (e) => e.matches && setMenu(false));

            // User dropdown.
            document.querySelectorAll('[data-dropdown]').forEach((dropdown) => {
                const button = dropdown.querySelector('[data-dropdown-toggle]');
                const panel = dropdown.querySelector('[data-dropdown-menu]');
                const chevron = dropdown.querySelector('[data-dropdown-chevron]');
                const set = (open) => {
                    ['invisible', 'opacity-0', 'scale-95', '-translate-y-1'].forEach((c) => panel.classList.toggle(c, !open));
                    chevron.classList.toggle('rotate-180', open);
                    button.setAttribute('aria-expanded', String(open));
                };
                button.addEventListener('click', (e) => {
                    e.stopPropagation();
                    set(button.getAttribute('aria-expanded') !== 'true');
                });
                document.addEventListener('click', (e) => { if (!dropdown.contains(e.target)) set(false); });
                document.addEventListener('keydown', (e) => { if (e.key === 'Escape') set(false); });
            });

            // Category mega menu: hovering or focusing a category shows its product types.
            document.querySelectorAll('[data-mega]').forEach((mega) => {
                const tabs = mega.querySelectorAll('[data-mega-tab]');
                const activate = (tab) => {
                    tabs.forEach((t) => t.toggleAttribute('data-active', t === tab));
                    mega.querySelectorAll('[data-mega-panel]').forEach((panel) => {
                        panel.classList.toggle('hidden', panel.dataset.megaPanel !== tab.dataset.megaTab);
                    });
                };
                tabs.forEach((tab) => {
                    tab.addEventListener('mouseenter', () => activate(tab));
                    tab.addEventListener('focus', () => activate(tab));
                });
            });

            // Esc closes the mobile menu; "/" focuses search.
            const search = document.getElementById('nav-search');
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') setMenu(false);
                const typing = /^(INPUT|TEXTAREA|SELECT)$/.test(document.activeElement.tagName);
                if (e.key === '/' && !typing && search.offsetParent) {
                    e.preventDefault();
                    search.focus();
                }
            });
        })();
    </script>
    @auth
        <script>window.__TP_WISH = @json(auth()->user()->wishlistProductIds());</script>
    @endauth
    <script>
        // Wishlist hearts (saved to the logged-in user's account). Works on any page with [data-wish].
        (function () {
            const authed = {{ auth()->check() ? 'true' : 'false' }};
            const ids = new Set((window.__TP_WISH || []).map(Number));
            const toggleUrl = @json(route('account.wishlist.toggle'));
            const loginUrl = @json(route('login'));
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            const paint = (btn, on) => {
                btn.classList.toggle('text-rose-500', on);
                const s = btn.querySelector('svg');
                if (s) s.setAttribute('fill', on ? 'currentColor' : 'none');
            };
            document.querySelectorAll('[data-wish]').forEach((btn) => {
                const id = Number(btn.dataset.wish);
                if (id) paint(btn, ids.has(id));
            });
            document.addEventListener('click', async (e) => {
                const btn = e.target.closest('[data-wish]');
                if (!btn) return;
                e.preventDefault(); e.stopPropagation();
                const id = Number(btn.dataset.wish);
                if (!id) return;
                if (!authed) { window.location.href = loginUrl; return; }
                btn.disabled = true;
                try {
                    const res = await fetch(toggleUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                        body: JSON.stringify({ product_id: id }),
                    });
                    if (!res.ok) throw new Error('failed');
                    const data = await res.json();
                    if (data.wished) ids.add(id); else ids.delete(id);
                    paint(btn, data.wished);
                    if (btn.animate) btn.animate([{ transform: 'scale(1)' }, { transform: 'scale(1.3)' }, { transform: 'scale(1)' }], { duration: 240 });
                } catch (err) { /* ignore */ }
                finally { btn.disabled = false; }
            });
        })();
    </script>
</body>
</html>
