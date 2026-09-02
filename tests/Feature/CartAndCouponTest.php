<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartAndCouponTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_add_item_to_cart_with_stock_validation(): void
    {
        $customer = User::factory()->customer()->create();
        $product = Product::factory()->create(['price' => 50, 'discount_price' => null, 'stock' => 10, 'status' => 'active']);

        $response = $this->actingAs($customer, 'sanctum')->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.subtotal', 100)
            ->assertJsonPath('data.tax', 10)
            ->assertJsonPath('data.shipping', 0)
            ->assertJsonPath('data.grand_total', 110);

        // Test overstock failure
        $overResponse = $this->actingAs($customer, 'sanctum')->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'quantity' => 15,
        ]);

        $overResponse->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_customer_can_update_and_remove_cart_items(): void
    {
        $customer = User::factory()->customer()->create();
        $product = Product::factory()->create(['price' => 20, 'stock' => 15]);
        $cart = Cart::factory()->create(['user_id' => $customer->id]);
        $cartItem = $cart->items()->create(['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 20]);

        // Update quantity
        $updateRes = $this->actingAs($customer, 'sanctum')->putJson("/api/v1/cart/items/{$cartItem->id}", [
            'quantity' => 3,
        ]);
        $updateRes->assertOk()->assertJsonPath('data.subtotal', 60);

        // Remove item
        $delRes = $this->actingAs($customer, 'sanctum')->deleteJson("/api/v1/cart/items/{$cartItem->id}");
        $delRes->assertNoContent();
        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }

    public function test_customer_can_apply_and_remove_coupon(): void
    {
        $customer = User::factory()->customer()->create();
        $product = Product::factory()->create(['price' => 100, 'stock' => 10]);
        $cart = Cart::factory()->create(['user_id' => $customer->id]);
        $cart->items()->create(['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 100]);

        $coupon = Coupon::factory()->create([
            'code' => 'TEST20',
            'type' => 'percentage',
            'value' => 20,
            'minimum_order_amount' => 50,
            'status' => true,
        ]);

        $applyRes = $this->actingAs($customer, 'sanctum')->postJson('/api/v1/cart/apply-coupon', [
            'code' => 'TEST20',
        ]);

        $applyRes->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.discount', 20)
            ->assertJsonPath('data.coupon_code', 'TEST20');

        $removeRes = $this->actingAs($customer, 'sanctum')->deleteJson('/api/v1/cart/coupon');
        $removeRes->assertOk()
            ->assertJsonPath('data.discount', 0)
            ->assertJsonPath('data.coupon_code', null);
    }
}
