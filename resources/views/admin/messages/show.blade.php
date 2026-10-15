@extends('layouts.admin', ['title' => 'Message'])

@section('content')
<a href="{{ route('admin.messages.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-800">
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
    Back to messages
</a>

<div class="max-w-2xl rounded-2xl border border-gray-200 bg-white p-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ $message->name }}</h2>
            <p class="text-sm text-gray-500">{{ $message->email }}</p>
        </div>
        <span class="text-xs text-gray-400">{{ $message->created_at->format('d M Y, g:i A') }}</span>
    </div>

    <div class="mt-5 rounded-xl bg-gray-50 p-5 text-gray-700">
        {!! nl2br(e($message->message)) !!}
    </div>

    <div class="mt-6 flex gap-3">
        <a href="mailto:{{ $message->email }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Reply by email</a>
        <form method="POST" action="{{ route('admin.messages.destroy', $message) }}" onsubmit="return confirm('Delete this message?')">
            @csrf @method('DELETE')
            <button class="rounded-lg border border-red-200 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50">Delete</button>
        </form>
    </div>
</div>
@endsection
