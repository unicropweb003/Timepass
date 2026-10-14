@extends('layouts.app', ['title' => 'Search'])

@section('content')
<div class="mx-auto max-w-3xl">
    <h1 class="text-2xl font-bold text-gray-900">
        @if ($q === '')
            Search
        @else
            Results for "{{ $q }}"
        @endif
    </h1>
    <p class="mt-1 text-sm text-gray-500">{{ count($results) }} result(s)</p>

    @if ($q === '')
        <div class="mt-8 rounded-2xl border border-dashed border-gray-300 bg-white p-12 text-center text-gray-500">
            Type something in the search bar above to find a page.
        </div>
    @elseif (count($results) === 0)
        <div class="mt-8 rounded-2xl border border-dashed border-gray-300 bg-white p-12 text-center">
            <p class="text-gray-500">No results found for "{{ $q }}".</p>
            <a href="{{ route('home') }}" class="mt-3 inline-block text-sm font-medium text-indigo-600 hover:text-indigo-500">← Back to home</a>
        </div>
    @else
        <div class="mt-6 space-y-3">
            @foreach ($results as $result)
                <a href="{{ route($result['route']) }}"
                    class="block rounded-2xl border border-gray-200 bg-white p-5 transition hover:border-indigo-300 hover:shadow-sm">
                    <h2 class="font-semibold text-indigo-600">{{ $result['title'] }}</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ $result['description'] }}</p>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
