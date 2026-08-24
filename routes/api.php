<?php

use App\Http\Controllers\Api\ApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('register', [ApiController::class, 'register']);
        Route::post('login', [ApiController::class, 'login']);
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [ApiController::class, 'logout']);
            Route::get('me', [ApiController::class, 'me']);
        });
    });

    Route::get('categories', [ApiController::class, 'categories']);
    Route::get('categories/{category}', [ApiController::class, 'category']);
    Route::get('categories/{category}/products', [ApiController::class, 'categoryProducts']);
    Route::get('products', [ApiController::class, 'products']);
    Route::get('products/search', [ApiController::class, 'products']);
    Route::get('products/{product}', [ApiController::class, 'product']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('admin/categories', [ApiController::class, 'storeCategory']);
        Route::put('admin/categories/{category}', [ApiController::class, 'updateCategory']);
        Route::delete('admin/categories/{category}', [ApiController::class, 'deleteCategory']);
        Route::post('vendor/products', [ApiController::class, 'storeProduct']);
        Route::put('vendor/products/{product}', [ApiController::class, 'updateProduct']);
        Route::delete('vendor/products/{product}', [ApiController::class, 'deleteProduct']);
        Route::get('cart', [ApiController::class, 'cart']);
        Route::post('cart/items', [ApiController::class, 'addCartItem']);
        Route::delete('cart/items/{item}', [ApiController::class, 'removeCartItem']);
        Route::delete('cart/clear', [ApiController::class, 'clearCart']);
        Route::get('wishlist', [ApiController::class, 'wishlist']);
        Route::post('wishlist/items', [ApiController::class, 'addWishlist']);
        Route::delete('wishlist/items/{product}', [ApiController::class, 'removeWishlist']);
        Route::delete('wishlist/clear', [ApiController::class, 'clearWishlist']);
    });
});
