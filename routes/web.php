<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin;
use App\Http\Controllers\POS;

Route::get('/', function () {
    return redirect()->route('pos.cashier');
});

require __DIR__ . '/auth.php';

// POS Cashier (semua user yang login)
Route::middleware(['auth'])->prefix('pos')->name('pos.')->group(function () {
    Route::get('/', [POS\CashierController::class, 'index'])->name('cashier');
    Route::get('/all', [POS\CashierController::class, 'getAll'])->name('all');
    Route::get('/products', [POS\CashierController::class, 'getProducts'])->name('products');
    Route::get('/all', [POS\CashierController::class, 'getAll'])->name('all');
    Route::get('/bundles', [POS\CashierController::class, 'getBundles'])->name('bundles');
    Route::get('/popular', [POS\CashierController::class, 'getPopular'])->name('popular');
    Route::post('/checkout', [POS\CashierController::class, 'checkout'])->name('checkout');
    Route::get('/receipt/{transaction}', [POS\CashierController::class, 'receipt'])->name('receipt');
});

// Admin Panel
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::resource('categories', Admin\CategoryController::class)->except(['show']);
    Route::resource('products', Admin\ProductController::class)->except(['show']);
    Route::resource('customers', Admin\CustomerController::class)->except(['show']);
    Route::resource('users', Admin\UserController::class)->except(['show']);
    Route::resource('bundles', Admin\BundleController::class)->except(['show']);

    Route::get('transactions', [Admin\TransactionController::class, 'index'])->name('transactions.index');
    Route::get('transactions/{transaction}', [Admin\TransactionController::class, 'show'])->name('transactions.show');
    Route::delete('transactions/{transaction}', [Admin\TransactionController::class, 'destroy'])->name('transactions.destroy');

    Route::get('settings/store', [Admin\StoreSettingController::class, 'edit'])->name('settings.store.edit');
    Route::put('settings/store', [Admin\StoreSettingController::class, 'update'])->name('settings.store.update');
});
