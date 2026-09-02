<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isCustomer() && $order->user_id === $user->id) {
            return true;
        }

        if ($user->isVendor() && $user->vendor) {
            return $order->items()->where('vendor_id', $user->vendor->id)->exists();
        }

        return false;
    }

    public function cancel(User $user, Order $order): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $order->user_id === $user->id && $order->canBeCancelled();
    }

    public function updateStatus(User $user, Order $order): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isVendor() && $user->vendor) {
            return $order->items()->where('vendor_id', $user->vendor->id)->exists();
        }

        return false;
    }
}
