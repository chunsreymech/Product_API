<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->words(3, true);
        return [
            'vendor_id' => Vendor::factory(),
            'category_id' => Category::factory(),
            'name' => ucfirst($name),
            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(100, 99999),
            'sku' => 'SKU-' . strtoupper(Str::random(8)),
            'description' => fake()->paragraphs(2, true),
            'price' => fake()->randomFloat(2, 10, 500),
            'discount_price' => null,
            'stock' => fake()->numberBetween(5, 120),
            'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500&auto=format&fit=crop&q=60',
            'status' => 'active',
            'featured' => fake()->boolean(20),
        ];
    }

    public function withDiscount(?float $discountPrice = null): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_price' => $discountPrice ?? round(($attributes['price'] ?? 50) * 0.8, 2),
        ]);
    }
}
