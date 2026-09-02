<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_review_purchased_product_only(): void
    {
        $customer = User::factory()->customer()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['user_id' => $customer->id]);
        OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $product->id]);

        $response = $this->actingAs($customer, 'sanctum')->postJson("/api/v1/products/{$product->id}/reviews", [
            'order_id' => $order->id,
            'rating' => 5,
            'comment' => 'Outstanding quality product!',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.rating', 5);

        // Try to review a product not purchased
        $unpurchasedProduct = Product::factory()->create();
        $badResponse = $this->actingAs($customer, 'sanctum')->postJson("/api/v1/products/{$unpurchasedProduct->id}/reviews", [
            'order_id' => $order->id,
            'rating' => 4,
        ]);

        $badResponse->assertStatus(422);
    }

    public function test_customer_can_update_and_delete_own_review(): void
    {
        $customer = User::factory()->customer()->create();
        $review = Review::factory()->create(['customer_id' => $customer->id, 'rating' => 4]);

        $updRes = $this->actingAs($customer, 'sanctum')->putJson("/api/v1/reviews/{$review->id}", [
            'rating' => 5,
            'comment' => 'Updated comment',
        ]);
        $updRes->assertOk()->assertJsonPath('data.rating', 5);

        $delRes = $this->actingAs($customer, 'sanctum')->deleteJson("/api/v1/reviews/{$review->id}");
        $delRes->assertNoContent();
    }
}
