{{-- "Watch & shop": short upright product videos (a model showing the product), like
     shoppable reels. Videos play silently in the row while visible; tapping one opens a
     full-screen player with sound and the product's "View product" link. Products without
     a video yet appear as a moving photo reel that links to the product page.
     Expects: $reels (catalogue products, those with a 'video' first) and $accent (fn slug → [hex, hex]). --}}
@php
    $money = fn ($v) => '₹'.number_format((int) $v);
    $items = collect($reels)->map(fn ($p) => $p + [
        'sources' => \App\Support\VideoUrl::sources($p['video'] ?? null),
        'poster' => \App\Support\VideoUrl::poster($p['video'] ?? null),
        'image_url' => asset(($p['image'] ?? null) ?: 'images/no-image.svg'),
        'url' => $p['url'] ?? route('products'),
    ]);
    // Data for the full-screen player (video reels only, in row order).
    $player = $items->filter(fn ($r) => $r['sources'])->values()->map(fn ($r) => [
        'name' => $r['name'],
        'category' => $r['category'],
        'url' => $r['url'],
        'image' => $r['image_url'],
        'price' => $money($r['price']),
        'mrp' => ! empty($r['mrp']) && $r['mrp'] > $r['price'] ? $money($r['mrp']) : null,
        'sources' => $r['sources'],
        'poster' => $r['poster'],
    ]);
    $videoIndex = 0;
@endphp

@if ($items->isNotEmpty())
<style>
    /* Photo reels: slow zoom-and-drift so a still product photo feels like video. */
    @keyframes tp-reel-kb { 0%, 100% { transform: scale(1) translate(0, 0); } 50% { transform: scale(1.14) translate(-3%, -2%); } }
    @keyframes tp-reel-bg { 0%, 100% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } }
    .tp-reel-kb { animation: tp-reel-kb 11s ease-in-out infinite; }
    .tp-reel-bg {
        background-color: #f7f3ee;
        background-image: linear-gradient(160deg, #f7f3ee 0%, var(--tp-c1) 35%, #f7f3ee 60%, var(--tp-c2) 100%);
        background-size: 250% 250%;
        animation: tp-reel-bg 12s ease-in-out infinite;
    }
    .tp-reels-track { scrollbar-width: none; }
    .tp-reels-track::-webkit-scrollbar { display: none; }
    [data-reel-progress] { transition: width .25s linear; }
    @media (prefers-reduced-motion: reduce) { .tp-reel-kb, .tp-reel-bg { animation: none; } }
</style>

<section data-reveal data-reels class="mt-16" aria-labelledby="reels-title">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-sm font-bold uppercase tracking-wider text-indigo-600">See it in action</p>
            <h2 id="reels-title" class="font-display mt-1 text-2xl font-medium tracking-tight text-gray-900 sm:text-3xl">Watch &amp; shop.</h2>
        </div>
        <div class="hidden gap-2 sm:flex">
            @foreach (['prev' => ['Scroll back', 'M15.75 19.5 8.25 12l7.5-7.5'], 'next' => ['Scroll forward', 'm8.25 4.5 7.5 7.5-7.5 7.5']] as $dir => [$label, $path])
                <button type="button" data-reels-{{ $dir }} aria-label="{{ $label }}"
                    class="flex h-10 w-10 items-center justify-center rounded-full bg-white text-gray-800 shadow-sm ring-1 ring-gray-900/10 transition hover:-translate-y-0.5 hover:shadow-md">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.4" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/></svg>
                </button>
            @endforeach
        </div>
    </div>

    <div data-reels-track class="tp-reels-track -mx-4 mt-8 flex snap-x snap-mandatory scroll-px-4 gap-4 overflow-x-auto px-4 pb-2 sm:-mx-6 sm:scroll-px-6 sm:px-6">
        @foreach ($items as $reel)
            @php
                [$c1, $c2] = $accent($reel['category_slug'] ?? '');
                $isVideo = ! empty($reel['sources']);
            @endphp
            <article class="group relative aspect-[9/16] w-[47%] shrink-0 snap-start overflow-hidden rounded-[1.75rem] shadow-sm ring-1 ring-gray-900/5 transition-shadow duration-300 hover:shadow-xl hover:shadow-indigo-500/15 sm:w-[31%] lg:w-[calc((100%-4rem)/5)]">
                {{-- Photo layer: the whole reel for photo reels, and what shows before a video loads. --}}
                <div class="tp-reel-bg absolute inset-0" style="--tp-c1: {{ $c1 }}2e; --tp-c2: {{ $c2 }}40; animation-delay: -{{ $loop->index * 2.3 }}s">
                    <img src="{{ $reel['image_url'] }}" alt="" loading="lazy"
                         onerror="this.onerror=null;this.src='{{ asset('images/no-image.svg') }}';"
                         class="tp-reel-kb h-full w-full object-contain px-6 pb-24 pt-12" style="animation-delay: -{{ $loop->index * 1.7 }}s">
                </div>

                @if ($isVideo)
                    <video data-reel-video muted playsinline loop preload="none" @if ($reel['poster']) poster="{{ $reel['poster'] }}" @endif
                        class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.03]">
                        @foreach ($reel['sources'] as $src)
                            <source src="{{ $src }}">
                        @endforeach
                    </video>
                    <div class="pointer-events-none absolute inset-x-0 top-0 h-24 bg-gradient-to-b from-black/45 to-transparent"></div>
                    <div class="pointer-events-none absolute inset-x-3 top-2.5 h-[3px] overflow-hidden rounded-full bg-white/35">
                        <div data-reel-progress class="h-full w-0 rounded-full bg-white"></div>
                    </div>
                    <span class="pointer-events-none absolute left-3 top-5 inline-flex items-center gap-1 rounded-full bg-black/35 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-white backdrop-blur">
                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.14v13.72a1 1 0 0 0 1.52.85l10.6-6.86a1 1 0 0 0 0-1.7L9.52 4.29A1 1 0 0 0 8 5.14Z"/></svg>
                        Watch
                    </span>
                    {{-- Big play hint on hover --}}
                    <span class="pointer-events-none absolute left-1/2 top-[42%] flex h-14 w-14 -translate-x-1/2 -translate-y-1/2 scale-75 items-center justify-center rounded-full bg-white/25 text-white opacity-0 ring-1 ring-white/50 backdrop-blur transition duration-300 group-hover:scale-100 group-hover:opacity-100">
                        <svg class="ml-0.5 h-6 w-6" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.14v13.72a1 1 0 0 0 1.52.85l10.6-6.86a1 1 0 0 0 0-1.7L9.52 4.29A1 1 0 0 0 8 5.14Z"/></svg>
                    </span>
                    <button type="button" data-reel-open="{{ $videoIndex++ }}" aria-label="Watch the {{ $reel['name'] }} video"
                        class="absolute inset-0 z-10 cursor-pointer focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-inset focus-visible:ring-indigo-500/60"></button>
                @else
                    <span class="pointer-events-none absolute left-3 top-3.5 rounded-full bg-white/85 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider shadow-sm backdrop-blur" style="color: {{ $c2 }}">{{ $reel['category'] }}</span>
                    <a href="{{ $reel['url'] }}" tabindex="-1" aria-hidden="true" class="absolute inset-0 z-10"></a>
                @endif

                {{-- Shoppable product tag --}}
                {{-- Phones: no thumbnail or struck-through MRP, so the name has room on the narrow card. --}}
                <a href="{{ $reel['url'] }}" class="absolute inset-x-2 bottom-2 z-20 flex items-center gap-2 rounded-2xl bg-white/90 p-2 pl-3 shadow-lg ring-1 ring-gray-900/5 backdrop-blur transition hover:bg-white sm:inset-x-2.5 sm:bottom-2.5 sm:gap-2.5 sm:pl-2 sm:pr-2.5">
                    <img src="{{ $reel['image_url'] }}" alt="" loading="lazy"
                         onerror="this.onerror=null;this.src='{{ asset('images/no-image.svg') }}';"
                         class="hidden h-10 w-10 shrink-0 rounded-xl bg-[#f7f3ee] object-contain p-1 sm:block">
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-xs font-bold text-gray-900">{{ $reel['name'] }}</span>
                        <span class="mt-0.5 flex items-baseline gap-1.5 text-xs">
                            <span class="font-black text-gray-900">{{ $money($reel['price']) }}</span>
                            @if (! empty($reel['mrp']) && $reel['mrp'] > $reel['price'])
                                <span class="hidden text-[10px] text-gray-400 line-through sm:inline">{{ $money($reel['mrp']) }}</span>
                            @endif
                        </span>
                    </span>
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-white" style="background-image: linear-gradient(135deg, {{ $c1 }}, {{ $c2 }})" aria-hidden="true">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </span>
                </a>
            </article>
        @endforeach
    </div>

    @if ($player->isNotEmpty())
        <script type="application/json" data-reel-data>@json($player)</script>

        {{-- Full-screen player --}}
        <dialog data-reel-dialog aria-label="Product videos"
            class="fixed inset-0 m-0 h-dvh max-h-none w-screen max-w-none border-0 bg-transparent p-0 text-white backdrop:bg-black/90">
            <div data-reel-stage class="flex h-full w-full items-center justify-center sm:p-6">
                <div data-reel-frame class="relative h-full w-full touch-none select-none overflow-hidden bg-black sm:aspect-[9/16] sm:h-[min(88vh,52rem)] sm:w-auto sm:rounded-3xl sm:shadow-2xl">
                    <video data-reel-player playsinline class="h-full w-full object-cover"></video>

                    <div class="pointer-events-none absolute inset-x-0 top-0 h-28 bg-gradient-to-b from-black/60 to-transparent"></div>
                    <div class="pointer-events-none absolute inset-x-0 bottom-0 h-48 bg-gradient-to-t from-black/70 to-transparent"></div>

                    <div class="absolute inset-x-4 top-3 h-[3px] overflow-hidden rounded-full bg-white/30">
                        <div data-reel-progress class="h-full w-0 rounded-full bg-white"></div>
                    </div>

                    <div class="absolute inset-x-4 top-6 flex items-center justify-between gap-3">
                        <p class="min-w-0 truncate text-xs font-semibold text-white/85"><span data-reel-category></span> · <span data-reel-counter></span></p>
                        <div class="flex shrink-0 gap-2">
                            <button type="button" data-reel-sound aria-label="Mute"
                                class="flex h-9 w-9 items-center justify-center rounded-full bg-black/35 ring-1 ring-white/25 backdrop-blur transition hover:bg-black/55">
                                <svg data-icon-on class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.114 5.636a9 9 0 0 1 0 12.728M16.463 8.288a5.25 5.25 0 0 1 0 7.424M6.75 8.25l4.72-4.72a.75.75 0 0 1 1.28.53v15.88a.75.75 0 0 1-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.009 9.009 0 0 1 2.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75Z"/></svg>
                                <svg data-icon-off class="hidden h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 9.75 19.5 12m0 0 2.25 2.25M19.5 12l2.25-2.25M19.5 12l-2.25 2.25m-10.5-6 4.72-4.72a.75.75 0 0 1 1.28.53v15.88a.75.75 0 0 1-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.009 9.009 0 0 1 2.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75Z"/></svg>
                            </button>
                            <button type="button" data-reel-close aria-label="Close" autofocus
                                class="flex h-9 w-9 items-center justify-center rounded-full bg-black/35 ring-1 ring-white/25 backdrop-blur transition hover:bg-black/55">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Shown while paused (tap the video to pause / play) --}}
                    <span data-reel-paused class="pointer-events-none absolute left-1/2 top-1/2 hidden h-16 w-16 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full bg-black/40 ring-1 ring-white/40 backdrop-blur">
                        <svg class="ml-1 h-7 w-7" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.14v13.72a1 1 0 0 0 1.52.85l10.6-6.86a1 1 0 0 0 0-1.7L9.52 4.29A1 1 0 0 0 8 5.14Z"/></svg>
                    </span>

                    {{-- Product card --}}
                    <div class="absolute inset-x-3 bottom-3 flex items-center gap-3 rounded-2xl bg-white p-2.5 text-gray-900 shadow-2xl">
                        <img data-reel-image src="" alt="" class="h-14 w-14 shrink-0 rounded-xl bg-[#f7f3ee] object-contain p-1">
                        <div class="min-w-0 flex-1">
                            <p data-reel-name class="truncate text-sm font-bold"></p>
                            <p class="mt-0.5 flex items-baseline gap-1.5">
                                <span data-reel-price class="text-base font-black"></span>
                                <span data-reel-mrp class="text-xs text-gray-400 line-through"></span>
                            </p>
                        </div>
                        <a data-reel-link href="#" class="inline-flex h-10 shrink-0 items-center gap-1.5 rounded-full bg-gray-900 px-4 text-xs font-bold text-white transition hover:bg-gray-700">
                            View product
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                        </a>
                    </div>
                </div>

                @if ($player->count() > 1)
                    @foreach (['prev' => ['Previous video', 'left-6', 'M15.75 19.5 8.25 12l7.5-7.5'], 'next' => ['Next video', 'right-6', 'm8.25 4.5 7.5 7.5-7.5 7.5']] as $dir => [$label, $pos, $path])
                        <button type="button" data-reel-{{ $dir }} aria-label="{{ $label }}"
                            class="absolute {{ $pos }} top-1/2 hidden h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-white/15 ring-1 ring-white/30 backdrop-blur transition hover:bg-white/30 sm:flex">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2.4" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/></svg>
                        </button>
                    @endforeach
                @endif
            </div>
        </dialog>
    @endif
</section>

<script>
    (function () {
        const section = document.querySelector('[data-reels]');
        if (!section) return;
        const track = section.querySelector('[data-reels-track]');
        const step = (dir) => track.scrollBy({ left: dir * track.clientWidth * 0.8, behavior: 'smooth' });
        section.querySelector('[data-reels-prev]')?.addEventListener('click', () => step(-1));
        section.querySelector('[data-reels-next]')?.addEventListener('click', () => step(1));

        // Progress bar under each video.
        const trackProgress = (video, bar) => video.addEventListener('timeupdate', () => {
            bar.style.width = video.duration ? (video.currentTime / video.duration * 100) + '%' : '0';
        });

        // Row videos: play silently only while on screen (saves data and battery);
        // not at all for reduced motion or data-saver users.
        const rowVideos = [...section.querySelectorAll('video[data-reel-video]')];
        rowVideos.forEach((v) => trackProgress(v, v.closest('article').querySelector('[data-reel-progress]')));
        const visible = new Set();
        let paused = false;
        const quiet = window.matchMedia('(prefers-reduced-motion: reduce)').matches || navigator.connection?.saveData;
        if (!quiet && 'IntersectionObserver' in window) {
            const io = new IntersectionObserver((entries) => entries.forEach((en) => {
                const on = en.isIntersecting && en.intersectionRatio >= 0.6;
                on ? visible.add(en.target) : visible.delete(en.target);
                if (on && !paused) en.target.play().catch(() => {}); else en.target.pause();
            }), { threshold: [0, 0.6] });
            rowVideos.forEach((v) => io.observe(v));
        }

        // Full-screen player.
        const dialog = section.querySelector('[data-reel-dialog]');
        if (!dialog) return;
        const reels = JSON.parse(section.querySelector('[data-reel-data]').textContent);
        const player = dialog.querySelector('[data-reel-player]');
        const $ = (sel) => dialog.querySelector(sel);
        const pausedIcon = $('[data-reel-paused]');
        const soundBtn = $('[data-reel-sound]');
        let index = 0;
        let muted = false;

        trackProgress(player, $('[data-reel-progress]'));
        player.loop = reels.length === 1;
        player.addEventListener('ended', () => show(index + 1));
        player.addEventListener('play', () => { pausedIcon.classList.add('hidden'); pausedIcon.classList.remove('flex'); });
        player.addEventListener('pause', () => { if (dialog.open) { pausedIcon.classList.remove('hidden'); pausedIcon.classList.add('flex'); } });

        const setMuted = (value) => {
            muted = player.muted = value;
            soundBtn.setAttribute('aria-label', muted ? 'Unmute' : 'Mute');
            soundBtn.querySelector('[data-icon-on]').classList.toggle('hidden', muted);
            soundBtn.querySelector('[data-icon-off]').classList.toggle('hidden', !muted);
        };
        const play = () => player.play().catch((err) => {
            // The browser refused sound without a tap: play muted instead.
            if (err.name === 'NotAllowedError' && !player.muted) { setMuted(true); player.play().catch(() => {}); }
        });

        function show(i) {
            index = (i + reels.length) % reels.length;
            const r = reels[index];
            player.replaceChildren(...r.sources.map((src) => Object.assign(document.createElement('source'), { src })));
            r.poster ? (player.poster = r.poster) : player.removeAttribute('poster');
            $('[data-reel-progress]').style.width = '0';
            player.load();
            setMuted(muted);
            play();
            $('[data-reel-name]').textContent = r.name;
            $('[data-reel-category]').textContent = r.category;
            $('[data-reel-counter]').textContent = (index + 1) + ' / ' + reels.length;
            $('[data-reel-price]').textContent = r.price;
            $('[data-reel-mrp]').textContent = r.mrp || '';
            $('[data-reel-image]').src = r.image;
            $('[data-reel-image]').alt = r.name;
            $('[data-reel-link]').href = r.url;
        }

        section.querySelectorAll('[data-reel-open]').forEach((btn) => btn.addEventListener('click', () => {
            paused = true;
            rowVideos.forEach((v) => v.pause());
            document.documentElement.style.overflow = 'hidden';
            dialog.showModal();
            show(Number(btn.dataset.reelOpen));
        }));

        dialog.addEventListener('close', () => {
            player.pause();
            player.replaceChildren();
            player.load(); // stop downloading
            document.documentElement.style.overflow = '';
            paused = false;
            visible.forEach((v) => v.play().catch(() => {}));
        });
        $('[data-reel-close]').addEventListener('click', () => dialog.close());
        $('[data-reel-stage]').addEventListener('click', (e) => { if (e.target === e.currentTarget) dialog.close(); });
        $('[data-reel-prev]')?.addEventListener('click', () => show(index - 1));
        $('[data-reel-next]')?.addEventListener('click', () => show(index + 1));
        soundBtn.addEventListener('click', () => { setMuted(!muted); play(); });
        dialog.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowRight' || e.key === 'ArrowDown') { e.preventDefault(); show(index + 1); }
            if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') { e.preventDefault(); show(index - 1); }
        });

        // Tap the video to pause / play; swipe up or left for the next one, down or right to go back.
        let start = null;
        let swiped = false;
        const frame = $('[data-reel-frame]');
        frame.addEventListener('pointerdown', (e) => { start = { x: e.clientX, y: e.clientY }; swiped = false; });
        frame.addEventListener('pointerup', (e) => {
            if (!start || reels.length < 2) return;
            const dx = e.clientX - start.x;
            const dy = e.clientY - start.y;
            start = null;
            if (Math.max(Math.abs(dx), Math.abs(dy)) < 50) return;
            swiped = true;
            show(index + ((Math.abs(dy) > Math.abs(dx) ? dy : dx) < 0 ? 1 : -1));
        });
        player.addEventListener('click', () => {
            if (swiped) { swiped = false; return; }
            player.paused ? play() : player.pause();
        });
    })();
</script>
@endif
