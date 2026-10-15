<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = User::where('is_admin', false)
            ->withCount('orders')
            ->when($request->query('q'), function ($q, $term) {
                $q->where(fn ($w) => $w->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%"));
            })
            ->latest()
            ->paginate(15)->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function show(User $customer)
    {
        abort_if($customer->is_admin, 404);
        $customer->load(['orders' => fn ($q) => $q->latest(), 'addresses']);

        return view('admin.customers.show', compact('customer'));
    }

    public function toggleBlock(User $customer)
    {
        abort_if($customer->is_admin, 403);
        $customer->is_blocked = ! $customer->is_blocked;
        $customer->save();

        return back()->with('status', $customer->is_blocked ? 'Customer blocked.' : 'Customer unblocked.');
    }
}
