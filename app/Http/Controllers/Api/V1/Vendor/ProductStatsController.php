<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Vendor\ProductStatsResource;
use App\Models\Product;
use App\Services\Vendor\ProductStatsService;
use Illuminate\Http\JsonResponse;

class ProductStatsController extends Controller
{
    protected ProductStatsService $statsService;

    public function __construct(ProductStatsService $statsService)
    {
        $this->authorizeResource(Product::class, 'product');
        $this->statsService = $statsService;
    }

    /**
     * Get product analytics stats (vendor only).
     */
    public function show(Product $product): JsonResponse
    {
        $stats = $this->statsService->getProductStats($product);

        if (!$stats) {
            return response()->api([
                'product_id' => $product->id,
                'views_count' => 0,
                'whatsapp_clicks' => 0,
                'favorites_count' => 0,
                'last_viewed_at' => null,
            ]);
        }

        return response()->api(new ProductStatsResource($stats));
    }
}
