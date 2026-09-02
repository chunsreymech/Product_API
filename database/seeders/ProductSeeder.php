<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = Vendor::all();
        $categories = Category::all()->keyBy('slug');

        $products = [
            // Electronics
            ['name' => 'Sony WH-1000XM5 Wireless Headphones', 'cat' => 'electronics', 'price' => 349.00, 'disc' => 299.00, 'img' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500&auto=format&fit=crop&q=60'],
            ['name' => 'JBL Charge 5 Portable Bluetooth Speaker', 'cat' => 'electronics', 'price' => 179.00, 'disc' => 149.00, 'img' => 'https://images.unsplash.com/photo-1545454675-3531b543be5d?w=500&auto=format&fit=crop&q=60'],
            ['name' => 'Anker 737 Power Bank 24000mAh', 'cat' => 'electronics', 'price' => 129.00, 'disc' => null, 'img' => 'https://images.unsplash.com/photo-1609592426815-54605e54d8b9?w=500&auto=format&fit=crop&q=60'],
            ['name' => 'Logitech MX Master 3S Wireless Mouse', 'cat' => 'electronics', 'price' => 99.00, 'disc' => 89.00, 'img' => 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=500&auto=format&fit=crop&q=60'],
            ['name' => 'Keychron K2 Mechanical Keyboard', 'cat' => 'electronics', 'price' => 85.00, 'disc' => null, 'img' => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=500&auto=format&fit=crop&q=60'],

            // Mobile Phones
            ['name' => 'iPhone 15 Pro Max 256GB', 'cat' => 'mobile-phones', 'price' => 1199.00, 'disc' => 1149.00, 'img' => 'https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=500&auto=format&fit=crop&q=60'],
            ['name' => 'Samsung Galaxy S24 Ultra 512GB', 'cat' => 'mobile-phones', 'price' => 1299.00, 'disc' => 1199.00, 'img' => 'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=500&auto=format&fit=crop&q=60'],
            ['name' => 'Google Pixel 8 Pro 128GB', 'cat' => 'mobile-phones', 'price' => 899.00, 'disc' => 799.00, 'img' => 'https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=500&auto=format&fit=crop&q=60'],
            ['name' => 'Xiaomi 14 Ultra Leica Optics', 'cat' => 'mobile-phones', 'price' => 949.00, 'disc' => null, 'img' => 'https://images.unsplash.com/photo-1580910051074-3eb694886505?w=500&auto=format&fit=crop&q=60'],
            ['name' => 'OnePlus 12 5G Smartphone', 'cat' => 'mobile-phones', 'price' => 799.00, 'disc' => 729.00, 'img' => 'https://images.unsplash.com/photo-1565849904461-04a58ad377e0?w=500&auto=format&fit=crop&q=60'],

            // Laptops
            ['name' => 'MacBook Pro 16" M3 Pro 512GB', 'cat' => 'laptops', 'price' => 2499.00, 'disc' => 2399.00, 'img' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=500&auto=format&fit=crop&q=60'],
            ['name' => 'Dell XPS 15 OLED InfinityEdge', 'cat' => 'laptops', 'price' => 1899.00, 'disc' => 1749.00, 'img' => 'https://images.unsplash.com/photo-1593642632823-8f785ba67e45?w=500&auto=format&fit=crop&q=60'],
            ['name' => 'ASUS ROG Zephyrus G14 Gaming Laptop', 'cat' => 'laptops', 'price' => 1599.00, 'disc' => null, 'img' => 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=500&auto=format&fit=crop&q=60'],
            ['name' => 'Lenovo ThinkPad X1 Carbon Gen 11', 'cat' => 'laptops', 'price' => 1450.00, 'disc' => 1380.00, 'img' => 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=500&auto=format&fit=crop&q=60'],
            ['name' => 'Apple MacBook Air 15" M2', 'cat' => 'laptops', 'price' => 1299.00, 'disc' => 1199.00, 'img' => 'https://images.unsplash.com/photo-1611186871348-b1ce696e52c9?w=500&auto=format&fit=crop&q=60'],

            // Fashion
            ['name' => 'Traditional Khmer Silk Sampot Hol', 'cat' => 'fashion', 'price' => 120.00, 'disc' => 99.00, 'img' => 'https://images.unsplash.com/photo-1583391733956-3750e0ff4e8b?w=500&auto=format&fit=crop&q=60'],
            ['name' => 'Men Khmer Cotton Krama Shirt', 'cat' => 'fashion', 'price' => 35.00, 'disc' => null, 'img' => 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=500&auto=format&fit=crop&q=60'],
            ['name' => 'Handwoven Lotus Silk Scarf', 'cat' => 'fashion', 'price' => 65.00, 'disc' => 55.00, 'img' => 'https://images.unsplash.com/photo-1601924994987-69e26d50dc26?w=500&auto=format&fit=crop&q=60'],
            ['name' => 'Urban Canvas Casual Jacket', 'cat' => 'fashion', 'price' => 79.00, 'disc' => 69.00, 'img' => 'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=500&auto=format&fit=crop&q=60'],
            ['name' => 'Organic Linen Summer Dress', 'cat' => 'fashion', 'price' => 49.00, 'disc' => null, 'img' => 'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=500&auto=format&fit=crop&q=60'],

            // Shoes
            ['name' => 'Nike Air Zoom Pegasus 40', 'cat' => 'shoes', 'price' => 130.00, 'disc' => 110.00, 'img' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=500&auto=format&fit=crop&q=60'],
            ['name' => 'Adidas Ultraboost Light Running Shoes', 'cat' => 'shoes', 'price' => 190.00, 'disc' => 160.00, 'img' => 'https://images.unsplash.com/photo-1584735935682-2f2b69dff9d2?w=500&auto=format&fit=crop&q=60'],
            ['name' => 'Handcrafted Leather Oxford Shoes', 'cat' => 'shoes', 'price' => 145.00, 'disc' => null, 'img' => 'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?w=500&auto=format&fit=crop&q=60'],
            ['name' => 'New Balance 574 Core Classics', 'cat' => 'shoes', 'price' => 89.00, 'disc' => 79.00, 'img' => 'https://images.unsplash.com/photo-1539185441755-769473a23570?w=500&auto=format&fit=crop&q=60'],
            ['name' => 'Comfort Slides Sandals with Cushion', 'cat' => 'shoes', 'price' => 25.00, 'disc' => 19.99, 'img' => 'https://images.unsplash.com/photo-1603808033192-082d6919d3e1?w=500&auto=format&fit=crop&q=60'],

            // Beauty
            ['name' => 'Jasmine & Rice Bran Organic Face Oil', 'cat' => 'beauty', 'price' => 28.00, 'disc' => 22.00, 'img' => 'https://images.unsplash.com/photo-1608248597359-009139f75ec5?w=500&auto=format&fit=crop&q=60'],
            ['name' => 'Lemongrass Herbal Body Scrub 250g', 'cat' => 'beauty', 'price' => 16.00, 'disc' => null, 'img' => 'https://images.unsplash.com/photo-1571781926291-c477ebfd024b?w=500&auto=format&fit=crop&q=60'],
            ['name' => 'Natural Coconut Lip Balm Set', 'cat' => 'beauty', 'price' => 12.00, 'disc' => 9.50, 'img' => 'https://images.unsplash.com/photo-1599305090598-fe179d501227?w=500&auto=format&fit=crop&q=60'],
            ['name' => 'Hydrating Aloe Vera Mist 100ml', 'cat' => 'beauty', 'price' => 14.00, 'disc' => null, 'img' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=500&auto=format&fit=crop&q=60'],
            ['name' => 'Kampot Pepper & Turmeric Healing Soap', 'cat' => 'beauty', 'price' => 8.50, 'disc' => 6.50, 'img' => 'https://images.unsplash.com/photo-1607006314041-e18e8d894812?w=500&auto=format&fit=crop&q=60'],

            // Home & Kitchen
            ['name' => 'Nespresso Vertuo Pop Coffee Machine', 'cat' => 'home-kitchen', 'price' => 149.00, 'disc' => 129.00, 'img' => 'https://images.unsplash.com/photo-1517668808822-9ebb02f2a0e6?w=500&auto=format&fit=crop&q=60'],
            ['name' => 'Cast Iron Dutch Oven Pot 5L', 'cat' => 'home-kitchen', 'price' => 89.00, 'disc' => 75.00, 'img' => 'https://images.unsplash.com/photo-1584990347449-399a9a3b8396?w=500&auto=format&fit=crop&q=60'],
            ['name' => 'Handcarved Teak Wood Cutting Board', 'cat' => 'home-kitchen', 'price' => 38.00, 'disc' => null, 'img' => 'https://images.unsplash.com/photo-1590794056226-79ef3a8147e1?w=500&auto=format&fit=crop&q=60'],
            ['name' => 'Ceramic Stoneware Dinner Plate Set', 'cat' => 'home-kitchen', 'price' => 59.00, 'disc' => 49.00, 'img' => 'https://images.unsplash.com/photo-1610701596007-11502861dcfa?w=500&auto=format&fit=crop&q=60'],
            ['name' => 'Stainless Steel Japanese Chef Knife', 'cat' => 'home-kitchen', 'price' => 69.00, 'disc' => null, 'img' => 'https://images.unsplash.com/photo-1593618998160-e34014e67546?w=500&auto=format&fit=crop&q=60'],

            // Sports
            ['name' => 'Pro Eco Yoga Mat 6mm Non-Slip', 'cat' => 'sports', 'price' => 35.00, 'disc' => 29.00, 'img' => 'https://images.unsplash.com/photo-1601925260368-ae2f83cf8b7f?w=500&auto=format&fit=crop&q=60'],
            ['name' => 'Adjustable Dumbbell Set 20KG', 'cat' => 'sports', 'price' => 110.00, 'disc' => 95.00, 'img' => 'https://images.unsplash.com/photo-1584735935682-2f2b69dff9d2?w=500&auto=format&fit=crop&q=60'],
            ['name' => 'Stainless Steel Insulated Water Bottle 1L', 'cat' => 'sports', 'price' => 22.00, 'disc' => null, 'img' => 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=500&auto=format&fit=crop&q=60'],
            ['name' => 'Outdoor Lightweight Trekking Backpack 40L', 'cat' => 'sports', 'price' => 75.00, 'disc' => 62.00, 'img' => 'https://images.unsplash.com/photo-1622560480605-d83c853bc5c3?w=500&auto=format&fit=crop&q=60'],
            ['name' => 'Speed Jump Rope with Ball Bearings', 'cat' => 'sports', 'price' => 12.00, 'disc' => 9.99, 'img' => 'https://images.unsplash.com/photo-1591258370814-01609b341790?w=500&auto=format&fit=crop&q=60'],

            // Books
            ['name' => 'A History of Cambodia (David Chandler)', 'cat' => 'books', 'price' => 24.00, 'disc' => null, 'img' => 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?w=500&auto=format&fit=crop&q=60'],
            ['name' => 'Angkor: Heart of an Asian Empire', 'cat' => 'books', 'price' => 32.00, 'disc' => 28.00, 'img' => 'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=500&auto=format&fit=crop&q=60'],
            ['name' => 'First They Killed My Father (Loung Ung)', 'cat' => 'books', 'price' => 18.00, 'disc' => 15.00, 'img' => 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?w=500&auto=format&fit=crop&q=60'],
            ['name' => 'The Art of Cambodian Cooking', 'cat' => 'books', 'price' => 26.00, 'disc' => null, 'img' => 'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?w=500&auto=format&fit=crop&q=60'],
            ['name' => 'Building Business in Southeast Asia', 'cat' => 'books', 'price' => 29.00, 'disc' => 25.00, 'img' => 'https://images.unsplash.com/photo-1589829085413-56de8ae18c73?w=500&auto=format&fit=crop&q=60'],

            // Accessories
            ['name' => 'Minimalist Leather Slim Wallet', 'cat' => 'accessories', 'price' => 35.00, 'disc' => 29.00, 'img' => 'https://images.unsplash.com/photo-1627123424574-724758594e93?w=500&auto=format&fit=crop&q=60'],
            ['name' => 'Classic Polarized Aviator Sunglasses', 'cat' => 'accessories', 'price' => 45.00, 'disc' => null, 'img' => 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?w=500&auto=format&fit=crop&q=60'],
            ['name' => 'Vintage Chronograph Leather Watch', 'cat' => 'accessories', 'price' => 185.00, 'disc' => 159.00, 'img' => 'https://images.unsplash.com/photo-1524805444758-089113d48a6d?w=500&auto=format&fit=crop&q=60'],
            ['name' => 'Canvas Laptop Messenger Bag 15"', 'cat' => 'accessories', 'price' => 65.00, 'disc' => 55.00, 'img' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=500&auto=format&fit=crop&q=60'],
            ['name' => 'Handmade Silver Apsara Pendant', 'cat' => 'accessories', 'price' => 95.00, 'disc' => null, 'img' => 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?w=500&auto=format&fit=crop&q=60'],
        ];

        foreach ($products as $idx => $p) {
            $cat = $categories->get($p['cat']) ?? $categories->first();
            $vendor = $vendors[$idx % count($vendors)];
            $stock = rand(20, 80);

            $product = Product::firstOrCreate(
                ['sku' => 'SKU-' . str_pad($idx + 1, 4, '0', STR_PAD_LEFT)],
                [
                    'vendor_id' => $vendor->id,
                    'category_id' => $cat->id,
                    'name' => $p['name'],
                    'slug' => Str::slug($p['name']) . '-' . ($idx + 1),
                    'description' => "High quality {$p['name']} curated for modern e-commerce customers in Cambodia.",
                    'price' => $p['price'],
                    'discount_price' => $p['disc'],
                    'stock' => $stock,
                    'image' => $p['img'],
                    'status' => 'active',
                    'featured' => $idx < 8,
                ]
            );

            ProductImage::firstOrCreate(
                ['product_id' => $product->id, 'path' => $p['img']],
                ['is_primary' => true]
            );

            InventoryTransaction::firstOrCreate(
                ['product_id' => $product->id, 'type' => 'stock_in'],
                [
                    'vendor_id' => $vendor->id,
                    'quantity' => $stock,
                    'notes' => 'Initial catalogue stock',
                ]
            );
        }
    }
}
