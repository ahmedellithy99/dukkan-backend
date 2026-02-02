<?php

namespace App\Services\Vendor;

use App\Filters\Vendor\ShopFilter;
use App\Models\Shop;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ShopService
{
    /**
     * Get shops for a vendor with filtering and pagination.
     */
    public function getShops(Request $request, int $perPage = 20): LengthAwarePaginator
    {
        return  Shop::with(['location', 'owner', 'media', 'products'])
            ->withCount('products')
            ->where('owner_id', $request->user()->id)
            ->filter(new ShopFilter($request))
            ->paginate($perPage)
            ->appends($request->query());
    }

    /**
     * Get a single shop with relationships.
     */
    public function getShop($shop): Shop
    {
        return $shop->load(['location.city.governorate', 'owner', 'products', 'media'])
            ->loadCount('products');
    }

    /**
     * Create a new shop.
     */
    public function createShop(array $data): Shop
    {
        return DB::transaction(function () use ($data) {

            $location = Location::create(
                [
                    'city_id' => $data['city_id'],
                    'area' => $data['area'],
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude']
                ]
            );

            $shop = Shop::create([
                'owner_id' => $data['owner_id'],
                'location_id' => $location->id,
                'name' => $data['name'],
                'description' => $data['description'],
                'whatsapp_number' => $data['whatsapp_number'],
                'phone_number' => $data['phone_number'],
            ]);

            // Handle logo upload if provided
            if (isset($data['logo']) && $data['logo']) {
                $shop->addMediaFromRequest('logo')
                    ->usingFileName('logo.webp')
                    ->toMediaCollection('logo');
            }

            return $shop->load(['location.city.governorate', 'owner', 'media']);
        });
    }

    /**
     * Update a shop.
     * 
     * Note: Authorization is handled by ShopPolicy in the controller middleware
     */
    public function updateShop(Shop $shop, array $data): Shop
    {
        return DB::transaction(function () use ($shop, $data) {
            // Handle location update if location data is provided
            if (isset($data['city_id']) || isset($data['area']) || isset($data['latitude']) || isset($data['longitude'])) {
                // Get current location or create new one
                $location = $shop->location;

                $locationData = [];
                if (isset($data['city_id'])) $locationData['city_id'] = $data['city_id'];
                if (isset($data['area'])) $locationData['area'] = $data['area'];
                if (isset($data['latitude'])) $locationData['latitude'] = $data['latitude'];
                if (isset($data['longitude'])) $locationData['longitude'] = $data['longitude'];

                $location->update($locationData);

                // Remove location fields from shop data
                unset($data['city_id'], $data['area'], $data['latitude'], $data['longitude']);
            }

            // Handle logo update if provided
            if (isset($data['logo']) && $data['logo']) {
                // Clear existing logo first
                $shop->clearMediaCollection('logo');

                // Add new logo
                $shop->addMediaFromRequest('logo')
                    ->usingFileName('logo.webp')
                    ->toMediaCollection('logo');

                // Remove logo from data array
                unset($data['logo']);
            }

            // Update shop with remaining data
            if (!empty($data)) {
                $shop->update($data);
            }

            $shop->refresh();
            return $shop->load(['location.city.governorate', 'owner', 'media']);
        });
    }

    /**
     * Soft delete a shop.
     * 
     * Note: Authorization is handled by ShopPolicy in the controller middleware
     */
    public function deleteShop(Shop $shop): bool
    {
        return $shop->delete();
    }

    /**
     * Restore a soft-deleted shop.
     * 
     * Note: Authorization is handled by ShopPolicy in the controller middleware
     */
    public function restoreShop(Shop $shop): void
    {
        $shop->restore();
        $shop->refresh();
        $shop->load(['location.city.governorate', 'owner', 'media']);
    }
}
