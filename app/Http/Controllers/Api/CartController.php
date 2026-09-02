<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddCartItemRequest;
use App\Http\Requests\ApplyCouponRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    use ApiResponse;

    /**
     * Get or initialize customer cart.
     */
    private function getOrCreateCart(Request $request): Cart
    {
        return Cart::firstOrCreate(
            ['user_id' => $request->user()->id],
            ['coupon_code' => null]
        );
    }

    /**
     * View current shopping cart.
     */
    public function index(Request $request): JsonResponse
    {
        $cart = $this->getOrCreateCart($request);
        $cart->load(['items.product.category', 'items.product.vendor', 'items.product.images', 'coupon']);

        return $this->success(new CartResource($cart), 'Cart retrieved successfully');
    }

    /**
     * Add item to cart with stock validation.
     */
    public function addItem(AddCartItemRequest $request): JsonResponse
    {
        $data = $request->validated();
        $product = Product::findOrFail($data['product_id']);

        if ($product->status !== 'active') {
            return $this->error('This product is currently unavailable', [], 400);
        }

        $cart = $this->getOrCreateCart($request);
        $existingItem = $cart->items()->where('product_id', $product->id)->first();
        $targetQuantity = $existingItem ? $existingItem->quantity + $data['quantity'] : $data['quantity'];

        if ($targetQuantity > $product->stock) {
            return $this->error('Insufficient stock available', [
                'quantity' => ["Only {$product->stock} items available in stock."],
            ], 422);
        }

        if ($existingItem) {
            $existingItem->update([
                'quantity' => $targetQuantity,
                'unit_price' => $product->sale_price,
            ]);
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => $data['quantity'],
                'unit_price' => $product->sale_price,
            ]);
        }

        $cart->load(['items.product.category', 'items.product.vendor', 'items.product.images', 'coupon']);

        return $this->success(new CartResource($cart), 'Item added to cart successfully', 201);
    }

    /**
     * Update quantity of a cart item with stock check.
     */
    public function updateItem(UpdateCartItemRequest $request, CartItem $item): JsonResponse
    {
        $cart = $this->getOrCreateCart($request);

        if ($item->cart_id !== $cart->id) {
            return $this->error('Unauthorized to update this cart item', [], 403);
        }

        $data = $request->validated();
        $product = $item->product;

        if ($data['quantity'] > $product->stock) {
            return $this->error('Insufficient stock available', [
                'quantity' => ["Only {$product->stock} items available in stock."],
            ], 422);
        }

        $item->update([
            'quantity' => $data['quantity'],
            'unit_price' => $product->sale_price,
        ]);

        $cart->load(['items.product.category', 'items.product.vendor', 'items.product.images', 'coupon']);

        return $this->success(new CartResource($cart), 'Cart item updated successfully');
    }

    /**
     * Remove item from cart.
     */
    public function removeItem(Request $request, CartItem $item): JsonResponse
    {
        $cart = $this->getOrCreateCart($request);

        if ($item->cart_id !== $cart->id) {
            return $this->error('Unauthorized to delete this cart item', [], 403);
        }

        $item->delete();

        return $this->noContent();
    }

    /**
     * Clear all items from cart.
     */
    public function clear(Request $request): JsonResponse
    {
        $cart = $this->getOrCreateCart($request);
        $cart->items()->delete();
        $cart->update(['coupon_code' => null]);

        return $this->noContent();
    }

    /**
     * Apply coupon code to cart.
     */
    public function applyCoupon(ApplyCouponRequest $request): JsonResponse
    {
        $data = $request->validated();
        $cart = $this->getOrCreateCart($request);
        $cart->load(['items.product']);

        $totals = $cart->calculateTotals();
        $coupon = Coupon::where('code', $data['code'])->first();

        if (!$coupon || !$coupon->isValid($totals['subtotal'])) {
            return $this->error('Coupon is invalid or minimum order requirement not met', [
                'code' => ['The specified coupon cannot be applied to your current cart.'],
            ], 422);
        }

        $cart->update(['coupon_code' => $coupon->code]);
        $cart->load(['items.product.category', 'items.product.vendor', 'items.product.images', 'coupon']);

        return $this->success(new CartResource($cart), 'Coupon applied successfully');
    }

    /**
     * Remove coupon code from cart.
     */
    public function removeCoupon(Request $request): JsonResponse
    {
        $cart = $this->getOrCreateCart($request);
        $cart->update(['coupon_code' => null]);
        $cart->load(['items.product.category', 'items.product.vendor', 'items.product.images', 'coupon']);

        return $this->success(new CartResource($cart), 'Coupon removed successfully');
    }
}
