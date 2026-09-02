<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\VendorResource;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    use ApiResponse;

    /**
     * Get aggregated admin analytics and dashboard metrics.
     */
    public function index(Request $request): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return $this->error('Admin access required', [], 403);
        }

        $totalCustomers = User::where('role', 'customer')->count();
        $totalVendors = Vendor::count();
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $completedOrders = Order::where('status', 'delivered')->count();

        $totalSales = (float) Order::whereNotIn('status', ['cancelled', 'refunded'])->sum('grand_total');

        $recentOrders = Order::with(['user', 'items.product', 'payment'])
            ->latest()
            ->limit(5)
            ->get();

        // Top products by quantity sold
        $topProductIds = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->pluck('product_id');

        $topProducts = Product::whereIn('id', $topProductIds)->with(['category', 'vendor'])->get();

        // Top vendors by total revenue or items sold
        $topVendorIds = OrderItem::select('vendor_id', DB::raw('SUM(total_price) as total_revenue'))
            ->groupBy('vendor_id')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->pluck('vendor_id');

        $topVendors = Vendor::whereIn('id', $topVendorIds)->withCount('products')->get();

        return $this->success([
            'metrics' => [
                'total_customers' => $totalCustomers,
                'total_vendors' => $totalVendors,
                'total_products' => $totalProducts,
                'total_categories' => $totalCategories,
                'total_orders' => $totalOrders,
                'pending_orders' => $pendingOrders,
                'completed_orders' => $completedOrders,
                'total_sales' => round($totalSales, 2),
            ],
            'recent_orders' => OrderResource::collection($recentOrders),
            'top_products' => ProductResource::collection($topProducts),
            'top_vendors' => VendorResource::collection($topVendors),
        ], 'Admin dashboard metrics retrieved successfully');
    }
}
