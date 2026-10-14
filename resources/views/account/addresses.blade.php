@extends('layouts.account', ['title' => 'Delivery Addresses'])

@section('account')
<h1 class="text-2xl font-bold text-gray-900">Delivery Addresses</h1>
<p class="mt-1 text-gray-500">Where we deliver your orders. The default address is used first at checkout.</p>

<div class="mt-6 grid gap-8 xl:grid-cols-[1fr_24rem]">
    {{-- Saved addresses --}}
    <div class="space-y-4">
        @forelse ($addresses as $address)
            <div class="rounded-2xl bg-white p-5 ring-1 {{ $address->is_default ? 'ring-indigo-300' : 'ring-gray-200' }}">
                <div class="flex flex-wrap items-center gap-2">
                    <p class="font-semibold text-gray-900">{{ $address->full_name }}</p>
                    <span class="rounded bg-gray-100 px-1.5 py-0.5 text-[11px] font-semibold text-gray-600">{{ $address->label }}</span>
                    @if ($address->is_default)
                        <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-[11px] font-semibold text-indigo-700 ring-1 ring-indigo-200">Default</span>
                    @endif
                </div>
                <p class="mt-2 text-sm text-gray-600">{{ $address->oneLine() }}</p>
                <p class="mt-1 text-sm text-gray-500">Phone: {{ $address->phone }}</p>
                <div class="mt-4 flex gap-4 border-t border-gray-100 pt-3 text-sm font-semibold">
                    @unless ($address->is_default)
                        <form method="POST" action="{{ route('account.addresses.default', $address) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="text-indigo-600 hover:text-indigo-700">Set as default</button>
                        </form>
                    @endunless
                    <form method="POST" action="{{ route('account.addresses.destroy', $address) }}" onsubmit="return confirm('Remove this address?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-700">Remove</button>
                    </form>
                </div>
            </div>
        @empty
            @include('account.partials.empty', [
                'icon' => 'M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z',
                'title' => 'No addresses yet',
                'text' => 'Add a delivery address using the form.',
            ])
        @endforelse
    </div>

    {{-- Add address --}}
    <form method="POST" action="{{ route('account.addresses.store') }}" class="h-fit space-y-4 rounded-2xl bg-white p-6 ring-1 ring-gray-200">
        @csrf
        <h2 class="font-semibold text-gray-900">Add a new address</h2>
        <div class="grid gap-4 sm:grid-cols-2">
            <x-account.field name="full_name" label="Full name" :value="auth()->user()->name" autocomplete="name" />
            <x-account.field name="phone" label="Phone number" type="tel" :value="auth()->user()->phone" inputmode="numeric" maxlength="15" />
        </div>
        <x-account.field name="line1" label="House no., building, street" autocomplete="address-line1" />
        <x-account.field name="line2" label="Area, landmark (optional)" autocomplete="address-line2" />
        <div class="grid gap-4 sm:grid-cols-3">
            <x-account.field name="city" label="City" autocomplete="address-level2" />
            <x-account.field name="state" label="State" autocomplete="address-level1" />
            <x-account.field name="pincode" label="Pincode" inputmode="numeric" maxlength="6" autocomplete="postal-code" />
        </div>
        <div>
            <span class="mb-1.5 block text-sm font-medium text-gray-700">Address type</span>
            <div class="flex gap-2">
                @foreach (['Home', 'Work', 'Other'] as $label)
                    <label class="cursor-pointer">
                        <input type="radio" name="label" value="{{ $label }}" class="peer sr-only" @checked(old('label', 'Home') === $label)>
                        <span class="block rounded-full px-4 py-1.5 text-sm font-medium ring-1 ring-gray-200 transition peer-checked:bg-indigo-50 peer-checked:text-indigo-700 peer-checked:ring-indigo-300 peer-focus-visible:ring-2">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>
        <label class="flex w-fit cursor-pointer items-center gap-2.5 text-sm text-gray-600">
            <input type="checkbox" name="is_default" value="1" class="h-4 w-4 rounded accent-indigo-600" @checked(old('is_default'))>
            Make this my default address
        </label>
        <button type="submit" class="flex h-11 w-full items-center justify-center rounded-xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500 font-semibold text-white shadow-lg shadow-purple-500/25 transition hover:shadow-xl">Save address</button>
    </form>
</div>
@endsection
