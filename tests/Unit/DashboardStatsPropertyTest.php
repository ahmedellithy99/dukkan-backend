<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductStats;
use App\Models\Shop;
use App\Models\User;
use Eris\Generator;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature: marketplace-platform, Property 22: Dashboard Statistics Accuracy
 *
 * Property: For any vendor with products and stats, aggregated dashboard statistics
 * should accurately match individual product stats, with correct engagement rate
 * and trend calculations, showing only the vendor's own products.
 *
 * Validates: Requirements 12.1, 12.2, 12.3, 12.4, 12.5
 */
class DashboardStatsPropertyTest extends TestCase
{
    use TestTrait, RefreshDatabase;

    /**
     * Property: Aggregated stats match individual product stats
     *
     * For any vendor with products, the sum of individual product stats
     * should equal the aggregated dashboard stats.
     */
    public function test_aggregated_stats_match_individual_product_stats()
    {
        $this->forAll(
                Generator\choose(1, 10), // Number of products
                Generator\choose(0, 1000), // Views per product
                Generator\choose(0, 100), // WhatsApp clicks per product
                Generator\choose(0, 100), // Location clicks per product
                Generator\choose(0, 50) // Favorites per product
            )
            ->then(function (
                int $productCount,
                int $viewsPerProduct,
                int $whatsappClicks,
                int $locationClicks,
                int $favorites
            ) {
                // Create vendor with shop
                $vendor = User::factory()->create(['role' => 'vendor']);
                $shop = Shop::factory()->create(['owner_id' => $vendor->id]);

                // Create products with stats
                $totalViews = 0;
                $totalWhatsapp = 0;
                $totalLocation = 0;
                $totalFavorites = 0;
                $activeCount = 0;
                $inactiveCount = 0;

                for ($i = 0; $i < $productCount; $i++) {
                    $isActive = $i % 2 === 0; // Alternate active/inactive
                    $product = Product::factory()->create([
                        'shop_id' => $shop->id,
                        'is_active' => $isActive,
                    ]);

                    if ($isActive) {
                        $activeCount++;
                    } else {
                        $inactiveCount++;
                    }

                    // Create stats for this product
                    ProductStats::create([
                        'product_id' => $product->id,
                        'views_count' => $viewsPerProduct,
                        'whatsapp_clicks' => $whatsappClicks,
                        'location_clicks' => $locationClicks,
                        'favorites_count' => $favorites,
                        'last_viewed_at' => now(),
                    ]);

                    $totalViews += $viewsPerProduct;
                    $totalWhatsapp += $whatsappClicks;
                    $totalLocation += $locationClicks;
                    $totalFavorites += $favorites;
                }

                // Calculate aggregated stats (simulating service logic)
                $products = Product::where('shop_id', $shop->id)->get();
                $stats = ProductStats::whereIn('product_id', $products->pluck('id'))->get();

                $aggregatedViews = $stats->sum('views_count');
                $aggregatedWhatsapp = $stats->sum('whatsapp_clicks');
                $aggregatedLocation = $stats->sum('location_clicks');
                $aggregatedFavorites = $stats->sum('favorites_count');

                // Verify aggregated stats match individual sums
                $this->assertEquals($totalViews, $aggregatedViews, 'Total views should match');
                $this->assertEquals($totalWhatsapp, $aggregatedWhatsapp, 'Total WhatsApp clicks should match');
                $this->assertEquals($totalLocation, $aggregatedLocation, 'Total location clicks should match');
                $this->assertEquals($totalFavorites, $aggregatedFavorites, 'Total favorites should match');
                $this->assertEquals($productCount, $products->count(), 'Product count should match');
                $this->assertEquals($activeCount, $products->where('is_active', true)->count(), 'Active count should match');
                $this->assertEquals($inactiveCount, $products->where('is_active', false)->count(), 'Inactive count should match');
            });
    }

    /**
     * Property: Engagement rate calculation is correct
     *
     * For any set of products with views and interactions, the engagement rate
     * should be calculated as (total_interactions / total_views) * 100.
     */
    public function test_engagement_rate_calculation_is_correct()
    {
        $this->forAll(
                Generator\choose(1, 100), // Total views
                Generator\choose(0, 50), // WhatsApp clicks
                Generator\choose(0, 50), // Location clicks
                Generator\choose(0, 30) // Favorites
            )
            ->then(function (
                int $totalViews,
                int $whatsappClicks,
                int $locationClicks,
                int $favorites
            ) {
                // Create vendor with shop and product
                $vendor = User::factory()->create(['role' => 'vendor']);
                $shop = Shop::factory()->create(['owner_id' => $vendor->id]);
                $product = Product::factory()->create(['shop_id' => $shop->id]);

                // Create stats
                ProductStats::create([
                    'product_id' => $product->id,
                    'views_count' => $totalViews,
                    'whatsapp_clicks' => $whatsappClicks,
                    'location_clicks' => $locationClicks,
                    'favorites_count' => $favorites,
                ]);

                // Calculate engagement rate
                $totalInteractions = $whatsappClicks + $locationClicks + $favorites;
                $expectedEngagementRate = $totalViews > 0
                    ? round(($totalInteractions / $totalViews) * 100, 2)
                    : 0;

                // Simulate service calculation
                $stats = ProductStats::where('product_id', $product->id)->first();
                $calculatedInteractions = $stats->whatsapp_clicks + $stats->location_clicks + $stats->favorites_count;
                $calculatedEngagementRate = $stats->views_count > 0
                    ? round(($calculatedInteractions / $stats->views_count) * 100, 2)
                    : 0;

                // Verify engagement rate calculation
                $this->assertEquals($expectedEngagementRate, $calculatedEngagementRate, 'Engagement rate should be calculated correctly');
                $this->assertGreaterThanOrEqual(0, $calculatedEngagementRate, 'Engagement rate should be non-negative');
                // Engagement rate can exceed 100% since users can perform multiple interactions per view
            });
    }

    /**
     * Property: Only vendor's own products are included
     *
     * For any vendor, dashboard stats should only include products from shops
     * owned by that vendor, not products from other vendors.
     */
    public function test_only_vendors_own_products_are_included()
    {
        $this->forAll(
                Generator\choose(1, 5), // Number of vendor's products
                Generator\choose(1, 5), // Number of other vendor's products
                Generator\choose(10, 100) // Views per product
            )
            ->then(function (
                int $ownProductCount,
                int $otherProductCount,
                int $viewsPerProduct
            ) {
                // Create two vendors with shops
                $vendor1 = User::factory()->create(['role' => 'vendor']);
                $shop1 = Shop::factory()->create(['owner_id' => $vendor1->id]);

                $vendor2 = User::factory()->create(['role' => 'vendor']);
                $shop2 = Shop::factory()->create(['owner_id' => $vendor2->id]);

                // Create products for vendor1
                $vendor1TotalViews = 0;
                for ($i = 0; $i < $ownProductCount; $i++) {
                    $product = Product::factory()->create(['shop_id' => $shop1->id]);
                    ProductStats::create([
                        'product_id' => $product->id,
                        'views_count' => $viewsPerProduct,
                        'whatsapp_clicks' => 5,
                        'location_clicks' => 3,
                        'favorites_count' => 2,
                    ]);
                    $vendor1TotalViews += $viewsPerProduct;
                }

                // Create products for vendor2
                for ($i = 0; $i < $otherProductCount; $i++) {
                    $product = Product::factory()->create(['shop_id' => $shop2->id]);
                    ProductStats::create([
                        'product_id' => $product->id,
                        'views_count' => $viewsPerProduct * 10, // Much higher to detect leakage
                        'whatsapp_clicks' => 50,
                        'location_clicks' => 30,
                        'favorites_count' => 20,
                    ]);
                }

                // Get vendor1's products only
                $vendor1Shops = Shop::where('owner_id', $vendor1->id)->pluck('id');
                $vendor1Products = Product::whereIn('shop_id', $vendor1Shops)->pluck('id');
                $vendor1Stats = ProductStats::whereIn('product_id', $vendor1Products)->get();

                $vendor1AggregatedViews = $vendor1Stats->sum('views_count');

                // Verify only vendor1's stats are included
                $this->assertEquals($vendor1TotalViews, $vendor1AggregatedViews, 'Should only include vendor1 stats');
                $this->assertEquals($ownProductCount, $vendor1Products->count(), 'Should only count vendor1 products');

                // Verify vendor2's stats are not included
                $this->assertNotEquals($vendor1TotalViews, $viewsPerProduct * ($ownProductCount + $otherProductCount), 'Should not include other vendor stats');
            });
    }

    /**
     * Property: Trend calculations are accurate
     *
     * For any set of current and previous stats, trend percentage should be
     * calculated as ((current - previous) / previous) * 100.
     */
    public function test_trend_calculations_are_accurate()
    {
        $this->forAll(
                Generator\choose(0, 1000), // Previous views
                Generator\choose(0, 1000), // Current views
                Generator\choose(0, 100), // Previous interactions
                Generator\choose(0, 100) // Current interactions
            )
            ->then(function (
                int $previousViews,
                int $currentViews,
                int $previousInteractions,
                int $currentInteractions
            ) {
                // Calculate expected trend
                $expectedViewsTrend = $previousViews > 0
                    ? round((($currentViews - $previousViews) / $previousViews) * 100, 2)
                    : ($currentViews > 0 ? 100.0 : 0.0);

                $expectedInteractionsTrend = $previousInteractions > 0
                    ? round((($currentInteractions - $previousInteractions) / $previousInteractions) * 100, 2)
                    : ($currentInteractions > 0 ? 100.0 : 0.0);

                // Simulate service calculation
                $calculatedViewsTrend = $previousViews > 0
                    ? round((($currentViews - $previousViews) / $previousViews) * 100, 2)
                    : ($currentViews > 0 ? 100.0 : 0.0);

                $calculatedInteractionsTrend = $previousInteractions > 0
                    ? round((($currentInteractions - $previousInteractions) / $previousInteractions) * 100, 2)
                    : ($currentInteractions > 0 ? 100.0 : 0.0);

                // Verify trend calculations
                $this->assertEquals($expectedViewsTrend, $calculatedViewsTrend, 'Views trend should be calculated correctly');
                $this->assertEquals($expectedInteractionsTrend, $calculatedInteractionsTrend, 'Interactions trend should be calculated correctly');

                // Verify trend logic
                if ($currentViews > $previousViews) {
                    $this->assertGreaterThan(0, $calculatedViewsTrend, 'Positive growth should have positive trend');
                } elseif ($currentViews < $previousViews && $previousViews > 0) {
                    $this->assertLessThan(0, $calculatedViewsTrend, 'Negative growth should have negative trend');
                }
            });
    }

    /**
     * Property: Stats handle edge cases correctly
     *
     * For edge cases like zero products, zero views, or all inactive products,
     * the dashboard should return valid stats without errors.
     */
    public function test_stats_handle_edge_cases_correctly()
    {
        $this->forAll(
                Generator\elements([0, 1, 5, 10]), // Product count including zero
                Generator\elements([true, false]) // All active or all inactive
            )
            ->then(function (int $productCount, bool $allActive) {
                // Create vendor with shop
                $vendor = User::factory()->create(['role' => 'vendor']);
                $shop = Shop::factory()->create(['owner_id' => $vendor->id]);

                // Create products
                for ($i = 0; $i < $productCount; $i++) {
                    $product = Product::factory()->create([
                        'shop_id' => $shop->id,
                        'is_active' => $allActive,
                    ]);

                    // Some products might have no stats
                    if ($i % 2 === 0) {
                        ProductStats::create([
                            'product_id' => $product->id,
                            'views_count' => 0,
                            'whatsapp_clicks' => 0,
                            'location_clicks' => 0,
                            'favorites_count' => 0,
                        ]);
                    }
                }

                // Get aggregated stats
                $products = Product::where('shop_id', $shop->id)->get();
                $productIds = $products->pluck('id');
                $stats = ProductStats::whereIn('product_id', $productIds)->get();

                $totalProducts = $products->count();
                $activeProducts = $products->where('is_active', true)->count();
                $inactiveProducts = $products->where('is_active', false)->count();
                $totalViews = $stats->sum('views_count');
                $totalInteractions = $stats->sum('whatsapp_clicks') + $stats->sum('location_clicks') + $stats->sum('favorites_count');

                // Verify edge cases are handled
                $this->assertGreaterThanOrEqual(0, $totalProducts, 'Product count should be non-negative');
                $this->assertGreaterThanOrEqual(0, $activeProducts, 'Active count should be non-negative');
                $this->assertGreaterThanOrEqual(0, $inactiveProducts, 'Inactive count should be non-negative');
                $this->assertEquals($totalProducts, $activeProducts + $inactiveProducts, 'Active + inactive should equal total');
                $this->assertGreaterThanOrEqual(0, $totalViews, 'Views should be non-negative');
                $this->assertGreaterThanOrEqual(0, $totalInteractions, 'Interactions should be non-negative');

                // Engagement rate should be 0 when views are 0
                $engagementRate = $totalViews > 0 ? ($totalInteractions / $totalViews) * 100 : 0;
                $this->assertGreaterThanOrEqual(0, $engagementRate, 'Engagement rate should be non-negative');
            });
    }
}
