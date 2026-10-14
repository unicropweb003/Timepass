{{-- Labelled input for the My Account forms, with the server-side validation error under it.
     'bag' picks the error bag (e.g. "password" for the change-password form). --}}
@props(['name', 'label', 'type' => 'text', 'value' => null, 'bag' => 'default'])

@php $error = $errors->getBag($bag)->first($name); @endphp

<div>
    <label for="{{ $name }}" class="mb-1.5 block text-sm font-medium text-gray-700">{{ $label }}</label>
    <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}"
        @if ($type !== 'password') value="{{ old($name, $value) }}" @endif
        {{ $attributes->class([
            'block h-11 w-full rounded-xl border bg-gray-50/70 px-4 text-sm text-gray-900 placeholder-gray-400 transition focus:bg-white focus:outline-none focus:ring-4',
            $error ? 'border-red-400 focus:ring-red-500/15' : 'border-gray-200 focus:border-indigo-500 focus:ring-indigo-500/15',
        ]) }}>
    @if ($error)
        <p class="mt-1.5 text-sm text-red-600">{{ $error }}</p>
    @endif
</div>
