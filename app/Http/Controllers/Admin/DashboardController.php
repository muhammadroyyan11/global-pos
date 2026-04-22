<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\Customer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        $todaySales = Transaction::whereDate('created_at', $today)
            ->where('status', 'completed')->sum('total');

        $monthlySales = Transaction::where('created_at', '>=', $thisMonth)
            ->where('status', 'completed')->sum('total');

        $todayTransactions = Transaction::whereDate('created_at', $today)
            ->where('status', 'completed')->count();

        $totalProducts = Product::where('is_active', true)->count();
        $lowStockProducts = Product::where('stock', '<=', 5)->where('is_active', true)->count();
        $totalCustomers = Customer::count();

        $recentTransactions = Transaction::with(['user', 'customer'])
            ->latest()->take(10)->get();

        $topProducts = \App\Models\TransactionItem::selectRaw('product_id, product_name, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue')
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_qty')
            ->take(5)->get();

        // Sales chart last 7 days
        $salesChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $salesChart[] = [
                'date' => $date->format('d M'),
                'total' => Transaction::whereDate('created_at', $date)
                    ->where('status', 'completed')->sum('total'),
            ];
        }

        return view('admin.dashboard', compact(
            'todaySales', 'monthlySales', 'todayTransactions',
            'totalProducts', 'lowStockProducts', 'totalCustomers',
            'recentTransactions', 'topProducts', 'salesChart'
        ));
    }
}
