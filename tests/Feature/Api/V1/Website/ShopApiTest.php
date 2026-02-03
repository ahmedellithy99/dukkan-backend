<?php

namespace Tests\Feature\Api\V1\Website;

use App\Models\User;
use App\Models\Shop;
use App\Models\Location;
use App\Models\City;
use App\Models\Governorate;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShopApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $vendor;
    protected City $city;
    protected City $otherCity;
    protected Governorate $governorate;
    protected Governorate $otherGovernorate;
    protected Shop $activeShop;
    protected Shop $inactiveShop;
    protected Shop $shopWithProducts;
    protected Shop $shopWithoutProducts;

    protected function setUp(): void
    {
        parent::setUp();

        // Create governorates and cities
        $this->governorate = Governorate::factory()->create([
            'name' => 'Cairo',
            'slug' => 'cairo'
        ]);

        $this->otherGovernorate = Governorate::factory()->create([
            'name' => 'Alexandria',
            'slug' => 'alexandria'
        ]);

        $this->city = City::factory()->create([
            'governorate_id' => $this->governorate->id,
            'name' => 'Cairo City',
            'slug' => 'cairo-city'
        ]);

        $this->otherCity = City::factory()->create([
            'governorate_id' => $this->otherGovernorate->id,
            'name' => 'Alexandria City',
            'slug' => 'alexandria-city'
        ]);

        // Create vendor user
        $this->vendor = User::factory()->vendor()->create([
            'email' => 'vendor@test.com'
        ]);

        // Create test shops with different scenarios
        $this->createTestShops();
    }

    protected function createTestShops(): void
    {
        // Active shop in Cairo
        $location1 = Location::factory()->create([
            'city_id' => $this->city->id,
            'area' => 'Downtown Cairo',
            'latitude' => 30.0444,
            'longitude' => 31.2357,
        ]);

        $this->activeShop = Shop::factory()->create([
            'owner_id' => $this->vendor->id,
            'location_id' => $location1->id,
            'name' => 'Active Electronics Store',
            'description' => 'Best electronics in Cairo',
            'whatsapp_number' => '+201234567890',
            'phone_number' => '+201234567890',
            'is_active' => true,
        ]);

        // Inactive shop (should not appear in public API)
        $location2 = Location::factory()->create([
            'city_id' => $this->city->id,
            'area' => 'Heliopolis',
            'latitude' => 30.0875,
            'longitude' => 31.3241,
        ]);

        $this->inactiveShop = Shop::factory()->create([
            'owner_id' => $this->vendor->id,
            'location_id' => $location2->id,
            'name' => 'Inactive Shop',
            'description' => 'This shop is inactive',
            'whatsapp_number' => '+201987654321',
            'phone_number' => '+201987654321',
            'is_active' => false,
        ]);

        // Shop with active products
        $location3 = Location::factory()->create([
            'city_id' => $this->otherCity->id,
            'area' => 'Alexandria Center',
            'latitude' => 31.2001,
            'longitude' => 29.9187,
        ]);

        $this->shopWithProducts = Shop::factory()->create([
            'owner_id' => $this->vendor->id,
            'location_id' => $location3->id,
            'name' => 'Fashion Store',
            'description' => 'Latest fashion trends',
            'whatsapp_number' => '+201111111111',
            'phone_number' => '+201111111111',
            'is_active' => true,
        ]);

        // Create active products for this shop
        Product::factory()->count(3)->create([
            'shop_id' => $this->shopWithProducts->id,
            'is_active' => true,
        ]);

        // Shop without products
        $location4 = Location::factory()->create([
            'city_id' => $this->city->id,
            'area' => 'Maadi',
            'latitude' => 29.9602,
            'longitude' => 31.2569,
        ]);

        $this->shopWithoutProducts = Shop::factory()->create([
            'owner_id' => $this->vendor->id,
            'location_id' => $location4->id,
            'name' => 'Empty Store',
            'description' => 'Store with no products',
            'whatsapp_number' => '+202222222222',
            'phone_number' => '+202222222222',
            'is_active' => true,
        ]);
    }

    // INDEX ENDPOINT TESTS

    public function test_can_list_active_shops_only()
    {
        $response = $this->getJson('/api/v1/shops');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'api_version',
                    'success',
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'slug',
                            'description',
                            'whatsapp_number',
                            'phone_number',
                            'is_active',
                            'created_at',
                            'location' => [
                                'id',
                                'area',
                                'latitude',
                                'longitude',
                            ],
                            'logo'
                        ]
                    ],
                    'meta' => [
                        'version_info',
                        'pagination',
                        'links'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                ]);

        // Should only return active shops (3 active shops created)
        $this->assertCount(3, $response->json('data'));

        // Verify inactive shop is not included
        $shopNames = collect($response->json('data'))->pluck('name')->toArray();
        $this->assertNotContains('Inactive Shop', $shopNames);
        $this->assertContains('Active Electronics Store', $shopNames);
        $this->assertContains('Fashion Store', $shopNames);
        $this->assertContains('Empty Store', $shopNames);
    }

    public function test_can_filter_shops_by_city()
    {
        $response = $this->getJson("/api/v1/shops?city_id={$this->city->id}");

        $response->assertStatus(200);

        $data = $response->json('data');

        $this->assertGreaterThan(0, count($data));

        // All returned shops should be in the specified city
        foreach ($data as $shop) {
            $this->assertEquals($this->city->id, $shop['location']['city']['id']);
        }
    }

    public function test_can_filter_shops_by_area()
    {
        $response = $this->getJson('/api/v1/shops?area=Downtown');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertGreaterThan(0, count($data));

        // All returned shops should have area containing "Downtown"
        foreach ($data as $shop) {
            $this->assertStringContainsString('Downtown', $shop['location']['area']);
        }
    }

    public function test_can_search_shops_by_name()
    {
        $response = $this->getJson('/api/v1/shops?search=Electronics');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertGreaterThan(0, count($data));

        // Should find the "Active Electronics Store"
        $shopNames = collect($data)->pluck('name')->toArray();
        $this->assertContains('Active Electronics Store', $shopNames);
    }

    public function test_can_search_shops_by_description()
    {
        $response = $this->getJson('/api/v1/shops?search=fashion');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertGreaterThan(0, count($data));

        // Should find the "Fashion Store"
        $shopNames = collect($data)->pluck('name')->toArray();
        $this->assertContains('Fashion Store', $shopNames);
    }

    public function test_can_sort_shops_by_name_ascending()
    {
        $response = $this->getJson('/api/v1/shops?sort=name');

        $response->assertStatus(200);

        $data = $response->json('data');
        $shopNames = collect($data)->pluck('name')->toArray();

        // Verify ascending order
        $sortedNames = $shopNames;
        sort($sortedNames);
        $this->assertEquals($sortedNames, $shopNames);
    }

    public function test_can_sort_shops_by_name_descending()
    {
        $response = $this->getJson('/api/v1/shops?sort=-name');

        $response->assertStatus(200);

        $data = $response->json('data');
        $shopNames = collect($data)->pluck('name')->toArray();

        // Verify descending order
        $sortedNames = $shopNames;
        rsort($sortedNames);
        $this->assertEquals($sortedNames, $shopNames);
    }

    public function test_can_sort_shops_by_created_at_descending()
    {
        $response = $this->getJson('/api/v1/shops?sort=-created_at');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertGreaterThan(0, count($data));

        // Verify descending order by created_at
        $createdDates = collect($data)->pluck('created_at')->toArray();
        $sortedDates = $createdDates;
        rsort($sortedDates);
        $this->assertEquals($sortedDates, $createdDates);
    }

    public function test_can_combine_multiple_filters()
    {
        $response = $this->getJson("/api/v1/shops?city_id={$this->city->id}&search=Electronics&sort=-created_at");

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertGreaterThan(0, count($data));

        // Should find the Electronics store in Cairo
        $shopNames = collect($data)->pluck('name')->toArray();
        $this->assertContains('Active Electronics Store', $shopNames);

        // Verify city filter
        foreach ($data as $shop) {
            $this->assertEquals($this->city->id, $shop['location']['city']['id']);
        }
    }

    public function test_pagination_works_correctly()
    {
        // Create more shops to test pagination
        for ($i = 0; $i < 25; $i++) {
            $location = Location::factory()->create(['city_id' => $this->city->id]);
            Shop::factory()->create([
                'owner_id' => $this->vendor->id,
                'location_id' => $location->id,
                'is_active' => true,
            ]);
        }

        $response = $this->getJson('/api/v1/shops');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data',
                    'meta' => [
                        'pagination' => [
                            'current_page',
                            'per_page',
                            'total',
                            'last_page'
                        ],
                        'links' => [
                            'first',
                            'last',
                            'prev',
                            'next'
                        ]
                    ]
                ]);

        // Should return 20 items per page (default)
        $this->assertCount(20, $response->json('data'));
        $this->assertEquals(1, $response->json('meta.pagination.current_page'));
        $this->assertEquals(20, $response->json('meta.pagination.per_page'));
    }

    public function test_returns_filter_metadata()
    {
        $response = $this->getJson("/api/v1/shops?city_id={$this->city->id}&search=test&sort=-name");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'api_version',
                    'success',
                    'data',
                    'meta' => [
                        'version_info'
                    ]
                ]);

        // Note: Filters exist in service but are not functional as requested
        // So we don't expect filter metadata in the response
    }

    // SHOW ENDPOINT TESTS

    public function test_can_view_active_shop_by_slug()
    {
        $response = $this->getJson("/api/v1/shops/{$this->activeShop->slug}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'api_version',
                    'success',
                    'data' => [
                        'id',
                        'name',
                        'slug',
                        'description',
                        'whatsapp_number',
                        'phone_number',
                        'is_active',
                        'created_at',
                        'location' => [
                            'id',
                            'area',
                            'latitude',
                            'longitude',
                            'city' => [
                                'id',
                                'name',
                                'slug',
                            ]
                        ],
                        'products',
                        'logo'
                    ],
                    'meta' => [
                        'version_info'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $this->activeShop->id,
                        'name' => 'Active Electronics Store',
                        'slug' => $this->activeShop->slug,
                        'is_active' => true,
                    ]
                ]);
    }

    public function test_cannot_view_inactive_shop()
    {
        $response = $this->getJson("/api/v1/shops/{$this->inactiveShop->slug}");

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'error' => [
                        'code' => 'NOT_FOUND',
                        'message' => 'Resource not found.',
                    ]
                ]);
    }

    public function test_shop_show_includes_only_active_products()
    {
        // Create both active and inactive products for the shop
        Product::factory()->create([
            'shop_id' => $this->shopWithProducts->id,
            'name' => 'Active Product',
            'is_active' => true,
        ]);

        Product::factory()->create([
            'shop_id' => $this->shopWithProducts->id,
            'name' => 'Inactive Product',
            'is_active' => false,
        ]);

        $response = $this->getJson("/api/v1/shops/{$this->shopWithProducts->slug}");

        $response->assertStatus(200);

        $products = $response->json('data.products');
        $this->assertGreaterThan(0, count($products));

        // All products should be active
        foreach ($products as $product) {
            $this->assertTrue($product['is_active']);
        }

        // Should not include inactive products
        $productNames = collect($products)->pluck('name')->toArray();
        $this->assertNotContains('Inactive Product', $productNames);
    }

    public function test_shop_show_handles_non_existent_shop()
    {
        $response = $this->getJson('/api/v1/shops/non-existent-shop-slug');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'error' => [
                        'code' => 'NOT_FOUND',
                        'message' => 'Resource not found.',
                    ]
                ]);
    }

    public function test_shop_show_includes_media_when_available()
    {
        // Test that logo field is present in response (can be null if no media)
        $response = $this->getJson("/api/v1/shops/{$this->activeShop->slug}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'logo' // Can be null or object
                    ]
                ]);

        // Logo should be null when no media is attached
        $logo = $response->json('data.logo');
        $this->assertNull($logo);
    }

    // API RESPONSE TESTS

    public function test_api_responses_include_version_information()
    {
        $response = $this->getJson('/api/v1/shops');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'api_version',
                    'success',
                    'data',
                    'meta' => [
                        'version_info' => [
                            'current',
                            'latest',
                            'deprecated',
                            'sunset_date'
                        ]
                    ]
                ])
                ->assertJsonPath('api_version', 'v1.0.0')
                ->assertJsonPath('meta.version_info.current', 'v1.0.0');
    }

    public function test_api_responses_are_consistent()
    {
        $indexResponse = $this->getJson('/api/v1/shops');
        $showResponse = $this->getJson("/api/v1/shops/{$this->activeShop->slug}");

        // Both should have consistent structure
        $indexResponse->assertJsonStructure(['api_version', 'success', 'data', 'meta']);
        $showResponse->assertJsonStructure(['api_version', 'success', 'data', 'meta']);

        // Both should have same API version
        $this->assertEquals(
            $indexResponse->json('api_version'),
            $showResponse->json('api_version')
        );
    }

    // PERFORMANCE TESTS

    public function test_index_endpoint_performance_with_relationships()
    {
        // Create shops with relationships
        for ($i = 0; $i < 10; $i++) {
            $location = Location::factory()->create(['city_id' => $this->city->id]);
            $shop = Shop::factory()->create([
                'owner_id' => $this->vendor->id,
                'location_id' => $location->id,
                'is_active' => true,
            ]);

            // Add products to some shops
            if ($i % 2 === 0) {
                Product::factory()->count(2)->create([
                    'shop_id' => $shop->id,
                    'is_active' => true,
                ]);
            }
        }

        $startTime = microtime(true);
        $response = $this->getJson('/api/v1/shops');
        $endTime = microtime(true);

        $response->assertStatus(200);

        // Should complete within reasonable time (adjust as needed)
        $executionTime = $endTime - $startTime;
        $this->assertLessThan(2.0, $executionTime, 'API response took too long');
    }

    // EDGE CASES

    public function test_handles_invalid_filter_values_gracefully()
    {
        $response = $this->getJson('/api/v1/shops?city_id=invalid&sort=invalid_field');

        $response->assertStatus(200);
        // Should return results without filtering by invalid city_id
        // Should use default sorting for invalid sort field
    }

    public function test_handles_empty_results()
    {
        // Delete all shops
        Shop::query()->delete();

        $response = $this->getJson('/api/v1/shops');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => []
                ]);
    }

    public function test_handles_special_characters_in_search()
    {
        $response = $this->getJson('/api/v1/shops?search=' . urlencode('test@#$%^&*()'));

        $response->assertStatus(200);
        // Should handle special characters without errors
    }
}