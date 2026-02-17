<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductActivity;
use App\Models\Shop;
use App\Models\User;
use Eris\Generator;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature: marketplace-platform, Property 23: Activity Feed Accuracy
 *
 * Property: For any vendor with product activities, the recent activity feed
 * should return activities in chronological order, track all activity types correctly,
 * show only the vendor's product activities, and support timeframe filtering.
 *
 * Validates: Requirements 13.1, 13.2, 13.3, 13.4, 13.5
 */
class RecentActivityPropertyTest extends TestCase
{
    use TestTrait, RefreshDatabase;

    /**
     * Property: Activities are returned in chronological order
     *
     * For any set of activities with different timestamps, they should be
     * returned in descending chronological order (most recent first).
     */
    public function test_activities_are_returned_in_chronological_order()
    {
        $this->forAll(
                Generator\choose(2, 20) // Number of activities
            )
            ->then(function (int $activityCount) {
                // Create vendor with shop and product
                $vendor = User::factory()->create(['role' => 'vendor']);
                $shop = Shop::factory()->create(['owner_id' => $vendor->id]);
                $product = Product::factory()->create(['shop_id' => $shop->id]);

                // Use a fixed reference time
                $referenceTime = now();

                // Create activities with different timestamps
                for ($i = 0; $i < $activityCount; $i++) {
                    $timestamp = $referenceTime->copy()->subMinutes($i * 10);
                    
                    ProductActivity::create([
                        'product_id' => $product->id,
                        'activity_type' => 'view',
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ]);
                }

                // Retrieve activities
                $activities = ProductActivity::where('product_id', $product->id)
                    ->orderBy('created_at', 'desc')
                    ->get();

                // Verify chronological order (most recent first)
                $this->assertEquals($activityCount, $activities->count(), 'Should return all activities');
                
                for ($i = 0; $i < $activities->count() - 1; $i++) {
                    $this->assertGreaterThanOrEqual(
                        $activities[$i + 1]->created_at->timestamp,
                        $activities[$i]->created_at->timestamp,
                        'Activities should be in descending chronological order'
                    );
                }

                // Verify most recent is first (within 1 second tolerance for timing)
                $expectedFirst = $referenceTime->copy()->subMinutes(0);
                $this->assertLessThanOrEqual(
                    1,
                    abs($expectedFirst->timestamp - $activities->first()->created_at->timestamp),
                    'Most recent activity should be first (within 1 second tolerance)'
                );
            });
    }

    /**
     * Property: All activity types are tracked correctly
     *
     * For any combination of activity types, each type should be tracked
     * and retrievable with correct type information.
     */
    public function test_all_activity_types_are_tracked_correctly()
    {
        $this->forAll(
                Generator\choose(1, 5), // Activities per type
                Generator\elements(['view', 'whatsapp_click', 'location_click', 'sms_click', 'favorite'])
            )
            ->then(function (int $countPerType, string $activityType) {
                // Create vendor with shop and product
                $vendor = User::factory()->create(['role' => 'vendor']);
                $shop = Shop::factory()->create(['owner_id' => $vendor->id]);
                $product = Product::factory()->create(['shop_id' => $shop->id]);

                // Create activities of specific type
                for ($i = 0; $i < $countPerType; $i++) {
                    ProductActivity::create([
                        'product_id' => $product->id,
                        'activity_type' => $activityType,
                        'created_at' => now()->subMinutes($i),
                    ]);
                }

                // Retrieve activities by type
                $activities = ProductActivity::where('product_id', $product->id)
                    ->where('activity_type', $activityType)
                    ->get();

                // Verify all activities are of correct type
                $this->assertEquals($countPerType, $activities->count(), 'Should return all activities of type');
                
                foreach ($activities as $activity) {
                    $this->assertEquals($activityType, $activity->activity_type, 'Activity type should match');
                    $this->assertEquals($product->id, $activity->product_id, 'Product ID should match');
                }

                // Verify activity type is one of the valid types
                $validTypes = ['view', 'whatsapp_click', 'location_click', 'sms_click', 'favorite'];
                $this->assertContains($activityType, $validTypes, 'Activity type should be valid');
            });
    }

    /**
     * Property: Only vendor's product activities are shown
     *
     * For any vendor, the activity feed should only include activities
     * from products belonging to shops owned by that vendor.
     */
    public function test_only_vendors_product_activities_are_shown()
    {
        $this->forAll(
                Generator\choose(1, 5), // Vendor 1 activities
                Generator\choose(1, 5)  // Vendor 2 activities
            )
            ->then(function (int $vendor1Activities, int $vendor2Activities) {
                // Create two vendors with shops and products
                $vendor1 = User::factory()->create(['role' => 'vendor']);
                $shop1 = Shop::factory()->create(['owner_id' => $vendor1->id]);
                $product1 = Product::factory()->create(['shop_id' => $shop1->id]);

                $vendor2 = User::factory()->create(['role' => 'vendor']);
                $shop2 = Shop::factory()->create(['owner_id' => $vendor2->id]);
                $product2 = Product::factory()->create(['shop_id' => $shop2->id]);

                // Create activities for vendor1's product
                for ($i = 0; $i < $vendor1Activities; $i++) {
                    ProductActivity::create([
                        'product_id' => $product1->id,
                        'activity_type' => 'view',
                        'created_at' => now()->subMinutes($i),
                    ]);
                }

                // Create activities for vendor2's product
                for ($i = 0; $i < $vendor2Activities; $i++) {
                    ProductActivity::create([
                        'product_id' => $product2->id,
                        'activity_type' => 'whatsapp_click',
                        'created_at' => now()->subMinutes($i),
                    ]);
                }

                // Get vendor1's shop IDs and product IDs
                $vendor1ShopIds = Shop::where('owner_id', $vendor1->id)->pluck('id');
                $vendor1ProductIds = Product::whereIn('shop_id', $vendor1ShopIds)->pluck('id');

                // Retrieve activities for vendor1's products only
                $vendor1ActivitiesResult = ProductActivity::whereIn('product_id', $vendor1ProductIds)->get();

                // Verify only vendor1's activities are included
                $this->assertEquals($vendor1Activities, $vendor1ActivitiesResult->count(), 'Should only include vendor1 activities');
                
                foreach ($vendor1ActivitiesResult as $activity) {
                    $this->assertTrue(
                        $vendor1ProductIds->contains($activity->product_id),
                        'Activity should belong to vendor1 product'
                    );
                    $this->assertFalse(
                        $activity->product_id === $product2->id,
                        'Should not include vendor2 activities'
                    );
                }

                // Verify vendor2's activities are separate
                $vendor2ShopIds = Shop::where('owner_id', $vendor2->id)->pluck('id');
                $vendor2ProductIds = Product::whereIn('shop_id', $vendor2ShopIds)->pluck('id');
                $vendor2ActivitiesResult = ProductActivity::whereIn('product_id', $vendor2ProductIds)->get();

                $this->assertEquals($vendor2Activities, $vendor2ActivitiesResult->count(), 'Should only include vendor2 activities');
            });
    }

    /**
     * Property: Timeframe filtering works correctly
     *
     * For any timeframe filter, only activities within that timeframe
     * should be returned, excluding older activities.
     */
    public function test_timeframe_filtering_works_correctly()
    {
        $this->forAll(
                Generator\choose(7, 30), // Days for timeframe
                Generator\choose(5, 10)  // Activities per group
            )
            ->then(function (int $days, int $activitiesPerGroup) {
                // Create vendor with shop and product
                $vendor = User::factory()->create(['role' => 'vendor']);
                $shop = Shop::factory()->create(['owner_id' => $vendor->id]);
                $product = Product::factory()->create(['shop_id' => $shop->id]);

                // Use a single reference time for all calculations
                $referenceTime = now();

                // Create recent activities (within timeframe)
                for ($i = 0; $i < $activitiesPerGroup; $i++) {
                    // Create activities 1 to ($days - 2) days ago
                    $daysAgo = rand(1, max(1, $days - 2));
                    $timestamp = (clone $referenceTime)->subDays($daysAgo);
                    
                    ProductActivity::create([
                        'product_id' => $product->id,
                        'activity_type' => 'view',
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ]);
                }

                // Create old activities (outside timeframe)
                $oldTimestamps = [];
                for ($i = 0; $i < $activitiesPerGroup; $i++) {
                    // Create activities ($days + 2) to ($days + 30) days ago
                    $daysAgo = $days + rand(2, 30);
                    $timestamp = (clone $referenceTime)->subDays($daysAgo);
                    $oldTimestamps[] = ['days_ago' => $daysAgo, 'timestamp' => $timestamp->timestamp];
                    
                    ProductActivity::create([
                        'product_id' => $product->id,
                        'activity_type' => 'whatsapp_click',
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ]);
                }

                // Calculate cutoff date for filtering
                $cutoffDate = (clone $referenceTime)->subDays($days);
                
                // Debug: Check if old timestamps are actually before cutoff
                foreach ($oldTimestamps as $old) {
                    $this->assertLessThan(
                        $cutoffDate->timestamp,
                        $old['timestamp'],
                        "Old activity (days_ago: {$old['days_ago']}) should be before cutoff (days: $days)"
                    );
                }
                
                // Apply timeframe filter
                $filteredActivities = ProductActivity::where('product_id', $product->id)
                    ->where('created_at', '>=', $cutoffDate)
                    ->get();

                // Get all activities and manually count recent vs old
                $allActivities = ProductActivity::where('product_id', $product->id)->get();
                
                $recentCount = 0;
                $oldCount = 0;
                $debugInfo = [];
                foreach ($allActivities as $activity) {
                    $activityTimestamp = $activity->created_at->timestamp;
                    $cutoffTimestamp = $cutoffDate->timestamp;
                    $isRecent = $activity->created_at >= $cutoffDate;
                    
                    $debugInfo[] = [
                        'type' => $activity->activity_type,
                        'timestamp' => $activityTimestamp,
                        'cutoff' => $cutoffTimestamp,
                        'diff' => $activityTimestamp - $cutoffTimestamp,
                        'is_recent' => $isRecent,
                    ];
                    
                    // Use the same comparison as the query
                    if ($isRecent) {
                        $recentCount++;
                    } else {
                        $oldCount++;
                    }
                }

                // If all are recent, dump debug info
                if ($oldCount === 0 && count($debugInfo) > 0) {
                    // Find a whatsapp_click activity (should be old)
                    $whatsappActivity = collect($debugInfo)->firstWhere('type', 'whatsapp_click');
                    if ($whatsappActivity) {
                        $this->fail("All activities are recent. WhatsApp activity (should be old): timestamp={$whatsappActivity['timestamp']}, cutoff={$whatsappActivity['cutoff']}, diff={$whatsappActivity['diff']}");
                    } else {
                        $sample = $debugInfo[0];
                        $this->fail("All activities are recent. Sample: type={$sample['type']}, timestamp={$sample['timestamp']}, cutoff={$sample['cutoff']}, diff={$sample['diff']}");
                    }
                }

                // Verify filtered activities match manual count
                $this->assertEquals(
                    $recentCount,
                    $filteredActivities->count(),
                    'Filtered activities should match manual count'
                );
                
                // Verify all filtered activities are within timeframe
                foreach ($filteredActivities as $activity) {
                    $this->assertGreaterThanOrEqual(
                        $cutoffDate->timestamp,
                        $activity->created_at->timestamp,
                        'Activity should be within timeframe'
                    );
                }

                // Verify total count
                $this->assertEquals($activitiesPerGroup * 2, $allActivities->count(), 'Total activities should match');
                
                // Verify we have both recent and old activities
                $this->assertGreaterThan(0, $recentCount, 'Should have some recent activities');
                $this->assertGreaterThan(
                    0,
                    $oldCount,
                    "Should have some old activities (recent: $recentCount, old: $oldCount, days: $days)"
                );
            });
    }

    /**
     * Property: Activity feed includes product details
     *
     * For any activity, the feed should include associated product details
     * through proper relationship loading.
     */
    public function test_activity_feed_includes_product_details()
    {
        $this->forAll(
                Generator\choose(1, 10) // Number of activities
            )
            ->then(function (int $activityCount) {
                // Create vendor with shop and product
                $vendor = User::factory()->create(['role' => 'vendor']);
                $shop = Shop::factory()->create(['owner_id' => $vendor->id]);
                $product = Product::factory()->create([
                    'shop_id' => $shop->id,
                    'name' => 'Test Product',
                ]);

                // Create activities
                for ($i = 0; $i < $activityCount; $i++) {
                    ProductActivity::create([
                        'product_id' => $product->id,
                        'activity_type' => 'view',
                        'created_at' => now()->subMinutes($i),
                    ]);
                }

                // Retrieve activities with product relationship
                $activities = ProductActivity::with('product')
                    ->where('product_id', $product->id)
                    ->get();

                // Verify product details are included
                $this->assertEquals($activityCount, $activities->count(), 'Should return all activities');
                
                foreach ($activities as $activity) {
                    $this->assertNotNull($activity->product, 'Product relationship should be loaded');
                    $this->assertEquals($product->id, $activity->product->id, 'Product ID should match');
                    $this->assertEquals($product->name, $activity->product->name, 'Product name should match');
                    $this->assertEquals($shop->id, $activity->product->shop_id, 'Shop ID should match');
                }
            });
    }

    /**
     * Property: Activity tracking handles edge cases
     *
     * For edge cases like no activities, single activity, or activities
     * at exact timeframe boundaries, the system should handle them correctly.
     */
    public function test_activity_tracking_handles_edge_cases()
    {
        $this->forAll(
                Generator\elements([0, 1, 2]), // Activity count including zero
                Generator\choose(1, 7)         // Days for timeframe
            )
            ->then(function (int $activityCount, int $days) {
                // Create vendor with shop and product
                $vendor = User::factory()->create(['role' => 'vendor']);
                $shop = Shop::factory()->create(['owner_id' => $vendor->id]);
                $product = Product::factory()->create(['shop_id' => $shop->id]);

                // Create activities
                for ($i = 0; $i < $activityCount; $i++) {
                    ProductActivity::create([
                        'product_id' => $product->id,
                        'activity_type' => 'view',
                        'created_at' => now()->subDays($i),
                    ]);
                }

                // Retrieve activities
                $allActivities = ProductActivity::where('product_id', $product->id)->get();
                $recentActivities = ProductActivity::where('product_id', $product->id)
                    ->where('created_at', '>=', now()->subDays($days))
                    ->get();

                // Verify edge cases are handled
                $this->assertEquals($activityCount, $allActivities->count(), 'Should handle any activity count');
                $this->assertGreaterThanOrEqual(0, $recentActivities->count(), 'Recent activities should be non-negative');
                $this->assertLessThanOrEqual($activityCount, $recentActivities->count(), 'Recent should be subset of all');

                // Verify empty result set is handled
                if ($activityCount === 0) {
                    $this->assertEmpty($allActivities, 'Should handle zero activities');
                    $this->assertEmpty($recentActivities, 'Should handle zero recent activities');
                }

                // Verify single activity is handled
                if ($activityCount === 1) {
                    $this->assertCount(1, $allActivities, 'Should handle single activity');
                }
            });
    }

    /**
     * Property: Multiple products per vendor are tracked separately
     *
     * For any vendor with multiple products, activities should be tracked
     * per product and aggregatable across all vendor products.
     */
    public function test_multiple_products_per_vendor_are_tracked_separately()
    {
        $this->forAll(
                Generator\choose(2, 5),  // Number of products
                Generator\choose(1, 10)  // Activities per product
            )
            ->then(function (int $productCount, int $activitiesPerProduct) {
                // Create vendor with shop
                $vendor = User::factory()->create(['role' => 'vendor']);
                $shop = Shop::factory()->create(['owner_id' => $vendor->id]);

                // Create multiple products with activities
                $productIds = [];
                $totalActivities = 0;

                for ($p = 0; $p < $productCount; $p++) {
                    $product = Product::factory()->create(['shop_id' => $shop->id]);
                    $productIds[] = $product->id;

                    // Create activities for this product
                    for ($i = 0; $i < $activitiesPerProduct; $i++) {
                        ProductActivity::create([
                            'product_id' => $product->id,
                            'activity_type' => 'view',
                            'created_at' => now()->subMinutes($i),
                        ]);
                        $totalActivities++;
                    }
                }

                // Verify each product has correct activity count
                foreach ($productIds as $productId) {
                    $productActivities = ProductActivity::where('product_id', $productId)->get();
                    $this->assertEquals(
                        $activitiesPerProduct,
                        $productActivities->count(),
                        'Each product should have correct activity count'
                    );
                }

                // Verify aggregated activities across all vendor products
                $vendorShopIds = Shop::where('owner_id', $vendor->id)->pluck('id');
                $vendorProductIds = Product::whereIn('shop_id', $vendorShopIds)->pluck('id');
                $allVendorActivities = ProductActivity::whereIn('product_id', $vendorProductIds)->get();

                $this->assertEquals(
                    $totalActivities,
                    $allVendorActivities->count(),
                    'Aggregated activities should match total'
                );
                $this->assertEquals(
                    $productCount * $activitiesPerProduct,
                    $allVendorActivities->count(),
                    'Total should equal products times activities per product'
                );
            });
    }
}
