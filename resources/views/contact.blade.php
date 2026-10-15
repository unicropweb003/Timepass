@extends('layouts.app', ['title' => 'Contact'])

@section('content')
@php
    $email   = config('shop.contact.email');
    $phone   = config('shop.contact.phone');
    $address = config('shop.contact.address');
    $hours   = config('shop.contact.hours');
    $social  = config('shop.social', []);
@endphp

<div class="mx-auto max-w-5xl">

    {{-- Page heading --}}
    <div class="text-center">
        <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-indigo-600">
            Get in touch
        </span>
        <h1 class="mt-4 text-4xl font-bold tracking-tight text-gray-900">Contact us</h1>
        <p class="mx-auto mt-3 max-w-xl text-gray-600">
            Have a question about an order, a product or anything else? Send us a message
            and our team will get back to you as soon as possible.
        </p>
    </div>

    @if (session('status'))
        <div class="mx-auto mt-8 flex max-w-3xl items-center gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
            <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            </svg>
            {{ session('status') }}
        </div>
    @endif

    <div class="mt-10 grid grid-cols-1 gap-6 lg:grid-cols-5">

        {{-- Left: contact information panel --}}
        <div class="lg:col-span-2">
            <div class="relative h-full overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 via-indigo-600 to-purple-600 p-8 text-white shadow-lg">
                {{-- decorative circles --}}
                <div class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10"></div>
                <div class="pointer-events-none absolute -bottom-12 -left-8 h-44 w-44 rounded-full bg-white/10"></div>

                <div class="relative">
                    <h2 class="text-xl font-bold">Contact information</h2>
                    <p class="mt-2 text-sm text-indigo-100">Reach us through any of the channels below.</p>

                    <ul class="mt-8 space-y-6">
                        {{-- Email --}}
                        <li class="flex items-start gap-4">
                            <span class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-white/15">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>
                                </svg>
                            </span>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-indigo-200">Email</p>
                                <a href="mailto:{{ $email }}" class="mt-0.5 block font-semibold hover:underline">{{ $email }}</a>
                            </div>
                        </li>

                        {{-- Phone --}}
                        <li class="flex items-start gap-4">
                            <span class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-white/15">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/>
                                </svg>
                            </span>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-indigo-200">Phone</p>
                                <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="mt-0.5 block font-semibold hover:underline">{{ $phone }}</a>
                            </div>
                        </li>

                        {{-- Address --}}
                        <li class="flex items-start gap-4">
                            <span class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-white/15">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                                </svg>
                            </span>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-indigo-200">Address</p>
                                <p class="mt-0.5 font-semibold">{{ $address }}</p>
                            </div>
                        </li>

                        {{-- Hours --}}
                        <li class="flex items-start gap-4">
                            <span class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-white/15">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                </svg>
                            </span>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-indigo-200">Working hours</p>
                                <p class="mt-0.5 font-semibold">{{ $hours }}</p>
                            </div>
                        </li>
                    </ul>

                    {{-- Social links --}}
                    @if (!empty(array_filter($social)))
                        <div class="mt-10 border-t border-white/20 pt-6">
                            <p class="text-xs font-medium uppercase tracking-wide text-indigo-200">Follow us</p>
                            <div class="mt-3 flex gap-3">
                                @if (!empty($social['instagram']))
                                    <a href="{{ $social['instagram'] }}" target="_blank" rel="noopener" aria-label="Instagram" class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/15 transition hover:bg-white/25">
                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.2c3.2 0 3.6 0 4.9.07 1.2.06 1.8.25 2.2.42.56.22.96.48 1.38.9.42.42.68.82.9 1.38.17.4.36 1 .42 2.2.06 1.3.07 1.7.07 4.9s0 3.6-.07 4.9c-.06 1.2-.25 1.8-.42 2.2-.22.56-.48.96-.9 1.38-.42.42-.82.68-1.38.9-.4.17-1 .36-2.2.42-1.3.06-1.7.07-4.9.07s-3.6 0-4.9-.07c-1.2-.06-1.8-.25-2.2-.42a3.8 3.8 0 0 1-1.38-.9 3.8 3.8 0 0 1-.9-1.38c-.17-.4-.36-1-.42-2.2C2.2 15.6 2.2 15.2 2.2 12s0-3.6.07-4.9c.06-1.2.25-1.8.42-2.2.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.4-.17 1-.36 2.2-.42C8.4 2.2 8.8 2.2 12 2.2Zm0 1.8c-3.1 0-3.5 0-4.7.07-1.1.05-1.7.24-2.1.4-.5.2-.9.44-1.3.84-.4.4-.64.8-.84 1.3-.16.4-.35 1-.4 2.1C2.6 10.5 2.6 10.9 2.6 14s0 3.5.07 4.7c.05 1.1.24 1.7.4 2.1.2.5.44.9.84 1.3.4.4.8.64 1.3.84.4.16 1 .35 2.1.4 1.2.07 1.6.07 4.7.07s3.5 0 4.7-.07c1.1-.05 1.7-.24 2.1-.4.5-.2.9-.44 1.3-.84.4-.4.64-.8.84-1.3.16-.4.35-1 .4-2.1.07-1.2.07-1.6.07-4.7s0-3.5-.07-4.7c-.05-1.1-.24-1.7-.4-2.1a3.5 3.5 0 0 0-.84-1.3 3.5 3.5 0 0 0-1.3-.84c-.4-.16-1-.35-2.1-.4-1.2-.07-1.6-.07-4.7-.07Zm0 3.06a4.94 4.94 0 1 1 0 9.88 4.94 4.94 0 0 1 0-9.88Zm0 8.14a3.2 3.2 0 1 0 0-6.4 3.2 3.2 0 0 0 0 6.4Zm6.3-8.34a1.15 1.15 0 1 1-2.3 0 1.15 1.15 0 0 1 2.3 0Z"/></svg>
                                    </a>
                                @endif
                                @if (!empty($social['facebook']))
                                    <a href="{{ $social['facebook'] }}" target="_blank" rel="noopener" aria-label="Facebook" class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/15 transition hover:bg-white/25">
                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12a10 10 0 1 0-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.5 1.49-3.89 3.77-3.89 1.1 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.44 2.89h-2.34v6.99A10 10 0 0 0 22 12Z"/></svg>
                                    </a>
                                @endif
                                @if (!empty($social['youtube']))
                                    <a href="{{ $social['youtube'] }}" target="_blank" rel="noopener" aria-label="YouTube" class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/15 transition hover:bg-white/25">
                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23 12s0-3.2-.4-4.7a2.5 2.5 0 0 0-1.76-1.77C19.33 5.1 12 5.1 12 5.1s-7.33 0-8.84.42A2.5 2.5 0 0 0 1.4 7.3C1 8.8 1 12 1 12s0 3.2.4 4.7a2.5 2.5 0 0 0 1.76 1.77c1.51.43 8.84.43 8.84.43s7.33 0 8.84-.43a2.5 2.5 0 0 0 1.76-1.77C23 15.2 23 12 23 12Zm-13.5 3.03V8.97L15.5 12l-6 3.03Z"/></svg>
                                    </a>
                                @endif
                                @if (!empty($social['x']))
                                    <a href="{{ $social['x'] }}" target="_blank" rel="noopener" aria-label="X" class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/15 transition hover:bg-white/25">
                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.9 2H22l-7.2 8.26L23.3 22h-6.6l-5.18-6.78L5.6 22H2.5l7.7-8.84L1 2h6.77l4.68 6.19L18.9 2Zm-1.16 18h1.83L7.34 3.9H5.38L17.74 20Z"/></svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right: contact form --}}
        <div class="lg:col-span-3">
            <form method="POST" action="{{ route('contact.submit') }}" class="h-full rounded-2xl border border-gray-200 bg-white p-8 shadow-sm">
                @csrf
                <h2 class="text-xl font-bold text-gray-900">Send us a message</h2>
                <p class="mt-1 text-sm text-gray-500">We usually reply within one business day.</p>

                <div class="mt-6 space-y-5">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Your name</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Jane Doe"
                            class="mt-1 block w-full rounded-lg border px-3.5 py-2.5 shadow-sm transition focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 @error('name') border-red-400 @else border-gray-300 @enderror">
                        @error('name') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="you@example.com"
                            class="mt-1 block w-full rounded-lg border px-3.5 py-2.5 shadow-sm transition focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 @error('email') border-red-400 @else border-gray-300 @enderror">
                        @error('email') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                        <textarea id="message" name="message" rows="5" placeholder="How can we help you?"
                            class="mt-1 block w-full rounded-lg border px-3.5 py-2.5 shadow-sm transition focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 @error('message') border-red-400 @else border-gray-300 @enderror">{{ old('message') }}</textarea>
                        @error('message') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-3 font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:ring-offset-2">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.126A59.768 59.768 0 0 1 21.485 12 59.77 59.77 0 0 1 3.27 20.876L5.999 12Zm0 0h7.5"/>
                        </svg>
                        Send message
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
