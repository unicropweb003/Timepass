{{-- Empty state box. Expects: $icon (SVG path), $title, $text; optional $action ([label, url]). --}}
<div class="rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center">
    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" /></svg>
    </div>
    <h3 class="mt-4 font-semibold text-gray-900">{{ $title }}</h3>
    <p class="mx-auto mt-1 max-w-sm text-sm text-gray-500">{{ $text }}</p>
    @isset($action)
        <a href="{{ $action[1] }}" class="mt-5 inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-indigo-600 to-purple-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-500/25 transition hover:shadow-lg">{{ $action[0] }}</a>
    @endisset
</div>
