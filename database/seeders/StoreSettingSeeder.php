<?php

namespace Database\Seeders;

use App\Models\StoreSetting;
use Illuminate\Database\Seeder;

class StoreSettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'store_name'    => 'Jeruk Lokal',
            'store_tagline' => 'No Sugar · No Water · 100% Pure Orange',
            'store_address' => 'Jl. Segar No. 1, Kota Buah',
            'store_phone'   => '0812-3456-7890',
            'store_social'  => 'IG: @jeruklokal | jeruklokal.id',
        ];

        foreach ($defaults as $key => $value) {
            StoreSetting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
