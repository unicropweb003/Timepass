@extends('layouts.admin', ['title' => $review->exists ? 'Edit review' : 'New review'])

@section('content')
<a href="{{ route('admin.reviews.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-800">
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
    Back to reviews
</a>

<form method="POST" action="{{ $review->exists ? route('admin.reviews.update', $review) : route('admin.reviews.store') }}" class="max-w-2xl">
    @csrf
    @if ($review->exists) @method('PUT') @endif

    @if ($errors->any())
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="space-y-6 rounded-2xl border border-gray-200 bg-white p-6">
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-gray-700">Customer name</label>
                <input type="text" name="name" value="{{ old('name', $review->name) }}" required
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/40">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Tagline <span class="text-gray-400">— optional</span></label>
                <input type="text" name="tagline" value="{{ old('tagline', $review->tagline) }}" placeholder="e.g. Skin care lover"
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/40">
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-gray-700">Rating</label>
                <select name="rating" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/40">
                    @for ($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}" @selected((int) old('rating', $review->rating) === $i)>{{ $i }} star{{ $i > 1 ? 's' : '' }} ({{ str_repeat('★', $i) }}{{ str_repeat('☆', 5 - $i) }})</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Sort order <span class="text-gray-400">— lower shows first</span></label>
                <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $review->sort_order ?? 0) }}"
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/40">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Review text</label>
            <textarea name="text" rows="4" required
                class="mt-1 block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/40">{{ old('text', $review->text) }}</textarea>
        </div>

        <label class="flex items-center gap-2 text-sm font-medium text-gray-700">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $review->is_active ?? true))
                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            Show on the home page
        </label>
    </div>

    <div class="mt-5 flex gap-3">
        <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 font-semibold text-white hover:bg-indigo-500">
            {{ $review->exists ? 'Save changes' : 'Add review' }}
        </button>
        <a href="{{ route('admin.reviews.index') }}" class="rounded-lg border border-gray-300 px-5 py-2.5 font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
    </div>
</form>
@endsection
