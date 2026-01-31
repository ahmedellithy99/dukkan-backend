<?php

namespace App\Policies\Vendor;

use App\Models\Location;
use App\Models\User;

class LocationPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Location $location): bool
    {
        // Ensure the user is a vendor
        if ($user->role !== 'vendor') {
            return false;
        }

        // Load the shop relationship if not already loaded
        if (!$location->relationLoaded('shop')) {
            $location->load('shop');
        }

        // Check if the location has a shop and the vendor owns it
        return $location->shop && $location->shop->owner_id === $user->id;
    }
}
