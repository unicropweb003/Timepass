{{-- Brand panel beside the login / register forms (large screens only).
     Expects: $heading, $text and $points (list of short benefit lines). --}}
<aside class="relative hidden flex-col justify-between overflow-hidden bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 p-10 text-white lg:flex xl:p-12">
    <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-white/10 blur-2xl"></div>
    <div class="pointer-events-none absolute -bottom-32 -left-20 h-80 w-80 rounded-full bg-pink-400/30 blur-3xl"></div>

    <a href="{{ route('home') }}" class="relative flex w-fit items-center gap-2.5">
        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 text-lg font-black ring-1 ring-white/30">T</span>
        <span class="text-xl font-black tracking-tight">TimePass</span>
    </a>

    <div class="relative my-10">
        <h2 class="text-3xl font-bold leading-tight xl:text-4xl">{{ $heading }}</h2>
        <p class="mt-3 max-w-sm text-white/80">{{ $text }}</p>
        <ul class="mt-8 space-y-3">
            @foreach ($points as $point)
                <li class="flex items-center gap-3 text-sm font-medium">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white/20 ring-1 ring-white/30">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                    </span>
                    {{ $point }}
                </li>
            @endforeach
        </ul>
    </div>

    {{-- Product thumbnails --}}
    <div class="relative flex items-end gap-4">
        <div class="flex items-end">
            <img src="{{ asset('images/promos/skincare-kit.jpg') }}" alt="" class="h-28 w-20 -rotate-6 rounded-2xl object-cover shadow-xl ring-2 ring-white/50">
            <img src="{{ asset('images/promos/keratin-hair-care.jpg') }}" alt="" class="relative z-10 -ml-3 h-32 w-24 rounded-2xl object-cover shadow-xl ring-2 ring-white/50">
            <img src="{{ asset('images/promos/electrolyte.jpg') }}" alt="" class="-ml-3 h-28 w-20 rotate-6 rounded-2xl object-cover shadow-xl ring-2 ring-white/50">
        </div>
        <p class="max-w-[10rem] pb-2 text-sm text-white/80">Skin care, hair care, hydration and more.</p>
    </div>
</aside>
