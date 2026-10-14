{{-- Text field for the login / register forms: label, leading icon, optional show/hide
     password button and the inline error element that public/js/auth.js fills in. --}}
@props(['name', 'label', 'icon', 'type' => 'text', 'toggle' => false, 'error' => true])

<div class="group/field">
    <label for="{{ $name }}" class="mb-1.5 block text-sm font-medium text-gray-700">{{ $label }}</label>
    <div class="group relative">
        <svg class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400 transition group-focus-within:text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" />
        </svg>
        <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}"
            {{ $attributes->class([
                'block h-12 w-full rounded-xl border border-gray-200 bg-gray-50/70 pl-11 text-sm text-gray-900 placeholder-gray-400 transition',
                'focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-500/15',
                'group-has-[.error:not(.hidden)]/field:border-red-400 group-has-[.error:not(.hidden)]/field:focus:ring-red-500/15',
                $toggle ? 'pr-12' : 'pr-4',
            ]) }}>
        @if ($toggle)
            <button type="button" data-toggle-password aria-label="Show password"
                class="absolute right-1.5 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600">
                <svg data-eye class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
                <svg data-eye-off class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                </svg>
            </button>
        @endif
    </div>
    @if ($error)
        <p class="error mt-1.5 hidden text-sm text-red-600" data-for="{{ $name }}"></p>
    @endif
</div>
