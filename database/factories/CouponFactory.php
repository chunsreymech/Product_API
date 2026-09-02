<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        $isPercentage = fake()->boolean();
        return [
            'code' => strtoupper(fake()->unique()->lexify('PROMO???')),
            'type' => $isPercentage ? 'percentage' : 'fixed',
            'value' => $isPercentage ? fake()->numberBetween(5, 30) : fake()->numberBetween(5, 50),
            'minimum_order_amount' => fake()->randomElement([0, 20, 50, 100]),
            'maximum_discount' => $isPercentage ? fake()->randomElement([20, 50, 100]) : null,
            'start_date' => now()->subDays(5),
            'end_date' => now()->addMonths(2),
            'usage_limit' => fake()->randomElement([50, 100, 500, null]),
            'used_count' => fake()->numberBetween(0, 10),
            'status' => true,
        ];
    }
}
