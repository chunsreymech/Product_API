<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    use ApiResponse;

    /**
     * List all categories with filtering and sorting.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Category::query()->withCount('products');

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        } else {
            $query->where('status', 'active');
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $sort = $request->query('sort', 'name');
        $direction = $request->query('direction', 'asc');
        $allowedSorts = ['name', 'created_at', 'id'];

        if (in_array($sort, $allowedSorts, true)) {
            $query->orderBy($sort, strtolower($direction) === 'desc' ? 'desc' : 'asc');
        }

        $perPage = (int) $request->query('per_page', 15);
        $categories = $query->paginate($perPage);

        return $this->paginated(CategoryResource::collection($categories), 'Categories retrieved successfully');
    }

    /**
     * Get single category details.
     */
    public function show(Category $category): JsonResponse
    {
        $category->load(['products' => function ($q) {
            $q->where('status', 'active')->with(['vendor', 'images'])->limit(10);
        }]);

        return $this->success(new CategoryResource($category), 'Category retrieved successfully');
    }

    /**
     * Get paginated products for a category.
     */
    public function products(Category $category, Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $products = $category->products()
            ->where('status', 'active')
            ->with(['vendor', 'category', 'images'])
            ->latest()
            ->paginate($perPage);

        return $this->paginated(ProductResource::collection($products), 'Category products retrieved successfully');
    }

    /**
     * Admin: create new category.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return $this->error('Admin access required', [], 403);
        }

        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']);
        $data['status'] = $data['status'] ?? 'active';

        $category = Category::create($data);

        return $this->success(new CategoryResource($category), 'Category created successfully', 201);
    }

    /**
     * Admin: update category.
     */
    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return $this->error('Admin access required', [], 403);
        }

        $data = $request->validated();
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $category->update($data);

        return $this->success(new CategoryResource($category), 'Category updated successfully');
    }

    /**
     * Admin: delete category.
     */
    public function destroy(Request $request, Category $category): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return $this->error('Admin access required', [], 403);
        }

        $category->delete();

        return $this->noContent();
    }
}
