<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::factory()->create(['name' => 'API Admin', 'email' => 'admin@example.com', 'role' => 'admin']);
        foreach (range(1, 5) as $number) {
            $user = User::factory()->create(['name' => 'Vendor '.$number, 'email' => 'vendor'.$number.'@example.com', 'role' => 'vendor']);
            Vendor::create(['user_id' => $user->id, 'shop_name' => 'Phnom Penh Shop '.$number, 'slug' => 'phnom-penh-shop-'.$number, 'status' => 'active']);
        }
        foreach (range(1, 20) as $number) User::factory()->create(['name' => 'Customer '.$number, 'email' => 'customer'.$number.'@example.com', 'role' => 'customer']);
        $names = ['Electronics', 'Mobile Phones', 'Laptops', 'Fashion', 'Shoes', 'Beauty', 'Home & Kitchen', 'Sports', 'Books', 'Accessories'];
        foreach ($names as $name) Category::create(['name' => $name, 'slug' => Str::slug($name), 'status' => 'active']);
        $vendors = Vendor::all(); $categories = Category::all();
        foreach (range(1, 50) as $number) Product::create(['vendor_id' => $vendors->random()->id, 'category_id' => $categories->random()->id, 'name' => 'Cambodian Demo Product '.$number, 'slug' => 'demo-product-'.$number, 'sku' => 'SKU-'.$number, 'description' => 'Quality product for everyday Cambodian households.', 'price' => rand(5, 500), 'discount_price' => rand(0, 1) ? rand(4, 400) : null, 'stock' => rand(5, 100), 'featured' => $number <= 5, 'status' => 'active']);
    }
}
