@extends('layouts.app', ['title' => 'Login'])

@section('content')
<div class="relative isolate mx-auto max-w-5xl lg:my-4">
    <div class="pointer-events-none absolute -left-16 top-10 -z-10 h-72 w-72 rounded-full bg-indigo-300/30 blur-3xl"></div>
    <div class="pointer-events-none absolute -right-16 bottom-0 -z-10 h-72 w-72 rounded-full bg-pink-300/30 blur-3xl"></div>

    <div class="grid overflow-hidden rounded-3xl bg-white shadow-2xl shadow-indigo-500/10 ring-1 ring-gray-200 lg:grid-cols-2">
        @include('auth.partials.panel', [
            'heading' => 'Welcome back to TimePass',
            'text' => 'Log in to pick up where you left off and catch the latest offers.',
            'points' => [
                'Member-only offers and early sale access',
                'Free delivery on orders above ₹499',
                'Easy 7-day returns and exchanges',
            ],
        ])

        <div class="flex flex-col justify-center p-6 sm:p-10 xl:p-12">
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 via-purple-600 to-pink-500 text-white shadow-lg shadow-purple-500/30">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25" /></svg>
            </span>
            <h1 class="mt-5 text-3xl font-bold tracking-tight text-gray-900">Welcome back</h1>
            <p class="mt-2 text-gray-500">Log in to your TimePass account.</p>

            <form id="loginForm" class="mt-8 space-y-5" novalidate>
                <x-auth.input name="email" type="email" label="Email address" autocomplete="email" placeholder="you@example.com"
                    icon="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />

                <x-auth.input name="password" type="password" label="Password" autocomplete="current-password" placeholder="Enter your password" toggle
                    icon="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />

                <p id="formError" class="hidden rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700 ring-1 ring-red-100"></p>

                <button type="submit" id="submitBtn"
                    class="group flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500 font-semibold text-white shadow-lg shadow-purple-500/30 transition hover:-translate-y-0.5 hover:shadow-xl hover:shadow-purple-500/40 disabled:pointer-events-none disabled:opacity-80">
                    <svg class="hidden h-5 w-5 animate-spin group-disabled:block" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4Z" /></svg>
                    <span class="group-disabled:hidden">Log in</span>
                    <span class="hidden group-disabled:inline">Logging in...</span>
                    <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5 group-disabled:hidden" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                </button>
            </form>

            <div class="mt-8 flex items-center gap-3 text-xs font-medium uppercase tracking-wider text-gray-400">
                <span class="h-px flex-1 bg-gray-200"></span>
                New to TimePass?
                <span class="h-px flex-1 bg-gray-200"></span>
            </div>
            <a href="{{ route('register') }}"
                class="mt-5 flex h-12 w-full items-center justify-center rounded-xl border border-gray-200 font-semibold text-gray-800 transition hover:border-indigo-300 hover:bg-indigo-50/50 hover:text-indigo-700">
                Create an account
            </a>
        </div>
    </div>
</div>

<script>
    window.AUTH_ENDPOINT = @json(route('api.login'));
</script>
<script src="{{ asset('js/auth.js') }}"></script>
@endsection
