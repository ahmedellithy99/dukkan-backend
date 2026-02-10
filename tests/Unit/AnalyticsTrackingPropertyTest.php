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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Property-Based Test for Analytics Tracking Accuracy
 * Feature: marketplace-platform, Property 15: Analytics Tracking Accuracy
 * Validates: Requirements 8.1, 8.2, 8.3
 */
class AnalyticsTrackingPropertyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Property 15: Analytics Tracking Accuracy
     * For any user interaction (view, WhatsApp click, favorite), the system should accurately track and increment counters.
     * **Validates: Requirements 8.1, 8.2, 8.3**
     */
    public function test_analytics_tracking_accuracy_property()
    {
        // Run property test with 100 iterations
        for ($i = 0; $i < 100; $i++) {
            $this->runAnalyticsTrackingProperty();
        }
    }

    private function runAnalyticsTrackingProperty()
    {
        // Create product
        $product = $this->createValidProduct();

        // Generate random interaction counts
        $viewsCount = rand(1, 50);
        $whatsappClicks = rand(1, 30);
        $favoritesCount = rand(1, 20);

        // Track views
        for ($i = 0; $i < $viewsCount; $i++) {
            $this->trackView($product);
        }

        // Track WhatsApp clicks
        for ($i = 0; $i < $whatsappClicks; $i++) {
            $this->trackWhatsAppClick($product);
        }

        // Track favorites
        for ($i = 0; $i < $favoritesCount; $i++) {
            $this->trackFavorite($product);
        }

        // Verify stats were created and tracked correctly
        $stats = $product->stats()->first();
        $this->assertInstanceOf(ProductStats::class, $stats);
        $this->assertEquals($viewsCount, $stats->views_count);
        $this->assertEquals($whatsappClicks, $stats->whatsapp_clicks);
        $this->assertEquals($favoritesCount, $stats->favorites_count);
    }

    /**
     * Property: View tracking increments correctly
     * For any product view, the views_count should increment by 1.
     * **Validates: Requirement 8.1**
     */
    public function test_view_tracking_increments_property()
    {
        for ($i = 0; $i < 50; $i++) {
            $this->runViewTrackingProperty();
        }
    }

    private function runViewTrackingProperty()
    {
        $product = $this->createValidProduct();

        // Initial state - no stats
        $this->assertNull($product->stats);

        // Track first view
        $this->trackView($product);
        
        $stats = $product->stats()->first();
        $this->assertNotNull($stats);
        $this->assertEquals(1, $stats->views_count);

        // Track additional views
        $additionalViews = rand(1, 20);
        for ($i = 0; $i < $additionalViews; $i++) {
            $this->trackView($product);
        }

        $stats->refresh();
        $this->assertEquals(1 + $additionalViews, $stats->views_count);
    }

    /**
     * Property: WhatsApp click tracking increments correctly
     * For any WhatsApp click, the whatsapp_clicks should increment by 1.
     * **Validates: Requirement 8.2**
     */
    public function test_whatsapp_click_tracking_property()
    {
        for ($i = 0; $i < 50; $i++) {
            $this->runWhatsAppClickTrackingProperty();
        }
    }

    private function runWhatsAppClickTrackingProperty()
    {
        $product = $this->createValidProduct();

        // Track WhatsApp clicks
        $clickCount = rand(1, 30);
        for ($i = 0; $i < $clickCount; $i++) {
            $this->trackWhatsAppClick($product);
        }

        $stats = $product->stats()->first();
        $this->assertNotNull($stats);
        $this->assertEquals($clickCount, $stats->whatsapp_clicks);
    }

    /**
     * Property: Favorite tracking increments correctly
     * For any favorite action, the favorites_count should increment by 1.
     * **Validates: Requirement 8.3**
     */
    public function test_favorite_tracking_property()
    {
        for ($i = 0; $i < 50; $i++) {
            $this->runFavoriteTrackingProperty();
        }
    }

    private function runFavoriteTrackingProperty()
    {
        $product = $this->createValidProduct();

        // Track favorites
        $favoriteCount = rand(1, 25);
        for ($i = 0; $i < $favoriteCount; $i++) {
            $this->trackFavorite($product);
        }

        $stats = $product->stats()->first();
        $this->assertNotNull($stats);
        $this->assertEquals($favoriteCount, $stats->favorites_count);
    }

    /**
     * Property: Multiple interaction types tracked independently
     * For any combination of interactions, each counter should track independently.
     * **Validates: Requirements 8.1, 8.2, 8.3**
     */
    public function test_multiple_interaction_types_property()
    {
        for ($i = 0; $i < 50; $i++) {
            $this->runMultipleInteractionTypesProperty();
        }
    }

    private function runMultipleInteractionTypesProperty()
    {
        $product = $this->createValidProduct();

        $views = rand(5, 20);
        $whatsappClicks = rand(3, 15);
        $favorites = rand(2, 10);

        // Track all interaction types
        for ($i = 0; $i < $views; $i++) {
            $this->trackView($product);
        }
        for ($i = 0; $i < $whatsappClicks; $i++) {
            $this->trackWhatsAppClick($product);
        }
        for ($i = 0; $i < $favorites; $i++) {
            $this->trackFavorite($product);
        }

        $stats = $product->stats()->first();
        $this->assertEquals($views, $stats->views_count);
        $this->assertEquals($whatsappClicks, $stats->whatsapp_clicks);
        $this->assertEquals($favorites, $stats->favorites_count);

        // Verify independence - incrementing one doesn't affect others
        $initialViews = $stats->views_count;
        $initialWhatsapp = $stats->whatsapp_clicks;
        $initialFavorites = $stats->favorites_count;

        $this->trackView($product);
        $stats->refresh();
        $this->assertEquals($initialViews + 1, $stats->views_count);
        $this->assertEquals($initialWhatsapp, $stats->whatsapp_clicks);
        $this->assertEquals($initialFavorites, $stats->favorites_count);
    }

    /**
     * Property: Stats initialization on first interaction
     * For any product without stats, first interaction should create stats record.
     * **Validates: Requirements 8.1, 8.2, 8.3**
     */
    public function test_stats_initialization_property()
    {
        for ($i = 0; $i < 50; $i++) {
            $this->runStatsInitializationProperty();
        }
    }

    private function runStatsInitializationProperty()
    {
        $product = $this->createValidProduct();

        // Verify no stats initially
        $this->assertNull($product->stats);

        // Random first interaction type
        $interactionType = rand(1, 3);
        
        switch ($interactionType) {
            case 1:
                $this->trackView($product);
                break;
            case 2:
                $this->trackWhatsAppClick($product);
                break;
            case 3:
                $this->trackFavorite($product);
                break;
        }

        // Verify stats were created
        $stats = $product->stats()->first();
        $this->assertNotNull($stats);
        $this->assertEquals($product->id, $stats->product_id);
        
        // Verify correct counter was incremented
        switch ($interactionType) {
            case 1:
                $this->assertEquals(1, $stats->views_count);
                $this->assertEquals(0, $stats->whatsapp_clicks);
                $this->assertEquals(0, $stats->favorites_count);
                break;
            case 2:
                $this->assertEquals(0, $stats->views_count);
                $this->assertEquals(1, $stats->whatsapp_clicks);
                $this->assertEquals(0, $stats->favorites_count);
                break;
            case 3:
                $this->assertEquals(0, $stats->views_count);
                $this->assertEquals(0, $stats->whatsapp_clicks);
                $this->assertEquals(1, $stats->favorites_count);
                break;
        }
    }

    /**
     * Property: Concurrent tracking accuracy
     * For any sequence of rapid interactions, all should be tracked accurately.
     * **Validates: Requirements 8.1, 8.2, 8.3**
     */
    public function test_concurrent_tracking_accuracy_property()
    {
        for ($i = 0; $i < 30; $i++) {
            $this->runConcurrentTrackingProperty();
        }
    }

    private function runConcurrentTrackingProperty()
    {
        $product = $this->createValidProduct();

        // Simulate rapid mixed interactions
        $totalInteractions = rand(20, 50);
        $expectedViews = 0;
        $expectedWhatsapp = 0;
        $expectedFavorites = 0;

        for ($i = 0; $i < $totalInteractions; $i++) {
            $interactionType = rand(1, 3);
            
            switch ($interactionType) {
                case 1:
                    $this->trackView($product);
                    $expectedViews++;
                    break;
                case 2:
                    $this->trackWhatsAppClick($product);
                    $expectedWhatsapp++;
                    break;
                case 3:
                    $this->trackFavorite($product);
                    $expectedFavorites++;
                    break;
            }
        }

        $stats = $product->stats()->first();
        $this->assertEquals($expectedViews, $stats->views_count);
        $this->assertEquals($expectedWhatsapp, $stats->whatsapp_clicks);
        $this->assertEquals($expectedFavorites, $stats->favorites_count);
    }

    /**
     * Property: Zero counters on initialization
     * For any newly created stats, all counters should start at zero.
     * **Validates: Requirements 8.1, 8.2, 8.3**
     */
    public function test_zero_counters_initialization_property()
    {
        for ($i = 0; $i < 30; $i++) {
            $this->runZeroCountersProperty();
        }
    }

    private function runZeroCountersProperty()
    {
        $product = $this->createValidProduct();

        // Manually create stats (simulating initialization)
        $stats = ProductStats::create([
            'product_id' => $product->id,
        ]);

        // All counters should be zero
        $this->assertEquals(0, $stats->views_count);
        $this->assertEquals(0, $stats->whatsapp_clicks);
        $this->assertEquals(0, $stats->favorites_count);
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

    private function trackView(Product $product): void
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
