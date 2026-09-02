<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_and_filter_products(): void
    {
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();
        $vendor = Vendor::factory()->create();

        Product::factory()->create(['category_id' => $category1->id, 'vendor_id' => $vendor->id, 'price' => 50, 'status' => 'active', 'name' => 'Budget Phone']);
        Product::factory()->create(['category_id' => $category2->id, 'vendor_id' => $vendor->id, 'price' => 500, 'status' => 'active', 'name' => 'Flagship Phone']);

        // Test Category Filter
        $res = $this->getJson("/api/v1/products?category_id={$category1->id}");
        $res->assertOk()->assertJsonCount(1, 'data');

        // Test Price Range Filter
        $resPrice = $this->getJson('/api/v1/products?min_price=100&max_price=600');
        $resPrice->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.name', 'Flagship Phone');

        // Test Search Filter
        $resSearch = $this->getJson('/api/v1/products/search?search=Budget');
        $resSearch->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.name', 'Budget Phone');
    }

    public function test_can_view_single_product_and_related_products(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $related = Product::factory()->count(2)->create(['category_id' => $category->id]);

        $res = $this->getJson("/api/v1/products/{$product->id}");
        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $product->id);

        $resRel = $this->getJson("/api/v1/products/{$product->id}/related");
        $resRel->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data');
    }

    public function test_vendor_can_create_product_and_stock_is_logged(): void
    {
        $user = User::factory()->vendor()->create();
        $vendor = Vendor::factory()->create(['user_id' => $user->id]);
        $category = Category::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/vendor/products', [
            'category_id' => $category->id,
            'name' => 'Khmer Mechanical Keyboard',
            'sku' => 'KM-KB-001',
            'description' => 'Custom RGB keyboard with Khmer fonts',
            'price' => 85.00,
            'discount_price' => 75.00,
            'stock' => 20,
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Khmer Mechanical Keyboard')
            ->assertJsonPath('data.vendor_id', $vendor->id);

        $this->assertDatabaseHas('products', ['sku' => 'KM-KB-001', 'stock' => 20]);
        $this->assertDatabaseHas('inventory_transactions', ['type' => 'stock_in', 'quantity' => 20]);
    }

    public function test_vendor_can_update_own_product(): void
    {
        $user = User::factory()->vendor()->create();
        $vendor = Vendor::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create(['vendor_id' => $vendor->id, 'name' => 'Original Name']);

        $response = $this->actingAs($user, 'sanctum')->putJson("/api/v1/vendor/products/{$product->id}", [
            'name' => 'Updated Product Name',
            'price' => 99.00,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Updated Product Name');
    }

    public function test_vendor_cannot_modify_other_vendors_product(): void
    {
        $user1 = User::factory()->vendor()->create();
        $vendor1 = Vendor::factory()->create(['user_id' => $user1->id]);
        $product = Product::factory()->create(['vendor_id' => $vendor1->id]);

        $user2 = User::factory()->vendor()->create();
        Vendor::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($user2, 'sanctum')->putJson("/api/v1/vendor/products/{$product->id}", [
            'name' => 'Malicious Update',
        ]);

        $response->assertStatus(403);
    }
}
