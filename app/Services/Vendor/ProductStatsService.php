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

    /**
     * Track a product view (internal use).
     */
    public function trackView(Product $product): ProductStats
    {
        $stats = $product->stats()->firstOrCreate(
            ['product_id' => $product->id],
            [
                'views_count' => 0,
                'whatsapp_clicks' => 0,
                'location_clicks' => 0,
                'favorites_count' => 0,
            ]
        );

        $stats->increment('views_count');
        $stats->update(['last_viewed_at' => now()]);

        return $stats->refresh();
    }

    /**
     * Track a WhatsApp click (internal use).
     */
    public function trackWhatsAppClick(Product $product): ProductStats
    {
        $stats = $product->stats()->firstOrCreate(
            ['product_id' => $product->id],
            [
                'views_count' => 0,
                'whatsapp_clicks' => 0,
                'location_clicks' => 0,
                'favorites_count' => 0,
            ]
        );

        $stats->increment('whatsapp_clicks');

        return $stats->refresh();
    }

    /**
     * Track a location click (internal use).
     */
    public function trackLocationClick(Product $product): ProductStats
    {
        $stats = $product->stats()->firstOrCreate(
            ['product_id' => $product->id],
            [
                'views_count' => 0,
                'whatsapp_clicks' => 0,
                'location_clicks' => 0,
                'favorites_count' => 0,
            ]
        );

        $stats->increment('location_clicks');

        return $stats->refresh();
    }

    /**
     * Track a favorite action (internal use).
     */
    public function trackFavorite(Product $product): ProductStats
    {
        $stats = $product->stats()->firstOrCreate(
            ['product_id' => $product->id],
            [
                'views_count' => 0,
                'whatsapp_clicks' => 0,
                'location_clicks' => 0,
                'favorites_count' => 0,
            ]
        );

        $stats->increment('favorites_count');

        return $stats->refresh();
    }
}
