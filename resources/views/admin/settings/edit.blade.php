@extends('layouts.admin', ['title' => 'Site settings'])

@section('content')
<form method="POST" action="{{ route('admin.settings.update') }}" class="max-w-2xl">
    @csrf @method('PUT')

    @if ($errors->any())
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="space-y-6 rounded-2xl border border-gray-200 bg-white p-6">
        <h2 class="font-semibold text-gray-900">Contact details <span class="text-sm font-normal text-gray-400">— shown on the Contact page &amp; footer</span></h2>
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input type="text" name="contact[email]" value="{{ old('contact.email', $contact['email'] ?? '') }}"
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/40">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Phone</label>
                <input type="text" name="contact[phone]" value="{{ old('contact.phone', $contact['phone'] ?? '') }}"
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/40">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Address</label>
                <input type="text" name="contact[address]" value="{{ old('contact.address', $contact['address'] ?? '') }}"
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/40">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Working hours</label>
                <input type="text" name="contact[hours]" value="{{ old('contact.hours', $contact['hours'] ?? '') }}"
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/40">
            </div>
        </div>
    </div>

    <div class="mt-6 space-y-6 rounded-2xl border border-gray-200 bg-white p-6">
        <h2 class="font-semibold text-gray-900">Social links</h2>
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            @foreach (['instagram' => 'Instagram', 'facebook' => 'Facebook', 'youtube' => 'YouTube', 'x' => 'X (Twitter)'] as $key => $label)
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ $label }}</label>
                    <input type="url" name="social[{{ $key }}]" value="{{ old('social.'.$key, $social[$key] ?? '') }}" placeholder="https://…"
                        class="mt-1 block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/40">
                </div>
            @endforeach
        </div>
    </div>

    <div class="mt-5">
        <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 font-semibold text-white hover:bg-indigo-500">Save settings</button>
    </div>
</form>
@endsection
