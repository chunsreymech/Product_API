<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerAddressController extends Controller
{
    use ApiResponse;

    /**
     * List all customer addresses.
     */
    public function index(Request $request): JsonResponse
    {
        $addresses = $request->user()->addresses()->orderBy('is_default', 'desc')->get();

        return $this->success(AddressResource::collection($addresses), 'Addresses retrieved successfully');
    }

    /**
     * Create a new address for the customer.
     */
    public function store(StoreAddressRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if (!empty($data['is_default'])) {
            $user->addresses()->update(['is_default' => false]);
        } else {
            $data['is_default'] = $user->addresses()->count() === 0;
        }

        $address = $user->addresses()->create($data);

        return $this->success(new AddressResource($address), 'Address created successfully', 201);
    }

    /**
     * Get specific address.
     */
    public function show(Request $request, Address $address): JsonResponse
    {
        if ($address->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return $this->error('Unauthorized to view this address', [], 403);
        }

        return $this->success(new AddressResource($address), 'Address retrieved successfully');
    }

    /**
     * Update customer address.
     */
    public function update(UpdateAddressRequest $request, Address $address): JsonResponse
    {
        $user = $request->user();

        if ($address->user_id !== $user->id && !$user->isAdmin()) {
            return $this->error('Unauthorized to update this address', [], 403);
        }

        $data = $request->validated();

        if (!empty($data['is_default'])) {
            $user->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update($data);

        return $this->success(new AddressResource($address), 'Address updated successfully');
    }

    /**
     * Delete customer address.
     */
    public function destroy(Request $request, Address $address): JsonResponse
    {
        $user = $request->user();

        if ($address->user_id !== $user->id && !$user->isAdmin()) {
            return $this->error('Unauthorized to delete this address', [], 403);
        }

        $address->delete();

        return $this->noContent();
    }
}
