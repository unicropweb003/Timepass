<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /** Add/remove a product from the logged-in user's wishlist (AJAX). */
    public function toggle(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        $user = $request->user();
        $existing = $user->wishlists()->where('product_id', $data['product_id'])->first();

        if ($existing) {
            $existing->delete();
            $wished = false;
        } else {
            $user->wishlists()->create(['product_id' => $data['product_id']]);
            $wished = true;
        }

        return response()->json([
            'wished' => $wished,
            'count' => $user->wishlists()->count(),
        ]);
    }

    /** The customer's "My Wishlist" page. */
    public function index(Request $request)
    {
        $ids = $request->user()->wishlists()->latest()->pluck('product_id')->all();
        $categories = collect(config('shop.categories', []))->keyBy('slug');

        $products = Product::whereIn('id', $ids)->where('is_active', true)->get()
            ->sortBy(fn ($p) => array_search($p->id, $ids))
            ->map(fn ($p) => [
                'id' => $p->id,
                'slug' => $p->slug,
                'name' => $p->name,
                'category' => $categories[$p->category]['name'] ?? $p->category,
                'category_slug' => $p->category,
                'type' => $categories[$p->category]['types'][$p->type] ?? $p->type,
                'price' => (int) $p->price,
                'mrp' => $p->mrp !== null ? (int) $p->mrp : null,
                'rating' => (float) $p->rating,
                'reviews' => (int) $p->reviews,
                'image' => $p->image,
                'url' => route('product.show', $p->slug),
            ])->values();

        return view('account.wishlist', compact('products'));
    }
}
