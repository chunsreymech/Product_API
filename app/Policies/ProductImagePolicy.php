<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;

class ProductImagePolicy
{
    public function create(User $user, Product $product): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isVendor() && $product->vendor && $product->vendor->user_id === $user->id;
    }

    public function delete(User $user, ProductImage $image): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $product = $image->product;
        return $user->isVendor() && $product && $product->vendor && $product->vendor->user_id === $user->id;
    }
}
