<?php

namespace Tests\Feature\Api\V1\Website;

use App\Models\Product;
use App\Models\Shop;
use App\Models\Subcategory;
use App\Models\Location;
use App\Models\City;
use App\Models\Governorate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OffersApiTest extends TestCase
{
    use RefreshDatabase;

    protected City $cairo;
    protected Location $cairoLocation;
    protected Shop $cairoShop;
    protected Subcategory $subcategory;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Cairo city
        $governorate = Governorate::factory()->create(['name' => 'Cairo']);
        $this->cairo = City::factory()->create([
            'governorate_id' => $governorate->id,
            'name' => 'Cairo',
            'slug' => 'cairo',
        ]);

        // Create location in Cairo
        $this->cairoLocation = Location::factory()->create(['city_id' => $this->cairo->id]);

        // Create vendor and shop in Cairo
        $vendor = User::factory()->create(['role' => 'vendor']);
        $this->cairoShop = Shop::factory()->create([
            'owner_id' => $vendor->id,
            'location_id' => $this->cairoLocation->id,
            'is_active' => true,
        ]);

        // Create subcategory
        $this->subcategory = Subcategory::factory()->create();
    }

    public function test_public_can_list_products_with_discounts()
    {
        // Create products with different discount types
        Product::factory()->create([
            'shop_id' => $this->cairoShop->id,
            'subcategory_id' => $this->subcategory->id,
            'name' => 'Product with Percent Discount',
            'price' => 100,
            'discount_type' => 'percent',
            'discount_value' => 20,
            'is_active' => true,
        ]);

        Product::factory()->create([
            'shop_id' => $this->cairoShop->id,
            'subcategory_id' => $this->subcategory->id,
            'name' => 'Product with Amount Discount',
            'price' => 100,
            'discount_type' => 'amount',
            'discount_value' => 30,
            'is_active' => true,
        ]);

        // Create product without discount (should not appear)
        Product::factory()->create([
            'shop_id' => $this->cairoShop->id,
            'subcategory_id' => $this->subcategory->id,
            'name' => 'Product without Discount',
            'price' => 100,
            'discount_type' => null,
            'discount_value' => null,
            'is_active' => true,
        ]);

        $response = $this->getJsonWithCity('/api/v1/offers');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'price',
                            'discount_type',
                            'discount_value',
                        ]
                    ]
                ]);

        // Should only return products with discounts
        $this->assertEquals(2, count($response->json('data')));
    }

    public function test_offers_are_ordered_by_discount_value()
    {
        // Create products with different discount amounts
        $product1 = Product::factory()->create([
            'shop_id' => $this->cairoShop->id,
            'subcategory_id' => $this->subcategory->id,
            'name' => 'Small Discount',
            'price' => 100,
            'discount_type' => 'amount',
            'discount_value' => 10,
            'is_active' => true,
        ]);

        $product2 = Product::factory()->create([
            'shop_id' => $this->cairoShop->id,
            'subcategory_id' => $this->subcategory->id,
            'name' => 'Large Discount',
            'price' => 100,
            'discount_type' => 'amount',
            'discount_value' => 50,
            'is_active' => true,
        ]);

        $product3 = Product::factory()->create([
            'shop_id' => $this->cairoShop->id,
            'subcategory_id' => $this->subcategory->id,
            'name' => 'Medium Discount',
            'price' => 100,
            'discount_type' => 'amount',
            'discount_value' => 30,
            'is_active' => true,
        ]);

        $response = $this->getJsonWithCity('/api/v1/offers');

        $response->assertStatus(200);

        $data = $response->json('data');
        
        // First product should have the largest discount
        $this->assertEquals('Large Discount', $data[0]['name']);
        $this->assertEquals(50, $data[0]['discount_value']);
        
        // Second should be medium
        $this->assertEquals('Medium Discount', $data[1]['name']);
        $this->assertEquals(30, $data[1]['discount_value']);
        
        // Third should be smallest
        $this->assertEquals('Small Discount', $data[2]['name']);
        $this->assertEquals(10, $data[2]['discount_value']);
    }

    public function test_offers_only_show_active_products()
    {
        // Create active product with discount
        Product::factory()->create([
            'shop_id' => $this->cairoShop->id,
            'subcategory_id' => $this->subcategory->id,
            'name' => 'Active Product',
            'price' => 100,
            'discount_type' => 'percent',
            'discount_value' => 20,
            'is_active' => true,
        ]);

        // Create inactive product with discount (should not appear)
        Product::factory()->create([
            'shop_id' => $this->cairoShop->id,
            'subcategory_id' => $this->subcategory->id,
            'name' => 'Inactive Product',
            'price' => 100,
            'discount_type' => 'percent',
            'discount_value' => 50,
            'is_active' => false,
        ]);

        $response = $this->getJsonWithCity('/api/v1/offers');

        $response->assertStatus(200);

        // Should only return active product
        $this->assertEquals(1, count($response->json('data')));
        $this->assertEquals('Active Product', $response->json('data.0.name'));
    }

    public function test_offers_endpoint_does_not_require_authentication()
    {
        $response = $this->getJsonWithCity('/api/v1/offers');

        // Should not return 401 Unauthorized
        $response->assertStatus(200);
    }

    public function test_empty_offers_returns_empty_array()
    {
        $response = $this->getJsonWithCity('/api/v1/offers');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => []
                ]);
    }
}
