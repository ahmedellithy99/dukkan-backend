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
     */
    public function getPublicShops(Request $request, int $perPage = 20): LengthAwarePaginator
    {
        return  Shop::with(['location.city', 'owner', 'media'])
            ->active() 
            ->filter(new ShopFilter($request))
            ->paginate($perPage)
            ->appends($request->query());
    }

    /**
     * Get a single public shop (active only) with relationships.
     */
    public function getPublicShop(string $slug): Shop
    {
        return Shop::with(['location.city', 'owner', 'media','products' => function ($query) {
            $query->where('is_active', true); 
        }])
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();
    }
}
