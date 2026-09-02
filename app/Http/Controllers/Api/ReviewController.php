<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    use ApiResponse;

    /**
     * List approved reviews for a product.
     */
    public function indexForProduct(Product $product, Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $reviews = $product->reviews()
            ->where('status', 'approved')
            ->with('customer')
            ->latest()
            ->paginate($perPage);

        return $this->paginated(ReviewResource::collection($reviews), 'Reviews retrieved successfully');
    }

    /**
     * Submit a review for a purchased product.
     */
    public function store(StoreReviewRequest $request, Product $product): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        // Check if customer purchased this product in the specified order
        $order = Order::where('id', $data['order_id'])
            ->where('user_id', $user->id)
            ->first();

        if (!$order) {
            return $this->error('Order not found or does not belong to you', [
                'order_id' => ['The specified order is invalid.'],
            ], 422);
        }

        $purchased = $order->items()->where('product_id', $product->id)->exists();

        if (!$purchased) {
            return $this->error('You can only review products that you have purchased in this order', [
                'product_id' => ['This product was not part of the specified order.'],
            ], 422);
        }

        // Check if already reviewed for this order
        $existing = Review::where('customer_id', $user->id)
            ->where('product_id', $product->id)
            ->where('order_id', $order->id)
            ->first();

        if ($existing) {
            return $this->error('You have already submitted a review for this product on this order', [], 422);
        }

        $review = Review::create([
            'customer_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'status' => 'approved',
        ]);

        return $this->success(new ReviewResource($review->load(['customer', 'product'])), 'Review submitted successfully', 201);
    }

    /**
     * Update review.
     */
    public function update(UpdateReviewRequest $request, Review $review): JsonResponse
    {
        $user = $request->user();

        if ($review->customer_id !== $user->id && !$user->isAdmin()) {
            return $this->error('Unauthorized to update this review', [], 403);
        }

        $data = $request->validated();
        $review->update($data);

        return $this->success(new ReviewResource($review->load(['customer', 'product'])), 'Review updated successfully');
    }

    /**
     * Delete review.
     */
    public function destroy(Request $request, Review $review): JsonResponse
    {
        $user = $request->user();

        if ($review->customer_id !== $user->id && !$user->isAdmin()) {
            return $this->error('Unauthorized to delete this review', [], 403);
        }

        $review->delete();

        return $this->noContent();
    }
}
