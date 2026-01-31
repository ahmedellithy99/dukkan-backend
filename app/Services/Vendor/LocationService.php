<?php

namespace App\Services\Vendor;

use App\Models\Location;

class LocationService
{
    /**
     * Update a location.
     *
     * Note: Authorization is handled by LocationPolicy in the controller
     */
    public function updateLocation(Location $location, array $data): Location
    {
        $location->update($data);

        return $location->refresh(['city.governorate']);
    }
}
