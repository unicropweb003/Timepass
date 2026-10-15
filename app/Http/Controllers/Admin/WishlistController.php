<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;

class WishlistController extends Controller
{
    public function index()
    {
        // Products ranked by how many customers wishlisted them.
        $rows = Wishlist::query()
            ->selectRaw('product_id, COUNT(*) as wish_count')
            ->groupBy('product_id')
            ->orderByDesc('wish_count')
            ->with('product')
            ->get()
            ->filter(fn ($r) => $r->product !== null)
            ->values();

        $totalEntries = Wishlist::count();
        $uniqueCustomers = Wishlist::distinct('user_id')->count('user_id');

        return view('admin.wishlists.index', compact('rows', 'totalEntries', 'uniqueCustomers'));
    }
}
