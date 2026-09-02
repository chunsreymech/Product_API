<?php

namespace Database\Factories;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryTransactionFactory extends Factory
{
    protected $model = InventoryTransaction::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'vendor_id' => Vendor::factory(),
            'quantity' => fake()->numberBetween(5, 50),
            'type' => fake()->randomElement(['stock_in', 'stock_out', 'order_deduction', 'order_restoration', 'adjustment']),
            'notes' => 'Inventory record batch',
        ];
    }
}
