<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Support\PerPage;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        // Products ranked by how many customers wishlisted them (product_id breaks
        // ties, so a product never moves between pages).
        $rows = Wishlist::query()
            ->selectRaw('product_id, COUNT(*) as wish_count')
            ->has('product')
            ->groupBy('product_id')
            ->orderByDesc('wish_count')
            ->orderBy('product_id')
            ->with('product')
            ->paginate(PerPage::from($request))
            ->withQueryString();

        // Bars are drawn relative to the most-wished product overall, not just this page.
        $maxCount = (int) Wishlist::query()->selectRaw('COUNT(*) as c')->groupBy('product_id')->orderByDesc('c')->value('c');

        $totalEntries = Wishlist::count();
        $uniqueCustomers = Wishlist::distinct('user_id')->count('user_id');

        return view('admin.wishlists.index', compact('rows', 'maxCount', 'totalEntries', 'uniqueCustomers'));
    }
}
