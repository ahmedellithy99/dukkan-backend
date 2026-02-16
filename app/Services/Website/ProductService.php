<?php

namespace App\Services\Website;

use App\Filters\Website\ProductFilter;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ProductService
{
    /**
     * Get active products with filtering and pagination for public website.
     * Filters by city if X-City header is provided.
     * Uses JOIN for better performance instead of subquery.
     */
    public function getProducts(Request $request, int $perPage = 20): LengthAwarePaginator
    {
        $query = Product::query()
            ->select('products.*')
            ->with(['media', 'shop.location'])
            ->where('products.is_active', true);

        // Filter by city if provided via X-City header
        // Using JOIN instead of whereHas for better performance
        $city = $request->attributes->get('city');
        if ($city) {
            $query->join('shops', 'products.shop_id', '=', 'shops.id')
                  ->join('locations', 'shops.location_id', '=', 'locations.id')
                  ->where('locations.city_id', $city->id);
        }

        return $query->filter(new ProductFilter($request))
            ->paginate($perPage)
            ->appends($request->query());
    }

    /**
     * Get a single active product with relationships for public website.
     */
    public function getProduct(Product $product): Product
    {
        return $product->load([
            'shop.location',
            'attributeValues.attribute',
            'media'
        ]);
    }

    /**
     * Get products with active discounts ordered by discount value for homepage offers.
     * Filters by city if X-City header is provided.
     * Uses JOIN for better performance instead of subquery.
     */
    public function getOffers(Request $request, int $perPage = 20): Collection
    {
        $query = Product::query()
            ->select('products.*')
            ->with(['media'])
            ->where('products.is_active', true)
            ->onDiscount();

        // Filter by city if provided via X-City header
        // Using JOIN instead of whereHas for better performance
        $city = $request->attributes->get('city');
        if ($city) {
            $query->join('shops', 'products.shop_id', '=', 'shops.id')
                  ->join('locations', 'shops.location_id', '=', 'locations.id')
                  ->where('locations.city_id', $city->id);
        }

        return $query->orderByRaw('
                CASE 
                    WHEN products.discount_type = "percent" THEN products.price * (products.discount_value / 100)
                    WHEN products.discount_type = "amount" THEN products.discount_value
                    ELSE 0
                END DESC
            ')
            ->limit($perPage)
            ->get();
    }
}
