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
        return Product::with(['shop.location.city', 'subcategory.category', 'media'])
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
            'shop',
            'subcategory.category',
            'attributeValues.attribute',
            'media'
        ]);
    }
}
