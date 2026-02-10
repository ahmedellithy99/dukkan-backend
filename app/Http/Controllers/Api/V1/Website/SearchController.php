<?php

namespace App\Http\Controllers\Api\V1\Website;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Website\ProductResource;
use App\Http\Resources\V1\Website\ShopResource;
use App\Services\Website\ProductService;
use App\Services\Website\ShopService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(
        private ProductService $productService,
        private ShopService $shopService
    ) {}

    /**
     * Get search suggestions (autocomplete) for quick results
     * Returns lightweight suggestions as user types
     */
    public function suggestions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => 'required|string|min:1|max:100',
            'type' => 'required|string|in:products,shops',
            'limit' => 'sometimes|integer|min:1|max:10',
        ]);

        $searchTerm = $validated['q'];
        $searchType = $validated['type'];
        $limit = $validated['limit'] ?? 5;

        $suggestions = [];

        if ($searchType === 'products') {
            $suggestions = $this->productService->getProducts(
                new Request(['search' => $searchTerm]),
                $limit
            )->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => $product->price,
                    'image' => $product->getFirstMediaUrl('primary', 'thumb'),
                ];
            });
        } elseif ($searchType === 'shops') {
            $suggestions = $this->shopService->getPublicShops(
                new Request(['search' => $searchTerm]),
                $limit
            )->map(function ($shop) {
                return [
                    'id' => $shop->id,
                    'name' => $shop->name,
                    'slug' => $shop->slug,
                    'image' => $shop->getFirstMediaUrl('logo', 'thumb'),
                ];
            });
        }

        return response()->api([
            'query' => $searchTerm,
            'type' => $searchType,
            'suggestions' => $suggestions,
        ]);
    }

    /**
     * Unified search endpoint for products and shops
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => 'required|string|min:2|max:100',
            'type' => 'required|string|in:products,shops',
            'limit' => 'sometimes|integer|min:1|max:50',
        ]);

        $searchTerm = $validated['q'];
        $searchType = $validated['type'];
        $limit = $validated['limit'] ?? 10;

        $results = [];

        // Search products
        if ($searchType === 'products') {
            $productRequest = new Request(['search' => $searchTerm]);
            $productRequest->attributes->add($request->attributes->all());
            
            $products = $this->productService->getProducts($productRequest, $limit);
            
            $results['products'] = [
                'data' => ProductResource::collection($products->items()),
                'total' => $products->total(),
                'has_more' => $products->hasMorePages(),
            ];
        }

        // Search shops
        if ($searchType === 'shops') {
            $shopRequest = new Request(['search' => $searchTerm]);
            $shopRequest->attributes->add($request->attributes->all());
            
            $shops = $this->shopService->getPublicShops($shopRequest, $limit);
            
            $results['shops'] = [
                'data' => ShopResource::collection($shops->items()),
                'total' => $shops->total(),
                'has_more' => $shops->hasMorePages(),
            ];
        }

        return response()->api([
            'query' => $searchTerm,
            'type' => $searchType,
            'results' => $results,
        ]);
    }
}
