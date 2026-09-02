<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorOrderController extends Controller
{
    use ApiResponse;

    /**
     * List all orders containing the authenticated vendor's products.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isVendor() || !$user->vendor) {
            return $this->error('Vendor profile not found', [], 403);
        }

        $vendorId = $user->vendor->id;
        $perPage = (int) $request->query('per_page', 15);

        $query = Order::whereHas('items', function ($q) use ($vendorId) {
            $q->where('vendor_id', $vendorId);
        })->with(['user', 'items' => function ($q) use ($vendorId) {
            $q->where('vendor_id', $vendorId)->with('product');
        }, 'payment'])->latest();

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $orders = $query->paginate($perPage);

        return $this->paginated(OrderResource::collection($orders), 'Vendor orders retrieved successfully');
    }

    /**
     * Show details of a vendor's order.
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();

        if (!$user->isVendor() || !$user->vendor) {
            return $this->error('Vendor profile not found', [], 403);
        }

        $vendorId = $user->vendor->id;
        $hasItems = $order->items()->where('vendor_id', $vendorId)->exists();

        if (!$hasItems) {
            return $this->error('Unauthorized to view this order', [], 403);
        }

        $order->load(['user', 'items' => function ($q) use ($vendorId) {
            $q->where('vendor_id', $vendorId)->with('product');
        }, 'payment']);

        return $this->success(new OrderResource($order), 'Order retrieved successfully');
    }

    /**
     * Update order status by vendor.
     */
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        $user = $request->user();

        if (!$user->isVendor() || !$user->vendor) {
            return $this->error('Vendor profile not found', [], 403);
        }

        $vendorId = $user->vendor->id;
        $hasItems = $order->items()->where('vendor_id', $vendorId)->exists();

        if (!$hasItems) {
            return $this->error('Unauthorized to update this order', [], 403);
        }

        $data = $request->validated();
        $order->update(['status' => $data['status']]);

        return $this->success(new OrderResource($order->fresh(['user', 'items.product', 'payment'])), 'Order status updated successfully');
    }
}
