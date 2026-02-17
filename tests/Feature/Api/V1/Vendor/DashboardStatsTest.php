<?php

namespace Tests\Feature\Api\V1\Vendor;

use App\Models\Product;
use App\Models\ProductStats;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_can_get_dashboard_stats()
    {
        // Create vendor with shop and products
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = Shop::factory()->create(['owner_id' => $vendor->id]);
        
        $product1 = Product::factory()->create([
            'shop_id' => $shop->id,
            'is_active' => true,
        ]);
        
        $product2 = Product::factory()->create([
            'shop_id' => $shop->id,
            'is_active' => false,
        ]);

        // Create stats for products
        ProductStats::create([
            'product_id' => $product1->id,
            'views_count' => 100,
            'whatsapp_clicks' => 20,
            'location_clicks' => 10,
            'favorites_count' => 5,
        ]);

        ProductStats::create([
            'product_id' => $product2->id,
            'views_count' => 50,
            'whatsapp_clicks' => 10,
            'location_clicks' => 5,
            'favorites_count' => 2,
        ]);

        // Make authenticated request
        $response = $this->actingAs($vendor, 'sanctum')
            ->getJson('/api/v1/vendor/dashboard/stats');

        // Assert response structure and values
        $response->assertStatus(200)
            ->assertJsonStructure([
                'api_version',
                'success',
                'data' => [
                    'total_products',
                    'active_products',
                    'inactive_products',
                    'total_views',
                    'total_whatsapp_clicks',
                    'total_location_clicks',
                    'total_favorites',
                    'engagement_rate',
                    'trends' => [
                        'views_change',
                        'whatsapp_change',
                        'location_change',
                        'favorites_change',
                    ],
                ],
                'meta',
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_products' => 2,
                    'active_products' => 1,
                    'inactive_products' => 1,
                    'total_views' => 150,
                    'total_whatsapp_clicks' => 30,
                    'total_location_clicks' => 15,
                    'total_favorites' => 7,
                    'engagement_rate' => 34.67, // (30+15+7)/150 * 100
                ],
            ]);
    }

    public function test_vendor_only_sees_own_products_stats()
    {
        // Create two vendors
        $vendor1 = User::factory()->create(['role' => 'vendor']);
        $vendor2 = User::factory()->create(['role' => 'vendor']);

        $shop1 = Shop::factory()->create(['owner_id' => $vendor1->id]);
        $shop2 = Shop::factory()->create(['owner_id' => $vendor2->id]);

        // Create products for both vendors
        $product1 = Product::factory()->create(['shop_id' => $shop1->id]);
        $product2 = Product::factory()->create(['shop_id' => $shop2->id]);

        ProductStats::create([
            'product_id' => $product1->id,
            'views_count' => 100,
            'whatsapp_clicks' => 20,
            'location_clicks' => 10,
            'favorites_count' => 5,
        ]);

        ProductStats::create([
            'product_id' => $product2->id,
            'views_count' => 1000,
            'whatsapp_clicks' => 200,
            'location_clicks' => 100,
            'favorites_count' => 50,
        ]);

        // Vendor1 should only see their own stats
        $response = $this->actingAs($vendor1, 'sanctum')
            ->getJson('/api/v1/vendor/dashboard/stats');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_products' => 1,
                    'total_views' => 100,
                    'total_whatsapp_clicks' => 20,
                ],
            ]);

        // Verify vendor2's stats are not included
        $this->assertNotEquals(1100, $response->json('data.total_views'));
    }

    public function test_unauthenticated_user_cannot_access_dashboard_stats()
    {
        $response = $this->getJson('/api/v1/vendor/dashboard/stats');

        $response->assertStatus(401);
    }

    public function test_dashboard_stats_handles_vendor_with_no_products()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = Shop::factory()->create(['owner_id' => $vendor->id]);

        $response = $this->actingAs($vendor, 'sanctum')
            ->getJson('/api/v1/vendor/dashboard/stats');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_products' => 0,
                    'active_products' => 0,
                    'inactive_products' => 0,
                    'total_views' => 0,
                    'total_whatsapp_clicks' => 0,
                    'total_location_clicks' => 0,
                    'total_favorites' => 0,
                    'engagement_rate' => 0,
                ],
            ]);
    }
}
