<?php

namespace Tests\Feature\Api\V1\Website;

use App\Models\City;
use App\Models\Governorate;
use App\Models\Location;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchApiTest extends TestCase
{
    use RefreshDatabase;

    protected City $city;
    protected Location $location;

    protected function setUp(): void
    {
        parent::setUp();

        $governorate = Governorate::firstOrCreate(
            ['slug' => 'cairo'],
            ['name' => 'Cairo']
        );
        
        $this->city = City::firstOrCreate(
            ['slug' => 'cairo', 'governorate_id' => $governorate->id],
            ['name' => 'Cairo']
        );

        $this->location = Location::factory()->create(['city_id' => $this->city->id]);
    }

    public function test_search_products_returns_matching_products()
    {
        $shop = Shop::factory()->create([
            'name' => 'Ahmed Electronics Store',
            'description' => 'Best electronics in town',
            'location_id' => $this->location->id,
            'is_active' => true,
        ]);

        Product::factory()->create([
            'name' => 'Electronics Gadget',
            'description' => 'Amazing electronic device',
            'shop_id' => $shop->id,
            'is_active' => true,
        ]);

        $response = $this->getJsonWithCity('/api/v1/search?q=electronics&type=products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'api_version',
                'success',
                'data' => [
                    'query',
                    'type',
                    'results' => [
                        'products' => [
                            'data',
                            'total',
                            'has_more',
                        ],
                    ],
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'query' => 'electronics',
                    'type' => 'products',
                ],
            ]);

        $data = $response->json('data.results');
        
        $this->assertGreaterThanOrEqual(1, $data['products']['total']);
        $this->assertArrayNotHasKey('shops', $data);
    }

    public function test_search_shops_returns_matching_shops()
    {
        Shop::factory()->create([
            'name' => 'Ahmed Electronics Store',
            'description' => 'Best electronics in town',
            'location_id' => $this->location->id,
            'is_active' => true,
        ]);

        $response = $this->getJsonWithCity('/api/v1/search?q=electronics&type=shops');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'api_version',
                'success',
                'data' => [
                    'query',
                    'type',
                    'results' => [
                        'shops' => [
                            'data',
                            'total',
                            'has_more',
                        ],
                    ],
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'query' => 'electronics',
                    'type' => 'shops',
                ],
            ]);

        $data = $response->json('data.results');
        
        $this->assertGreaterThanOrEqual(1, $data['shops']['total']);
        $this->assertArrayNotHasKey('products', $data);
    }

    public function test_search_can_filter_by_type_products_only()
    {
        $shop = Shop::factory()->create([
            'name' => 'Test Shop',
            'location_id' => $this->location->id,
            'is_active' => true,
        ]);

        Product::factory()->create([
            'name' => 'Test Product',
            'shop_id' => $shop->id,
            'is_active' => true,
        ]);

        $response = $this->getJsonWithCity('/api/v1/search?q=test&type=products');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'type' => 'products',
                ],
            ]);

        $data = $response->json('data.results');
        
        $this->assertArrayHasKey('products', $data);
        $this->assertArrayNotHasKey('shops', $data);
    }

    public function test_search_can_filter_by_type_shops_only()
    {
        $shop = Shop::factory()->create([
            'name' => 'Test Shop',
            'location_id' => $this->location->id,
            'is_active' => true,
        ]);

        Product::factory()->create([
            'name' => 'Test Product',
            'shop_id' => $shop->id,
            'is_active' => true,
        ]);

        $response = $this->getJsonWithCity('/api/v1/search?q=test&type=shops');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'type' => 'shops',
                ],
            ]);

        $data = $response->json('data.results');
        
        $this->assertArrayHasKey('shops', $data);
        $this->assertArrayNotHasKey('products', $data);
    }

    public function test_search_respects_city_filter()
    {
        $governorate2 = Governorate::factory()->create();
        $city2 = City::factory()->create(['governorate_id' => $governorate2->id]);
        $location2 = Location::factory()->create(['city_id' => $city2->id]);

        Shop::factory()->create([
            'name' => 'Cairo Electronics',
            'location_id' => $this->location->id,
            'is_active' => true,
        ]);

        Shop::factory()->create([
            'name' => 'Other Electronics',
            'location_id' => $location2->id,
            'is_active' => true,
        ]);

        $response = $this->getJsonWithCity('/api/v1/search?q=electronics&type=shops');

        $response->assertStatus(200);
        
        $shops = $response->json('data.results.shops.data');
        
        $this->assertCount(1, $shops);
        $this->assertEquals('Cairo Electronics', $shops[0]['name']);
    }

    public function test_search_validates_required_query_parameter()
    {
        $response = $this->getJsonWithCity('/api/v1/search?type=products');

        $response->assertStatus(422)
            ->assertJsonStructure([
                'api_version',
                'success',
                'error' => [
                    'code',
                    'message',
                    'fields',
                ],
            ]);
        
        $this->assertArrayHasKey('q', $response->json('error.fields'));
    }

    public function test_search_validates_required_type_parameter()
    {
        $response = $this->getJsonWithCity('/api/v1/search?q=test');

        $response->assertStatus(422)
            ->assertJsonStructure([
                'api_version',
                'success',
                'error' => [
                    'code',
                    'message',
                    'fields',
                ],
            ]);
        
        $this->assertArrayHasKey('type', $response->json('error.fields'));
    }

    public function test_search_validates_minimum_query_length()
    {
        $response = $this->getJsonWithCity('/api/v1/search?q=a&type=products');

        $response->assertStatus(422)
            ->assertJsonStructure([
                'api_version',
                'success',
                'error' => [
                    'code',
                    'message',
                    'fields',
                ],
            ]);
        
        $this->assertArrayHasKey('q', $response->json('error.fields'));
    }

    public function test_search_validates_type_parameter()
    {
        $response = $this->getJsonWithCity('/api/v1/search?q=test&type=invalid');

        $response->assertStatus(422)
            ->assertJsonStructure([
                'api_version',
                'success',
                'error' => [
                    'code',
                    'message',
                    'fields',
                ],
            ]);
        
        $this->assertArrayHasKey('type', $response->json('error.fields'));
    }

    public function test_search_respects_limit_parameter()
    {
        $shop = Shop::factory()->create([
            'location_id' => $this->location->id,
            'is_active' => true,
        ]);

        Product::factory()->count(15)->create([
            'name' => 'Test Product',
            'shop_id' => $shop->id,
            'is_active' => true,
        ]);

        $response = $this->getJsonWithCity('/api/v1/search?q=test&type=products&limit=5');

        $response->assertStatus(200);
        
        $products = $response->json('data.results.products.data');
        
        $this->assertCount(5, $products);
    }

    public function test_search_only_returns_active_products_and_shops()
    {
        $location1 = Location::factory()->create(['city_id' => $this->city->id]);
        $location2 = Location::factory()->create(['city_id' => $this->city->id]);
        
        $shop = Shop::factory()->create([
            'name' => 'Active Shop',
            'location_id' => $location1->id,
            'is_active' => true,
        ]);

        Shop::factory()->create([
            'name' => 'Inactive Shop',
            'location_id' => $location2->id,
            'is_active' => false,
        ]);

        Product::factory()->create([
            'name' => 'Active Product',
            'shop_id' => $shop->id,
            'is_active' => true,
        ]);

        Product::factory()->create([
            'name' => 'Inactive Product',
            'shop_id' => $shop->id,
            'is_active' => false,
        ]);

        $response = $this->getJsonWithCity('/api/v1/search?q=shop&type=shops');

        $response->assertStatus(200);
        
        $shops = $response->json('data.results.shops.data');
        
        $this->assertCount(1, $shops);
        $this->assertEquals('Active Shop', $shops[0]['name']);

        $response2 = $this->getJsonWithCity('/api/v1/search?q=product&type=products');

        $response2->assertStatus(200);
        
        $products = $response2->json('data.results.products.data');
        
        $this->assertCount(1, $products);
        $this->assertEquals('Active Product', $products[0]['name']);
    }

    public function test_search_matches_product_description()
    {
        $shop = Shop::factory()->create([
            'location_id' => $this->location->id,
            'is_active' => true,
        ]);

        Product::factory()->create([
            'name' => 'Generic Product',
            'description' => 'This is a unique description with special keyword',
            'shop_id' => $shop->id,
            'is_active' => true,
        ]);

        $response = $this->getJsonWithCity('/api/v1/search?q=special&type=products');

        $response->assertStatus(200);
        
        $products = $response->json('data.results.products.data');
        
        $this->assertGreaterThanOrEqual(1, count($products));
        $this->assertEquals('Generic Product', $products[0]['name']);
    }

    public function test_search_matches_shop_description()
    {
        Shop::factory()->create([
            'name' => 'Generic Shop',
            'description' => 'This shop has a unique description with special keyword',
            'location_id' => $this->location->id,
            'is_active' => true,
        ]);

        $response = $this->getJsonWithCity('/api/v1/search?q=special&type=shops');

        $response->assertStatus(200);
        
        $shops = $response->json('data.results.shops.data');
        
        $this->assertGreaterThanOrEqual(1, count($shops));
        $this->assertEquals('Generic Shop', $shops[0]['name']);
    }

    public function test_search_returns_empty_results_when_no_matches()
    {
        $response = $this->getJsonWithCity('/api/v1/search?q=nonexistentterm12345&type=products');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'results' => [
                        'products' => [
                            'total' => 0,
                        ],
                    ],
                ],
            ]);
    }

    public function test_suggestions_returns_product_suggestions()
    {
        $shop = Shop::factory()->create([
            'location_id' => $this->location->id,
            'is_active' => true,
        ]);

        Product::factory()->count(3)->create([
            'name' => 'Electronics Gadget',
            'shop_id' => $shop->id,
            'is_active' => true,
        ]);

        $response = $this->getJsonWithCity('/api/v1/search/suggestions?q=elec&type=products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'api_version',
                'success',
                'data' => [
                    'query',
                    'type',
                    'suggestions' => [
                        '*' => [
                            'id',
                            'name',
                            'slug',
                            'price',
                            'image',
                        ],
                    ],
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'query' => 'elec',
                    'type' => 'products',
                ],
            ]);

        $suggestions = $response->json('data.suggestions');
        $this->assertLessThanOrEqual(5, count($suggestions));
    }

    public function test_suggestions_returns_shop_suggestions()
    {
        Shop::factory()->count(3)->create([
            'name' => 'Electronics Store',
            'is_active' => true,
        ])->each(function ($shop) {
            $shop->update(['location_id' => Location::factory()->create(['city_id' => $this->city->id])->id]);
        });

        $response = $this->getJsonWithCity('/api/v1/search/suggestions?q=elec&type=shops');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'api_version',
                'success',
                'data' => [
                    'query',
                    'type',
                    'suggestions' => [
                        '*' => [
                            'id',
                            'name',
                            'slug',
                            'image',
                        ],
                    ],
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'query' => 'elec',
                    'type' => 'shops',
                ],
            ]);

        $suggestions = $response->json('data.suggestions');
        $this->assertLessThanOrEqual(5, count($suggestions));
    }

    public function test_suggestions_respects_limit_parameter()
    {
        $shop = Shop::factory()->create([
            'location_id' => $this->location->id,
            'is_active' => true,
        ]);

        Product::factory()->count(10)->create([
            'name' => 'Test Product',
            'shop_id' => $shop->id,
            'is_active' => true,
        ]);

        $response = $this->getJsonWithCity('/api/v1/search/suggestions?q=test&type=products&limit=3');

        $response->assertStatus(200);
        
        $suggestions = $response->json('data.suggestions');
        $this->assertCount(3, $suggestions);
    }

    public function test_suggestions_validates_required_parameters()
    {
        $response = $this->getJsonWithCity('/api/v1/search/suggestions?q=test');

        $response->assertStatus(422);
        
        // Check if validation errors exist in the response
        $response->assertJsonStructure([
            'api_version',
            'success',
            'error' => [
                'code',
                'message',
                'fields',
            ],
        ]);
        
        $this->assertArrayHasKey('type', $response->json('error.fields'));
    }

    public function test_suggestions_allows_single_character_query()
    {
        $shop = Shop::factory()->create([
            'location_id' => $this->location->id,
            'is_active' => true,
        ]);

        Product::factory()->create([
            'name' => 'Apple iPhone',
            'shop_id' => $shop->id,
            'is_active' => true,
        ]);

        $response = $this->getJsonWithCity('/api/v1/search/suggestions?q=a&type=products');

        $response->assertStatus(200);
    }
}
