@extends('layouts.app', ['title' => 'Contact'])

@section('content')
<div class="mx-auto max-w-xl">
    <h1 class="text-3xl font-bold text-gray-900">Contact us</h1>
    <p class="mt-2 text-gray-600">Have a question? Send us a message and we'll get back to you.</p>

    @if (session('status'))
        <div class="mt-6 rounded-lg bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('contact.submit') }}" class="mt-6 space-y-5 rounded-2xl border border-gray-200 bg-white p-8">
        @csrf
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}"
                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}"
                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
            <textarea id="message" name="message" rows="4"
                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('message') }}</textarea>
            @error('message') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 font-semibold text-white hover:bg-indigo-500">
            Send message
        </button>
    </form>
</div>
@endsection
