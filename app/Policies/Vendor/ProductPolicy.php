<?php

namespace App\Policies\Vendor;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Determine whether the user can view any products.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'vendor';
    }

    /**
     * Determine whether the user can view the product.
     */
    public function view(User $user, Product $product): bool
    {
        if ($user->role !== 'vendor') {
            return false;
        }

        if (!$product->relationLoaded('shop')) {
            $product->load('shop');
        }

        return $product->shop->owner_id === $user->id;
    }

    /**
     * Determine whether the user can create products.
     */
    public function create(User $user): bool
    {
        return $user->role === 'vendor';
    }

    /**
     * Determine whether the user can update the product.
     */
    public function update(User $user, Product $product): bool
    {
        if ($user->role !== 'vendor') {
            return false;
        }

        if (!$product->relationLoaded('shop')) {
            $product->load('shop');
        }

        return $product->shop->owner_id === $user->id;
    }

    /**
     * Determine whether the user can delete the product.
     */
    public function delete(User $user, Product $product): bool
    {
        if ($user->role !== 'vendor') {
            return false;
        }

        if (!$product->relationLoaded('shop')) {
            $product->load('shop');
        }

        return $product->shop->owner_id === $user->id;
    }

    /**
     * Determine whether the user can restore the product.
     */
    public function restore(User $user, Product $product): bool
    {
        if ($user->role !== 'vendor') {
            return false;
        }

        if (!$product->relationLoaded('shop')) {
            $product->load('shop');
        }

        return $product->shop->owner_id === $user->id;
    }
}