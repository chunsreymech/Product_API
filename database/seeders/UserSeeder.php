<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = Hash::make('password');

        // 1. Admin User
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'API Admin',
                'password' => $defaultPassword,
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // 2. 5 Vendors
        $vendors = [
            [
                'name' => 'Sokha Tech',
                'email' => 'vendor1@example.com',
                'shop_name' => 'Phnom Penh Tech Zone',
                'slug' => 'phnom-penh-tech-zone',
                'phone' => '+855 12 345 678',
                'address' => 'No. 128, Russian Federation Blvd, Toul Kork, Phnom Penh',
                'description' => 'Premier electronics and digital gadgets store in Phnom Penh.',
                'logo' => 'https://images.unsplash.com/photo-1550009158-9ebf69173e03?w=300&auto=format&fit=crop&q=60',
            ],
            [
                'name' => 'Chanthy Silk & Apparel',
                'email' => 'vendor2@example.com',
                'shop_name' => 'Angkor Fashion Hub',
                'slug' => 'angkor-fashion-hub',
                'phone' => '+855 17 888 999',
                'address' => 'Sivutha Blvd, Svay Dangkum, Siem Reap',
                'description' => 'Authentic Cambodian silk garments and contemporary fashion wear.',
                'logo' => 'https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?w=300&auto=format&fit=crop&q=60',
            ],
            [
                'name' => 'Rithy Khmer Crafts',
                'email' => 'vendor3@example.com',
                'shop_name' => 'Siem Reap Handicrafts & Gifts',
                'slug' => 'siem-reap-handicrafts',
                'phone' => '+855 93 456 789',
                'address' => 'Old Market Area, Krong Siem Reap',
                'description' => 'Handmade stone carvings, wooden crafts, and cultural gifts.',
                'logo' => 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?w=300&auto=format&fit=crop&q=60',
            ],
            [
                'name' => 'Bopha Nature Mart',
                'email' => 'vendor4@example.com',
                'shop_name' => 'Battambang Organic & Living',
                'slug' => 'battambang-organic',
                'phone' => '+855 78 123 456',
                'address' => 'Street 1, Romchek 4, Battambang',
                'description' => 'Organic teas, natural Kampot spices, and home wellness essentials.',
                'logo' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=300&auto=format&fit=crop&q=60',
            ],
            [
                'name' => 'Vannak Smart Devices',
                'email' => 'vendor5@example.com',
                'shop_name' => 'Khmer Mobile & Accessories',
                'slug' => 'khmer-mobile-accessories',
                'phone' => '+855 85 999 111',
                'address' => 'Monivong Blvd, Boeung Keng Kang 1, Phnom Penh',
                'description' => 'Authorized reseller for smartphones, smartwatches, and chargers.',
                'logo' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=300&auto=format&fit=crop&q=60',
            ],
        ];

        foreach ($vendors as $vData) {
            $user = User::firstOrCreate(
                ['email' => $vData['email']],
                [
                    'name' => $vData['name'],
                    'password' => $defaultPassword,
                    'role' => 'vendor',
                    'email_verified_at' => now(),
                ]
            );

            Vendor::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'shop_name' => $vData['shop_name'],
                    'slug' => $vData['slug'],
                    'phone' => $vData['phone'],
                    'email' => $vData['email'],
                    'address' => $vData['address'],
                    'description' => $vData['description'],
                    'logo' => $vData['logo'],
                    'status' => 'active',
                ]
            );
        }

        // 3. 20 Customers with Cambodian names
        $cambodianNames = [
            'Sok Dara', 'Keo Bopha', 'Meng Sreynich', 'Chan Vatanaka', 'Chea Pisey',
            'Nhem Sovann', 'Rath Mony', 'Lim Heng', 'Tep Sreypov', 'Heng Visal',
            'Ung Chantha', 'Srun Chhay', 'Pich Sambath', 'Ly Bunly', 'Samreth Theary',
            'Kosal Rachana', 'Vibol Oudom', 'Sinath Kolab', 'Bunna Ratana', 'Chhun Sothea'
        ];

        foreach (range(1, 20) as $index) {
            $name = $cambodianNames[$index - 1] ?? "Customer {$index}";
            User::firstOrCreate(
                ['email' => "customer{$index}@example.com"],
                [
                    'name' => $name,
                    'password' => $defaultPassword,
                    'role' => 'customer',
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
