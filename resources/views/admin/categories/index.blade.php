@extends('layouts.admin', ['title' => 'Categories'])

@section('content')
<div class="mb-5 flex items-center justify-between">
    <p class="text-sm text-gray-500">{{ $categories->count() }} categories</p>
    <a href="{{ route('admin.categories.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Add category
    </a>
</div>

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @forelse ($categories as $cat)
        <div class="rounded-2xl border border-gray-200 bg-white p-5">
            <div class="flex items-start justify-between">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $cat->icon ?: 'M6 6.878V6a2.25 2.25 0 0 1 2.25-2.25h7.5A2.25 2.25 0 0 1 18 6v.878' }}"/></svg>
                </span>
                @if ($cat->is_active)
                    <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-emerald-200">Active</span>
                @else
                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 ring-1 ring-gray-200">Hidden</span>
                @endif
            </div>
            <h3 class="mt-4 font-semibold text-gray-900">{{ $cat->name }}</h3>
            <p class="mt-1 text-sm text-gray-500">{{ $cat->description }}</p>
            <p class="mt-3 text-xs text-gray-400">
                {{ count($cat->types ?? []) }} types · {{ $counts[$cat->slug] ?? 0 }} products
            </p>
            <div class="mt-4 flex gap-2">
                <a href="{{ route('admin.categories.edit', $cat) }}" class="flex-1 rounded-lg border border-gray-300 px-3 py-1.5 text-center text-xs font-medium text-gray-700 hover:bg-gray-50">Edit</a>
                <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}" onsubmit="return confirm('Delete this category? Products in it will remain but become uncategorised.')" class="flex-1">
                    @csrf @method('DELETE')
                    <button class="w-full rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">Delete</button>
                </form>
            </div>
        </div>
    @empty
        <p class="col-span-full py-10 text-center text-gray-500">No categories yet.</p>
    @endforelse
</div>
@endsection
