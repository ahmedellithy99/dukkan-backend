<?php

namespace Tests\Feature\Api\V1\Vendor;

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

class ProductStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_can_view_product_stats()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = $this->createShop($vendor);
        $product = $this->createProduct($shop);

        // Create stats
        ProductStats::create([
            'product_id' => $product->id,
            'views_count' => 100,
            'whatsapp_clicks' => 25,
            'location_clicks' => 30,
            'favorites_count' => 15,
            'last_viewed_at' => now(),
        ]);

        $response = $this->actingAs($vendor, 'sanctum')
            ->getJson("/api/v1/vendor/products/{$product->slug}/stats");

        $response->assertOk()
            ->assertJsonStructure([
                'api_version',
                'success',
                'data' => [
                    'product_id',
                    'views_count',
                    'whatsapp_clicks',
                    'location_clicks',
                    'favorites_count',
                    'last_viewed_at',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'product_id' => $product->id,
                    'views_count' => 100,
                    'whatsapp_clicks' => 25,
                    'location_clicks' => 30,
                    'favorites_count' => 15,
                ],
            ]);
    }

    public function test_vendor_can_view_product_stats_with_no_data()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = $this->createShop($vendor);
        $product = $this->createProduct($shop);

        $response = $this->actingAs($vendor, 'sanctum')
            ->getJson("/api/v1/vendor/products/{$product->slug}/stats");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'product_id' => $product->id,
                    'views_count' => 0,
                    'whatsapp_clicks' => 0,
                    'location_clicks' => 0,
                    'favorites_count' => 0,
                    'last_viewed_at' => null,
                ],
            ]);
    }

    public function test_vendor_cannot_view_other_vendor_product_stats()
    {
        $vendor1 = User::factory()->create(['role' => 'vendor']);
        $vendor2 = User::factory()->create(['role' => 'vendor']);
        
        $shop = $this->createShop($vendor1);
        $product = $this->createProduct($shop);

        $response = $this->actingAs($vendor2, 'sanctum')
            ->getJson("/api/v1/vendor/products/{$product->slug}/stats");

        $response->assertForbidden();
    }

    public function test_guest_cannot_view_product_stats()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = $this->createShop($vendor);
        $product = $this->createProduct($shop);

        $response = $this->getJson("/api/v1/vendor/products/{$product->slug}/stats");

        $response->assertUnauthorized();
    }

    private function createShop(User $vendor): Shop
    {
        $governorate = Governorate::factory()->create();
        $city = City::factory()->create(['governorate_id' => $governorate->id]);
        $location = Location::factory()->create(['city_id' => $city->id]);

        return Shop::factory()->create([
            'owner_id' => $vendor->id,
            'location_id' => $location->id,
        ]);
    }

    private function createProduct(Shop $shop): Product
    {
        $category = Category::factory()->create();
        $subcategory = Subcategory::factory()->create(['category_id' => $category->id]);

        return Product::factory()->create([
            'shop_id' => $shop->id,
            'subcategory_id' => $subcategory->id,
        ]);
    }
}
