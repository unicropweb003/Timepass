@extends('layouts.admin', ['title' => $category->exists ? 'Edit category' : 'New category'])

@section('content')
@php
    // Render existing types as "slug | Name" lines for the textarea.
    $typesText = old('types_text');
    if ($typesText === null) {
        $lines = [];
        foreach (($category->types ?? []) as $slug => $name) {
            $lines[] = $slug . ' | ' . $name;
        }
        $typesText = implode("\n", $lines);
    }
@endphp

<a href="{{ route('admin.categories.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-800">
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
    Back to categories
</a>

<form method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" class="max-w-2xl">
    @csrf
    @if ($category->exists) @method('PUT') @endif

    @if ($errors->any())
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="space-y-6 rounded-2xl border border-gray-200 bg-white p-6">
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="name" value="{{ old('name', $category->name) }}" required
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/40">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Slug <span class="text-gray-400">— optional, auto from name</span></label>
                <input type="text" name="slug" value="{{ old('slug', $category->slug) }}" placeholder="skin-care"
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/40">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Description</label>
            <input type="text" name="description" value="{{ old('description', $category->description) }}"
                class="mt-1 block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/40">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Types <span class="text-gray-400">— one per line</span></label>
            <textarea name="types_text" rows="6" placeholder="serums | Serums&#10;moisturizers | Moisturizers"
                class="mt-1 block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 font-mono text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/40">{{ $typesText }}</textarea>
            <p class="mt-1 text-xs text-gray-400">Format: <code>slug | Display Name</code> (or just the name — slug is generated). These are the sub-filters shown under a category.</p>
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-gray-700">Sort order</label>
                <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $category->sort_order ?? 0) }}"
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/40">
            </div>
            <div class="flex items-end">
                <label class="flex items-center gap-2 text-sm font-medium text-gray-700">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active ?? true))
                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    Active (shown on the store)
                </label>
            </div>
        </div>

        <details class="rounded-lg bg-gray-50 p-4 text-sm">
            <summary class="cursor-pointer font-medium text-gray-700">Advanced: icon SVG path</summary>
            <textarea name="icon" rows="2" placeholder="M9.813 15.904 9 18.75l…"
                class="mt-2 block w-full rounded-lg border border-gray-300 px-3 py-2 font-mono text-xs shadow-sm focus:border-indigo-500">{{ old('icon', $category->icon) }}</textarea>
            <p class="mt-1 text-xs text-gray-400">An outline (heroicons-style) 24×24 SVG path. Optional.</p>
        </details>
    </div>

    <div class="mt-5 flex gap-3">
        <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 font-semibold text-white hover:bg-indigo-500">
            {{ $category->exists ? 'Save changes' : 'Create category' }}
        </button>
        <a href="{{ route('admin.categories.index') }}" class="rounded-lg border border-gray-300 px-5 py-2.5 font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
    </div>
</form>
@endsection
