<?php

namespace App\Services\Vendor;

use App\Exceptions\Domain\Location\LocationAccessDeniedException;
use App\Models\Location;
use App\Models\User;

class LocationService
{
    /**
     * Create a new location.
     */
    public function createLocation(array $data): Location
    {
        $location = Location::create($data);
        $location->load(['city.governorate']);

        return $location;
    }

    /**
     * Update a location (only if vendor owns the shop using this location).
     * 
     * @throws LocationAccessDeniedException When vendor doesn't own the shop using this location
     */
    public function updateLocation(Location $location, array $data, User $vendor): bool
    {
        // Check if vendor owns the shop that uses this location
        $shop = $location->shop()->first();
        if (!$shop || $shop->owner_id !== $vendor->id) {
            throw new LocationAccessDeniedException();
        }

        return $location->update($data);
    }
}