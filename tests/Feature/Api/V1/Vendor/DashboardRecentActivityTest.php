<?php

namespace Tests\Feature\Api\V1\Vendor;

use App\Models\Product;
use App\Models\ProductActivity;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardRecentActivityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test vendor can retrieve recent activity feed
     */
    public function test_vendor_can_retrieve_recent_activity_feed()
    {
        // Create vendor with shop and product
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = Shop::factory()->create(['owner_id' => $vendor->id]);
        $product = Product::factory()->create(['shop_id' => $shop->id]);

        // Create some activities
        ProductActivity::create([
            'product_id' => $product->id,
            'activity_type' => 'view',
            'created_at' => now()->subHours(1),
        ]);

        ProductActivity::create([
            'product_id' => $product->id,
            'activity_type' => 'whatsapp_click',
            'created_at' => now()->subHours(2),
        ]);

        // Make authenticated request
        $response = $this->actingAs($vendor, 'sanctum')
            ->getJson('/api/v1/vendor/dashboard/recent-activity');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'activities',
                    'timeframe_days',
                    'count',
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'timeframe_days' => 7,
                    'count' => 2,
                ],
            ]);

        // Verify activities are in chronological order (most recent first)
        $activities = $response->json('data.activities');
        $this->assertCount(2, $activities);
        $this->assertEquals('view', $activities[0]['activity_type']);
        $this->assertEquals('whatsapp_click', $activities[1]['activity_type']);
    }

    /**
     * Test vendor can filter activities by timeframe
     */
    public function test_vendor_can_filter_activities_by_timeframe()
    {
        // Create vendor with shop and product
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = Shop::factory()->create(['owner_id' => $vendor->id]);
        $product = Product::factory()->create(['shop_id' => $shop->id]);

        // Create recent activity (within 3 days)
        ProductActivity::create([
            'product_id' => $product->id,
            'activity_type' => 'view',
            'created_at' => now()->subDays(2),
        ]);

        // Create old activity (outside 3 days)
        ProductActivity::create([
            'product_id' => $product->id,
            'activity_type' => 'whatsapp_click',
            'created_at' => now()->subDays(5),
        ]);

        // Request with 3-day timeframe
        $response = $this->actingAs($vendor, 'sanctum')
            ->getJson('/api/v1/vendor/dashboard/recent-activity?days=3');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'timeframe_days' => 3,
                    'count' => 1,
                ],
            ]);

        // Verify only recent activity is included
        $activities = $response->json('data.activities');
        $this->assertCount(1, $activities);
        $this->assertEquals('view', $activities[0]['activity_type']);
    }

    /**
     * Test vendor only sees activities from their own products
     */
    public function test_vendor_only_sees_own_product_activities()
    {
        // Create two vendors with shops and products
        $vendor1 = User::factory()->create(['role' => 'vendor']);
        $shop1 = Shop::factory()->create(['owner_id' => $vendor1->id]);
        $product1 = Product::factory()->create(['shop_id' => $shop1->id]);

        $vendor2 = User::factory()->create(['role' => 'vendor']);
        $shop2 = Shop::factory()->create(['owner_id' => $vendor2->id]);
        $product2 = Product::factory()->create(['shop_id' => $shop2->id]);

        // Create activities for both products
        ProductActivity::create([
            'product_id' => $product1->id,
            'activity_type' => 'view',
            'created_at' => now()->subHours(1),
        ]);

        ProductActivity::create([
            'product_id' => $product2->id,
            'activity_type' => 'whatsapp_click',
            'created_at' => now()->subHours(2),
        ]);

        // Vendor1 should only see their own activities
        $response = $this->actingAs($vendor1, 'sanctum')
            ->getJson('/api/v1/vendor/dashboard/recent-activity');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'count' => 1,
                ],
            ]);

        $activities = $response->json('data.activities');
        $this->assertCount(1, $activities);
        $this->assertEquals($product1->id, $activities[0]['product_id']);
    }

    /**
     * Test activity feed includes product details
     */
    public function test_activity_feed_includes_product_details()
    {
        // Create vendor with shop and product
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = Shop::factory()->create(['owner_id' => $vendor->id]);
        $product = Product::factory()->create([
            'shop_id' => $shop->id,
            'name' => 'Test Product',
        ]);

        // Create activity
        ProductActivity::create([
            'product_id' => $product->id,
            'activity_type' => 'view',
            'created_at' => now()->subHours(1),
        ]);

        // Make request
        $response = $this->actingAs($vendor, 'sanctum')
            ->getJson('/api/v1/vendor/dashboard/recent-activity');

        $response->assertStatus(200);

        // Verify product details are included
        $activities = $response->json('data.activities');
        $this->assertCount(1, $activities);
        $this->assertArrayHasKey('product', $activities[0]);
        $this->assertEquals('Test Product', $activities[0]['product']['name']);
        $this->assertEquals($product->id, $activities[0]['product']['id']);
    }

    /**
     * Test activity feed respects limit parameter
     */
    public function test_activity_feed_respects_limit_parameter()
    {
        // Create vendor with shop and product
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = Shop::factory()->create(['owner_id' => $vendor->id]);
        $product = Product::factory()->create(['shop_id' => $shop->id]);

        // Create 10 activities
        for ($i = 0; $i < 10; $i++) {
            ProductActivity::create([
                'product_id' => $product->id,
                'activity_type' => 'view',
                'created_at' => now()->subMinutes($i),
            ]);
        }

        // Request with limit of 5
        $response = $this->actingAs($vendor, 'sanctum')
            ->getJson('/api/v1/vendor/dashboard/recent-activity?limit=5');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'count' => 5,
                ],
            ]);

        $activities = $response->json('data.activities');
        $this->assertCount(5, $activities);
    }

    /**
     * Test unauthenticated request is rejected for recent activity
     */
    public function test_unauthenticated_request_is_rejected_for_recent_activity()
    {
        $response = $this->getJson('/api/v1/vendor/dashboard/recent-activity');

        $response->assertStatus(401);
    }

    /**
     * Test unauthenticated request is rejected for stats
     */
    public function test_unauthenticated_request_is_rejected_for_stats()
    {
        $response = $this->getJson('/api/v1/vendor/dashboard/stats');

        $response->assertStatus(401);
    }

    /**
     * Test activity feed handles empty results
     */
    public function test_activity_feed_handles_empty_results()
    {
        // Create vendor with shop and product but no activities
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = Shop::factory()->create(['owner_id' => $vendor->id]);
        Product::factory()->create(['shop_id' => $shop->id]);

        // Make request
        $response = $this->actingAs($vendor, 'sanctum')
            ->getJson('/api/v1/vendor/dashboard/recent-activity');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'count' => 0,
                    'activities' => [],
                ],
            ]);
    }

    /**
     * Test non-vendor users cannot access dashboard
     */
    public function test_non_vendor_cannot_access_dashboard()
    {
        // Create a non-vendor user (admin)
        $admin = User::factory()->create(['role' => 'admin']);

        // Try to access recent activity endpoint
        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/vendor/dashboard/recent-activity');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'Forbidden.',
                ],
            ]);
    }

    /**
     * Test non-vendor users cannot access stats endpoint
     */
    public function test_non_vendor_cannot_access_stats()
    {
        // Create a non-vendor user (admin)
        $admin = User::factory()->create(['role' => 'admin']);

        // Try to access stats endpoint
        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/vendor/dashboard/stats');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'Forbidden.',
                ],
            ]);
    }
}
