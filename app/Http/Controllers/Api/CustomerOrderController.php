<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\Payment;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerOrderController extends Controller
{
    use ApiResponse;

    /**
     * List customer's orders.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $orders = $request->user()->orders()
            ->with(['items.product', 'items.vendor', 'payment'])
            ->latest()
            ->paginate($perPage);

        return $this->paginated(OrderResource::collection($orders), 'Orders retrieved successfully');
    }

    /**
     * Checkout: Place a new order from current cart.
     */
    public function store(CreateOrderRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $cart = Cart::where('user_id', $user->id)->with(['items.product.vendor', 'coupon'])->first();

        if (!$cart || $cart->items->isEmpty()) {
            return $this->error('Shopping cart is empty', [
                'cart' => ['Please add items to your cart before placing an order.'],
            ], 422);
        }

        // Determine shipping address
        $shippingAddress = $data['shipping_address'] ?? null;
        if (!empty($data['shipping_address_id'])) {
            $address = Address::where('user_id', $user->id)->where('id', $data['shipping_address_id'])->first();
            if ($address) {
                $shippingAddress = "{$address->label}: {$address->address}, {$address->city} (Phone: {$address->phone})";
            }
        }

        if (empty($shippingAddress)) {
            $defaultAddress = $user->addresses()->where('is_default', true)->first() ?? $user->addresses()->first();
            if ($defaultAddress) {
                $shippingAddress = "{$defaultAddress->label}: {$defaultAddress->address}, {$defaultAddress->city} (Phone: {$defaultAddress->phone})";
            } else {
                return $this->error('Shipping address is required', [
                    'shipping_address' => ['Please provide a shipping address or select a saved address.'],
                ], 422);
            }
        }

        // Validate stock for all items
        foreach ($cart->items as $cartItem) {
            $product = $cartItem->product;
            if (!$product || $product->status !== 'active') {
                return $this->error("Product '{$cartItem->product_id}' is no longer available", [], 422);
            }
            if ($cartItem->quantity > $product->stock) {
                return $this->error("Insufficient stock for '{$product->name}'", [
                    'stock' => ["Only {$product->stock} items available."],
                ], 422);
            }
        }

        $totals = $cart->calculateTotals();
        $paymentMethod = $data['payment_method'] ?? 'cash_on_delivery';

        return DB::transaction(function () use ($user, $cart, $totals, $shippingAddress, $paymentMethod, $data) {
            // Create Order
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'ORD-' . strtoupper(Str::random(10)),
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'tax' => $totals['tax'],
                'shipping' => $totals['shipping'],
                'grand_total' => $totals['grand_total'],
                'status' => 'pending',
                'shipping_address' => $shippingAddress,
                'payment_method' => $paymentMethod,
                'payment_status' => 'pending',
                'notes' => $data['notes'] ?? null,
            ]);

            // Create Order Items and update stock
            foreach ($cart->items as $cartItem) {
                $product = $cartItem->product;
                $unitPrice = $product->sale_price;
                $totalPrice = round($unitPrice * $cartItem->quantity, 2);

                $order->items()->create([
                    'product_id' => $product->id,
                    'vendor_id' => $product->vendor_id,
                    'product_name' => $product->name,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                ]);

                // Decrement product stock
                $product->decrement('stock', $cartItem->quantity);

                // Record inventory transaction
                InventoryTransaction::create([
                    'product_id' => $product->id,
                    'vendor_id' => $product->vendor_id,
                    'quantity' => -$cartItem->quantity,
                    'type' => 'order_deduction',
                    'reference_type' => Order::class,
                    'reference_id' => $order->id,
                    'notes' => "Deduction for Order #{$order->order_number}",
                ]);
            }

            // If coupon applied, increment usage count
            if ($cart->coupon_code) {
                Coupon::where('code', $cart->coupon_code)->increment('used_count');
            }

            // Create Payment record
            Payment::create([
                'order_id' => $order->id,
                'method' => $paymentMethod,
                'status' => 'pending',
                'amount' => $totals['grand_total'],
            ]);

            // Clear the cart
            $cart->items()->delete();
            $cart->update(['coupon_code' => null]);

            $order->load(['items.product', 'items.vendor', 'payment']);

            return $this->success(new OrderResource($order), 'Order placed successfully', 201);
        });
    }

    /**
     * Show single order details for customer.
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return $this->error('Unauthorized to view this order', [], 403);
        }

        $order->load(['items.product.images', 'items.vendor', 'payment', 'reviews']);

        return $this->success(new OrderResource($order), 'Order retrieved successfully');
    }

    /**
     * Customer: Cancel order and restore product stock.
     */
    public function cancel(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();

        if ($order->user_id !== $user->id && !$user->isAdmin()) {
            return $this->error('Unauthorized to cancel this order', [], 403);
        }

        if (!$order->canBeCancelled()) {
            return $this->error("Order with status '{$order->status}' cannot be cancelled", [], 400);
        }

        return DB::transaction(function () use ($order) {
            $order->load('items.product');

            // Restore stock for all items
            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock', $item->quantity);

                    InventoryTransaction::create([
                        'product_id' => $item->product_id,
                        'vendor_id' => $item->vendor_id,
                        'quantity' => $item->quantity,
                        'type' => 'order_restoration',
                        'reference_type' => Order::class,
                        'reference_id' => $order->id,
                        'notes' => "Restoration on cancellation of Order #{$order->order_number}",
                    ]);
                }
            }

            $order->update(['status' => 'cancelled']);

            if ($order->payment && $order->payment->status === 'paid') {
                $order->payment->update(['status' => 'refunded']);
            }

            return $this->success(new OrderResource($order->fresh(['items.product', 'payment'])), 'Order cancelled successfully');
        });
    }
}
