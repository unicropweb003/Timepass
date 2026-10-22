@extends('layouts.app', ['seo' => \App\Support\Seo::about()])

@section('content')
@php
    $about = config('shop.about', []);
    $aboutTitle = trim((string) ($about['title'] ?? 'About TimePass')) ?: 'About TimePass';
    $aboutContent = trim((string) ($about['content'] ?? 'TimePass brings beauty and everyday self-care into one easy-to-browse place. Explore skin care, hair care, makeup, fragrances and more, with clear product details to help you choose what feels right for you.'));
    $aboutImage = $about['image'] ?? null;
    $heroImage = $aboutImage ? asset($aboutImage) : asset('images/about-timepass.jpg');
    $categories = config('shop.categories', []);
@endphp

{{-- Spacious split hero inspired by the supplied reference, in TimePass colours. --}}
<section class="relative overflow-hidden bg-[#291d2b] text-white" aria-labelledby="about-title">
    <div class="pointer-events-none absolute -left-28 -top-44 h-96 w-96 rounded-full bg-[#70465d]/20 blur-3xl"></div>
    <div class="relative mx-auto grid max-w-7xl items-end gap-10 px-5 pt-14 sm:px-8 sm:pt-20 lg:min-h-[540px] lg:grid-cols-[1.15fr_0.85fr] lg:gap-16 lg:pt-16">
        <div class="pb-12 sm:pb-16 lg:pb-28">
            <nav aria-label="Breadcrumb" class="flex items-center gap-3 text-sm font-medium text-white/65">
                <a href="{{ route('home') }}" class="transition hover:text-white">Home</a>
                <svg class="h-4 w-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                </svg>
                <span class="text-[#f3b9d4]" aria-current="page">About TimePass</span>
            </nav>
            <h1 id="about-title" class="font-display mt-7 max-w-3xl text-5xl font-medium leading-[1.08] tracking-tight sm:text-6xl lg:text-[4.25rem]">
                {{ $aboutTitle }}
            </h1>
            <p class="mt-5 text-lg font-medium tracking-wide text-white/70 sm:text-xl">Beauty &amp; everyday self-care</p>
        </div>

        <div class="overflow-hidden rounded-t-2xl bg-[#b994a0] shadow-2xl shadow-black/20 lg:self-end">
            <img
                src="{{ $heroImage }}"
                alt="{{ $aboutImage ? $aboutTitle : 'Woman enjoying a simple skincare routine at home' }}"
                class="h-72 w-full object-cover object-[center_35%] sm:h-96 lg:h-[455px]"
                fetchpriority="high"
            >
        </div>
    </div>
</section>

<section class="bg-[#f8f5f1] px-4 py-8 sm:px-6 sm:py-14">
    <div class="mx-auto max-w-6xl rounded-2xl border border-[#e8ded6] bg-white px-6 py-10 shadow-sm sm:px-12 sm:py-14 lg:px-20 lg:py-16">
        <div class="max-w-4xl">
            <p class="whitespace-pre-line text-lg leading-[1.8] text-[#2d2a2b] sm:text-[1.35rem]">{{ $aboutContent }}</p>

            <section class="mt-14 sm:mt-16" aria-labelledby="about-offer">
                <h2 id="about-offer" class="font-display text-3xl font-medium tracking-tight text-[#3e2636] sm:text-4xl">What we offer</h2>
                <ul class="mt-6 list-disc space-y-3 pl-6 text-base leading-relaxed text-[#343032] marker:text-[#b45486] sm:text-lg">
                    @foreach ($categories as $category)
                        <li class="pl-1">
                            <a href="{{ route('products', ['category' => $category['slug']]) }}" class="font-semibold text-[#292329] underline decoration-transparent underline-offset-4 transition hover:text-[#9a3f70] hover:decoration-current">
                                {{ $category['name'] }}
                            </a>
                            <span aria-hidden="true"> — </span>{{ ucfirst($category['description']) }}.
                        </li>
                    @endforeach
                </ul>
            </section>

            <section class="mt-14 sm:mt-16" aria-labelledby="about-shopping">
                <h2 id="about-shopping" class="font-display text-3xl font-medium tracking-tight text-[#3e2636] sm:text-4xl">How we make shopping simple</h2>
                <p class="mt-5 text-base leading-[1.8] text-[#484044] sm:text-lg">
                    Browse by category, read the product details and choose the things that suit your routine. Keep favourites in your wishlist, or contact us when you need help with a product or an order.
                </p>

                <div class="mt-8 grid gap-4 border-t border-[#eee5e0] pt-8 sm:grid-cols-3 sm:gap-7">
                    <div>
                        <span class="font-display text-2xl text-[#ad5383]">01</span>
                        <h3 class="mt-2 font-semibold text-[#30252e]">Explore</h3>
                        <p class="mt-1 text-sm leading-relaxed text-[#6b6066]">Find products across your everyday beauty and care categories.</p>
                    </div>
                    <div>
                        <span class="font-display text-2xl text-[#ad5383]">02</span>
                        <h3 class="mt-2 font-semibold text-[#30252e]">Choose</h3>
                        <p class="mt-1 text-sm leading-relaxed text-[#6b6066]">Check the details and save the items you want to revisit.</p>
                    </div>
                    <div>
                        <span class="font-display text-2xl text-[#ad5383]">03</span>
                        <h3 class="mt-2 font-semibold text-[#30252e]">Connect</h3>
                        <p class="mt-1 text-sm leading-relaxed text-[#6b6066]">Send us a question whenever you need a hand.</p>
                    </div>
                </div>
            </section>

            <div class="mt-14 flex flex-wrap items-center gap-4 border-t border-[#eee5e0] pt-8 sm:mt-16">
                <a href="{{ route('products') }}" class="inline-flex items-center gap-2 rounded-full bg-[#43283c] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#6b385a] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#6b385a]">
                    Explore our products
                    <svg class="h-4 w-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" />
                    </svg>
                </a>
                <a href="{{ route('contact') }}" class="text-sm font-semibold text-[#6d385a] underline decoration-[#d7b3c4] underline-offset-4 transition hover:text-[#3e2636]">Get in touch</a>
            </div>
        </div>
    </div>
</section>
@endsection
