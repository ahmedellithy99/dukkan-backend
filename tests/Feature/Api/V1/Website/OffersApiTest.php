<?php

namespace Tests\Feature\Api\V1\Website;

use App\Models\Product;
use App\Models\Shop;
use App\Models\Subcategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OffersApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_list_products_with_discounts()
    {
        // Create shop and subcategory
        $shop = Shop::factory()->create(['is_active' => true]);
        $subcategory = Subcategory::factory()->create();

        // Create products with different discount types
        Product::factory()->create([
            'shop_id' => $shop->id,
            'subcategory_id' => $subcategory->id,
            'name' => 'Product with Percent Discount',
            'price' => 100,
            'discount_type' => 'percent',
            'discount_value' => 20,
            'is_active' => true,
        ]);

        Product::factory()->create([
            'shop_id' => $shop->id,
            'subcategory_id' => $subcategory->id,
            'name' => 'Product with Amount Discount',
            'price' => 100,
            'discount_type' => 'amount',
            'discount_value' => 30,
            'is_active' => true,
        ]);

        // Create product without discount (should not appear)
        Product::factory()->create([
            'shop_id' => $shop->id,
            'subcategory_id' => $subcategory->id,
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
        $shop = Shop::factory()->create(['is_active' => true]);
        $subcategory = Subcategory::factory()->create();

        // Create products with different discount amounts
        $product1 = Product::factory()->create([
            'shop_id' => $shop->id,
            'subcategory_id' => $subcategory->id,
            'name' => 'Small Discount',
            'price' => 100,
            'discount_type' => 'amount',
            'discount_value' => 10,
            'is_active' => true,
        ]);

        $product2 = Product::factory()->create([
            'shop_id' => $shop->id,
            'subcategory_id' => $subcategory->id,
            'name' => 'Large Discount',
            'price' => 100,
            'discount_type' => 'amount',
            'discount_value' => 50,
            'is_active' => true,
        ]);

        $product3 = Product::factory()->create([
            'shop_id' => $shop->id,
            'subcategory_id' => $subcategory->id,
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
        $shop = Shop::factory()->create(['is_active' => true]);
        $subcategory = Subcategory::factory()->create();

        // Create active product with discount
        Product::factory()->create([
            'shop_id' => $shop->id,
            'subcategory_id' => $subcategory->id,
            'name' => 'Active Product',
            'price' => 100,
            'discount_type' => 'percent',
            'discount_value' => 20,
            'is_active' => true,
        ]);

        // Create inactive product with discount (should not appear)
        Product::factory()->create([
            'shop_id' => $shop->id,
            'subcategory_id' => $subcategory->id,
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
