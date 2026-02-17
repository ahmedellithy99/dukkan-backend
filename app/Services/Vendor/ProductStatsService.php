<?php

namespace App\Services\Vendor;

use App\Models\Product;
use App\Models\ProductActivity;
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

        // Create activity record
        ProductActivity::create([
            'product_id' => $product->id,
            'activity_type' => 'view',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

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

        // Create activity record
        ProductActivity::create([
            'product_id' => $product->id,
            'activity_type' => 'whatsapp_click',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

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

        // Create activity record
        ProductActivity::create([
            'product_id' => $product->id,
            'activity_type' => 'location_click',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

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
