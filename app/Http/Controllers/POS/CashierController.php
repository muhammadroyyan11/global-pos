<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Bundle;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashierController extends Controller
{
    public function index()
    {
        $categories = Category::withCount(['products' => fn($q) => $q->where('is_active', true)])->get();
        $customers  = Customer::orderBy('name')->get();
        return view('pos.cashier', compact('categories', 'customers'));
    }

    // ── Semua produk + bundle (default tab + search global) ──
    public function getAll(Request $request)
    {
        $search = $request->search;

        $productQuery = Product::where('is_active', true)->where('stock', '>', 0)->with('category');
        if ($search) {
            $productQuery->where(fn($q) =>
                $q->where('name', 'like', "%$search%")->orWhere('sku', 'like', "%$search%")
            );
        }
        $products = $productQuery->get()->map(fn($p) => $this->mapProduct($p));

        $bundleQuery = Bundle::where('is_active', true)->with('items.product');
        if ($search) {
            $bundleQuery->where('name', 'like', "%$search%");
        }
        $bundles = $bundleQuery->get()->map(fn($b) => $this->mapBundle($b));

        // Bundle tampil duluan, lalu produk
        return response()->json($bundles->concat($products)->values());
    }

    // ── Per kategori ──
    public function getProducts(Request $request)
    {
        $query = Product::where('is_active', true)->where('stock', '>', 0)->with('category');

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->search) {
            $query->where(fn($q) =>
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%')
            );
        }

        return response()->json($query->get()->map(fn($p) => $this->mapProduct($p)));
    }

    // ── Bundling ──
    public function getBundles()
    {
        $bundles = Bundle::where('is_active', true)->with('items.product')->get()
            ->map(fn($b) => $this->mapBundle($b));

        return response()->json($bundles);
    }

    // ── Sering terjual ──
    public function getPopular()
    {
        $popular = TransactionItem::selectRaw('product_id, product_name, SUM(quantity) as total_sold')
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_sold')
            ->take(8)->get();

        $result = $popular->map(function ($item) {
            $product = Product::find($item->product_id);
            if (!$product || !$product->is_active || $product->stock <= 0) return null;
            return array_merge($this->mapProduct($product), ['total_sold' => $item->total_sold]);
        })->filter()->values();

        return response()->json($result);
    }

    // ── Checkout ──
    public function checkout(Request $request)
    {
        $request->validate([
            'items'            => 'required|array|min:1',
            'items.*.type'     => 'required|in:product,bundle',
            'items.*.id'       => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'paid_amount'      => 'required|numeric|min:0',
            'payment_method'   => 'required|in:cash,transfer,qris',
        ]);

        DB::beginTransaction();
        try {
            $subtotal  = 0;
            $lineItems = [];

            foreach ($request->items as $item) {
                if ($item['type'] === 'bundle') {
                    $bundle = Bundle::with('items.product')->findOrFail($item['id']);

                    foreach ($bundle->items as $bi) {
                        $product = Product::lockForUpdate()->findOrFail($bi->product_id);
                        $needed  = $bi->quantity * $item['quantity'];
                        if ($product->stock < $needed) {
                            throw new \Exception("Stok {$product->name} tidak mencukupi untuk bundling {$bundle->name}.");
                        }
                        // Tandai untuk kurangi stok
                        $lineItems[] = ['product' => $product, 'qty_deduct' => $needed, 'skip_invoice' => true];
                    }

                    $bundleSubtotal = $bundle->price * $item['quantity'];
                    $subtotal      += $bundleSubtotal;
                    $lineItems[]    = [
                        'product_id'   => null,
                        'product_name' => "📦 {$bundle->name} ×{$item['quantity']}",
                        'price'        => (float) $bundle->price,
                        'quantity'     => $item['quantity'],
                        'subtotal'     => $bundleSubtotal,
                    ];
                } else {
                    $product      = Product::lockForUpdate()->findOrFail($item['id']);
                    if ($product->stock < $item['quantity']) {
                        throw new \Exception("Stok {$product->name} tidak mencukupi.");
                    }
                    $itemSubtotal = $product->price * $item['quantity'];
                    $subtotal    += $itemSubtotal;
                    $lineItems[]  = [
                        'product'      => $product,
                        'product_id'   => $product->id,
                        'product_name' => $product->name,
                        'price'        => (float) $product->price,
                        'quantity'     => $item['quantity'],
                        'subtotal'     => $itemSubtotal,
                        'qty_deduct'   => $item['quantity'],
                    ];
                }
            }

            $discount = $request->discount ?? 0;
            $tax      = $request->tax ?? 0;
            $total    = $subtotal - $discount + $tax;
            $change   = $request->paid_amount - $total;

            if ($change < 0) throw new \Exception('Jumlah bayar kurang dari total.');

            $transaction = Transaction::create([
                'invoice_number' => Transaction::generateInvoice(),
                'user_id'        => auth()->id(),
                'customer_id'    => $request->customer_id,
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'tax'            => $tax,
                'total'          => $total,
                'paid_amount'    => $request->paid_amount,
                'change_amount'  => $change,
                'payment_method' => $request->payment_method,
                'status'         => 'completed',
                'notes'          => $request->notes,
            ]);

            foreach ($lineItems as $line) {
                // Kurangi stok
                if (!empty($line['product']) && !empty($line['qty_deduct'])) {
                    $line['product']->decrement('stock', $line['qty_deduct']);
                }
                // Simpan ke invoice (skip baris stok-only)
                if (empty($line['skip_invoice'])) {
                    TransactionItem::create([
                        'transaction_id' => $transaction->id,
                        'product_id'     => $line['product_id'] ?? 0,
                        'product_name'   => $line['product_name'],
                        'price'          => $line['price'],
                        'quantity'       => $line['quantity'],
                        'subtotal'       => $line['subtotal'],
                    ]);
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'transaction' => $transaction->load('items'), 'message' => 'Transaksi berhasil!']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function receipt(Transaction $transaction)
    {
        $transaction->load(['items', 'customer', 'user']);
        $store = \App\Models\StoreSetting::pluck('value', 'key');
        return view('pos.receipt', compact('transaction', 'store'));
    }

    // ── Helpers ──
    private function mapProduct(Product $p): array
    {
        return [
            'id'        => $p->id,
            'type'      => 'product',
            'name'      => $p->name,
            'price'     => (float) $p->price,
            'stock'     => $p->stock,
            'unit'      => $p->unit,
            'image_url' => $p->image_url,
            'category'  => $p->category?->name,
        ];
    }

    private function mapBundle(Bundle $b): array
    {
        $available = $b->items->every(fn($item) =>
            $item->product && $item->product->stock >= $item->quantity
        );
        return [
            'id'           => $b->id,
            'type'         => 'bundle',
            'name'         => $b->name,
            'description'  => $b->description,
            'price'        => (float) $b->price,
            'normal_price' => (float) $b->normal_price,
            'saving'       => (float) $b->saving,
            'image_url'    => $b->image_url,
            'available'    => $available,
            'items'        => $b->items->map(fn($item) => [
                'product_id'   => $item->product_id,
                'product_name' => $item->product->name ?? '-',
                'quantity'     => $item->quantity,
                'price'        => (float) ($item->product->price ?? 0),
            ]),
        ];
    }
}
