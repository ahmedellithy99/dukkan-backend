<?php

namespace App\Services\Vendor;

use App\Filters\Vendor\ProductFilter;
use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProductService
{
    /**
     * Get products for a vendor with filtering and pagination.
     */
    public function getProducts(Request $request, int $perPage = 20): LengthAwarePaginator
    {
        return  Product::with(['shop.location.city', 'subcategory.category', 'media'])
            ->where('shop_id', $request->shop->id)
            ->filter(new ProductFilter($request))
            ->paginate($perPage)
            ->appends($request->query());
    }

    /**
     * Get a single product with relationships.
     */
    public function getProduct(Product $product): Product
    {
        return $product->load([
            'shop',
            'subcategory.category',
            'attributeValues.attribute',
            'stats',
            'media'
        ]);
    }

    /**
     * Create a new product.
     */
    public function createProduct(array $data)
    {
        return DB::transaction(function () use ($data) {

            $product = Product::create([
                'shop_id' => $data['shop_id'],
                'subcategory_id' => $data['subcategory_id'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'price' => $data['price'],
                'stock_quantity' => $data['stock_quantity'],
                'is_active' => $data['is_active'] ?? true,
                'discount_type' => $data['discount_type'] ?? null,
                'discount_value' => $data['discount_value'] ?? null,
            ]);

            $product->addMediaFromRequest('main_image')
                ->toMediaCollection('main_image');

            $product->addMediaFromRequest('secondary_image')
                ->toMediaCollection('secondary_image');

            return $product->load(['shop', 'subcategory.category', 'media']);
        });
    }

    /**
     * Update a product.
     *
     * Note: Authorization is handled by ProductPolicy in the controller middleware
     */
    public function updateProduct(Product $product, array $data): Product
    {

        return DB::transaction(function () use ($data, $product) {

            $product->update([
                'subcategory_id' => $data['subcategory_id'] ?? $product->subcategory_id,
                'name' => $data['name'] ?? $product->name,
                'description' => $data['description'] ?? $product->description,
                'price' => $data['price'] ?? $product->price,
                'stock_quantity' => $data['stock_quantity'] ?? $product->stock_quantity,
                'is_active' => $data['is_active'] ?? $product->is_active,
                'discount_type' => $data['discount_type'] ?? $product->discount_type,
                'discount_value' => $data['discount_value'] ?? $product->discount_value,
            ]);

            if (isset($data['main_image'])) {
                $product->addMediaFromRequest('main_image')
                    ->toMediaCollection('main_image'); 
            }

            if (isset($data['secondary_image'])) {
                $product->addMediaFromRequest('secondary_image')
                    ->toMediaCollection('secondary_image');
            }

            return $product->refresh()->load(['shop', 'subcategory.category', 'media']);
        });
    }

    /**
     * Delete a product.
     *
     * Note: Authorization is handled by ProductPolicy in the controller middleware
     */
    public function deleteProduct(Product $product): bool
    {
        return $product->delete();
    }

    /**
     * Update product stock quantity.
     */
    public function updateStock(Product $product, int $quantity): Product
    {
        $product->update(['stock_quantity' => $quantity]);
        return $product->refresh();
    }

    /**
     * Increment product stock.
     */
    public function incrementStock(Product $product, int $quantity = 1): Product
    {
        $product->incrementStock($quantity);
        return $product->refresh();
    }

    /**
     * Decrement product stock.
     */
    public function decrementStock(Product $product, int $quantity = 1): bool
    {
        return $product->decrementStock($quantity);
    }

    /**
     * Toggle product active status.
     */
    public function toggleStatus(Product $product): Product
    {
        $product->update(['is_active' => !$product->is_active]);
        return $product->refresh();
    }

    /**
     * Apply discount to product.
     */
    public function applyDiscount(Product $product, array $data): Product
    {
        $product->update([
            'discount_type' => $data['discount_type'],
            'discount_value' => $data['discount_value'],
        ]);
        
        return $product->refresh();
    }

    /**
     * Remove discount from product.
     */
    public function removeDiscount(Product $product): Product
    {
        $product->update([
            'discount_type' => null,
            'discount_value' => null,
        ]);
        return $product->refresh();
    }
}
