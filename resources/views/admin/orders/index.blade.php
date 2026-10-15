@extends('layouts.admin', ['title' => 'Orders'])

@section('content')
<form method="GET" class="mb-5 flex flex-wrap gap-2">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Order # or customer…"
        class="w-full max-w-xs rounded-lg border border-gray-300 px-3.5 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/40">
    <select name="status" onchange="this.form.submit()" class="rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500">
        <option value="">All statuses</option>
        @foreach ($statuses as $s)<option value="{{ $s }}" @selected(request('status') === $s)>{{ ucwords(str_replace('_', ' ', $s)) }}</option>@endforeach
        <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
    </select>
    <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">Filter</button>
</form>

<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-6 py-3">Order</th>
                    <th class="px-6 py-3">Customer</th>
                    <th class="px-6 py-3">Date</th>
                    <th class="px-6 py-3">Total</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3 text-right">View</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($orders as $order)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 font-medium text-gray-900">#{{ $order->order_number }}</td>
                        <td class="px-6 py-3 text-gray-600">{{ $order->user->name ?? 'Guest' }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $order->created_at->format('d M Y') }}</td>
                        <td class="px-6 py-3 font-semibold text-gray-900">₹{{ number_format($order->total) }}</td>
                        <td class="px-6 py-3"><span class="rounded-full px-2 py-0.5 text-xs font-medium ring-1 {{ $order->statusClasses() }}">{{ $order->statusLabel() }}</span></td>
                        <td class="px-6 py-3 text-right">
                            <a href="{{ route('admin.orders.show', $order) }}" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">Details</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-10 text-center text-gray-500">No orders found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $orders->links() }}</div>
@endsection
