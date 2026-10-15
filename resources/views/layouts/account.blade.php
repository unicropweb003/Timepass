{{-- Shell for the "My Account" pages: sidebar (tabs on phones) + page content.
     Child views set $title and fill @section('account'). --}}
@extends('layouts.app', ['title' => ($title ?? 'My Account') . ' - My Account'])

@php
    $accountLinks = [
        ['account.index', 'Overview', 'account.index', 'm2.25 12 8.954-8.955a1.126 1.126 0 0 1 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25'],
        ['account.wishlist', 'My Wishlist', 'account.wishlist', 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z'],
        ['account.orders', 'My Orders', 'account.orders*', 'M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z'],
        ['account.transactions', 'Transactions', 'account.transactions', 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z'],
        ['account.addresses', 'Delivery Addresses', 'account.addresses', 'M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z'],
        ['account.profile', 'Edit Profile', 'account.profile', 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z'],
    ];
    $user = auth()->user();
@endphp

@section('content')
<div class="grid gap-8 lg:grid-cols-[17rem_1fr]">
    <aside>
        {{-- Profile card --}}
        <div class="flex items-center gap-3 rounded-2xl bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 p-4 text-white shadow-lg shadow-purple-500/20">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-white/20 text-lg font-bold ring-2 ring-white/40">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
            <div class="min-w-0">
                <p class="truncate font-semibold">{{ $user->name }}</p>
                <p class="truncate text-xs text-white/80">{{ $user->email }}</p>
            </div>
        </div>

        <nav class="-mx-4 mt-4 flex gap-2 overflow-x-auto px-4 pb-1 [scrollbar-width:none] lg:mx-0 lg:flex-col lg:gap-1 lg:overflow-visible lg:px-0" aria-label="My account">
            @foreach ($accountLinks as [$route, $label, $pattern, $icon])
                @php $active = request()->routeIs($pattern); @endphp
                <a href="{{ route($route) }}" @if ($active) aria-current="page" @endif
                    class="flex shrink-0 items-center gap-3 whitespace-nowrap rounded-xl px-4 py-2.5 text-sm font-semibold transition {{ $active ? 'bg-white text-indigo-700 shadow-sm ring-1 ring-indigo-100' : 'text-gray-600 hover:bg-white hover:text-gray-900' }}">
                    <svg class="h-5 w-5 {{ $active ? 'text-indigo-600' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" /></svg>
                    {{ $label }}
                </a>
            @endforeach
            <form method="POST" action="{{ route('logout') }}" class="shrink-0 lg:mt-2 lg:border-t lg:border-gray-200 lg:pt-2">
                @csrf
                <button type="submit" class="flex w-full items-center gap-3 whitespace-nowrap rounded-xl px-4 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-50">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" /></svg>
                    Logout
                </button>
            </form>
        </nav>
    </aside>

    <div class="min-w-0">
        @if (session('status'))
            <div class="mb-6 flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 ring-1 ring-emerald-200">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                {{ session('status') }}
            </div>
        @endif
        @yield('account')
    </div>
</div>
@endsection
