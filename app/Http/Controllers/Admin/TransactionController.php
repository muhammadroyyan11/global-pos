<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['user', 'customer']);

        if ($request->search) {
            $query->where('invoice_number', 'like', '%' . $request->search . '%');
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $transactions = $query->latest()->paginate(15)->withQueryString();
        $totalRevenue = $query->where('status', 'completed')->sum('total');

        return view('admin.transactions.index', compact('transactions', 'totalRevenue'));
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['user', 'customer', 'items.product']);
        return view('admin.transactions.show', compact('transaction'));
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->update(['status' => 'cancelled']);
        return redirect()->route('admin.transactions.index')->with('success', 'Transaksi dibatalkan.');
    }
}
