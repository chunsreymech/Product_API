<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Database\Seeder;

class CustomerDataSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::where('role', 'customer')->get();
        $products = Product::all();

        if ($customers->isEmpty() || $products->isEmpty()) {
            return;
        }

        // 1. Addresses
        foreach ($customers as $customer) {
            Address::firstOrCreate(
                ['user_id' => $customer->id, 'label' => 'Home'],
                [
                    'address' => 'Street ' . rand(100, 600) . ', House #' . rand(1, 99) . ', Sangkat Teuk Laak',
                    'city' => rand(0, 1) ? 'Phnom Penh' : 'Siem Reap',
                    'phone' => '+855 ' . rand(10, 99) . ' ' . rand(100, 999) . ' ' . rand(100, 999),
                    'is_default' => true,
                ]
            );

            Address::firstOrCreate(
                ['user_id' => $customer->id, 'label' => 'Office'],
                [
                    'address' => 'Floor ' . rand(2, 20) . ', Canadia Tower, Preah Monivong Blvd',
                    'city' => 'Phnom Penh',
                    'phone' => '+855 23 ' . rand(100, 999) . ' ' . rand(100, 999),
                    'is_default' => false,
                ]
            );
        }

        // 2. Wishlist items (30 items)
        foreach (range(1, 30) as $i) {
            $customer = $customers[$i % count($customers)];
            $prod = $products[($i * 3) % count($products)];
            Wishlist::firstOrCreate([
                'user_id' => $customer->id,
                'product_id' => $prod->id,
            ]);
        }

        // 3. Cart & Cart Items (50 items across customers)
        foreach ($customers as $cIdx => $customer) {
            $cart = Cart::firstOrCreate(['user_id' => $customer->id]);
            if ($cIdx < 12) {
                $numItems = rand(2, 4);
                for ($k = 0; $k < $numItems; $k++) {
                    $prod = $products[($cIdx * 3 + $k) % count($products)];
                    CartItem::firstOrCreate(
                        ['cart_id' => $cart->id, 'product_id' => $prod->id],
                        [
                            'quantity' => rand(1, 2),
                            'unit_price' => $prod->sale_price,
                        ]
                    );
                }
            }
        }
    }
}
