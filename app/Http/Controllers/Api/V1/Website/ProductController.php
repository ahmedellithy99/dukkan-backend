<?php

namespace App\Http\Controllers\Api\V1\Website;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Website\ProductResource;
use App\Models\Product;
use App\Services\Website\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
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
     * 
     * @param Product $product
     * @return JsonResponse
     */
    public function show(Product $product): JsonResponse
    {
        $product = $this->productService->getProduct($product);

        return response()->api(new ProductResource($product));
    }
}
