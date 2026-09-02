<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WishlistResource;
use App\Models\Product;
use App\Models\Wishlist;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    use ApiResponse;

    /**
     * List all items in customer's wishlist.
     */
    public function index(Request $request): JsonResponse
    {
        $items = $request->user()->wishlists()->with(['product.category', 'product.vendor', 'product.images'])->latest()->get();

        return $this->success(WishlistResource::collection($items), 'Wishlist retrieved successfully');
    }

    /**
     * Add product to wishlist (preventing duplicates).
     */
    public function addItem(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $user = $request->user();

        $wishlist = Wishlist::firstOrCreate([
            'user_id' => $user->id,
            'product_id' => $data['product_id'],
        ]);

        $wishlist->load(['product.category', 'product.vendor', 'product.images']);

        return $this->success(new WishlistResource($wishlist), 'Product added to wishlist successfully', 201);
    }

    /**
     * Remove product from wishlist.
     */
    public function removeItem(Request $request, int|string $product): JsonResponse
    {
        $productId = is_numeric($product) ? (int) $product : Product::where('slug', $product)->value('id');

        $request->user()->wishlists()->where('product_id', $productId)->delete();

        return $this->noContent();
    }

    /**
     * Clear all wishlist items.
     */
    public function clear(Request $request): JsonResponse
    {
        $request->user()->wishlists()->delete();

        return $this->noContent();
    }
}
