<?php

namespace App\Http\Controllers\Api\V1\Website;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Website\ProductResource;
use App\Models\Product;
use App\Services\Vendor\ProductStatsService;
use App\Services\Website\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected ProductService $productService;
    protected ProductStatsService $statsService;

    public function __construct(ProductService $productService, ProductStatsService $statsService)
    {
        $this->productService = $productService;
        $this->statsService = $statsService;
    }

    /**
     * Display a listing of active products for public browsing.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $products = $this->productService->getProducts($request, 20);

        return response()->api(ProductResource::collection($products), 200);
    }

    /**
     * Display the specified active product.
     * Automatically tracks view count.
     * 
     * @param Product $product
     * @return JsonResponse
     */
    public function show(Product $product): JsonResponse
    {
        // Track view
        $this->statsService->trackView($product);

        $product = $this->productService->getProduct($product);

        return response()->api(new ProductResource($product));
    }

    /**
     * Display products with active discounts for homepage offers section.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function offers(Request $request): JsonResponse
    {
        $offers = $this->productService->getOffers($request, 20);

        return response()->api(ProductResource::collection($offers), 200);
    }

    /**
     * Track WhatsApp click for a product.
     * 
     * @param Product $product
     * @return JsonResponse
     */
    public function trackWhatsAppClick(Product $product): JsonResponse
    {
        $this->statsService->trackWhatsAppClick($product);

        return response()->api(['message' => 'WhatsApp click tracked successfully']);
    }

    /**
     * Track location click for a product.
     * 
     * @param Product $product
     * @return JsonResponse
     */
    public function trackLocationClick(Product $product): JsonResponse
    {
        $this->statsService->trackLocationClick($product);

        return response()->api(['message' => 'Location click tracked successfully']);
    }

    public function trackViewClick(Product $product): JsonResponse
    {
        $this->statsService->trackView($product);

        return response()->api(['message' => 'Product click tracked successfully']);
    }
}
