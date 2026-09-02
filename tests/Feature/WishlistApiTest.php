<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_add_item_to_wishlist_without_duplicates(): void
    {
        $customer = User::factory()->customer()->create();
        $product = Product::factory()->create();

        // First add
        $res1 = $this->actingAs($customer, 'sanctum')->postJson('/api/v1/wishlist/items', [
            'product_id' => $product->id,
        ]);
        $res1->assertCreated()->assertJsonPath('success', true);

        // Duplicate add attempt
        $res2 = $this->actingAs($customer, 'sanctum')->postJson('/api/v1/wishlist/items', [
            'product_id' => $product->id,
        ]);
        $res2->assertCreated();

        $this->assertDatabaseCount('wishlists', 1);
    }

    public function test_customer_can_view_and_remove_wishlist_items(): void
    {
        $customer = User::factory()->customer()->create();
        $product = Product::factory()->create();
        Wishlist::create(['user_id' => $customer->id, 'product_id' => $product->id]);

        $listRes = $this->actingAs($customer, 'sanctum')->getJson('/api/v1/wishlist');
        $listRes->assertOk()->assertJsonCount(1, 'data');

        $delRes = $this->actingAs($customer, 'sanctum')->deleteJson("/api/v1/wishlist/items/{$product->id}");
        $delRes->assertNoContent();

        $this->assertDatabaseMissing('wishlists', ['product_id' => $product->id]);
    }
}
