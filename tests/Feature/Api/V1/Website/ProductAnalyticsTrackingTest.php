<?php

namespace Tests\Feature\Api\V1\Website;

use App\Models\Category;
use App\Models\City;
use App\Models\Governorate;
use App\Models\Location;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductAnalyticsTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_show_tracks_view_count()
    {
        $product = $this->createProduct();

        // View product
        $response = $this->getJsonWithCity("/api/v1/products/{$product->slug}");

        $response->assertOk();

        // Verify view was tracked
        $product->refresh();
        $stats = $product->stats;
        
        $this->assertNotNull($stats);
        $this->assertEquals(1, $stats->views_count);
        $this->assertNotNull($stats->last_viewed_at);
    }

    public function test_multiple_views_increment_count()
    {
        $product = $this->createProduct();

        // View product multiple times
        for ($i = 0; $i < 5; $i++) {
            $this->getJsonWithCity("/api/v1/products/{$product->slug}");
        }

        // Verify views were tracked
        $product->refresh();
        $stats = $product->stats;
        
        $this->assertEquals(5, $stats->views_count);
    }

    public function test_whatsapp_click_tracking()
    {
        $product = $this->createProduct();

        // Track WhatsApp click
        $response = $this->postJson("/api/v1/products/{$product->slug}/track/whatsapp");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'message' => 'WhatsApp click tracked successfully',
                ],
            ]);

        // Verify click was tracked
        $product->refresh();
        $stats = $product->stats;
        
        $this->assertNotNull($stats);
        $this->assertEquals(1, $stats->whatsapp_clicks);
    }

    public function test_location_click_tracking()
    {
        $product = $this->createProduct();

        // Track location click
        $response = $this->postJson("/api/v1/products/{$product->slug}/track/location");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'message' => 'Location click tracked successfully',
                ],
            ]);

        // Verify click was tracked
        $product->refresh();
        $stats = $product->stats;
        
        $this->assertNotNull($stats);
        $this->assertEquals(1, $stats->location_clicks);
    }

    public function test_multiple_tracking_types_work_independently()
    {
        $product = $this->createProduct();

        // Track different interactions
        $this->getJsonWithCity("/api/v1/products/{$product->slug}"); // View
        $this->getJsonWithCity("/api/v1/products/{$product->slug}"); // View
        $this->postJson("/api/v1/products/{$product->slug}/track/whatsapp"); // WhatsApp
        $this->postJson("/api/v1/products/{$product->slug}/track/location"); // Location
        $this->postJson("/api/v1/products/{$product->slug}/track/location"); // Location

        // Verify all were tracked independently
        $product->refresh();
        $stats = $product->stats;
        
        $this->assertEquals(2, $stats->views_count);
        $this->assertEquals(1, $stats->whatsapp_clicks);
        $this->assertEquals(2, $stats->location_clicks);
    }

    public function test_tracking_works_for_guest_users()
    {
        $product = $this->createProduct();

        // Guest user views product
        $response = $this->getJsonWithCity("/api/v1/products/{$product->slug}");
        $response->assertOk();

        // Guest user tracks WhatsApp click
        $response = $this->postJson("/api/v1/products/{$product->slug}/track/whatsapp");
        $response->assertOk();

        // Guest user tracks location click
        $response = $this->postJson("/api/v1/products/{$product->slug}/track/location");
        $response->assertOk();

        // Verify all were tracked
        $product->refresh();
        $stats = $product->stats;
        
        $this->assertEquals(1, $stats->views_count);
        $this->assertEquals(1, $stats->whatsapp_clicks);
        $this->assertEquals(1, $stats->location_clicks);
    }

    private function createProduct(): Product
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $governorate = Governorate::factory()->create();
        $city = City::factory()->create(['governorate_id' => $governorate->id]);
        $location = Location::factory()->create(['city_id' => $city->id]);
        
        $shop = Shop::factory()->create([
            'owner_id' => $vendor->id,
            'location_id' => $location->id,
            'is_active' => true,
        ]);

        $category = Category::factory()->create();
        $subcategory = Subcategory::factory()->create(['category_id' => $category->id]);

        return Product::factory()->create([
            'shop_id' => $shop->id,
            'subcategory_id' => $subcategory->id,
            'is_active' => true,
        ]);
    }
}
