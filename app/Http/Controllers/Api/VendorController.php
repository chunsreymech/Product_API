<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateVendorProfileRequest;
use App\Http\Resources\VendorResource;
use App\Models\Vendor;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VendorController extends Controller
{
    use ApiResponse;

    /**
     * List all public vendors.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Vendor::withCount('products')->where('status', 'active');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('shop_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->query('per_page', 15);
        $vendors = $query->paginate($perPage);

        return $this->paginated(VendorResource::collection($vendors), 'Vendors retrieved successfully');
    }

    /**
     * Get single vendor details with their active products.
     */
    public function show(Vendor $vendor): JsonResponse
    {
        $vendor->load(['products' => function ($q) {
            $q->where('status', 'active')->with(['category', 'images'])->latest();
        }]);

        return $this->success(new VendorResource($vendor), 'Vendor retrieved successfully');
    }

    /**
     * Get authenticated vendor's profile.
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isVendor() || !$user->vendor) {
            return $this->error('Vendor profile not found', [], 404);
        }

        $vendor = $user->vendor->load('user');

        return $this->success(new VendorResource($vendor), 'Vendor profile retrieved successfully');
    }

    /**
     * Update authenticated vendor's profile.
     */
    public function updateProfile(UpdateVendorProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isVendor() || !$user->vendor) {
            return $this->error('Vendor profile not found', [], 404);
        }

        $vendor = $user->vendor;
        $data = $request->validated();

        if (isset($data['shop_name'])) {
            $data['slug'] = Str::slug($data['shop_name']) . '-' . $vendor->id;
        }

        $vendor->update($data);

        return $this->success(new VendorResource($vendor->fresh('user')), 'Vendor profile updated successfully');
    }
}
