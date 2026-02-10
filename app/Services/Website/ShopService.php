<?php

namespace App\Services\Website;

use App\Filters\Website\ShopFilter;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ShopService
{
    /**
     * Get public shops (active only) with filtering and pagination.
     * Filters by city if X-City header is provided.
     * Uses JOIN for better performance instead of subquery.
     */
    public function getPublicShops(Request $request, int $perPage = 20): LengthAwarePaginator
    {
        $query = Shop::query()
            ->select('shops.*')
            ->with(['media'])
            ->active();

        // Filter by city if provided via X-City header
        // Using JOIN instead of whereHas for better performance
        $city = $request->attributes->get('city');
        if ($city) {
            $query->join('locations', 'shops.location_id', '=', 'locations.id')
                ->where('locations.city_id', $city->id);
        }

        return $query->filter(new ShopFilter($request))
            ->paginate($perPage)
            ->appends($request->query());
    }

    /**
     * Get a single public shop (active only) with relationships.
     */
    public function getPublicShop(string $slug): Shop
    {
        return Shop::with(['location.city', 'media', 'products' => function ($query) {
            $query->where('is_active', true);
        }])
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();
    }
}
