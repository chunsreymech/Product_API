<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::where('role', 'customer')->get();
        $products = Product::all();

        if ($customers->isEmpty() || $products->isEmpty()) {
            return;
        }

        $orderStatuses = ['delivered', 'delivered', 'delivered', 'shipped', 'processing', 'confirmed', 'pending', 'cancelled'];
        $paymentMethods = ['cash_on_delivery', 'demo_card', 'bank_transfer'];

        for ($i = 1; $i <= 100; $i++) {
            $customer = $customers[($i - 1) % count($customers)];
            $status = $orderStatuses[$i % count($orderStatuses)];
            $method = $paymentMethods[$i % count($paymentMethods)];

            $orderProducts = [
                $products[($i * 2) % count($products)],
                $products[($i * 2 + 1) % count($products)],
            ];

            $subtotal = 0.0;
            $itemsData = [];
            foreach ($orderProducts as $p) {
                $qty = rand(1, 3);
                $uPrice = (float) $p->sale_price;
                $tot = round($qty * $uPrice, 2);
                $subtotal += $tot;
                $itemsData[] = [
                    'product' => $p,
                    'quantity' => $qty,
                    'unit_price' => $uPrice,
                    'total_price' => $tot,
                ];
            }

            $discount = ($i % 3 === 0 && $subtotal > 50) ? 10.00 : 0.00;
            $taxable = max(0, $subtotal - $discount);
            $tax = round($taxable * 0.10, 2);
            $shipping = $subtotal >= 50 ? 0.00 : 3.00;
            $grandTotal = round($taxable + $tax + $shipping, 2);

            $order = Order::firstOrCreate(
                ['order_number' => 'ORD-' . str_pad($i, 5, '0', STR_PAD_LEFT)],
                [
                    'user_id' => $customer->id,
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'tax' => $tax,
                    'shipping' => $shipping,
                    'grand_total' => $grandTotal,
                    'status' => $status,
                    'shipping_address' => "Home: Street " . rand(100, 500) . ", Phnom Penh (Phone: +855 12 345 678)",
                    'payment_method' => $method,
                    'payment_status' => in_array($status, ['confirmed', 'processing', 'shipped', 'delivered']) ? 'paid' : ($status === 'cancelled' ? 'refunded' : 'pending'),
                    'notes' => $i % 5 === 0 ? 'Please call before delivery' : null,
                    'created_at' => now()->subDays(rand(1, 45)),
                ]
            );

            foreach ($itemsData as $item) {
                OrderItem::firstOrCreate(
                    ['order_id' => $order->id, 'product_id' => $item['product']->id],
                    [
                        'vendor_id' => $item['product']->vendor_id,
                        'product_name' => $item['product']->name,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total_price' => $item['total_price'],
                    ]
                );
            }

            Payment::firstOrCreate(
                ['order_id' => $order->id],
                [
                    'method' => $method,
                    'status' => in_array($status, ['confirmed', 'processing', 'shipped', 'delivered']) ? 'paid' : ($status === 'cancelled' ? 'refunded' : 'pending'),
                    'amount' => $grandTotal,
                    'transaction_id' => 'TXN-' . strtoupper(Str::random(10)),
                    'payment_details' => ['gateway' => 'demo', 'time' => now()->toISOString()],
                ]
            );

            // Add reviews for delivered orders
            if ($status === 'delivered' && $i <= 40) {
                $revProduct = $orderProducts[0];
                Review::firstOrCreate(
                    ['customer_id' => $customer->id, 'product_id' => $revProduct->id, 'order_id' => $order->id],
                    [
                        'rating' => rand(4, 5),
                        'comment' => 'Excellent quality and fast delivery in Phnom Penh! Highly recommended.',
                        'status' => 'approved',
                    ]
                );
            }
        }
    }
}
