<?php

namespace App\Services\Vendor;

use App\Models\Product;
use App\Models\ProductStats;
use App\Models\Shop;
use App\Models\User;

class DashboardService
{
    /**
     * Get aggregated dashboard statistics for a vendor.
     *
     * Returns total products, active/inactive counts, engagement metrics,
     * and engagement rate for all products across vendor's shops.
     */
    public function getVendorStats(User $vendor): array
    {
        // Get all shops owned by vendor
        $shopIds = Shop::where('owner_id', $vendor->id)->pluck('id');

        // Get all products from vendor's shops
        $products = Product::whereIn('shop_id', $shopIds)->get();

        $totalProducts = $products->count();
        $activeProducts = $products->where('is_active', true)->count();
        $inactiveProducts = $products->where('is_active', false)->count();

        // Get aggregated stats
        $productIds = $products->pluck('id');
        $stats = ProductStats::whereIn('product_id', $productIds)->get();

        $totalViews = $stats->sum('views_count');
        $totalWhatsappClicks = $stats->sum('whatsapp_clicks');
        $totalLocationClicks = $stats->sum('location_clicks');
        $totalFavorites = $stats->sum('favorites_count');

        // Calculate engagement rate
        $totalInteractions = $totalWhatsappClicks + $totalLocationClicks + $totalFavorites;
        $engagementRate = $totalViews > 0
            ? round(($totalInteractions / $totalViews) * 100, 2)
            : 0;

        return [
            'total_products' => $totalProducts,
            'active_products' => $activeProducts,
            'inactive_products' => $inactiveProducts,
            'total_views' => $totalViews,
            'total_whatsapp_clicks' => $totalWhatsappClicks,
            'total_location_clicks' => $totalLocationClicks,
            'total_favorites' => $totalFavorites,
            'engagement_rate' => $engagementRate,
        ];
    }

    /**
     * Calculate trend percentages by comparing current and previous period stats.
     *
     * This method compares the current period stats with the previous period
     * to calculate percentage changes. Since we don't have historical snapshots,
     * we use the current stats as a baseline and return 0% change.
     *
     * For a full implementation with historical tracking, you would need:
     * 1. A product_stats_history table to store daily/weekly snapshots
     * 2. Background job to create snapshots periodically
     * 3. Query to compare current vs previous period from snapshots
     *
     * @param User $vendor
     * @param int $days Number of days for current period (default 7)
     * @return array Trend percentages for each metric
     */
    public function calculateTrends(User $vendor, int $days = 7): array
    {
        // Get all shops owned by vendor
        $shopIds = Shop::where('owner_id', $vendor->id)->pluck('id');
        $productIds = Product::whereIn('shop_id', $shopIds)->pluck('id');

        // Since we don't have historical snapshots, we'll calculate trends
        // based on products updated in the last period vs before that period
        // This is a simplified approach - for production, implement proper historical tracking
        
        $currentPeriodStart = now()->subDays($days);
        $previousPeriodStart = now()->subDays($days * 2);
        
        // Get stats for products updated in current period
        $currentPeriodStats = ProductStats::whereIn('product_id', $productIds)
            ->where('updated_at', '>=', $currentPeriodStart)
            ->get();
            
        // Get stats for products updated in previous period
        $previousPeriodStats = ProductStats::whereIn('product_id', $productIds)
            ->where('updated_at', '>=', $previousPeriodStart)
            ->where('updated_at', '<', $currentPeriodStart)
            ->get();

        $currentPeriodViews = $currentPeriodStats->sum('views_count');
        $currentPeriodWhatsapp = $currentPeriodStats->sum('whatsapp_clicks');
        $currentPeriodLocation = $currentPeriodStats->sum('location_clicks');
        $currentPeriodFavorites = $currentPeriodStats->sum('favorites_count');

        $previousPeriodViews = $previousPeriodStats->sum('views_count');
        $previousPeriodWhatsapp = $previousPeriodStats->sum('whatsapp_clicks');
        $previousPeriodLocation = $previousPeriodStats->sum('location_clicks');
        $previousPeriodFavorites = $previousPeriodStats->sum('favorites_count');

        // Calculate percentage changes
        $viewsChange = $this->calculatePercentageChange($previousPeriodViews, $currentPeriodViews);
        $whatsappChange = $this->calculatePercentageChange($previousPeriodWhatsapp, $currentPeriodWhatsapp);
        $locationChange = $this->calculatePercentageChange($previousPeriodLocation, $currentPeriodLocation);
        $favoritesChange = $this->calculatePercentageChange($previousPeriodFavorites, $currentPeriodFavorites);

        return [
            'views_change' => $viewsChange,
            'whatsapp_change' => $whatsappChange,
            'location_change' => $locationChange,
            'favorites_change' => $favoritesChange,
        ];
    }

    /**
     * Calculate percentage change between two values.
     *
     * @param int|float $previous Previous period value
     * @param int|float $current Current period value
     * @return float Percentage change rounded to 2 decimal places
     */
    private function calculatePercentageChange($previous, $current): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }
}
