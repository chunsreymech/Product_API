<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            ['code' => 'KHMERNEWYEAR', 'type' => 'percentage', 'value' => 20.00, 'min' => 30.00, 'max' => 50.00, 'limit' => 500],
            ['code' => 'WELCOME10', 'type' => 'percentage', 'value' => 10.00, 'min' => 20.00, 'max' => 25.00, 'limit' => 1000],
            ['code' => 'FREESHIP', 'type' => 'fixed', 'value' => 3.00, 'min' => 15.00, 'max' => null, 'limit' => 300],
            ['code' => 'SAVE15', 'type' => 'fixed', 'value' => 15.00, 'min' => 100.00, 'max' => null, 'limit' => 200],
        ];

        foreach ($coupons as $cp) {
            Coupon::firstOrCreate(
                ['code' => $cp['code']],
                [
                    'type' => $cp['type'],
                    'value' => $cp['value'],
                    'minimum_order_amount' => $cp['min'],
                    'maximum_discount' => $cp['max'],
                    'start_date' => now()->subDays(10),
                    'end_date' => now()->addMonths(6),
                    'usage_limit' => $cp['limit'],
                    'used_count' => rand(5, 30),
                    'status' => true,
                ]
            );
        }
    }
}
