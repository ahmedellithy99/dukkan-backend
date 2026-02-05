<?php

namespace Tests\Feature\Api\V1\Vendor;

use App\Models\User;
use App\Models\Shop;
use App\Models\Location;
use App\Models\City;
use App\Models\Governorate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ShopApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $vendor;
    protected User $otherVendor;
    protected City $city;
    protected Governorate $governorate;
    protected Shop $shop;

    protected function setUp(): void
    {
        parent::setUp();

        // Create governorate and city
        $this->governorate = Governorate::factory()->create([
            'name' => 'Cairo',
        ]);

        $this->city = City::factory()->create([
            'governorate_id' => $this->governorate->id,
            'name' => 'Cairo City',
        ]);

        // Create vendor users
        $this->vendor = User::factory()->vendor()->create([
            'email' => 'vendor@test.com'
        ]);

        $this->otherVendor = User::factory()->vendor()->create([
            'email' => 'other-vendor@test.com'
        ]);

        // Create a shop for the vendor
        $location = Location::factory()->create([
            'city_id' => $this->city->id,
            'area' => 'Downtown',
            'latitude' => 30.0444,
            'longitude' => 31.2357,
        ]);

        $this->shop = Shop::factory()->create([
            'owner_id' => $this->vendor->id,
            'location_id' => $location->id,
            'name' => 'Test Shop',
            'description' => 'A test shop',
            'whatsapp_number' => '+201234567890',
            'phone_number' => '+201234567890',
            'is_active' => true, // Explicitly set to active for consistent testing
        ]);
    }

    // INDEX ENDPOINT TESTS

    public function test_vendor_can_list_their_shops()
    {
        Sanctum::actingAs($this->vendor);

        $response = $this->getJson('/api/v1/vendor/my-shops');

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
                            'updated_at',
                            'location' => [
                                'id',
                                'area',
                                'latitude',
                                'longitude',
                                'full_address',
                                'city' => [
                                    'id',
                                    'name',
                                    'slug',
                                ]
                            ],
                            'owner' => [
                                'id',
                                'name',
                                'email'
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
                    'data' => [
                        [
                            'id' => $this->shop->id,
                            'name' => 'Test Shop',
                            'slug' => $this->shop->slug,
                        ]
                    ]
                ]);
    }

    public function test_vendor_can_filter_shops_by_active_status()
    {
        // Create additional shops with different active status
        $location2 = Location::factory()->create(['city_id' => $this->city->id]);
        Shop::factory()->create([
            'owner_id' => $this->vendor->id,
            'location_id' => $location2->id,
            'name' => 'Inactive Shop',
            'is_active' => false,
        ]);

        Sanctum::actingAs($this->vendor);

        $response = $this->getJson('/api/v1/vendor/my-shops?is_active=true');

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.name', 'Test Shop');
    }

    public function test_unauthenticated_user_cannot_list_shops()
    {
        $response = $this->getJson('/api/v1/vendor/my-shops');

        $response->assertStatus(401)
                ->assertJson([
                    'success' => false,
                    'error' => [
                        'code' => 'UNAUTHENTICATED',
                        'message' => 'Unauthenticated.',
                    ]
                ]);
    }

    // STORE ENDPOINT TESTS

    public function test_vendor_can_create_shop_successfully()
    {
        Storage::fake('public');
        Sanctum::actingAs($this->vendor);

        $logo = UploadedFile::fake()->image('logo.jpg', 800, 600);

        $shopData = [
            'name' => 'New Electronics Store',
            'description' => 'Best electronics in town',
            'whatsapp_number' => '+201987654321',
            'phone_number' => '+201987654321',
            'city_id' => $this->city->id,
            'area' => 'Heliopolis',
            'latitude' => 30.0875,
            'longitude' => 31.3241,
            'logo' => $logo,
        ];

        $response = $this->postJson('/api/v1/vendor/my-shops', $shopData);

        $response->assertStatus(201)
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
                        'location',
                        'owner',
                        'logo'
                    ],
                    'meta' => [
                        'version_info'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'name' => 'New Electronics Store',
                        'description' => 'Best electronics in town',
                        'whatsapp_number' => '+201987654321',
                        'phone_number' => '+201987654321',
                        'location' => [
                            'area' => 'Heliopolis',
                            'latitude' => 30.0875,
                            'longitude' => 31.3241,
                        ]
                    ]
                ]);

        // Verify database
        $this->assertDatabaseHas('shops', [
            'name' => 'New Electronics Store',
            'owner_id' => $this->vendor->id,
        ]);

        $this->assertDatabaseHas('locations', [
            'area' => 'Heliopolis',
            'latitude' => 30.0875,
            'longitude' => 31.3241,
        ]);
    }

    public function test_vendor_can_create_shop_with_logo()
    {
        Storage::fake('public');
        Sanctum::actingAs($this->vendor);

        $logo = UploadedFile::fake()->image('logo.jpg', 800, 600);

        $shopData = [
            'name' => 'Shop with Logo',
            'description' => 'A shop with a logo',
            'whatsapp_number' => '+201987654321',
            'phone_number' => '+201987654321',
            'city_id' => $this->city->id,
            'area' => 'Maadi',
            'latitude' => 29.9602,
            'longitude' => 31.2569,
            'logo' => $logo,
        ];

        $response = $this->postJson('/api/v1/vendor/my-shops', $shopData);

        $response->assertStatus(201)
                ->assertJsonPath('data.name', 'Shop with Logo')
                ->assertJsonStructure([
                    'data' => [
                        'logo' => [
                            'id',
                            'name',
                            'file_name',
                            'mime_type',
                            'size',
                            'url',
                            'thumb_url'
                        ]
                    ]
                ]);

        // Verify shop was created
        $shop = Shop::where('name', 'Shop with Logo')->first();
        $this->assertNotNull($shop);
        $this->assertTrue($shop->hasMedia('logo'));
    }

    public function test_shop_creation_validates_required_fields()
    {
        Sanctum::actingAs($this->vendor);

        $response = $this->postJson('/api/v1/vendor/my-shops', []);

        $response->assertStatus(422)
                ->assertJsonStructure([
                    'api_version',
                    'success',
                    'error' => [
                        'code',
                        'message',
                        'fields'
                    ]
                ])
                ->assertJsonPath('error.fields.name.0', 'Shop name is required.')
                ->assertJsonPath('error.fields.whatsapp_number.0', 'WhatsApp number is required.')
                ->assertJsonPath('error.fields.phone_number.0', 'Phone number is required.')
                ->assertJsonPath('error.fields.city_id.0', 'City is required.')
                ->assertJsonPath('error.fields.area.0', 'Area is required.')
                ->assertJsonPath('error.fields.latitude.0', 'Latitude is required.')
                ->assertJsonPath('error.fields.longitude.0', 'Longitude is required.');
    }

    public function test_shop_creation_validates_coordinates_within_egypt()
    {
        Sanctum::actingAs($this->vendor);

        $shopData = [
            'name' => 'Invalid Location Shop',
            'description' => 'Shop with invalid coordinates',
            'whatsapp_number' => '+201987654321',
            'phone_number' => '+201987654321',
            'city_id' => $this->city->id,
            'area' => 'Invalid Area',
            'latitude' => 50.0, // Outside Egypt
            'longitude' => 50.0, // Outside Egypt
        ];

        $response = $this->postJson('/api/v1/vendor/my-shops', $shopData);

        $response->assertStatus(422)
                ->assertJsonStructure([
                    'api_version',
                    'success',
                    'error' => [
                        'code',
                        'message',
                        'fields' => [
                            'latitude',
                            'longitude'
                        ]
                    ]
                ]);
    }

    public function test_shop_creation_validates_logo_file_type_and_size()
    {
        Storage::fake('public');
        Sanctum::actingAs($this->vendor);

        // Test invalid file type
        $invalidFile = UploadedFile::fake()->create('document.pdf', 1000);

        $shopData = [
            'name' => 'Test Shop',
            'description' => 'Test description',
            'whatsapp_number' => '+201987654321',
            'phone_number' => '+201987654321',
            'city_id' => $this->city->id,
            'area' => 'Test Area',
            'latitude' => 30.0444,
            'longitude' => 31.2357,
            'logo' => $invalidFile,
        ];

        $response = $this->postJson('/api/v1/vendor/my-shops', $shopData);

        $response->assertStatus(422)
                ->assertJsonStructure([
                    'api_version',
                    'success',
                    'error' => [
                        'code',
                        'message',
                        'fields' => [
                            'logo'
                        ]
                    ]
                ]);

        // Test file too large (3MB)
        $largeFile = UploadedFile::fake()->image('large.jpg')->size(5072);

        $shopData['logo'] = $largeFile;

        $response = $this->postJson('/api/v1/vendor/my-shops', $shopData);

        $response->assertStatus(422)
                ->assertJsonStructure([
                    'api_version',
                    'success',
                    'error' => [
                        'code',
                        'message',
                        'fields' => [
                            'logo'
                        ]
                    ]
                ]);
    }

    // SHOW ENDPOINT TESTS

    public function test_vendor_can_view_their_shop()
    {
        Sanctum::actingAs($this->vendor);

        $response = $this->getJson("/api/v1/vendor/my-shops/{$this->shop->slug}");

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
                        'location',
                        'owner',
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
                        'id' => $this->shop->id,
                        'name' => 'Test Shop',
                        'slug' => $this->shop->slug,
                    ]
                ]);
    }

    public function test_vendor_cannot_view_other_vendors_shop()
    {
        // Create shop for other vendor
        $otherLocation = Location::factory()->create(['city_id' => $this->city->id]);
        $otherShop = Shop::factory()->create([
            'owner_id' => $this->otherVendor->id,
            'location_id' => $otherLocation->id,
        ]);

        Sanctum::actingAs($this->vendor);

        $response = $this->getJson("/api/v1/vendor/my-shops/{$otherShop->slug}");

        $response->assertStatus(403)
                ->assertJson([
                    'success' => false,
                    'error' => [
                        'code' => 'FORBIDDEN',
                        'message' => 'Forbidden.',
                    ]
                ]);
    }

    public function test_shop_show_handles_non_existent_shop()
    {
        Sanctum::actingAs($this->vendor);

        $response = $this->getJson('/api/v1/vendor/my-shops/non-existent-shop');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'error' => [
                        'code' => 'NOT_FOUND',
                        'message' => 'Resource not found.',
                    ]
                ]);
    }

    // UPDATE ENDPOINT TESTS

    public function test_vendor_can_update_their_shop()
    {
        Sanctum::actingAs($this->vendor);

        $updateData = [
            'name' => 'Updated Shop Name',
            'description' => 'Updated description',
            'whatsapp_number' => '+201111111111',
        ];

        $response = $this->putJson("/api/v1/vendor/my-shops/{$this->shop->slug}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $this->shop->id,
                        'name' => 'Updated Shop Name',
                        'description' => 'Updated description',
                        'whatsapp_number' => '+201111111111',
                    ]
                ]);

        // Verify database
        $this->assertDatabaseHas('shops', [
            'id' => $this->shop->id,
            'name' => 'Updated Shop Name',
            'description' => 'Updated description',
            'whatsapp_number' => '+201111111111',
        ]);
    }

    public function test_vendor_can_update_shop_location()
    {
        Sanctum::actingAs($this->vendor);

        $updateData = [
            'city_id' => $this->city->id,
            'area' => 'New Area',
            'latitude' => 30.1000,
            'longitude' => 31.3000,
        ];

        $response = $this->putJson("/api/v1/vendor/my-shops/{$this->shop->slug}", $updateData);

        $response->assertStatus(200)
                ->assertJsonPath('data.location.area', 'New Area')
                ->assertJsonPath('data.location.latitude', '30.10000000')
                ->assertJsonPath('data.location.longitude', '31.30000000');

        // Verify location was updated
        $this->assertDatabaseHas('locations', [
            'id' => $this->shop->location_id,
            'area' => 'New Area',
            'latitude' => 30.1000,
            'longitude' => 31.3000,
        ]);
    }

    public function test_vendor_can_update_shop_logo()
    {
        Storage::fake('public');
        Sanctum::actingAs($this->vendor);

        $newLogo = UploadedFile::fake()->image('new-logo.jpg', 600, 400);

        $updateData = [
            'logo' => $newLogo,
        ];

        $response = $this->putJson("/api/v1/vendor/my-shops/{$this->shop->slug}", $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'logo' => [
                            'id',
                            'name',
                            'file_name',
                            'mime_type',
                            'size',
                            'url',
                            'thumb_url'
                        ]
                    ]
                ]);

        // Verify logo was updated
        $this->shop->refresh();
        $this->assertTrue($this->shop->hasMedia('logo'));
    }

    public function test_vendor_cannot_update_other_vendors_shop()
    {
        // Create shop for other vendor
        $otherLocation = Location::factory()->create(['city_id' => $this->city->id]);
        $otherShop = Shop::factory()->create([
            'owner_id' => $this->otherVendor->id,
            'location_id' => $otherLocation->id,
        ]);

        Sanctum::actingAs($this->vendor);

        $updateData = [
            'name' => 'Hacked Shop Name',
        ];

        $response = $this->putJson("/api/v1/vendor/my-shops/{$otherShop->slug}", $updateData);

        $response->assertStatus(403)
                ->assertJson([
                    'success' => false,
                    'error' => [
                        'code' => 'FORBIDDEN',
                        'message' => 'Forbidden.',
                    ]
                ]);

        // Verify database was not updated
        $this->assertDatabaseMissing('shops', [
            'id' => $otherShop->id,
            'name' => 'Hacked Shop Name',
        ]);
    }

    public function test_shop_update_allows_partial_updates()
    {
        Sanctum::actingAs($this->vendor);

        $updateData = [
            'name' => 'Partially Updated Name',
        ];

        $response = $this->putJson("/api/v1/vendor/my-shops/{$this->shop->slug}", $updateData);

        $response->assertStatus(200)
                ->assertJsonPath('data.name', 'Partially Updated Name')
                ->assertJsonPath('data.description', 'A test shop') // Should remain unchanged
                ->assertJsonPath('data.whatsapp_number', '+201234567890'); // Should remain unchanged
    }

    // DELETE ENDPOINT TESTS

    public function test_vendor_can_soft_delete_their_shop()
    {
        Sanctum::actingAs($this->vendor);

        $response = $this->deleteJson("/api/v1/vendor/my-shops/{$this->shop->slug}");

        $response->assertStatus(204);

        // Verify soft delete
        $this->assertSoftDeleted('shops', [
            'id' => $this->shop->id,
        ]);

        // Verify shop is not in regular queries
        $this->assertDatabaseMissing('shops', [
            'id' => $this->shop->id,
            'deleted_at' => null,
        ]);
    }

    public function test_vendor_cannot_delete_other_vendors_shop()
    {
        // Create shop for other vendor
        $otherLocation = Location::factory()->create(['city_id' => $this->city->id]);
        $otherShop = Shop::factory()->create([
            'owner_id' => $this->otherVendor->id,
            'location_id' => $otherLocation->id,
        ]);

        Sanctum::actingAs($this->vendor);

        $response = $this->deleteJson("/api/v1/vendor/my-shops/{$otherShop->slug}");

        $response->assertStatus(403)
                ->assertJson([
                    'success' => false,
                    'error' => [
                        'code' => 'FORBIDDEN',
                        'message' => 'Forbidden.',
                    ]
                ]);

        // Verify shop was not deleted
        $this->assertDatabaseHas('shops', [
            'id' => $otherShop->id,
            'deleted_at' => null,
        ]);
    }

    // RESTORE ENDPOINT TESTS

    public function test_vendor_can_restore_their_soft_deleted_shop()
    {
        // Soft delete the shop first
        $this->shop->delete();

        Sanctum::actingAs($this->vendor);

        $response = $this->postJson("/api/v1/vendor/my-shops/{$this->shop->slug}/restore");

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
                        'location',
                        'owner'
                    ],
                    'meta' => [
                        'version_info'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $this->shop->id,
                        'name' => 'Test Shop',
                    ]
                ]);

        // Verify restoration
        $this->assertDatabaseHas('shops', [
            'id' => $this->shop->id,
            'deleted_at' => null,
        ]);
    }

    public function test_vendor_cannot_restore_other_vendors_shop()
    {
        // Create and soft delete shop for other vendor
        $otherLocation = Location::factory()->create(['city_id' => $this->city->id]);
        $otherShop = Shop::factory()->create([
            'owner_id' => $this->otherVendor->id,
            'location_id' => $otherLocation->id,
        ]);
        $otherShop->delete();

        Sanctum::actingAs($this->vendor);

        $response = $this->postJson("/api/v1/vendor/my-shops/{$otherShop->slug}/restore");

        $response->assertStatus(403)
                ->assertJson([
                    'success' => false,
                    'error' => [
                        'code' => 'FORBIDDEN',
                        'message' => 'Forbidden.',
                    ]
                ]);

        // Verify shop was not restored
        $this->assertSoftDeleted('shops', [
            'id' => $otherShop->id,
        ]);
    }

    public function test_restore_handles_non_existent_shop()
    {
        Sanctum::actingAs($this->vendor);

        $response = $this->postJson('/api/v1/vendor/my-shops/non-existent-slug/restore');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'error' => [
                        'code' => 'NOT_FOUND',
                        'message' => 'Resource not found.',
                    ]
                ]);
    }
    // AUTHORIZATION TESTS

    public function test_all_endpoints_require_authentication()
    {
        $endpoints = [
            ['GET', '/api/v1/vendor/my-shops'],
            ['POST', '/api/v1/vendor/my-shops'],
            ['GET', "/api/v1/vendor/my-shops/{$this->shop->slug}"],
            ['PUT', "/api/v1/vendor/my-shops/{$this->shop->slug}"],
            ['DELETE', "/api/v1/vendor/my-shops/{$this->shop->slug}"],
            ['POST', "/api/v1/vendor/my-shops/{$this->shop->slug}/restore"],
        ];

        foreach ($endpoints as [$method, $url]) {
            $response = $this->json($method, $url);
            
            $response->assertStatus(401)
                    ->assertJson([
                        'success' => false,
                        'error' => [
                            'code' => 'UNAUTHENTICATED',
                            'message' => 'Unauthenticated.',
                        ]
                    ]);
        }
    }

    public function test_api_responses_include_version_information()
    {
        Sanctum::actingAs($this->vendor);

        $response = $this->getJson('/api/v1/vendor/my-shops');

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
}