<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Support\PerPage;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = Transaction::with('user:id,name', 'order:id,order_number')
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('q'), fn ($q, $term) => $q->where('reference', 'like', "%{$term}%"))
            ->latest()
            ->paginate(PerPage::from($request))->withQueryString();

        return view('admin.transactions.index', compact('transactions'));
    }
}
