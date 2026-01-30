<?php

namespace Tests\Feature\Api\V1\Vendor;

use App\Models\User;
use App\Models\Location;
use App\Models\City;
use App\Models\Governorate;
use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LocationUpdateTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $vendor;
    protected Location $location;
    protected City $city;
    protected Governorate $governorate;

    protected function setUp(): void
    {
        parent::setUp();

        // Create governorate and city
        $this->governorate = Governorate::factory()->create([
            'name' => 'Cairo',
            'slug' => 'cairo'
        ]);

        $this->city = City::factory()->create([
            'governorate_id' => $this->governorate->id,
            'name' => 'Cairo City',
            'slug' => 'cairo-city'
        ]);

        // Create vendor user
        $this->vendor = User::factory()->vendor()->create([
            'email' => 'vendor@test.com'
        ]);

        // Create location
        $this->location = Location::factory()->create([
            'city_id' => $this->city->id,
            'area' => 'Downtown',
            'latitude' => 30.0444,
            'longitude' => 31.2357,
        ]);

        // Create shop owned by vendor using this location
        Shop::factory()->create([
            'owner_id' => $this->vendor->id,
            'location_id' => $this->location->id,
        ]);
    }

    public function test_vendor_can_update_location_successfully()
    {
        Sanctum::actingAs($this->vendor);

        $updateData = [
            'area' => 'Uptown',
            'latitude' => 30.0555,
            'longitude' => 31.2468,
            'city_id' => $this->city->id,
        ];

        $response = $this->putJson("/api/v1/vendor/locations/{$this->location->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'api_version',
                    'success',
                    'data' => [
                        'id',
                        'area',
                        'latitude',
                        'longitude',
                        'full_address',
                        'created_at',
                        'updated_at',
                        'city' => [
                            'id',
                            'name',
                            'slug',
                            'governorate' => [
                                'id',
                                'name',
                                'slug'
                            ]
                        ]
                    ],
                    'meta' => [
                        'version_info'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $this->location->id,
                        'area' => 'Uptown',
                        'latitude' => 30.0555,
                        'longitude' => 31.2468,
                    ]
                ]);

        // Verify database was updated
        $this->assertDatabaseHas('locations', [
            'id' => $this->location->id,
            'area' => 'Uptown',
            'latitude' => 30.0555,
            'longitude' => 31.2468,
        ]);
    }

    public function test_unauthenticated_user_cannot_update_location()
    {
        $updateData = [
            'area' => 'Uptown',
            'latitude' => 30.0555,
            'longitude' => 31.2468,
        ];

        $response = $this->putJson("/api/v1/vendor/locations/{$this->location->id}", $updateData);

        $response->assertStatus(401)
                ->assertJson([
                    'success' => false,
                    'error' => [
                        'code' => 'UNAUTHENTICATED',
                        'message' => 'Unauthenticated.',
                    ]
                ]);
    }

    public function test_location_update_validates_coordinates_within_egypt_bounds()
    {
        Sanctum::actingAs($this->vendor);

        $updateData = [
            'area' => 'Test Area',
            'latitude' => 50.0, // Outside Egypt bounds
            'longitude' => 50.0, // Outside Egypt bounds
            'city_id' => $this->city->id,
        ];

        $response = $this->putJson("/api/v1/vendor/locations/{$this->location->id}", $updateData);

        $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                    'error' => [
                        'code' => 'VALIDATION_FAILED',
                        'message' => 'Validation failed.',
                        'fields' => [
                            'latitude' => ['Latitude must be within Egypt bounds (22-32).'],
                            'longitude' => ['Longitude must be within Egypt bounds (25-37).']
                        ]
                    ]
                ]);
    }

    public function test_vendor_cannot_update_location_not_owned_by_them()
    {
        // Create another vendor
        $otherVendor = User::factory()->vendor()->create([
            'email' => 'other@test.com'
        ]);

        // Create a location with a shop owned by the other vendor
        $otherLocation = Location::factory()->create([
            'city_id' => $this->city->id,
            'area' => 'Other Area',
            'latitude' => 30.0333,
            'longitude' => 31.2222,
        ]);

        Shop::factory()->create([
            'owner_id' => $otherVendor->id,
            'location_id' => $otherLocation->id,
        ]);

        // Try to update the other vendor's location
        Sanctum::actingAs($this->vendor);

        $updateData = [
            'area' => 'Hacked Area',
            'latitude' => 30.0555,
            'longitude' => 31.2468,
        ];

        $response = $this->putJson("/api/v1/vendor/locations/{$otherLocation->id}", $updateData);

        $response->assertStatus(403)
                ->assertJsonStructure([
                    'api_version',
                    'success',
                    'error' => [
                        'code',
                        'message',
                        'fields'
                    ],
                    'meta' => [
                        'version_info'
                    ]
                ])
                ->assertJson([
                    'success' => false,
                    'error' => [
                        'code' => 'LOCATION_ACCESS_DENIED',
                        'message' => 'Access denied to location.',
                    ]
                ]);

        // Verify database was not updated
        $this->assertDatabaseHas('locations', [
            'id' => $otherLocation->id,
            'area' => 'Other Area', // Original value
        ]);
    }

    public function test_location_update_handles_non_existent_location()
    {
        Sanctum::actingAs($this->vendor);

        $updateData = [
            'area' => 'Test Area',
            'latitude' => 30.0444,
            'longitude' => 31.2357,
            'city_id' => $this->city->id,
        ];

        $response = $this->putJson("/api/v1/vendor/locations/999", $updateData);

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'error' => [
                        'code' => 'NOT_FOUND',
                        'message' => 'Resource not found.',
                    ]
                ]);
    }

    public function test_location_update_validates_string_field_lengths()
    {
        Sanctum::actingAs($this->vendor);

        $updateData = [
            'area' => str_repeat('a', 101), // Exceeds 100 character limit
            'latitude' => 30.0444,
            'longitude' => 31.2357,
            'city_id' => $this->city->id,
        ];

        $response = $this->putJson("/api/v1/vendor/locations/{$this->location->id}", $updateData);

        $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                    'error' => [
                        'code' => 'VALIDATION_FAILED',
                        'message' => 'Validation failed.',
                        'fields' => [
                            'area' => ['Area name cannot exceed 100 characters.']
                        ]
                    ]
                ]);
    }

    public function test_location_update_allows_partial_updates()
    {
        Sanctum::actingAs($this->vendor);

        // Only update area
        $updateData = [
            'area' => 'New Area Only',
        ];

        $response = $this->putJson("/api/v1/vendor/locations/{$this->location->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $this->location->id,
                        'area' => 'New Area Only',
                        'latitude' => 30.0444, // Should remain unchanged
                        'longitude' => 31.2357, // Should remain unchanged
                    ]
                ]);
    }
}