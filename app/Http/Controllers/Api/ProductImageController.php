<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductImageRequest;
use App\Http\Resources\ProductImageResource;
use App\Models\Product;
use App\Models\ProductImage;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductImageController extends Controller
{
    use ApiResponse;

    /**
     * Upload / Add an image to a product.
     */
    public function store(StoreProductImageRequest $request, Product $product): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && (!$product->vendor || $product->vendor->user_id !== $user->id)) {
            return $this->error('Unauthorized to add images to this product', [], 403);
        }

        $data = $request->validated();
        $isPrimary = !empty($data['is_primary']);

        if ($isPrimary) {
            $product->images()->update(['is_primary' => false]);
        }

        $image = $product->images()->create([
            'path' => $data['path'],
            'is_primary' => $isPrimary || $product->images()->count() === 0,
        ]);

        return $this->success(new ProductImageResource($image), 'Product image added successfully', 201);
    }

    /**
     * Remove an image from a product.
     */
    public function destroy(Request $request, Product $product, ProductImage $image): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && (!$product->vendor || $product->vendor->user_id !== $user->id)) {
            return $this->error('Unauthorized to delete images for this product', [], 403);
        }

        if ($image->product_id !== $product->id) {
            return $this->error('Image does not belong to this product', [], 400);
        }

        $image->delete();

        return $this->noContent();
    }
}
