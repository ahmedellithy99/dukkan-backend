<?php

namespace App\Policies\Vendor;

use App\Models\Shop;
use App\Models\User;

class ShopPolicy
{
    /**
     * Determine whether the user can view any shops.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'vendor';
    }

    /**
     * Determine whether the user can view the my_shop.
     */
    public function view(User $user, Shop $my_shop): bool
    {
        // Ensure the user is a vendor
        if ($user->role !== 'vendor') {
            return false;
        }

        // Load the relationship if not already loaded
        if (!$my_shop->relationLoaded('owner')) {
            $my_shop->load('owner');
        }

        // Check if the vendor owns the my_shop
        return $my_shop->owner_id === $user->id;
    }

    /**
     * Determine whether the user can create shops.
     */
    public function create(User $user): bool
    {
        return $user->role === 'vendor';
    }

    /**
     * Determine whether the user can update the my_shop.
     */
    public function update(User $user, Shop $my_shop): bool
    {
        // Ensure the user is a vendor
        if ($user->role !== 'vendor') {
            return false;
        }

        // Load the relationship if not already loaded
        if (!$my_shop->relationLoaded('owner')) {
            $my_shop->load('owner');
        }

        // Check if the vendor owns the my_shop
        return $my_shop->owner_id === $user->id;
    }

    /**
     * Determine whether the user can delete the my_shop.
     */
    public function delete(User $user, Shop $my_shop): bool
    {
        // Ensure the user is a vendor
        if ($user->role !== 'vendor') {
            return false;
        }

        // Load the relationship if not already loaded
        if (!$my_shop->relationLoaded('owner')) {
            $my_shop->load('owner');
        }

        // Check if the vendor owns the my_shop
        return $my_shop->owner_id === $user->id;
    }

    /**
     * Determine whether the user can restore the my_shop.
     */
    public function restore(User $user, Shop $my_shop): bool
    {
        // Ensure the user is a vendor
        if ($user->role !== 'vendor') {
            return false;
        }

        // Load the relationship if not already loaded
        if (!$my_shop->relationLoaded('owner')) {
            $my_shop->load('owner');
        }

        // Check if the vendor owns the my_shop
        return $my_shop->owner_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the my_shop.
     */
    public function forceDelete(User $user, Shop $my_shop): bool
    {
        // Only admins can force delete shops
        return $user->role === 'admin';
    }
}