@extends('layouts.account', ['title' => 'My Orders'])

@section('account')
<h1 class="text-2xl font-bold text-gray-900">My Orders</h1>
<p class="mt-1 text-gray-500">Track your orders and see their delivery status.</p>

<div class="mt-6 space-y-4">
    @forelse ($orders as $order)
        @include('account.partials.order-card')
    @empty
        @include('account.partials.empty', [
            'icon' => 'M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z',
            'title' => 'No orders yet',
            'text' => 'Your orders and their delivery details will show up here.',
            'action' => ['Start shopping', route('products')],
        ])
    @endforelse
</div>

<div class="mt-6">{{ $orders->links() }}</div>
@endsection
