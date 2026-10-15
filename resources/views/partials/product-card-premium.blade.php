{{-- Premium, 3D-tilt product card for the home page grids (best sellers, new arrivals).
     Expects: $product (name, category, type, price, mrp, rating, reviews, image, url,
     category_slug) and optional $accent = [fromHex, toHex] for the category colour. --}}
@php
    $accent = $accent ?? ['#6366f1', '#8b5cf6'];
    [$c1, $c2] = $accent;
    $grad = 'linear-gradient(135deg,' . $c1 . ',' . $c2 . ')';
    $hasDiscount = ! empty($product['mrp']) && $product['mrp'] > $product['price'];
    $off = $hasDiscount ? round(100 - $product['price'] / $product['mrp'] * 100) : 0;
    $url = $product['url'] ?? route('products');
@endphp
<div data-tilt class="group h-full [perspective:1200px]">
    <article data-tilt-card
        class="relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm transition-[transform,box-shadow] duration-300 [transform-style:preserve-3d] group-hover:shadow-2xl group-hover:shadow-indigo-500/20">

        {{-- Image area with a soft category-tinted wash --}}
        <div class="relative aspect-square overflow-hidden" style="background-image: linear-gradient(135deg, {{ $c1 }}14, {{ $c2 }}24)">
            @if ($hasDiscount)
                <span class="absolute left-3 top-3 z-20 rounded-full px-2.5 py-1 text-[11px] font-black text-white shadow-md" style="background-image: {{ $grad }}">{{ $off }}% OFF</span>
            @endif

            <button type="button" data-wish="{{ $product['id'] ?? '' }}" aria-label="Save {{ $product['name'] }} to favourites"
                class="absolute right-3 top-3 z-20 flex h-9 w-9 items-center justify-center rounded-full bg-white/90 text-gray-400 shadow-md ring-1 ring-gray-900/5 backdrop-blur transition hover:scale-110 hover:text-rose-500">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
            </button>

            <a href="{{ $url }}" tabindex="-1" aria-hidden="true" class="block h-full w-full">
                <img src="{{ asset($product['image'] ?: 'images/no-image.svg') }}"
                     onerror="this.onerror=null;this.src='{{ asset('images/no-image.svg') }}';"
                     alt="{{ $product['name'] }}" loading="lazy"
                     class="h-full w-full object-contain p-5 transition-transform duration-500 ease-out [transform:translateZ(45px)] group-hover:scale-[1.12]">
            </a>
        </div>

        {{-- Body --}}
        <div class="flex flex-1 flex-col p-4 [transform:translateZ(22px)]">
            <p class="text-[11px] font-bold uppercase tracking-wider" style="color: {{ $c2 }}">{{ $product['category'] }}</p>
            <h3 class="mt-1 line-clamp-2 text-sm font-bold leading-snug text-gray-900">
                <a href="{{ $url }}" class="transition hover:text-indigo-600">{{ $product['name'] }}</a>
            </h3>

            @isset($product['rating'])
                <div class="mt-1.5 flex items-center gap-1 text-xs text-gray-500">
                    <span class="text-amber-400">★</span>
                    <span class="font-semibold text-gray-700">{{ number_format($product['rating'], 1) }}</span>
                    <span>({{ $product['reviews'] ?? 0 }})</span>
                </div>
            @endisset

            <div class="mt-auto flex items-end justify-between pt-3">
                <div class="flex flex-col leading-tight">
                    <span class="text-lg font-black text-gray-900">₹{{ number_format($product['price']) }}</span>
                    @if ($hasDiscount)
                        <span class="text-xs text-gray-400 line-through">₹{{ number_format($product['mrp']) }}</span>
                    @endif
                </div>
                <a href="{{ route('contact', ['product' => $product['name']]) }}" aria-label="Enquire about {{ $product['name'] }}"
                   class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-white shadow-lg transition-transform duration-300 hover:rotate-90 hover:scale-110" style="background-image: {{ $grad }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2.4" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                </a>
            </div>
        </div>
    </article>
</div>
