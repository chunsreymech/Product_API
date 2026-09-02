<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_dashboard_metrics(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->customer()->count(3)->create();
        Vendor::factory()->count(2)->create();
        Category::factory()->count(2)->create();
        Product::factory()->count(5)->create();
        Order::factory()->count(4)->create();

        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/v1/admin/dashboard');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'metrics' => [
                        'total_customers',
                        'total_vendors',
                        'total_products',
                        'total_categories',
                        'total_orders',
                        'pending_orders',
                        'completed_orders',
                        'total_sales',
                    ],
                    'recent_orders',
                    'top_products',
                    'top_vendors',
                ],
            ]);
    }

    public function test_non_admin_cannot_access_admin_dashboard(): void
    {
        $customer = User::factory()->customer()->create();

        $response = $this->actingAs($customer, 'sanctum')->getJson('/api/v1/admin/dashboard');

        $response->assertForbidden();
    }
}
