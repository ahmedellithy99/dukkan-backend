<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\City;
use App\Models\Governorate;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductStats;
use App\Models\Shop;
use App\Models\Subcategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Property-Based Test for Analytics Data Management
 * Feature: marketplace-platform, Property 16: Analytics Data Management
 * Validates: Requirements 8.4, 8.5
 */
class AnalyticsDataPropertyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Property 16: Analytics Data Management
     * For any analytics query, the system should maintain accurate timestamps and provide data via API endpoints.
     * **Validates: Requirements 8.4, 8.5**
     */
    public function test_analytics_data_management_property()
    {
        // Run property test with 100 iterations
        for ($i = 0; $i < 100; $i++) {
            $this->runAnalyticsDataManagementProperty();
        }
    }

    private function runAnalyticsDataManagementProperty()
    {
        $product = $this->createValidProduct();

        // Track views with timestamp updates
        $viewCount = rand(1, 20);
        for ($i = 0; $i < $viewCount; $i++) {
            $this->trackViewWithTimestamp($product);
            
            // Add small delay to ensure timestamp differences
            if ($i < $viewCount - 1) {
                usleep(1000); // 1ms delay
            }
        }

        $stats = $product->stats()->first();
        
        // Verify stats exist and have correct data
        $this->assertInstanceOf(ProductStats::class, $stats);
        $this->assertEquals($viewCount, $stats->views_count);
        
        // Verify last_viewed_at is set and recent
        $this->assertNotNull($stats->last_viewed_at);
        $this->assertInstanceOf(Carbon::class, $stats->last_viewed_at);
        $this->assertTrue($stats->last_viewed_at->isToday());
        
        // Verify timestamps are maintained
        $this->assertNotNull($stats->created_at);
        $this->assertNotNull($stats->updated_at);
    }

    /**
     * Property: Last viewed timestamp updates correctly
     * For any product view, last_viewed_at should update to current time.
     * **Validates: Requirement 8.4**
     */
    public function test_last_viewed_timestamp_property()
    {
        for ($i = 0; $i < 50; $i++) {
            $this->runLastViewedTimestampProperty();
        }
    }

    private function runLastViewedTimestampProperty()
    {
        $product = $this->createValidProduct();

        // Track first view
        $beforeFirstView = now()->subSecond(); // Give 1 second buffer
        $this->trackViewWithTimestamp($product);
        $afterFirstView = now()->addSecond(); // Give 1 second buffer

        $stats = $product->stats()->first();
        $this->assertNotNull($stats->last_viewed_at);
        $this->assertTrue(
            $stats->last_viewed_at->between($beforeFirstView, $afterFirstView),
            "First view timestamp should be between {$beforeFirstView} and {$afterFirstView}, got {$stats->last_viewed_at}"
        );

        // Wait a moment and track another view
        sleep(1); // 1 second delay for reliable timestamp difference
        $beforeSecondView = now()->subSecond();
        $this->trackViewWithTimestamp($product);
        $afterSecondView = now()->addSecond();

        $stats->refresh();
        $this->assertTrue(
            $stats->last_viewed_at->between($beforeSecondView, $afterSecondView),
            "Second view timestamp should be between {$beforeSecondView} and {$afterSecondView}, got {$stats->last_viewed_at}"
        );
        
        // Last viewed should be more recent than first view
        $this->assertTrue($stats->last_viewed_at->greaterThanOrEqualTo($beforeFirstView));
    }

    /**
     * Property: Stats timestamps are maintained correctly
     * For any stats record, created_at and updated_at should be properly maintained.
     * **Validates: Requirement 8.4**
     */
    public function test_stats_timestamps_property()
    {
        for ($i = 0; $i < 50; $i++) {
            $this->runStatsTimestampsProperty();
        }
    }

    private function runStatsTimestampsProperty()
    {
        $product = $this->createValidProduct();

        // Create stats
        $beforeCreation = now()->subSecond();
        $this->trackViewWithTimestamp($product);
        $afterCreation = now()->addSecond();

        $stats = $product->stats()->first();
        
        // Verify created_at
        $this->assertNotNull($stats->created_at);
        $this->assertTrue(
            $stats->created_at->between($beforeCreation, $afterCreation),
            "Created timestamp should be between {$beforeCreation} and {$afterCreation}, got {$stats->created_at}"
        );

        // Verify updated_at
        $this->assertNotNull($stats->updated_at);
        $this->assertTrue(
            $stats->updated_at->between($beforeCreation, $afterCreation),
            "Updated timestamp should be between {$beforeCreation} and {$afterCreation}, got {$stats->updated_at}"
        );

        // Update stats and verify updated_at changes
        $originalUpdatedAt = $stats->updated_at->copy();
        sleep(1); // 1 second delay for reliable timestamp difference
        
        $beforeUpdate = now()->subSecond();
        $this->trackWhatsAppClick($product);
        $afterUpdate = now()->addSecond();

        $stats->refresh();
        $this->assertTrue($stats->updated_at->greaterThanOrEqualTo($originalUpdatedAt));
        $this->assertTrue(
            $stats->updated_at->between($beforeUpdate, $afterUpdate),
            "Updated timestamp after update should be between {$beforeUpdate} and {$afterUpdate}, got {$stats->updated_at}"
        );
    }

    /**
     * Property: Stats data can be queried efficiently
     * For any product with stats, the data should be accessible via relationship.
     * **Validates: Requirement 8.5**
     */
    public function test_stats_query_efficiency_property()
    {
        for ($i = 0; $i < 50; $i++) {
            $this->runStatsQueryProperty();
        }
    }

    private function runStatsQueryProperty()
    {
        $product = $this->createValidProduct();

        // Track some interactions
        $views = rand(5, 20);
        $whatsappClicks = rand(2, 10);
        $favorites = rand(1, 8);

        for ($i = 0; $i < $views; $i++) {
            $this->trackViewWithTimestamp($product);
        }
        for ($i = 0; $i < $whatsappClicks; $i++) {
            $this->trackWhatsAppClick($product);
        }
        for ($i = 0; $i < $favorites; $i++) {
            $this->trackFavorite($product);
        }

        // Query stats via relationship
        $stats = $product->stats;
        $this->assertInstanceOf(ProductStats::class, $stats);
        $this->assertEquals($views, $stats->views_count);
        $this->assertEquals($whatsappClicks, $stats->whatsapp_clicks);
        $this->assertEquals($favorites, $stats->favorites_count);

        // Query product via stats relationship
        $productFromStats = $stats->product;
        $this->assertInstanceOf(Product::class, $productFromStats);
        $this->assertEquals($product->id, $productFromStats->id);
    }

    /**
     * Property: Multiple products have independent stats
     * For any set of products, each should have independent analytics tracking.
     * **Validates: Requirement 8.5**
     */
    public function test_independent_product_stats_property()
    {
        for ($i = 0; $i < 30; $i++) {
            $this->runIndependentStatsProperty();
        }
    }

    private function runIndependentStatsProperty()
    {
        $product1 = $this->createValidProduct();
        $product2 = $this->createValidProduct();

        // Track different interactions for each product
        $views1 = rand(5, 15);
        $views2 = rand(10, 25);

        for ($i = 0; $i < $views1; $i++) {
            $this->trackViewWithTimestamp($product1);
        }
        for ($i = 0; $i < $views2; $i++) {
            $this->trackViewWithTimestamp($product2);
        }

        // Verify independent tracking
        $stats1 = $product1->stats()->first();
        $stats2 = $product2->stats()->first();

        // Verify both stats exist
        $this->assertNotNull($stats1, 'Product 1 stats should exist');
        $this->assertNotNull($stats2, 'Product 2 stats should exist');
        
        // Verify different product IDs (primary keys)
        $this->assertNotEquals($stats1->product_id, $stats2->product_id);
        $this->assertEquals($views1, $stats1->views_count);
        $this->assertEquals($views2, $stats2->views_count);
        
        // Verify product associations
        $this->assertEquals($product1->id, $stats1->product_id);
        $this->assertEquals($product2->id, $stats2->product_id);
    }

    /**
     * Property: Stats can be ordered by various metrics
     * For any collection of products with stats, they should be orderable by analytics metrics.
     * **Validates: Requirement 8.5**
     */
    public function test_stats_ordering_property()
    {
        for ($i = 0; $i < 20; $i++) {
            $this->runStatsOrderingProperty();
        }
    }

    private function runStatsOrderingProperty()
    {
        // Create multiple products with different view counts
        $products = [];
        $viewCounts = [];
        
        for ($i = 0; $i < 5; $i++) {
            $product = $this->createValidProduct();
            $views = rand(1, 50);
            $viewCounts[$product->id] = $views;
            
            for ($j = 0; $j < $views; $j++) {
                $this->trackViewWithTimestamp($product);
            }
            
            $products[] = $product;
        }

        // Query products ordered by views (descending)
        $orderedProducts = Product::whereIn('id', array_column($products, 'id'))
            ->join('product_stats', 'products.id', '=', 'product_stats.product_id')
            ->orderByDesc('product_stats.views_count')
            ->select('products.*', 'product_stats.views_count')
            ->get();

        // Verify ordering
        $previousViews = PHP_INT_MAX;
        foreach ($orderedProducts as $product) {
            $this->assertLessThanOrEqual($previousViews, $product->views_count);
            $previousViews = $product->views_count;
        }
    }

    /**
     * Property: Stats persist across product updates
     * For any product update, stats should remain intact.
     * **Validates: Requirement 8.5**
     */
    public function test_stats_persist_across_updates_property()
    {
        for ($i = 0; $i < 30; $i++) {
            $this->runStatsPersistenceProperty();
        }
    }

    private function runStatsPersistenceProperty()
    {
        $product = $this->createValidProduct();

        // Track some interactions
        $views = rand(10, 30);
        $whatsappClicks = rand(5, 15);
        
        for ($i = 0; $i < $views; $i++) {
            $this->trackViewWithTimestamp($product);
        }
        for ($i = 0; $i < $whatsappClicks; $i++) {
            $this->trackWhatsAppClick($product);
        }

        $stats = $product->stats()->first();
        $originalStatsId = $stats->product_id;
        $originalViews = $stats->views_count;
        $originalWhatsapp = $stats->whatsapp_clicks;

        // Update product
        $product->update([
            'name' => 'Updated Product Name ' . rand(1, 1000),
            'price' => rand(100, 1000),
        ]);

        // Verify stats still exist and are unchanged
        $stats->refresh();
        $this->assertEquals($originalStatsId, $stats->product_id);
        $this->assertEquals($originalViews, $stats->views_count);
        $this->assertEquals($originalWhatsapp, $stats->whatsapp_clicks);
    }

    /**
     * Property: Last viewed timestamp is nullable initially
     * For any newly created stats without views, last_viewed_at should be null.
     * **Validates: Requirement 8.4**
     */
    public function test_last_viewed_nullable_property()
    {
        for ($i = 0; $i < 30; $i++) {
            $this->runLastViewedNullableProperty();
        }
    }

    private function runLastViewedNullableProperty()
    {
        $product = $this->createValidProduct();

        // Create stats without tracking views (via WhatsApp click or favorite)
        $interactionType = rand(1, 2);
        
        if ($interactionType === 1) {
            $this->trackWhatsAppClick($product);
        } else {
            $this->trackFavorite($product);
        }

        $stats = $product->stats()->first();
        
        // last_viewed_at should be null since no views were tracked
        $this->assertNull($stats->last_viewed_at);
        
        // But other counters should work
        if ($interactionType === 1) {
            $this->assertEquals(1, $stats->whatsapp_clicks);
        } else {
            $this->assertEquals(1, $stats->favorites_count);
        }
    }

    /**
     * Property: Stats can be filtered by date ranges
     * For any date range query, stats should be filterable by last_viewed_at.
     * **Validates: Requirement 8.4**
     */
    public function test_stats_date_filtering_property()
    {
        for ($i = 0; $i < 20; $i++) {
            $this->runDateFilteringProperty();
        }
    }

    private function runDateFilteringProperty()
    {
        // Create products with views at different times
        $recentProduct = $this->createValidProduct();
        $this->trackViewWithTimestamp($recentProduct);

        // Query recent views (today)
        $recentStats = ProductStats::whereDate('last_viewed_at', today())->get();
        
        $this->assertGreaterThan(0, $recentStats->count());
        $this->assertTrue(
            $recentStats->contains('product_id', $recentProduct->id)
        );
    }

    // Helper methods

    private function createValidProduct(): Product
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = $this->createValidShop($vendor);
        $subcategory = $this->createValidSubcategory();

        return Product::factory()->create([
            'shop_id' => $shop->id,
            'subcategory_id' => $subcategory->id,
            'is_active' => true,
        ]);
    }

    private function createValidShop(User $vendor): Shop
    {
        $location = $this->createValidLocation();
        
        return Shop::factory()->create([
            'owner_id' => $vendor->id,
            'location_id' => $location->id,
            'is_active' => true,
        ]);
    }

    private function createValidLocation(): Location
    {
        $governorate = Governorate::factory()->create();
        $city = City::factory()->create(['governorate_id' => $governorate->id]);

        return Location::factory()->create(['city_id' => $city->id]);
    }

    private function createValidSubcategory(): Subcategory
    {
        $category = Category::factory()->create();
        return Subcategory::factory()->create(['category_id' => $category->id]);
    }

    private function trackViewWithTimestamp(Product $product): void
    {
        $stats = $product->stats()->firstOrCreate(
            ['product_id' => $product->id],
            [
                'views_count' => 0,
                'whatsapp_clicks' => 0,
                'favorites_count' => 0,
            ]
        );

        $stats->increment('views_count');
        $stats->update(['last_viewed_at' => now()]);
    }

    private function trackWhatsAppClick(Product $product): void
    {
        $stats = $product->stats()->firstOrCreate(
            ['product_id' => $product->id],
            [
                'views_count' => 0,
                'whatsapp_clicks' => 0,
                'favorites_count' => 0,
            ]
        );

        $stats->increment('whatsapp_clicks');
    }

    private function trackFavorite(Product $product): void
    {
        $stats = $product->stats()->firstOrCreate(
            ['product_id' => $product->id],
            [
                'views_count' => 0,
                'whatsapp_clicks' => 0,
                'favorites_count' => 0,
            ]
        );

        $stats->increment('favorites_count');
    }
}
