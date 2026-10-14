@extends('layouts.account', ['title' => 'Overview'])

@section('account')
<h1 class="text-2xl font-bold text-gray-900">Hello, {{ strtok(auth()->user()->name, ' ') }} 👋</h1>
<p class="mt-1 text-gray-500">Here's a quick look at your orders, payments and delivery details.</p>

{{-- Stats --}}
<div class="mt-6 grid gap-4 sm:grid-cols-3">
    @foreach ([
        ['Total orders', $orderCount, 'account.orders'],
        ['Active orders', $activeCount, 'account.orders'],
        ['Total spent', '₹' . number_format($totalSpent, 2), 'account.transactions'],
    ] as [$label, $value, $route])
        <a href="{{ route($route) }}" class="rounded-2xl bg-white p-5 ring-1 ring-gray-200 transition hover:ring-indigo-200">
            <p class="text-sm text-gray-500">{{ $label }}</p>
            <p class="mt-1 text-2xl font-bold text-gray-900">{{ $value }}</p>
        </a>
    @endforeach
</div>

<div class="mt-8 grid gap-8 xl:grid-cols-[1fr_20rem]">
    {{-- Recent orders --}}
    <div>
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Recent orders</h2>
            @if ($orderCount)
                <a href="{{ route('account.orders') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">View all &rarr;</a>
            @endif
        </div>
        <div class="mt-4 space-y-4">
            @forelse ($recentOrders as $order)
                @include('account.partials.order-card')
            @empty
                @include('account.partials.empty', [
                    'icon' => 'M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z',
                    'title' => 'No orders yet',
                    'text' => 'When you place an order, you can track its delivery here.',
                    'action' => ['Start shopping', route('products')],
                ])
            @endforelse
        </div>
    </div>

    {{-- Delivery address + quick links --}}
    <div class="space-y-4">
        <div class="rounded-2xl bg-white p-5 ring-1 ring-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-gray-900">Delivery address</h2>
                <a href="{{ route('account.addresses') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">{{ $defaultAddress ? 'Manage' : 'Add' }}</a>
            </div>
            @if ($defaultAddress)
                <p class="mt-3 text-sm font-medium text-gray-900">{{ $defaultAddress->full_name }} <span class="ml-1 rounded bg-gray-100 px-1.5 py-0.5 text-[11px] font-semibold text-gray-600">{{ $defaultAddress->label }}</span></p>
                <p class="mt-1 text-sm text-gray-600">{{ $defaultAddress->oneLine() }}</p>
                <p class="mt-1 text-sm text-gray-500">Phone: {{ $defaultAddress->phone }}</p>
            @else
                <p class="mt-3 text-sm text-gray-500">No delivery address saved yet.</p>
            @endif
        </div>

        <div class="rounded-2xl bg-white p-5 ring-1 ring-gray-200">
            <h2 class="font-semibold text-gray-900">Account details</h2>
            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Name</dt><dd class="truncate font-medium text-gray-900">{{ auth()->user()->name }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Email</dt><dd class="truncate font-medium text-gray-900">{{ auth()->user()->email }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Phone</dt><dd class="font-medium text-gray-900">{{ auth()->user()->phone }}</dd></div>
            </dl>
            <a href="{{ route('account.profile') }}" class="mt-4 inline-flex text-sm font-semibold text-indigo-600 hover:text-indigo-700">Edit profile &rarr;</a>
        </div>
    </div>
</div>
@endsection
