<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InventoryTransactionResource;
use App\Http\Resources\ProductResource;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    use ApiResponse;

    /**
     * Get vendor inventory summary (products with stock quantities).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isVendor() || !$user->vendor) {
            return $this->error('Vendor profile not found', [], 403);
        }

        $vendorId = $user->vendor->id;
        $perPage = (int) $request->query('per_page', 15);

        $query = Product::where('vendor_id', $vendorId)->with(['category', 'images']);

        if ($request->filled('low_stock')) {
            $threshold = (int) $request->query('low_stock', 5);
            $query->where('stock', '<=', $threshold);
        }

        $products = $query->orderBy('stock', 'asc')->paginate($perPage);

        return $this->paginated(ProductResource::collection($products), 'Inventory retrieved successfully');
    }

    /**
     * Get vendor inventory transaction audit trail.
     */
    public function transactions(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isVendor() || !$user->vendor) {
            return $this->error('Vendor profile not found', [], 403);
        }

        $vendorId = $user->vendor->id;
        $perPage = (int) $request->query('per_page', 20);

        $transactions = InventoryTransaction::where('vendor_id', $vendorId)
            ->with(['product'])
            ->latest()
            ->paginate($perPage);

        return $this->paginated(InventoryTransactionResource::collection($transactions), 'Inventory transactions retrieved successfully');
    }
}
