<?php

namespace App\Services\Vendor;

use App\Models\Product;
use App\Models\ProductStats;

class ProductStatsService
{
    /**
     * Get product stats.
     */
    public function getProductStats(Product $product): ?ProductStats
    {
        return $product->stats;
    }
}
