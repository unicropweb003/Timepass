<?php

namespace App\Observers;

use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Cache;

/**
 * Clears the cached admin dashboard when a record it shows is created, updated
 * or deleted (orders, transactions, products, categories, leads, customers).
 * Registered in AppServiceProvider.
 */
class AdminDashboardObserver
{
    public function saved(): void
    {
        $this->forget();
    }

    public function deleted(): void
    {
        $this->forget();
    }

    private function forget(): void
    {
        try {
            Cache::forget(DashboardController::CACHE_KEY);
        } catch (\Throwable $e) {
            // Cache unavailable: the entry expires on its own (TTL).
        }
    }
}
