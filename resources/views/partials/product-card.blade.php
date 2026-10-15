{{-- Product card (Products page grid + carousels).
     Expects: $product with name, category, type, description, rating, reviews,
     price, mrp, image (null = placeholder), url (detail page). --}}
@php
    $url = $product['url'] ?? route('products');
    $hasOff = ! empty($product['mrp']) && $product['mrp'] > $product['price'];
    $off = $hasOff ? round(100 - $product['price'] / $product['mrp'] * 100) : 0;
@endphp
<article class="group flex h-full flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white transition duration-300 hover:-translate-y-1.5 hover:border-indigo-200 hover:shadow-xl hover:shadow-indigo-500/15">
    {{-- Image → detail page, with zoom + "View details" overlay on hover --}}
    <a href="{{ $url }}" aria-label="View {{ $product['name'] }}"
        class="relative flex h-60 items-center justify-center overflow-hidden border-b border-gray-100 bg-white p-6">
        <span class="absolute left-4 top-4 z-10 rounded-full bg-white px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-indigo-700 shadow-sm ring-1 ring-gray-200">{{ $product['category'] }}</span>
        @if ($hasOff)
            <span class="absolute right-4 top-4 z-10 rounded-full bg-gradient-to-r from-rose-500 to-pink-500 px-2.5 py-1 text-[11px] font-black text-white shadow-sm">{{ $off }}% OFF</span>
        @endif
        <img src="{{ asset($product['image'] ?: 'images/no-image.svg') }}"
            onerror="this.onerror=null;this.src='{{ asset('images/no-image.svg') }}';"
            alt="{{ $product['name'] }}" loading="lazy"
            class="transition-transform duration-500 ease-out group-hover:scale-110 {{ $product['image'] ? 'max-h-full max-w-full object-contain' : 'h-full w-full rounded-xl object-cover' }}">
    </a>

    <div class="flex flex-1 flex-col p-5">
        <h3 class="line-clamp-2 text-lg font-bold leading-snug text-gray-900" title="{{ $product['name'] }}">
            <a href="{{ $url }}" class="transition-colors group-hover:text-indigo-600">{{ $product['name'] }}</a>
        </h3>
        <p class="mt-1 text-sm font-medium text-gray-500">{{ $product['type'] }}</p>
        <p class="mt-3 line-clamp-2 text-sm text-gray-400">{{ $product['description'] }}</p>
        @isset($product['price'])
            <p class="mt-4 flex flex-wrap items-baseline gap-x-2">
                <span class="text-xl font-bold text-gray-900">₹{{ number_format($product['price']) }}</span>
                @if ($hasOff)
                    <span class="text-sm text-gray-400 line-through">₹{{ number_format($product['mrp']) }}</span>
                    <span class="text-sm font-semibold text-emerald-600">{{ $off }}% off</span>
                @endif
            </p>
        @endisset
        <span class="mt-4 inline-flex w-fit items-center gap-1 rounded-full bg-indigo-50 px-3 py-1 text-sm font-semibold text-indigo-700 ring-1 ring-indigo-100">
            {{ number_format($product['rating'], 1) }}<span class="text-amber-500">★</span>
            <span class="font-medium text-indigo-600/80">({{ $product['reviews'] }})</span>
        </span>

        <div class="mt-auto grid grid-cols-2 gap-3 pt-6">
            <a href="{{ $url }}"
                class="rounded-lg border border-gray-300 py-2.5 text-center text-sm font-bold uppercase tracking-wider text-gray-800 transition hover:border-indigo-400 hover:bg-indigo-50 hover:text-indigo-600">Details</a>
            <a href="{{ route('contact', ['product' => $product['name']]) }}"
                class="inline-flex items-center justify-center gap-1.5 whitespace-nowrap rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 py-2.5 text-sm font-bold uppercase tracking-wider text-white shadow-md shadow-indigo-500/20 transition hover:-translate-y-0.5 hover:shadow-lg">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                Buy Now
            </a>
        </div>
    </div>
</article>
