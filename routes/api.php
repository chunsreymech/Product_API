<?php

use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\AdminOrderController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerAddressController;
use App\Http\Controllers\Api\CustomerOrderController;
use App\Http\Controllers\Api\CustomerProfileController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductImageController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\VendorController;
use App\Http\Controllers\Api\VendorOrderController;
use App\Http\Controllers\Api\WishlistController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // ==========================================
    // 1. Authentication
    // ==========================================
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('me', [AuthController::class, 'me']);
        });
    });

    // ==========================================
    // 2. Categories (Public)
    // ==========================================
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{category}', [CategoryController::class, 'show']);
    Route::get('categories/{category}/products', [CategoryController::class, 'products']);

    // ==========================================
    // 3. Products (Public)
    // ==========================================
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/search', [ProductController::class, 'search']);
    Route::get('products/{product}', [ProductController::class, 'show']);
    Route::get('products/{product}/related', [ProductController::class, 'related']);
    Route::get('products/{product}/reviews', [ReviewController::class, 'indexForProduct']);

    // ==========================================
    // 4. Vendors (Public)
    // ==========================================
    Route::get('vendors', [VendorController::class, 'index']);
    Route::get('vendors/{vendor}', [VendorController::class, 'show']);

    // ==========================================
    // Authenticated Routes
    // ==========================================
    Route::middleware('auth:sanctum')->group(function () {

        // 5. Customer Profile & Addresses
        Route::prefix('customer')->group(function () {
            Route::get('profile', [CustomerProfileController::class, 'profile']);
            Route::put('profile', [CustomerProfileController::class, 'updateProfile']);

            Route::get('addresses', [CustomerAddressController::class, 'index']);
            Route::post('addresses', [CustomerAddressController::class, 'store']);
            Route::get('addresses/{address}', [CustomerAddressController::class, 'show']);
            Route::put('addresses/{address}', [CustomerAddressController::class, 'update']);
            Route::delete('addresses/{address}', [CustomerAddressController::class, 'destroy']);

            // Customer Orders & Payments
            Route::get('orders', [CustomerOrderController::class, 'index']);
            Route::post('orders', [CustomerOrderController::class, 'store']);
            Route::get('orders/{order}', [CustomerOrderController::class, 'show']);
            Route::post('orders/{order}/cancel', [CustomerOrderController::class, 'cancel']);
            Route::get('orders/{order}/payment', [PaymentController::class, 'show']);
            Route::post('orders/{order}/payment', [PaymentController::class, 'store']);
        });

        // 6. Shopping Cart
        Route::prefix('cart')->group(function () {
            Route::get('/', [CartController::class, 'index']);
            Route::post('items', [CartController::class, 'addItem']);
            Route::put('items/{item}', [CartController::class, 'updateItem']);
            Route::delete('items/{item}', [CartController::class, 'removeItem']);
            Route::delete('clear', [CartController::class, 'clear']);
            Route::post('apply-coupon', [CartController::class, 'applyCoupon']);
            Route::delete('coupon', [CartController::class, 'removeCoupon']);
        });

        // 7. Wishlist
        Route::prefix('wishlist')->group(function () {
            Route::get('/', [WishlistController::class, 'index']);
            Route::post('items', [WishlistController::class, 'addItem']);
            Route::delete('items/{product}', [WishlistController::class, 'removeItem']);
            Route::delete('clear', [WishlistController::class, 'clear']);
        });

        // 8. Reviews
        Route::post('products/{product}/reviews', [ReviewController::class, 'store']);
        Route::put('reviews/{review}', [ReviewController::class, 'update']);
        Route::delete('reviews/{review}', [ReviewController::class, 'destroy']);

        // 9. Vendor Space
        Route::prefix('vendor')->group(function () {
            Route::get('profile', [VendorController::class, 'profile']);
            Route::put('profile', [VendorController::class, 'updateProfile']);

            Route::post('products', [ProductController::class, 'store']);
            Route::put('products/{product}', [ProductController::class, 'update']);
            Route::delete('products/{product}', [ProductController::class, 'destroy']);

            Route::post('products/{product}/images', [ProductImageController::class, 'store']);
            Route::delete('products/{product}/images/{image}', [ProductImageController::class, 'destroy']);

            Route::get('orders', [VendorOrderController::class, 'index']);
            Route::get('orders/{order}', [VendorOrderController::class, 'show']);
            Route::put('orders/{order}/status', [VendorOrderController::class, 'updateStatus']);

            Route::get('inventory', [InventoryController::class, 'index']);
            Route::get('inventory/transactions', [InventoryController::class, 'transactions']);
        });

        // 10. Admin Space
        Route::prefix('admin')->group(function () {
            Route::get('dashboard', [AdminDashboardController::class, 'index']);

            Route::post('categories', [CategoryController::class, 'store']);
            Route::put('categories/{category}', [CategoryController::class, 'update']);
            Route::delete('categories/{category}', [CategoryController::class, 'destroy']);

            Route::get('orders', [AdminOrderController::class, 'index']);
            Route::get('orders/{order}', [AdminOrderController::class, 'show']);
            Route::put('orders/{order}/status', [AdminOrderController::class, 'updateStatus']);
        });
    });
});
