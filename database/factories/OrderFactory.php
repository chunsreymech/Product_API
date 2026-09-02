<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 20, 500);
        $discount = fake()->randomElement([0, 5, 10, 15]);
        $tax = round(($subtotal - $discount) * 0.10, 2);
        $shipping = $subtotal >= 50 ? 0.00 : 3.00;
        $grandTotal = round(($subtotal - $discount) + $tax + $shipping, 2);

        $statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
        $status = fake()->randomElement($statuses);

        return [
            'user_id' => User::factory()->customer(),
            'order_number' => 'ORD-' . strtoupper(Str::random(10)),
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'shipping' => $shipping,
            'grand_total' => $grandTotal,
            'status' => $status,
            'shipping_address' => fake()->streetAddress() . ', Phnom Penh, Cambodia',
            'payment_method' => fake()->randomElement(['cash_on_delivery', 'demo_card', 'bank_transfer']),
            'payment_status' => in_array($status, ['confirmed', 'processing', 'shipped', 'delivered']) ? 'paid' : 'pending',
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
