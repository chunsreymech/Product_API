<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * Register a new user (customer or vendor).
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $role = $validated['role'] ?? 'customer';

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $role,
        ]);

        if ($role === 'vendor') {
            $shopName = $validated['shop_name'] ?? $user->name . ' Shop';
            $user->vendor()->create([
                'shop_name' => $shopName,
                'slug' => Str::slug($shopName) . '-' . $user->id,
                'email' => $user->email,
                'status' => 'active',
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'user' => new UserResource($user->load('vendor')),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'User registered successfully', 201);
    }

    /**
     * Authenticate user and issue API token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return $this->error('Invalid credentials', [
                'email' => ['The provided credentials do not match our records.'],
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'user' => new UserResource($user->load(['vendor', 'addresses'])),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Logged in successfully');
    }

    /**
     * Revoke authenticated user's current token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return $this->noContent();
    }

    /**
     * Get current authenticated user profile.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['vendor', 'addresses']);

        return $this->success(new UserResource($user), 'Authenticated user retrieved successfully');
    }
}
