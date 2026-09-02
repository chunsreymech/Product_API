<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCustomerProfileRequest;
use App\Http\Resources\UserResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CustomerProfileController extends Controller
{
    use ApiResponse;

    /**
     * Get customer profile with addresses.
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user()->load('addresses');

        return $this->success(new UserResource($user), 'Customer profile retrieved successfully');
    }

    /**
     * Update customer profile.
     */
    public function updateProfile(UpdateCustomerProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return $this->success(new UserResource($user->fresh('addresses')), 'Customer profile updated successfully');
    }
}
