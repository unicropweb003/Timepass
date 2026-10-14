{{-- Product carousel section with arrow buttons (script at the bottom of home.blade.php).
     Expects: $title, $products; optional $link ("View All" URL). Each product: name, category,
     type, description, rating, reviews, image (null = placeholder), optional url (Details link). --}}
<section class="mt-16" data-carousel>
    <div class="flex items-end justify-between border-b border-gray-200">
        <h2 class="relative pb-3 text-2xl font-bold text-gray-900">
            {{ $title }}
            <span class="absolute bottom-[-1px] left-1/4 h-1 w-14 rounded-full bg-gradient-to-r from-indigo-600 to-pink-500"></span>
        </h2>
        <div class="flex items-center gap-3 pb-3">
            <button type="button" data-carousel-prev aria-label="Previous products"
                class="flex h-9 w-9 items-center justify-center rounded-full bg-white text-gray-700 shadow-sm ring-1 ring-gray-200 transition hover:text-indigo-600 hover:ring-indigo-300 disabled:cursor-not-allowed disabled:opacity-40">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
            </button>
            <button type="button" data-carousel-next aria-label="Next products"
                class="flex h-9 w-9 items-center justify-center rounded-full bg-white text-gray-700 shadow-sm ring-1 ring-gray-200 transition hover:text-indigo-600 hover:ring-indigo-300 disabled:cursor-not-allowed disabled:opacity-40">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
            </button>
            <a href="{{ $link ?? route('products') }}" class="ml-2 inline-flex items-center gap-1 text-sm font-semibold text-indigo-600 hover:text-indigo-700">
                View All
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
            </a>
        </div>
    </div>

    <div data-carousel-track class="-mx-2 mt-6 flex snap-x snap-mandatory overflow-x-auto scroll-smooth pb-4 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
        @foreach ($products as $product)
            <div data-carousel-item class="w-full shrink-0 snap-start px-2 sm:w-1/2 lg:w-1/3 xl:w-1/4">
                @include('partials.product-card')
            </div>
        @endforeach
    </div>
</section>
