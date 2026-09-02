<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    use ApiResponse;

    /**
     * List all orders in the system with optional status filtering.
     */
    public function index(Request $request): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return $this->error('Admin access required', [], 403);
        }

        $perPage = (int) $request->query('per_page', 15);
        $query = Order::with(['user', 'items.product', 'items.vendor', 'payment'])->latest();

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->paginate($perPage);

        return $this->paginated(OrderResource::collection($orders), 'Orders retrieved successfully');
    }

    /**
     * Show full order details for admin.
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return $this->error('Admin access required', [], 403);
        }

        $order->load(['user', 'items.product.images', 'items.vendor', 'payment', 'reviews']);

        return $this->success(new OrderResource($order), 'Order retrieved successfully');
    }

    /**
     * Update order status by admin.
     */
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return $this->error('Admin access required', [], 403);
        }

        $data = $request->validated();
        $order->update(['status' => $data['status']]);

        return $this->success(new OrderResource($order->fresh(['user', 'items.product', 'payment'])), 'Order status updated successfully');
    }
}
