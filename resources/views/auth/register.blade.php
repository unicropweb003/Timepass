@extends('layouts.app', ['title' => 'Register'])

@section('content')
<div class="relative isolate mx-auto max-w-5xl lg:my-4">
    <div class="pointer-events-none absolute -left-16 top-10 -z-10 h-72 w-72 rounded-full bg-indigo-300/30 blur-3xl"></div>
    <div class="pointer-events-none absolute -right-16 bottom-0 -z-10 h-72 w-72 rounded-full bg-pink-300/30 blur-3xl"></div>

    <div class="grid overflow-hidden rounded-3xl bg-white shadow-2xl shadow-indigo-500/10 ring-1 ring-gray-200 lg:grid-cols-2">
        @include('auth.partials.panel', [
            'heading' => 'Join TimePass today',
            'text' => 'Create your free account in under a minute and start shopping smarter.',
            'points' => [
                'Quick sign up with just a few details',
                'Member-only offers and early sale access',
                'Secure account with an encrypted password',
            ],
        ])

        <div class="flex flex-col justify-center p-6 sm:p-10 xl:p-12">
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 via-purple-600 to-pink-500 text-white shadow-lg shadow-purple-500/30">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" /></svg>
            </span>
            <h1 class="mt-5 text-3xl font-bold tracking-tight text-gray-900">Create your account</h1>
            <p class="mt-2 text-gray-500">Fill in your details below to get started.</p>

            <form id="registerForm" class="mt-8 space-y-5" novalidate>
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-auth.input name="name" label="Full name" autocomplete="name" placeholder="Your name"
                        icon="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    <x-auth.input name="phone" type="tel" label="Phone number" inputmode="numeric" autocomplete="tel" maxlength="15" placeholder="10-digit number"
                        icon="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                </div>

                <x-auth.input name="email" type="email" label="Email address" autocomplete="email" placeholder="you@example.com"
                    icon="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-auth.input name="password" type="password" label="Password" autocomplete="new-password" placeholder="New password" toggle
                        icon="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                    <x-auth.input name="password_confirmation" type="password" label="Confirm password" autocomplete="new-password" placeholder="Repeat password" toggle :error="false"
                        icon="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                </div>

                {{-- Live checklist, updated by public/js/auth.js as the user types --}}
                <ul data-password-rules class="grid grid-cols-2 gap-x-4 gap-y-2 rounded-xl bg-gray-50 px-4 py-3 text-xs text-gray-500 ring-1 ring-gray-100">
                    @foreach (['length' => 'At least 8 characters', 'letter' => 'Contains a letter', 'number' => 'Contains a number', 'match' => 'Passwords match'] as $rule => $text)
                        <li data-rule="{{ $rule }}" data-met="false" class="group flex items-center gap-2 transition-colors data-[met=true]:text-emerald-700">
                            <span class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-gray-200 text-white transition-colors group-data-[met=true]:bg-emerald-500">
                                <svg class="h-2.5 w-2.5 opacity-0 transition-opacity group-data-[met=true]:opacity-100" fill="none" viewBox="0 0 24 24" stroke-width="4" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            </span>
                            {{ $text }}
                        </li>
                    @endforeach
                </ul>

                <p id="formError" class="hidden rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700 ring-1 ring-red-100"></p>

                <button type="submit" id="submitBtn"
                    class="group flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500 font-semibold text-white shadow-lg shadow-purple-500/30 transition hover:-translate-y-0.5 hover:shadow-xl hover:shadow-purple-500/40 disabled:pointer-events-none disabled:opacity-80">
                    <svg class="hidden h-5 w-5 animate-spin group-disabled:block" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4Z" /></svg>
                    <span class="group-disabled:hidden">Create account</span>
                    <span class="hidden group-disabled:inline">Creating account...</span>
                    <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5 group-disabled:hidden" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                </button>
            </form>

            <p class="mt-8 text-center text-sm text-gray-500">
                Already have an account?
                <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:text-indigo-500">Log in</a>
            </p>
        </div>
    </div>
</div>

<script>
    window.AUTH_ENDPOINT = @json(route('api.register'));
</script>
<script src="{{ asset('js/auth.js') }}"></script>
@endsection
