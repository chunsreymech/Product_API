<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Electronics', 'slug' => 'electronics', 'image' => 'https://images.unsplash.com/photo-1498049794561-7780e7231661?w=400&auto=format&fit=crop&q=60', 'description' => 'Smart gadgets, smart home appliances, and audio gear.'],
            ['name' => 'Mobile Phones', 'slug' => 'mobile-phones', 'image' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=400&auto=format&fit=crop&q=60', 'description' => 'Flagship and budget smartphones and tablets.'],
            ['name' => 'Laptops', 'slug' => 'laptops', 'image' => 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=400&auto=format&fit=crop&q=60', 'description' => 'Ultra-thin notebooks, gaming rigs, and workstation laptops.'],
            ['name' => 'Fashion', 'slug' => 'fashion', 'image' => 'https://images.unsplash.com/photo-1445205170230-053b83016050?w=400&auto=format&fit=crop&q=60', 'description' => 'Modern lifestyle apparel, traditional silk, and designer wear.'],
            ['name' => 'Shoes', 'slug' => 'shoes', 'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&auto=format&fit=crop&q=60', 'description' => 'Running shoes, casual sneakers, and leather loafers.'],
            ['name' => 'Beauty', 'slug' => 'beauty', 'image' => 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?w=400&auto=format&fit=crop&q=60', 'description' => 'Natural skincare, perfumes, cosmetics, and self-care.'],
            ['name' => 'Home & Kitchen', 'slug' => 'home-kitchen', 'image' => 'https://images.unsplash.com/photo-1556911220-e15b29be8c8f?w=400&auto=format&fit=crop&q=60', 'description' => 'Kitchen appliances, cookware, and cozy home decor.'],
            ['name' => 'Sports', 'slug' => 'sports', 'image' => 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?w=400&auto=format&fit=crop&q=60', 'description' => 'Fitness equipment, sportswear, outdoor gear, and yoga mats.'],
            ['name' => 'Books', 'slug' => 'books', 'image' => 'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=400&auto=format&fit=crop&q=60', 'description' => 'Cambodian history, literature, business, and self-help.'],
            ['name' => 'Accessories', 'slug' => 'accessories', 'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400&auto=format&fit=crop&q=60', 'description' => 'Watches, sunglasses, backpacks, and leather wallets.'],
        ];

        foreach ($categories as $cData) {
            Category::firstOrCreate(
                ['slug' => $cData['slug']],
                [
                    'name' => $cData['name'],
                    'description' => $cData['description'],
                    'image' => $cData['image'],
                    'status' => 'active',
                ]
            );
        }
    }
}
