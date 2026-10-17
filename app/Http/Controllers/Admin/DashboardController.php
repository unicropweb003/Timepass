<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    // The dashboard's ~11 queries, cached as one entry. Cleared by
    // App\Observers\AdminDashboardObserver whenever any record it shows changes.
    public const CACHE_KEY = 'admin:dashboard';

    public const CACHE_TTL = 300; // seconds

    public function index()
    {
        return view('admin.dashboard', Cache::remember(self::CACHE_KEY, self::CACHE_TTL, fn () => $this->build()));
    }

    private function build(): array
    {
        $revenue = Transaction::where('status', 'success')
            ->whereIn('type', ['payment', 'charge', 'capture'])
            ->sum('amount');

        // Fallback: if transaction types differ, use delivered/paid order totals.
        if ((float) $revenue === 0.0) {
            $revenue = Order::whereNotIn('status', ['cancelled'])->sum('total');
        }

        $stats = [
            'orders' => Order::count(),
            'revenue' => (float) $revenue,
            'products' => Product::count(),
            'customers' => User::where('is_admin', false)->count(),
            'unread_messages' => ContactMessage::whereNull('read_at')->count(),
            'categories' => Category::count(),
        ];

        $recentOrders = Order::with('user:id,name')->latest()->take(8)->get();
        $recentMessages = ContactMessage::latest()->take(5)->get();

        // Simple 7-day order trend for a mini chart.
        $trend = Order::selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('d')->pluck('c', 'd');

        $days = collect(range(6, 0))->map(function ($i) use ($trend) {
            $date = now()->subDays($i)->toDateString();

            return ['label' => now()->subDays($i)->format('D'), 'count' => (int) ($trend[$date] ?? 0)];
        });

        return compact('stats', 'recentOrders', 'recentMessages', 'days');
    }
}
