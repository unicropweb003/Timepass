@extends('layouts.admin', ['title' => 'Contact Leads'])

@section('content')
@php
    $statusStyles = [
        'new' => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
        'contacted' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'closed' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    ];
    $tabs = ['' => ['All', $counts->sum()]];
    foreach (\App\Models\ContactMessage::STATUSES as $key => $label) {
        $tabs[$key] = [$label, $counts[$key] ?? 0];
    }
@endphp

<p class="mb-5 text-sm text-gray-500">Everyone who fills in the Contact us form on the website appears here. Open a lead to see the full details and contact them.</p>

<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div class="flex flex-wrap gap-2">
        @foreach ($tabs as $key => [$label, $total])
            <a href="{{ route('admin.messages.index', array_filter(['status' => $key, 'q' => $q])) }}"
               class="rounded-full px-3.5 py-1.5 text-sm font-medium transition {{ (string) $status === (string) $key ? 'bg-gray-900 text-white' : 'bg-white text-gray-600 ring-1 ring-gray-200 hover:bg-gray-50' }}">
                {{ $label }} <span class="ml-1 opacity-70">{{ $total }}</span>
            </a>
        @endforeach
    </div>
    <form method="GET" class="flex gap-2">
        @if ($status)<input type="hidden" name="status" value="{{ $status }}">@endif
        <input type="text" name="q" value="{{ $q }}" placeholder="Name, email, phone or message…"
            class="w-64 rounded-lg border border-gray-300 px-3.5 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/40">
        <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">Search</button>
    </form>
</div>

<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-6 py-3">Lead</th>
                    <th class="px-6 py-3">Phone</th>
                    <th class="px-6 py-3">Subject / message</th>
                    <th class="px-6 py-3">Received</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($messages as $msg)
                    <tr class="hover:bg-gray-50 {{ $msg->read_at ? '' : 'bg-indigo-50/40' }}">
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-700">{{ strtoupper(substr($msg->name, 0, 1)) }}</span>
                                <div class="min-w-0">
                                    <p class="font-medium text-gray-900">
                                        {{ $msg->name }}
                                        @unless ($msg->read_at)<span class="ml-1 rounded bg-indigo-600 px-1.5 py-0.5 align-middle text-[10px] font-semibold uppercase text-white">Unread</span>@endunless
                                    </p>
                                    <p class="text-xs text-gray-500">{{ $msg->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-6 py-3 text-gray-600">{{ $msg->phone ?: '—' }}</td>
                        <td class="max-w-xs px-6 py-3">
                            @if ($msg->subject)<p class="text-xs font-semibold text-gray-700">{{ $msg->subject }}</p>@endif
                            <p class="truncate text-gray-600">{{ $msg->message }}</p>
                        </td>
                        <td class="whitespace-nowrap px-6 py-3 text-gray-500" title="{{ $msg->created_at->format('d M Y, g:i A') }}">{{ $msg->created_at->diffForHumans() }}</td>
                        <td class="px-6 py-3">
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium ring-1 {{ $statusStyles[$msg->status] ?? 'bg-gray-50 text-gray-700 ring-gray-200' }}">{{ $msg->statusLabel() }}</span>
                        </td>
                        <td class="px-6 py-3 text-right">
                            <a href="{{ route('admin.messages.show', $msg) }}" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <p class="font-medium text-gray-500">{{ $q !== '' || $status ? 'No leads match this filter.' : 'No leads yet.' }}</p>
                            <p class="mt-1 text-xs text-gray-400">When a visitor sends the Contact us form, their details appear here.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $messages->links() }}</div>
@endsection
