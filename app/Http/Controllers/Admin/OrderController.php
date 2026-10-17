<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\PerPage;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with('user:id,name')
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            // Grouped, so a search never escapes the status filter (… AND (number OR customer)).
            ->when($request->query('q'), fn ($q, $term) => $q->where(fn ($w) => $w
                ->where('order_number', 'like', "%{$term}%")
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"))))
            ->latest()
            ->paginate(PerPage::from($request))->withQueryString();

        $statuses = array_keys(Order::STEPS);

        return view('admin.orders.index', compact('orders', 'statuses'));
    }

    public function show(Order $order)
    {
        $order->load('items', 'user', 'transactions');
        $steps = Order::STEPS;

        return view('admin.orders.show', compact('order', 'steps'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', [...array_keys(Order::STEPS), 'cancelled'])],
        ]);

        $order->status = $data['status'];

        // Stamp the matching timestamp column when a step is reached.
        if ($data['status'] === 'cancelled') {
            $order->cancelled_at = now();
        } elseif (isset(Order::STEPS[$data['status']][1])) {
            $column = Order::STEPS[$data['status']][1];
            if ($column !== 'created_at' && ! $order->{$column}) {
                $order->{$column} = now();
            }
        }

        $order->save();

        return back()->with('status', 'Order status updated to '.$order->statusLabel().'.');
    }
}
