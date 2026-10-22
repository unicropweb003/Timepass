{{-- Promo banner: text on a coloured panel that fades into the product photo.
     Expects: $banner (image, alt, eyebrow, title, text, cta, url, panel, dark); optional $wide (bool). --}}
@php $wide = $wide ?? false; @endphp
<a href="{{ $banner['url'] }}"
    class="group relative flex h-full flex-col overflow-hidden rounded-2xl shadow-sm ring-1 ring-gray-900/5 sm:flex-row {{ $wide ? 'sm:min-h-[320px] lg:min-h-[380px]' : 'sm:min-h-[280px]' }}"
    style="background-color: {{ $banner['panel'] }}">
    <div class="relative z-10 flex flex-col justify-center p-6 sm:w-1/2 {{ $wide ? 'sm:p-10 lg:pl-14' : 'sm:p-8' }} {{ $banner['dark'] ? 'text-white' : 'text-gray-900' }}">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] {{ $banner['dark'] ? 'text-white/70' : 'text-gray-500' }}">{{ $banner['eyebrow'] }}</p>
        <h3 class="font-display mt-2 font-medium leading-tight {{ $wide ? 'text-2xl sm:text-4xl lg:text-5xl' : 'text-2xl lg:text-3xl' }}">{{ $banner['title'] }}</h3>
        <p class="mt-3 max-w-sm text-sm {{ $wide ? 'sm:text-base' : '' }} {{ $banner['dark'] ? 'text-white/80' : 'text-gray-600' }}">{{ $banner['text'] }}</p>
        <span class="mt-6 inline-flex w-fit items-center gap-1.5 rounded-full px-5 py-2.5 text-sm font-semibold shadow-sm transition group-hover:gap-2.5 {{ $banner['dark'] ? 'bg-white text-gray-900' : 'bg-gray-900 text-white' }}">
            {{ $banner['cta'] }}
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
        </span>
    </div>
    <div class="relative order-first aspect-[16/10] overflow-hidden sm:order-none sm:aspect-auto sm:w-1/2">
        <img src="{{ asset($banner['image']) }}" alt="{{ $banner['alt'] }}" loading="lazy"
            class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-105">
        {{-- Fade from the panel colour into the photo --}}
        <div class="pointer-events-none absolute inset-y-0 left-0 hidden w-2/5 sm:block"
            style="background: linear-gradient(to right, {{ $banner['panel'] }}, {{ $banner['panel'] }}00)"></div>
    </div>
</a>
