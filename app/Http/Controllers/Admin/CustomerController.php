<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\PerPage;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = User::where('is_admin', false)
            ->select(['id', 'name', 'email', 'phone', 'is_blocked', 'created_at'])
            ->withCount('orders')
            ->when($request->query('q'), function ($q, $term) {
                $q->where(fn ($w) => $w->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%"));
            })
            ->latest()
            ->paginate(PerPage::from($request))->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function show(Request $request, User $customer)
    {
        abort_if($customer->is_admin, 404);
        $customer->load('addresses');

        // Paginated instead of loading every order the customer ever placed.
        $orders = $customer->orders()->latest()->paginate(PerPage::from($request))->withQueryString();

        return view('admin.customers.show', compact('customer', 'orders'));
    }

    public function toggleBlock(User $customer)
    {
        abort_if($customer->is_admin, 403);
        $customer->is_blocked = ! $customer->is_blocked;
        $customer->save();

        return back()->with('status', $customer->is_blocked ? 'Customer blocked.' : 'Customer unblocked.');
    }
}
