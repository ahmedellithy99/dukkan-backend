<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Vendor\ApplyDiscountRequest;
use App\Http\Requests\V1\Vendor\StoreProductRequest;
use App\Http\Requests\V1\Vendor\UpdateProductRequest;
use App\Http\Requests\V1\Vendor\UpdateStockRequest;
use App\Http\Resources\V1\Vendor\ProductResource;
use App\Models\Product;
use App\Models\Shop;
use App\Services\Vendor\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->authorizeResource(Product::class, 'product');
        $this->productService = $productService;
    }

    /**
     * Display a listing of products for the vendor
     */
    public function index(Request $request, Shop $shop): JsonResponse
    {
        $products = $this->productService->getProducts($shop, $request, 20);

        return response()->api(ProductResource::collection($products),200);
    }

    /**
     * Store a newly created product
     */
    public function store(StoreProductRequest $request, Shop $shop): JsonResponse
    {
        $data = array_merge($request->validated(), ['shop_id' => $shop->id]);
        $product = $this->productService->createProduct($data);

        return response()->api(new ProductResource($product), 201);
    }

    /**
     * Display the specified product
     */
    public function show(Shop $shop, Product $product): JsonResponse
    {
        $product = $this->productService->getProduct($product);

        return response()->api(new ProductResource($product));
    }

    /**
     * Update the specified product
     */
    public function update(UpdateProductRequest $request, Shop $shop, Product $product): JsonResponse
    {
        $product = $this->productService->updateProduct($product, $request->validated());

        return response()->api(new ProductResource($product));
    }

    /**
     * Remove the specified product
     */
    public function destroy(Shop $shop, Product $product)
    {
        $this->productService->deleteProduct($product);
        
        return response()->api(null, 204);
    }

    /**
     * Toggle product active status
     */
    public function toggleStatus(Shop $shop,Product $product): JsonResponse
    {
        $this->authorize('update', $product);
        
        $product = $this->productService->toggleStatus($product);
        return response()->api(new ProductResource($product));
    }

    /**
     * Update product stock quantity
     */
    public function updateStock(UpdateStockRequest $request, Shop $shop, Product $product): JsonResponse
    {
        $this->authorize('update', $product);
        
        $product = $this->productService->updateStock($product, $request->stock_quantity);
        return response()->api(new ProductResource($product));
    }

    /**
     * Apply discount to product
     */
    public function applyDiscount(ApplyDiscountRequest $request, Shop $shop, Product $product): JsonResponse
    {
        $this->authorize('update', $product);
        
        $product = $this->productService->applyDiscount($product, $request->validated());
        
        return response()->api(new ProductResource($product));
    }

    /**
     * Remove discount from product
     */
    public function removeDiscount(Shop $shop,Product $product): JsonResponse
    {
        $this->authorize('update', $product);
        
        $product = $this->productService->removeDiscount($product);
        return response()->api(new ProductResource($product));
    }
}