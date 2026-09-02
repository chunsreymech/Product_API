<?php

namespace Tests\Feature;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorAndInventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_can_view_and_update_vendor_profile(): void
    {
        $user = User::factory()->vendor()->create();
        $vendor = Vendor::factory()->create(['user_id' => $user->id, 'shop_name' => 'Original Shop']);

        $res = $this->actingAs($user, 'sanctum')->getJson('/api/v1/vendor/profile');
        $res->assertOk()->assertJsonPath('data.shop_name', 'Original Shop');

        $updRes = $this->actingAs($user, 'sanctum')->putJson('/api/v1/vendor/profile', [
            'shop_name' => 'Updated Shop',
            'phone' => '+855 12 000 111',
        ]);
        $updRes->assertOk()->assertJsonPath('data.shop_name', 'Updated Shop');
    }

    public function test_vendor_can_view_inventory_and_transactions(): void
    {
        $user = User::factory()->vendor()->create();
        $vendor = Vendor::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create(['vendor_id' => $vendor->id, 'stock' => 15]);

        InventoryTransaction::factory()->create([
            'product_id' => $product->id,
            'vendor_id' => $vendor->id,
            'quantity' => 15,
            'type' => 'stock_in',
        ]);

        $invRes = $this->actingAs($user, 'sanctum')->getJson('/api/v1/vendor/inventory');
        $invRes->assertOk()->assertJsonCount(1, 'data');

        $txRes = $this->actingAs($user, 'sanctum')->getJson('/api/v1/vendor/inventory/transactions');
        $txRes->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_vendor_can_manage_product_images(): void
    {
        $user = User::factory()->vendor()->create();
        $vendor = Vendor::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create(['vendor_id' => $vendor->id]);

        $imgRes = $this->actingAs($user, 'sanctum')->postJson("/api/v1/vendor/products/{$product->id}/images", [
            'path' => 'https://example.com/photo.jpg',
            'is_primary' => true,
        ]);
        $imgRes->assertCreated()->assertJsonPath('success', true);

        $imageId = $imgRes->json('data.id');

        $delRes = $this->actingAs($user, 'sanctum')->deleteJson("/api/v1/vendor/products/{$product->id}/images/{$imageId}");
        $delRes->assertNoContent();
    }
}
