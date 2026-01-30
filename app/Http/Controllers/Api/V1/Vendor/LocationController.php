<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Vendor\UpdateLocationRequest;
use App\Http\Resources\V1\Vendor\LocationResource;
use App\Models\Location;
use App\Services\Vendor\LocationService;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    protected LocationService $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    /**
     * Update shop's location (only if location is not used by other shops and vendor owns the shop)
     */
    public function update(UpdateLocationRequest $request, Location $location): JsonResponse
    {
        $this->locationService->updateLocation($location, $request->validated(), $request->user());
        $location->refresh();
        $location->load(['city.governorate']);

        return response()->api(new LocationResource($location));
    }
}
