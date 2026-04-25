<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreSetting;
use Illuminate\Http\Request;

class StoreSettingController extends Controller
{
    public function edit()
    {
        $settings = StoreSetting::pluck('value', 'key');
        return view('admin.settings.store', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'store_name'    => 'required|string|max:100',
            'store_tagline' => 'nullable|string|max:200',
            'store_address' => 'nullable|string|max:300',
            'store_phone'   => 'nullable|string|max:50',
            'store_social'  => 'nullable|string|max:200',
        ]);

        foreach (['store_name', 'store_tagline', 'store_address', 'store_phone', 'store_social'] as $key) {
            StoreSetting::set($key, $request->input($key, ''));
        }

        return back()->with('success', 'Pengaturan toko berhasil disimpan.');
    }
}
