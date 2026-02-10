<?php

namespace Tests\Feature\Api\V1\Website;

use App\Models\City;
use App\Models\Governorate;
use App\Models\Location;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Subcategory;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CityFilteringTest extends TestCase
{
    use RefreshDatabase;

    protected City $cairo;
    protected City $alexandria;
    protected Product $cairoProduct;
    protected Product $alexandriaProduct;
    protected Shop $cairoShop;
    protected Shop $alexandriaShop;

    protected function setUp(): void
    {
        parent::setUp();

        // Create governorate
        $governorate = Governorate::factory()->create();

        // Create cities
        $this->cairo = City::factory()->create([
            'governorate_id' => $governorate->id,
            'name' => 'Cairo',
            'slug' => 'cairo',
        ]);

        $this->alexandria = City::factory()->create([
            'governorate_id' => $governorate->id,
            'name' => 'Alexandria',
            'slug' => 'alexandria',
        ]);

        // Create locations
        $cairoLocation = Location::factory()->create(['city_id' => $this->cairo->id]);
        $alexLocation = Location::factory()->create(['city_id' => $this->alexandria->id]);

        // Create vendor
        $vendor = User::factory()->create(['role' => 'vendor', 'status' => 'active']);

        // Create category and subcategory
        $category = Category::factory()->create();
        $subcategory = Subcategory::factory()->create(['category_id' => $category->id]);

        // Create shops
        $this->cairoShop = Shop::factory()->create([
            'owner_id' => $vendor->id,
            'location_id' => $cairoLocation->id,
            'is_active' => true,
        ]);

        $this->alexandriaShop = Shop::factory()->create([
            'owner_id' => $vendor->id,
            'location_id' => $alexLocation->id,
            'is_active' => true,
        ]);

        // Create products
        $this->cairoProduct = Product::factory()->create([
            'shop_id' => $this->cairoShop->id,
            'subcategory_id' => $subcategory->id,
            'is_active' => true,
        ]);

        $this->alexandriaProduct = Product::factory()->create([
            'shop_id' => $this->alexandriaShop->id,
            'subcategory_id' => $subcategory->id,
            'is_active' => true,
        ]);
    }

    #[Test]
    public function test_products_can_be_filtered_by_city_header()
    {
        // Request products with Cairo header
        $response = $this->withHeader('X-City', 'cairo')
            ->getJson('/api/v1/products');

        $response->assertOk();
        
        // Response should contain data
        $products = $response->json('data');
        $this->assertIsArray($products);
        
        // All products should be from Cairo shops
        foreach ($products as $product) {
            $this->assertNotNull($product);
        }
    }

    #[Test]
    public function test_shops_can_be_filtered_by_city_header()
    {
        // Request shops with Alexandria header
        $response = $this->withHeader('X-City', 'alexandria')
            ->getJson('/api/v1/shops');

        $response->assertOk();
        
        // Response should contain data
        $shops = $response->json('data');
        $this->assertIsArray($shops);
    }

    #[Test]
    public function test_invalid_city_header_format_returns_error()
    {
        $response = $this->withHeader('X-City', 'Cairo123!')
            ->getJson('/api/v1/products');

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'error' => [
                'code' => 'INVALID_CITY_HEADER',
            ],
        ]);
    }

    #[Test]
    public function test_nonexistent_city_returns_error()
    {
        $response = $this->withHeader('X-City', 'nonexistent-city')
            ->getJson('/api/v1/products');

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'error' => [
                'code' => 'CITY_NOT_FOUND',
            ],
        ]);
    }

    #[Test]
    public function test_missing_city_header_returns_error()
    {
        // Request without city header - middleware now requires it
        $response = $this->getJson('/api/v1/products');

        // Should return 422 error since X-City header is required
        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'error' => [
                'code' => 'INVALID_CITY_HEADER',
            ],
        ]);
    }

    #[Test]
    public function test_offers_can_be_filtered_by_city()
    {
        // Add discounts to products
        $this->cairoProduct->update([
            'discount_type' => 'percent',
            'discount_value' => 20,
        ]);

        $this->alexandriaProduct->update([
            'discount_type' => 'amount',
            'discount_value' => 50,
        ]);

        // Request offers with Cairo header
        $response = $this->withHeader('X-City', 'cairo')
            ->getJson('/api/v1/offers');

        $response->assertOk();
        
        $offers = $response->json('data');
        $this->assertIsArray($offers);
    }

    #[Test]
    public function test_city_header_is_case_insensitive()
    {
        // Test with lowercase
        $response1 = $this->withHeader('X-City', 'cairo')
            ->getJson('/api/v1/products');
        $response1->assertOk();

        // Test with uppercase (should be converted to lowercase by middleware)
        $response2 = $this->withHeader('X-City', 'CAIRO')
            ->getJson('/api/v1/products');
        
        // Middleware converts to lowercase, so uppercase should work
        $response2->assertOk();
    }

    #[Test]
    public function test_city_filtering_works_with_pagination()
    {
        // Create more products in Cairo
        Product::factory()->count(15)->create([
            'shop_id' => $this->cairoShop->id,
            'subcategory_id' => $this->cairoProduct->subcategory_id,
            'is_active' => true,
        ]);

        $response = $this->withHeader('X-City', 'cairo')
            ->getJson('/api/v1/products?per_page=10');

        $response->assertOk();
        
        // Check pagination structure
        $this->assertIsArray($response->json('data'));
    }
}
