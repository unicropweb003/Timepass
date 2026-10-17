@extends('layouts.admin', ['title' => 'Contact Lead'])

@section('content')
@php $wa = $message->phoneDigits(); @endphp
<a href="{{ route('admin.messages.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-800">
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
    Back to contact leads
</a>

<div class="grid max-w-5xl gap-6 lg:grid-cols-3">
    {{-- Message --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 lg:col-span-2">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Subject</p>
                <h2 class="mt-0.5 text-lg font-semibold text-gray-900">{{ $message->subject ?: 'General enquiry' }}</h2>
            </div>
            <span class="text-xs text-gray-400">{{ $message->created_at->format('d M Y, g:i A') }}</span>
        </div>

        <div class="mt-5 whitespace-pre-line break-words rounded-xl bg-gray-50 p-5 leading-relaxed text-gray-700">{{ $message->message }}</div>

        <div class="mt-6 flex flex-wrap gap-3">
            <a href="mailto:{{ $message->email }}?subject={{ rawurlencode('Re: '.($message->subject ?: 'Your message to TimePass')) }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                Reply by email
            </a>
            @if ($wa)
                <a href="tel:+{{ $wa }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Call</a>
                <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-50">WhatsApp</a>
            @endif
            <form method="POST" action="{{ route('admin.messages.destroy', $message) }}" onsubmit="return confirm('Delete this lead?')" class="ml-auto">
                @csrf @method('DELETE')
                <button class="rounded-lg border border-red-200 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50">Delete</button>
            </form>
        </div>
    </div>

    {{-- Lead details --}}
    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-6">
            <div class="flex items-center gap-3">
                <span class="flex h-11 w-11 items-center justify-center rounded-full bg-indigo-100 font-semibold text-indigo-700">{{ strtoupper(substr($message->name, 0, 1)) }}</span>
                <div class="min-w-0">
                    <p class="font-semibold text-gray-900">{{ $message->name }}</p>
                    <p class="text-xs text-gray-500">{{ $message->user ? 'Registered customer' : 'Website visitor' }}</p>
                </div>
            </div>
            <dl class="mt-5 space-y-3 text-sm">
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Email</dt>
                    <dd class="mt-0.5 break-all"><a href="mailto:{{ $message->email }}" class="text-indigo-600 hover:underline">{{ $message->email }}</a></dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Phone</dt>
                    <dd class="mt-0.5 text-gray-900">
                        @if ($wa)<a href="tel:+{{ $wa }}" class="text-indigo-600 hover:underline">{{ $message->phone }}</a>@else — @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Received</dt>
                    <dd class="mt-0.5 text-gray-900">{{ $message->created_at->format('d M Y, g:i A') }} <span class="text-gray-400">({{ $message->created_at->diffForHumans() }})</span></dd>
                </div>
                @if ($message->user)
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Customer account</dt>
                        <dd class="mt-0.5"><a href="{{ route('admin.customers.show', $message->user) }}" class="text-indigo-600 hover:underline">{{ $message->user->name }}</a></dd>
                    </div>
                @endif
                @if ($message->ip_address)
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">IP address</dt>
                        <dd class="mt-0.5 text-gray-600">{{ $message->ip_address }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        <form method="POST" action="{{ route('admin.messages.status', $message) }}" class="rounded-2xl border border-gray-200 bg-white p-6">
            @csrf @method('PATCH')
            <label for="status" class="text-xs font-medium uppercase tracking-wide text-gray-500">Follow-up status</label>
            <div class="mt-2 flex gap-2">
                <select id="status" name="status" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/40">
                    @foreach (\App\Models\ContactMessage::STATUSES as $key => $label)
                        <option value="{{ $key }}" @selected($message->status === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">Save</button>
            </div>
            <p class="mt-2 text-xs text-gray-400">Mark the lead as Contacted after you reach out, and Closed when it is resolved.</p>
        </form>
    </div>
</div>
@endsection
