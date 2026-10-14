@extends('layouts.account', ['title' => 'Transactions'])

@php
    $statusClasses = [
        'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'pending' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'failed' => 'bg-red-50 text-red-700 ring-red-200',
    ];
@endphp

@section('account')
<h1 class="text-2xl font-bold text-gray-900">Transactions</h1>
<p class="mt-1 text-gray-500">All your payments and refunds in one place.</p>

<div class="mt-6">
    @if ($transactions->isEmpty())
        @include('account.partials.empty', [
            'icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z',
            'title' => 'No transactions yet',
            'text' => 'Payments and refunds for your orders will be listed here.',
        ])
    @else
        <div class="overflow-hidden rounded-2xl bg-white ring-1 ring-gray-200">
            <table class="w-full text-left text-sm">
                <thead class="hidden bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500 sm:table-header-group">
                    <tr>
                        <th class="px-5 py-3">Date</th>
                        <th class="px-5 py-3">Reference</th>
                        <th class="px-5 py-3">Order</th>
                        <th class="px-5 py-3">Method</th>
                        <th class="px-5 py-3 text-right">Amount</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($transactions as $txn)
                        @php $isRefund = $txn->type === 'refund'; @endphp
                        <tr class="grid grid-cols-2 gap-1 px-5 py-4 sm:table-row sm:p-0">
                            <td class="text-gray-600 sm:px-5 sm:py-4">{{ $txn->created_at->format('d M Y') }}</td>
                            <td class="text-right font-mono text-xs text-gray-500 sm:px-5 sm:py-4 sm:text-left">{{ $txn->reference }}</td>
                            <td class="sm:px-5 sm:py-4">
                                @if ($txn->order)
                                    <a href="{{ route('account.orders.show', $txn->order->order_number) }}" class="font-medium text-indigo-600 hover:text-indigo-700">#{{ $txn->order->order_number }}</a>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="text-right text-gray-600 sm:px-5 sm:py-4 sm:text-left">{{ $txn->method }}{{ $isRefund ? ' (refund)' : '' }}</td>
                            <td class="font-semibold sm:px-5 sm:py-4 sm:text-right {{ $isRefund ? 'text-emerald-600' : 'text-gray-900' }}">{{ $isRefund ? '+' : '' }}₹{{ number_format($txn->amount, 2) }}</td>
                            <td class="text-right sm:px-5 sm:py-4 sm:text-left">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize ring-1 {{ $statusClasses[$txn->status] ?? 'bg-gray-50 text-gray-700 ring-gray-200' }}">{{ $txn->status }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $transactions->links() }}</div>
    @endif
</div>
@endsection
