<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Users
        User::create([
            'name' => 'Admin',
            'email' => 'admin@jeruklokal.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Kasir 1',
            'email' => 'kasir@jeruklokal.com',
            'password' => Hash::make('password'),
            'role' => 'cashier',
        ]);

        // Categories
        $cats = ['Jeruk Sunkist', 'Jeruk Lokal', 'Matcha Series'];
        foreach ($cats as $cat) {
            Category::create(['name' => $cat, 'slug' => Str::slug($cat)]);
        }

        // Products — sesuai menu Jeruk Lokal
        $products = [
            // Jeruk Sunkist
            ['name' => 'Pure Sunkist',      'category' => 'Jeruk Sunkist', 'price' => 27000, 'stock' => 100],
            ['name' => 'Sunkist Honey',     'category' => 'Jeruk Sunkist', 'price' => 30000, 'stock' => 100],
            ['name' => 'Sunkist Yakult',    'category' => 'Jeruk Sunkist', 'price' => 32000, 'stock' => 100],
            ['name' => 'Sunkist Coconut',   'category' => 'Jeruk Sunkist', 'price' => 30000, 'stock' => 100],
            // Jeruk Lokal
            ['name' => 'Pure Orange',       'category' => 'Jeruk Lokal',   'price' => 17000, 'stock' => 100],
            ['name' => 'Orange Honey',      'category' => 'Jeruk Lokal',   'price' => 19000, 'stock' => 100],
            ['name' => 'Orange Yakult',     'category' => 'Jeruk Lokal',   'price' => 22000, 'stock' => 100],
            ['name' => 'Coconut Orange',    'category' => 'Jeruk Lokal',   'price' => 19000, 'stock' => 100],
            // Matcha Series
            ['name' => 'Orange Matcha',     'category' => 'Matcha Series', 'price' => 20000, 'stock' => 100],
            ['name' => 'Yakult Matcha',     'category' => 'Matcha Series', 'price' => 22000, 'stock' => 100],
            ['name' => 'Coconut Matcha',    'category' => 'Matcha Series', 'price' => 20000, 'stock' => 100],
        ];

        foreach ($products as $p) {
            $category = Category::where('name', $p['category'])->first();
            Product::create([
                'category_id' => $category->id,
                'name'        => $p['name'],
                'sku'         => 'JL-' . strtoupper(Str::random(5)),
                'price'       => $p['price'],
                'cost_price'  => $p['price'] * 0.6,
                'stock'       => $p['stock'],
                'unit'        => 'cup',
                'is_active'   => true,
            ]);
        }

        // Customers
        Customer::create(['name' => 'Umum']);
        Customer::create(['name' => 'Budi Santoso', 'phone' => '081234567890']);
        Customer::create(['name' => 'Siti Rahayu',  'phone' => '082345678901']);
    }
}
