<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class VendorFactory extends Factory
{
    protected $model = Vendor::class;

    public function definition(): array
    {
        $shopName = fake()->company() . ' Mart';
        return [
            'user_id' => User::factory()->vendor(),
            'shop_name' => $shopName,
            'slug' => Str::slug($shopName) . '-' . fake()->unique()->numberBetween(100, 9999),
            'description' => fake()->paragraph(),
            'logo' => 'https://images.unsplash.com/photo-1578916171728-46686eac8d58?w=200&auto=format&fit=crop&q=60',
            'phone' => '+855 ' . fake()->numerify('## ### ###'),
            'email' => fake()->companyEmail(),
            'address' => fake()->streetAddress() . ', Phnom Penh, Cambodia',
            'status' => 'active',
        ];
    }
}
