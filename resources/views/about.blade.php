@extends('layouts.app', ['title' => 'About'])

@section('content')
<div class="mx-auto max-w-3xl">
    <h1 class="text-3xl font-bold text-gray-900">About TimePass</h1>
    <p class="mt-4 text-gray-600 leading-relaxed">
        TimePass is a simple, clean website built with Laravel. This page is where you can describe
        your company, your team and what you do. Use the navigation menu above to move between the
        Home, Products, About and Contact pages.
    </p>

    <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-3">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7Z"/></svg>
            </div>
            <h3 class="mt-4 font-semibold text-gray-900">Fast</h3>
            <p class="mt-1 text-sm text-gray-500">Lightweight pages that load quickly.</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-6 text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 0h10.5a1.5 1.5 0 0 1 1.5 1.5v6a1.5 1.5 0 0 1-1.5 1.5H6.75a1.5 1.5 0 0 1-1.5-1.5v-6a1.5 1.5 0 0 1 1.5-1.5Z"/></svg>
            </div>
            <h3 class="mt-4 font-semibold text-gray-900">Secure</h3>
            <p class="mt-1 text-sm text-gray-500">Accounts protected with hashed passwords.</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-6 text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.3 24.3 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23-.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5"/></svg>
            </div>
            <h3 class="mt-4 font-semibold text-gray-900">Flexible</h3>
            <p class="mt-1 text-sm text-gray-500">Easy to extend with your own content.</p>
        </div>
    </div>
</div>
@endsection
