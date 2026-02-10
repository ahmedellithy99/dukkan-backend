<?php

namespace App\Services\Website;

use App\Filters\Website\ProductFilter;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductService
{
    /**
     * Get active products with filtering and pagination for public website.
     */
    public function getProducts(Request $request, int $perPage = 20)
    {
        return Product::with(['media'])
            ->where('is_active', true)
            // ->whereHas('shop', function ($query) {
            //     $query->where('is_active', true);
            // })
            ->filter(new ProductFilter($request))
            ->get();
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
     */
    public function getOffers(Request $request, int $perPage = 20)
    {
        return Product::with(['media'])
            ->where('is_active', true)
            ->onDiscount()
            ->orderByRaw('
                CASE 
                    WHEN discount_type = "percent" THEN price * (discount_value / 100)
                    WHEN discount_type = "amount" THEN discount_value
                    ELSE 0
                END DESC
            ')
            ->get();
    }
}
