@extends('layouts.admin', ['title' => 'Messages'])

@section('content')
<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
    <div class="divide-y divide-gray-100">
        @forelse ($messages as $msg)
            <a href="{{ route('admin.messages.show', $msg) }}" class="flex items-start gap-4 px-6 py-4 hover:bg-gray-50 {{ $msg->read_at ? '' : 'bg-indigo-50/40' }}">
                <span class="mt-1 flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-700">{{ strtoupper(substr($msg->name, 0, 1)) }}</span>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between">
                        <p class="font-medium text-gray-900">{{ $msg->name }} @unless($msg->read_at)<span class="ml-1 inline-block h-2 w-2 rounded-full bg-indigo-500 align-middle"></span>@endunless</p>
                        <span class="text-xs text-gray-400">{{ $msg->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-xs text-gray-500">{{ $msg->email }}</p>
                    <p class="mt-1 truncate text-sm text-gray-600">{{ $msg->message }}</p>
                </div>
            </a>
        @empty
            <p class="px-6 py-10 text-center text-gray-500">No messages yet.</p>
        @endforelse
    </div>
</div>

<div class="mt-4">{{ $messages->links() }}</div>
@endsection
