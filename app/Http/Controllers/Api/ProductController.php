<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    use ApiResponse;

    /**
     * Build product query based on filter parameters.
     */
    private function buildProductQuery(Request $request)
    {
        $query = Product::with(['category', 'vendor', 'images', 'reviews'])
            ->where('status', 'active');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->query('category_id'));
        }

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->query('vendor_id'));
        }

        if ($request->has('featured')) {
            $featured = filter_var($request->query('featured'), FILTER_VALIDATE_BOOLEAN);
            $query->where('featured', $featured);
        }

        if ($request->filled('min_price')) {
            $minPrice = (float) $request->query('min_price');
            $query->where(function ($q) use ($minPrice) {
                $q->where(fn ($sq) => $sq->whereNotNull('discount_price')->where('discount_price', '>=', $minPrice))
                  ->orWhere(fn ($sq) => $sq->whereNull('discount_price')->where('price', '>=', $minPrice));
            });
        }

        if ($request->filled('max_price')) {
            $maxPrice = (float) $request->query('max_price');
            $query->where(function ($q) use ($maxPrice) {
                $q->where(fn ($sq) => $sq->whereNotNull('discount_price')->where('discount_price', '<=', $maxPrice))
                  ->orWhere(fn ($sq) => $sq->whereNull('discount_price')->where('price', '<=', $maxPrice));
            });
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $sort = $request->query('sort', 'created_at');
        $direction = strtolower($request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        if ($sort === 'price') {
            $query->orderByRaw('COALESCE(discount_price, price) ' . $direction);
        } elseif (in_array($sort, ['name', 'created_at', 'stock', 'featured'], true)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->latest();
        }

        return $query;
    }

    /**
     * List all products with advanced filtering and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $products = $this->buildProductQuery($request)->paginate($perPage);

        return $this->paginated(ProductResource::collection($products), 'Products retrieved successfully');
    }

    /**
     * Dedicated product search endpoint.
     */
    public function search(Request $request): JsonResponse
    {
        return $this->index($request);
    }

    /**
     * Get single product details.
     */
    public function show(Product $product): JsonResponse
    {
        $product->load([
            'category',
            'vendor.user',
            'images',
            'reviews' => function ($q) {
                $q->where('status', 'approved')->with('customer')->latest();
            },
        ]);

        return $this->success(new ProductResource($product), 'Product retrieved successfully');
    }

    /**
     * Get related products in the same category.
     */
    public function related(Product $product, Request $request): JsonResponse
    {
        $limit = (int) $request->query('limit', 6);
        $related = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'active')
            ->with(['category', 'vendor', 'images'])
            ->inRandomOrder()
            ->limit($limit)
            ->get();

        return $this->success(ProductResource::collection($related), 'Related products retrieved successfully');
    }

    /**
     * Vendor/Admin: Create a new product.
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isVendor() && !$user->isAdmin()) {
            return $this->error('Only vendors and admins can create products', [], 403);
        }

        $data = $request->validated();

        if ($user->isVendor()) {
            $vendor = $user->vendor;
            if (!$vendor) {
                return $this->error('Vendor profile not found for this user', [], 400);
            }
            $data['vendor_id'] = $vendor->id;
        } elseif ($user->isAdmin()) {
            if (empty($data['vendor_id'])) {
                return $this->error('Admin must provide a vendor_id for the product', [
                    'vendor_id' => ['The vendor_id field is required for admin product creation.'],
                ], 422);
            }
        }

        $data['slug'] = Str::slug($data['name']) . '-' . Str::lower(Str::random(6));
        $data['status'] = $data['status'] ?? 'active';
        $data['featured'] = $data['featured'] ?? false;

        $product = Product::create($data);

        // Record initial inventory transaction if stock > 0
        if ($product->stock > 0) {
            InventoryTransaction::create([
                'product_id' => $product->id,
                'vendor_id' => $product->vendor_id,
                'quantity' => $product->stock,
                'type' => 'stock_in',
                'notes' => 'Initial stock on product creation',
            ]);
        }

        return $this->success(new ProductResource($product->load(['category', 'vendor', 'images'])), 'Product created successfully', 201);
    }

    /**
     * Vendor/Admin: Update product.
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && (!$product->vendor || $product->vendor->user_id !== $user->id)) {
            return $this->error('Unauthorized to update this product', [], 403);
        }

        $data = $request->validated();
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']) . '-' . $product->id;
        }

        $oldStock = $product->stock;
        $product->update($data);

        // Track stock changes
        if (isset($data['stock']) && $data['stock'] !== $oldStock) {
            $diff = $data['stock'] - $oldStock;
            InventoryTransaction::create([
                'product_id' => $product->id,
                'vendor_id' => $product->vendor_id,
                'quantity' => abs($diff),
                'type' => $diff > 0 ? 'stock_in' : 'stock_out',
                'notes' => 'Manual stock update by vendor/admin',
            ]);
        }

        return $this->success(new ProductResource($product->fresh(['category', 'vendor', 'images'])), 'Product updated successfully');
    }

    /**
     * Vendor/Admin: Delete product.
     */
    public function destroy(Request $request, Product $product): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && (!$product->vendor || $product->vendor->user_id !== $user->id)) {
            return $this->error('Unauthorized to delete this product', [], 403);
        }

        $product->delete();

        return $this->noContent();
    }
}
