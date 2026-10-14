@extends('layouts.account', ['title' => 'Edit Profile'])

@section('account')
<h1 class="text-2xl font-bold text-gray-900">Edit Profile</h1>
<p class="mt-1 text-gray-500">Update your personal details and password.</p>

<div class="mt-6 grid gap-6 xl:grid-cols-2">
    <form method="POST" action="{{ route('account.profile.update') }}" class="h-fit space-y-4 rounded-2xl bg-white p-6 ring-1 ring-gray-200">
        @csrf @method('PUT')
        <h2 class="font-semibold text-gray-900">Personal details</h2>
        <x-account.field name="name" label="Full name" :value="auth()->user()->name" autocomplete="name" />
        <x-account.field name="phone" label="Phone number" type="tel" :value="auth()->user()->phone" inputmode="numeric" maxlength="15" autocomplete="tel" />
        <x-account.field name="email" label="Email address" type="email" :value="auth()->user()->email" autocomplete="email" />
        <button type="submit" class="flex h-11 w-full items-center justify-center rounded-xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500 font-semibold text-white shadow-lg shadow-purple-500/25 transition hover:shadow-xl">Save changes</button>
    </form>

    <form method="POST" action="{{ route('account.password.update') }}" class="h-fit space-y-4 rounded-2xl bg-white p-6 ring-1 ring-gray-200">
        @csrf @method('PUT')
        <h2 class="font-semibold text-gray-900">Change password</h2>
        <x-account.field name="current_password" label="Current password" type="password" bag="password" autocomplete="current-password" />
        <x-account.field name="password" label="New password" type="password" bag="password" autocomplete="new-password" />
        <x-account.field name="password_confirmation" label="Confirm new password" type="password" bag="password" autocomplete="new-password" />
        <p class="text-xs text-gray-500">At least 8 characters, with letters and numbers.</p>
        <button type="submit" class="flex h-11 w-full items-center justify-center rounded-xl border border-gray-200 font-semibold text-gray-800 transition hover:border-indigo-300 hover:bg-indigo-50/50 hover:text-indigo-700">Update password</button>
    </form>
</div>
@endsection
