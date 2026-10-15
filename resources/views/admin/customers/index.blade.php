@extends('layouts.admin', ['title' => 'Customers'])

@section('content')
<form method="GET" class="mb-5 flex gap-2">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Name, email or phone…"
        class="w-full max-w-sm rounded-lg border border-gray-300 px-3.5 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/40">
    <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">Search</button>
</form>

<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-6 py-3">Customer</th>
                    <th class="px-6 py-3">Phone</th>
                    <th class="px-6 py-3">Orders</th>
                    <th class="px-6 py-3">Joined</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($customers as $c)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3">
                            <p class="font-medium text-gray-900">{{ $c->name }}</p>
                            <p class="text-xs text-gray-500">{{ $c->email }}</p>
                        </td>
                        <td class="px-6 py-3 text-gray-600">{{ $c->phone ?: '—' }}</td>
                        <td class="px-6 py-3 text-gray-600">{{ $c->orders_count }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $c->created_at->format('d M Y') }}</td>
                        <td class="px-6 py-3">
                            @if ($c->is_blocked)
                                <span class="rounded-full bg-red-50 px-2 py-0.5 text-xs font-medium text-red-700 ring-1 ring-red-200">Blocked</span>
                            @else
                                <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-emerald-200">Active</span>
                            @endif
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.customers.show', $c) }}" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">View</a>
                                <form method="POST" action="{{ route('admin.customers.block', $c) }}" onsubmit="return confirm('{{ $c->is_blocked ? 'Unblock' : 'Block' }} this customer?')">
                                    @csrf @method('PATCH')
                                    <button class="rounded-lg border px-3 py-1.5 text-xs font-medium {{ $c->is_blocked ? 'border-emerald-200 text-emerald-700 hover:bg-emerald-50' : 'border-red-200 text-red-600 hover:bg-red-50' }}">
                                        {{ $c->is_blocked ? 'Unblock' : 'Block' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-10 text-center text-gray-500">No customers found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $customers->links() }}</div>
@endsection
