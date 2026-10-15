@extends('layouts.admin', ['title' => 'Transactions'])

@section('content')
<form method="GET" class="mb-5 flex flex-wrap gap-2">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Reference…"
        class="w-full max-w-xs rounded-lg border border-gray-300 px-3.5 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/40">
    <select name="status" onchange="this.form.submit()" class="rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500">
        <option value="">All</option>
        @foreach (['success', 'pending', 'failed', 'refunded'] as $s)<option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>@endforeach
    </select>
    <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">Filter</button>
</form>

<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-6 py-3">Reference</th>
                    <th class="px-6 py-3">Customer</th>
                    <th class="px-6 py-3">Order</th>
                    <th class="px-6 py-3">Type</th>
                    <th class="px-6 py-3">Amount</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($transactions as $t)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 font-medium text-gray-900">{{ $t->reference }}</td>
                        <td class="px-6 py-3 text-gray-600">{{ $t->user->name ?? '—' }}</td>
                        <td class="px-6 py-3 text-gray-600">
                            @if ($t->order)<a href="{{ route('admin.orders.show', $t->order) }}" class="text-indigo-600 hover:underline">#{{ $t->order->order_number }}</a>@else — @endif
                        </td>
                        <td class="px-6 py-3 text-gray-600">{{ ucfirst($t->type) }}</td>
                        <td class="px-6 py-3 font-semibold text-gray-900">₹{{ number_format($t->amount) }}</td>
                        <td class="px-6 py-3">
                            @php $sc = match($t->status) { 'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-200', 'failed' => 'bg-red-50 text-red-700 ring-red-200', 'refunded' => 'bg-purple-50 text-purple-700 ring-purple-200', default => 'bg-amber-50 text-amber-700 ring-amber-200' }; @endphp
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium ring-1 {{ $sc }}">{{ ucfirst($t->status) }}</span>
                        </td>
                        <td class="px-6 py-3 text-gray-500">{{ $t->created_at->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-10 text-center text-gray-500">No transactions found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $transactions->links() }}</div>
@endsection
