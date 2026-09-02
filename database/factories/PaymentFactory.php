<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'method' => fake()->randomElement(['cash_on_delivery', 'demo_card', 'bank_transfer']),
            'status' => fake()->randomElement(['pending', 'paid', 'failed']),
            'amount' => fake()->randomFloat(2, 20, 500),
            'transaction_id' => 'TXN-' . strtoupper(Str::random(12)),
            'payment_details' => [
                'demo' => true,
                'gateway' => 'demo_gateway',
            ],
        ];
    }
}
