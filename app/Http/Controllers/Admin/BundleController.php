<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Models\BundleItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BundleController extends Controller
{
    public function index()
    {
        $bundles = Bundle::withCount('items')->latest()->paginate(10);
        return view('admin.bundles.index', compact('bundles'));
    }

    public function create()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();
        return view('admin.bundles.form', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:200',
            'price'      => 'required|numeric|min:0',
            'description'=> 'nullable|string',
            'image'      => 'nullable|image|max:2048',
            'products'   => 'required|array|min:1',
            'products.*' => 'exists:products,id',
            'quantities' => 'required|array',
            'quantities.*'=> 'integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $data = $request->only('name', 'price', 'description', 'is_active');
            $data['is_active'] = $request->boolean('is_active', true);

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('bundles', 'public');
            }

            $bundle = Bundle::create($data);

            foreach ($request->products as $i => $productId) {
                BundleItem::create([
                    'bundle_id'  => $bundle->id,
                    'product_id' => $productId,
                    'quantity'   => $request->quantities[$i] ?? 1,
                ]);
            }

            DB::commit();
            return redirect()->route('admin.bundles.index')->with('success', 'Bundling berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function edit(Bundle $bundle)
    {
        $bundle->load('items.product');
        $products = Product::where('is_active', true)->orderBy('name')->get();
        return view('admin.bundles.form', compact('bundle', 'products'));
    }

    public function update(Request $request, Bundle $bundle)
    {
        $request->validate([
            'name'        => 'required|string|max:200',
            'price'       => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|max:2048',
            'products'    => 'required|array|min:1',
            'products.*'  => 'exists:products,id',
            'quantities'  => 'required|array',
            'quantities.*'=> 'integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $data = $request->only('name', 'price', 'description');
            $data['is_active'] = $request->boolean('is_active', true);

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('bundles', 'public');
            }

            $bundle->update($data);

            // Hapus items lama, insert baru
            $bundle->items()->delete();
            foreach ($request->products as $i => $productId) {
                BundleItem::create([
                    'bundle_id'  => $bundle->id,
                    'product_id' => $productId,
                    'quantity'   => $request->quantities[$i] ?? 1,
                ]);
            }

            DB::commit();
            return redirect()->route('admin.bundles.index')->with('success', 'Bundling berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function destroy(Bundle $bundle)
    {
        $bundle->delete();
        return redirect()->route('admin.bundles.index')->with('success', 'Bundling berhasil dihapus.');
    }
}
