@extends('layouts.admin', ['title' => 'Dashboard'])

@section('content')
@php
    $firstName = strtok(auth()->user()->name ?? 'there', ' ');

    // Stat cards: label, value, link, [from,to] gradient, icon path.
    $cards = [
        ['Total orders', number_format($stats['orders']), route('admin.orders.index'),
            ['#3b82f6', '#2563eb'], 'M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z'],
        ['Revenue', '₹' . number_format($stats['revenue']), route('admin.transactions.index'),
            ['#10b981', '#059669'], 'M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        ['Products', number_format($stats['products']), route('admin.products.index'),
            ['#6366f1', '#7c3aed'], 'm21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9'],
        ['Customers', number_format($stats['customers']), route('admin.customers.index'),
            ['#a855f7', '#9333ea'], 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z'],
    ];

    // Area-line chart geometry (looks clean even when every day is zero).
    $chartW = 680; $chartH = 210;
    $padL = 12; $padR = 12; $padT = 20; $padB = 28;
    $plotW = $chartW - $padL - $padR;
    $plotH = $chartH - $padT - $padB;
    $vals = $days->pluck('count')->all();
    $maxV = max(1, max($vals));
    $cnt = count($vals);
    $stepX = $cnt > 1 ? $plotW / ($cnt - 1) : 0;
    $pts = [];
    foreach ($days->values() as $i => $d) {
        $pts[] = [
            'x' => round($padL + $i * $stepX, 1),
            'y' => round($padT + $plotH - ($d['count'] / $maxV) * $plotH, 1),
            'label' => $d['label'],
            'count' => $d['count'],
        ];
    }
    $baseY = $padT + $plotH;
    $linePts = implode(' ', array_map(fn ($p) => $p['x'] . ',' . $p['y'], $pts));
    $areaPts = $pts[0]['x'] . ',' . $baseY . ' ' . $linePts . ' ' . end($pts)['x'] . ',' . $baseY;
    $weekTotal = array_sum($vals);
@endphp

{{-- Greeting --}}
<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div>
        <h2 class="text-xl font-bold text-gray-900 sm:text-2xl">Welcome back, {{ $firstName }} 👋</h2>
        <p class="mt-1 text-sm text-gray-500">Here's what's happening with your store · {{ now()->format('l, d M Y') }}</p>
    </div>
    <a href="{{ route('admin.products.create') }}"
       class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-indigo-500/30 transition hover:-translate-y-0.5 hover:bg-indigo-500">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Add new product
    </a>
</div>

{{-- Stat cards --}}
<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
    @foreach ($cards as [$label, $value, $route, [$c1, $c2], $icon])
        <a href="{{ $route }}" class="group relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 transition hover:-translate-y-1 hover:shadow-lg hover:shadow-gray-300/50">
            <div class="absolute right-0 top-0 h-24 w-24 -translate-y-8 translate-x-8 rounded-full opacity-10 transition group-hover:opacity-20" style="background-image: linear-gradient(135deg, {{ $c1 }}, {{ $c2 }})"></div>
            <div class="relative flex items-center justify-between">
                <p class="text-sm font-medium text-gray-500">{{ $label }}</p>
                <span class="flex h-10 w-10 items-center justify-center rounded-xl text-white shadow-md" style="background-image: linear-gradient(135deg, {{ $c1 }}, {{ $c2 }})">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                </span>
            </div>
            <p class="relative mt-3 text-3xl font-black tracking-tight text-gray-900">{{ $value }}</p>
            <p class="relative mt-2 inline-flex items-center gap-1 text-xs font-semibold text-gray-400 transition group-hover:text-indigo-600">
                View details
                <svg class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2.4" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
            </p>
        </a>
    @endforeach
</div>

<div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
    {{-- Orders trend (area-line chart) --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 lg:col-span-2">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-gray-900">Orders overview</h2>
                <p class="text-xs text-gray-500">Last 7 days</p>
            </div>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                <span class="h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                {{ $weekTotal }} this week
            </span>
        </div>
        <svg viewBox="0 0 {{ $chartW }} {{ $chartH }}" class="mt-4 w-full" style="height: 230px" preserveAspectRatio="xMidYMid meet">
            <defs>
                <linearGradient id="area-grad" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0" stop-color="#6366f1" stop-opacity="0.22"/>
                    <stop offset="1" stop-color="#6366f1" stop-opacity="0"/>
                </linearGradient>
            </defs>
            @for ($k = 0; $k <= 3; $k++)
                @php $gy = round($padT + $plotH * $k / 3, 1); @endphp
                <line x1="{{ $padL }}" y1="{{ $gy }}" x2="{{ $chartW - $padR }}" y2="{{ $gy }}" stroke="#eef0f4" stroke-width="1" vector-effect="non-scaling-stroke"/>
            @endfor
            <polygon points="{{ $areaPts }}" fill="url(#area-grad)"/>
            <polyline points="{{ $linePts }}" fill="none" stroke="#6366f1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke"/>
            @foreach ($pts as $p)
                <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="4" fill="#ffffff" stroke="#6366f1" stroke-width="2.5" vector-effect="non-scaling-stroke"/>
                @if ($p['count'] > 0)
                    <text x="{{ $p['x'] }}" y="{{ $p['y'] - 10 }}" text-anchor="middle" font-size="12" font-weight="700" fill="#4338ca" font-family="system-ui, sans-serif">{{ $p['count'] }}</text>
                @endif
                <text x="{{ $p['x'] }}" y="{{ $chartH - 8 }}" text-anchor="middle" font-size="12" fill="#9ca3af" font-family="system-ui, sans-serif">{{ $p['label'] }}</text>
            @endforeach
            @if ($weekTotal === 0)
                <text x="{{ $chartW / 2 }}" y="{{ round($padT + $plotH / 2, 1) }}" text-anchor="middle" font-size="13" fill="#b6bcc6" font-family="system-ui, sans-serif">No orders in the last 7 days yet</text>
            @endif
        </svg>
    </div>

    {{-- Side cards --}}
    <div class="space-y-4">
        <a href="{{ route('admin.messages.index') }}" class="flex items-center justify-between rounded-2xl border border-gray-200 bg-white p-5 transition hover:-translate-y-0.5 hover:shadow-md">
            <div>
                <p class="text-sm text-gray-500">Unread messages</p>
                <p class="mt-1 text-2xl font-black text-gray-900">{{ $stats['unread_messages'] }}</p>
            </div>
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 text-white shadow-md">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
            </span>
        </a>
        <a href="{{ route('admin.categories.index') }}" class="flex items-center justify-between rounded-2xl border border-gray-200 bg-white p-5 transition hover:-translate-y-0.5 hover:shadow-md">
            <div>
                <p class="text-sm text-gray-500">Categories</p>
                <p class="mt-1 text-2xl font-black text-gray-900">{{ $stats['categories'] }}</p>
            </div>
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-md">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 7.125C2.25 6.504 2.754 6 3.375 6h6c.621 0 1.125.504 1.125 1.125v3.75c0 .621-.504 1.125-1.125 1.125h-6a1.125 1.125 0 0 1-1.125-1.125v-3.75ZM14.25 8.625c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v8.25c0 .621-.504 1.125-1.125 1.125h-5.25a1.125 1.125 0 0 1-1.125-1.125v-8.25ZM3.75 16.125c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v2.25c0 .621-.504 1.125-1.125 1.125h-5.25a1.125 1.125 0 0 1-1.125-1.125v-2.25Z"/></svg>
            </span>
        </a>
        <div class="rounded-2xl border border-gray-200 bg-white p-5">
            <p class="text-sm font-semibold text-gray-900">Quick actions</p>
            <div class="mt-3 space-y-1">
                @foreach ([
                    ['admin.products.create', 'Add product', 'M12 4.5v15m7.5-7.5h-15'],
                    ['admin.categories.create', 'Add category', 'M12 4.5v15m7.5-7.5h-15'],
                    ['admin.settings.edit', 'Store settings', 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.43.992a7.03 7.03 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z'],
                ] as [$r, $label, $icon])
                    <a href="{{ route($r) }}" class="flex items-center gap-3 rounded-lg px-2.5 py-2 text-sm font-medium text-gray-700 transition hover:bg-indigo-50 hover:text-indigo-700">
                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                        {{ $label }}
                        <svg class="ml-auto h-4 w-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
    {{-- Recent orders --}}
    <div class="rounded-2xl border border-gray-200 bg-white">
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
            <h2 class="font-semibold text-gray-900">Recent orders</h2>
            <a href="{{ route('admin.orders.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">View all</a>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse ($recentOrders as $order)
                <a href="{{ route('admin.orders.show', $order) }}" class="flex items-center justify-between px-6 py-3 transition hover:bg-gray-50">
                    <div>
                        <p class="font-medium text-gray-900">#{{ $order->order_number }}</p>
                        <p class="text-xs text-gray-500">{{ $order->user->name ?? 'Guest' }} · {{ $order->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-gray-900">₹{{ number_format($order->total) }}</p>
                        <span class="inline-block rounded-full px-2 py-0.5 text-xs font-medium ring-1 {{ $order->statusClasses() }}">{{ $order->statusLabel() }}</span>
                    </div>
                </a>
            @empty
                <div class="flex flex-col items-center px-6 py-10 text-center">
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z"/></svg>
                    </span>
                    <p class="mt-3 text-sm font-medium text-gray-500">No orders yet</p>
                    <p class="text-xs text-gray-400">Orders will appear here once customers start buying.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Recent messages --}}
    <div class="rounded-2xl border border-gray-200 bg-white">
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
            <h2 class="font-semibold text-gray-900">Recent messages</h2>
            <a href="{{ route('admin.messages.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">View all</a>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse ($recentMessages as $msg)
                <a href="{{ route('admin.messages.show', $msg) }}" class="block px-6 py-3 transition hover:bg-gray-50">
                    <div class="flex items-center justify-between">
                        <p class="font-medium text-gray-900">{{ $msg->name }}</p>
                        @unless ($msg->read_at)<span class="h-2 w-2 rounded-full bg-indigo-500"></span>@endunless
                    </div>
                    <p class="truncate text-xs text-gray-500">{{ $msg->message }}</p>
                </a>
            @empty
                <div class="flex flex-col items-center px-6 py-10 text-center">
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z"/></svg>
                    </span>
                    <p class="mt-3 text-sm font-medium text-gray-500">No messages yet</p>
                    <p class="text-xs text-gray-400">Customer enquiries from the contact form show up here.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
