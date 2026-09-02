<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderAndPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_checkout_and_place_order(): void
    {
        $customer = User::factory()->customer()->create();
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->create([
            'vendor_id' => $vendor->id,
            'price' => 100,
            'discount_price' => null,
            'stock' => 10,
            'status' => 'active',
        ]);

        $cart = Cart::factory()->create(['user_id' => $customer->id]);
        $cart->items()->create(['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 100]);

        $response = $this->actingAs($customer, 'sanctum')->postJson('/api/v1/customer/orders', [
            'shipping_address' => 'No. 22, Street 310, BKK1, Phnom Penh',
            'payment_method' => 'cash_on_delivery',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.subtotal', 200)
            ->assertJsonPath('data.status', 'pending');

        // Stock decreased
        $this->assertEquals(8, $product->fresh()->stock);

        // Inventory transaction logged
        $this->assertDatabaseHas('inventory_transactions', [
            'product_id' => $product->id,
            'type' => 'order_deduction',
            'quantity' => -2,
        ]);

        // Cart emptied
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_customer_can_cancel_pending_order_and_stock_is_restored(): void
    {
        $customer = User::factory()->customer()->create();
        $product = Product::factory()->create(['stock' => 5]);
        $order = Order::factory()->create(['user_id' => $customer->id, 'status' => 'pending']);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $response = $this->actingAs($customer, 'sanctum')->postJson("/api/v1/customer/orders/{$order->id}/cancel");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'cancelled');

        // Stock restored from 5 to 8
        $this->assertEquals(8, $product->fresh()->stock);
        $this->assertDatabaseHas('inventory_transactions', [
            'product_id' => $product->id,
            'type' => 'order_restoration',
            'quantity' => 3,
        ]);
    }

    public function test_customer_can_process_demo_payment(): void
    {
        $customer = User::factory()->customer()->create();
        $order = Order::factory()->create(['user_id' => $customer->id, 'status' => 'pending']);

        $response = $this->actingAs($customer, 'sanctum')->postJson("/api/v1/customer/orders/{$order->id}/payment", [
            'method' => 'demo_card',
            'card_number' => '4242424242424242',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'paid')
            ->assertJsonPath('data.method', 'demo_card');

        $this->assertEquals('confirmed', $order->fresh()->status);
    }

    public function test_vendor_and_admin_order_management(): void
    {
        $vendorUser = User::factory()->vendor()->create();
        $vendor = Vendor::factory()->create(['user_id' => $vendorUser->id]);
        $order = Order::factory()->create(['status' => 'pending']);
        OrderItem::factory()->create(['order_id' => $order->id, 'vendor_id' => $vendor->id]);

        // Vendor views order
        $vendorRes = $this->actingAs($vendorUser, 'sanctum')->getJson('/api/v1/vendor/orders');
        $vendorRes->assertOk()->assertJsonCount(1, 'data');

        // Vendor updates status
        $statusRes = $this->actingAs($vendorUser, 'sanctum')->putJson("/api/v1/vendor/orders/{$order->id}/status", [
            'status' => 'processing',
        ]);
        $statusRes->assertOk()->assertJsonPath('data.status', 'processing');

        // Admin updates status
        $admin = User::factory()->admin()->create();
        $adminRes = $this->actingAs($admin, 'sanctum')->putJson("/api/v1/admin/orders/{$order->id}/status", [
            'status' => 'shipped',
        ]);
        $adminRes->assertOk()->assertJsonPath('data.status', 'shipped');
    }
}
