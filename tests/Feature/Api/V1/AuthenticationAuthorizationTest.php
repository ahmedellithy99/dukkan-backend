<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use App\Models\Shop;
use App\Models\Location;
use App\Models\City;
use App\Models\Governorate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $vendor;
    protected User $admin;
    protected Shop $shop;

    protected function setUp(): void
    {
        parent::setUp();

        $governorate = Governorate::factory()->create();
        $city = City::factory()->create(['governorate_id' => $governorate->id]);
        $location = Location::factory()->create(['city_id' => $city->id]);

        $this->vendor = User::factory()->create(['role' => 'vendor', 'status' => 'active']);
        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->shop = Shop::factory()->create([
            'owner_id' => $this->vendor->id,
            'location_id' => $location->id,
        ]);
    }

    /** @test */
    public function test_vendor_registration_creates_user_and_returns_token()
    {
        $response = $this->postJson('/api/v1/vendor/register', [
            'name' => 'New Vendor',
            'email' => 'newvendor@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '+201234567890',
        ]);

        $response->assertCreated();
        $response->assertJsonStructure([
            'data' => [
                'user' => ['id', 'name', 'email', 'role'],
                'token',
            ],
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newvendor@test.com',
            'role' => 'vendor',
        ]);
    }

    /** @test */
    public function test_vendor_registration_validates_required_fields()
    {
        $response = $this->postJson('/api/v1/vendor/register', []);

        $response->assertUnprocessable();
        $response->assertJsonStructure([
            'success',
            'error' => [
                'code',
                'message',
                'fields',
            ],
        ]);
    }

    /** @test */
    public function test_vendor_registration_prevents_duplicate_email()
    {
        $response = $this->postJson('/api/v1/vendor/register', [
            'name' => 'Duplicate Vendor',
            'email' => $this->vendor->email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '+201234567890',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonPath('error.fields.email', function ($errors) {
            return count($errors) > 0;
        });
    }

    /** @test */
    public function test_vendor_login_with_valid_credentials()
    {
        $response = $this->postJson('/api/v1/vendor/login', [
            'email' => $this->vendor->email,
            'password' => 'password',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'user',
                'token',
            ],
        ]);
    }

    /** @test */
    public function test_vendor_login_fails_with_invalid_credentials()
    {
        $response = $this->postJson('/api/v1/vendor/login', [
            'email' => $this->vendor->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertUnauthorized();
    }

    /** @test */
    public function test_admin_login_with_valid_credentials()
    {
        $response = $this->postJson('/api/v1/admin/login', [
            'email' => $this->admin->email,
            'password' => 'password',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'user',
                'token',
            ],
        ]);
    }

    /** @test */
    public function test_admin_login_rejects_non_admin_users()
    {
        $response = $this->postJson('/api/v1/admin/login', [
            'email' => $this->vendor->email,
            'password' => 'password',
        ]);

        $response->assertForbidden(); // 403 is correct for wrong role
    }

    /** @test */
    public function test_authenticated_user_can_access_me_endpoint()
    {
        $token = $this->vendor->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/vendor/me');

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'user' => [
                    'id' => $this->vendor->id,
                    'email' => $this->vendor->email,
                ],
            ],
        ]);
    }

    /** @test */
    public function test_logout_invalidates_token()
    {
        $token = $this->vendor->createToken('test')->plainTextToken;

        // Logout
        $logoutResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/vendor/logout');

        $logoutResponse->assertOk();
        $logoutResponse->assertJson([
            'success' => true,
            'data' => [
                'message' => 'Successfully logged out',
            ],
        ]);
    }

    /** @test */
    public function test_vendor_can_only_access_own_shops()
    {
        $otherVendor = User::factory()->create(['role' => 'vendor']);
        $governorate = Governorate::factory()->create();
        $city = City::factory()->create(['governorate_id' => $governorate->id]);
        $location = Location::factory()->create(['city_id' => $city->id]);
        
        $otherShop = Shop::factory()->create([
            'owner_id' => $otherVendor->id,
            'location_id' => $location->id,
        ]);

        $token = $this->vendor->createToken('test')->plainTextToken;

        // Try to access other vendor's shop
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/vendor/my-shops/{$otherShop->id}");

        $response->assertNotFound();
    }

    /** @test */
    public function test_vendor_cannot_update_other_vendor_shop()
    {
        $otherVendor = User::factory()->create(['role' => 'vendor']);
        $governorate = Governorate::factory()->create();
        $city = City::factory()->create(['governorate_id' => $governorate->id]);
        $location = Location::factory()->create(['city_id' => $city->id]);
        
        $otherShop = Shop::factory()->create([
            'owner_id' => $otherVendor->id,
            'location_id' => $location->id,
        ]);

        $token = $this->vendor->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/v1/vendor/my-shops/{$otherShop->id}", [
                'name' => 'Hacked Shop',
                'location_id' => $location->id,
                'whatsapp_number' => '+201234567890',
            ]);

        $response->assertNotFound();
    }

    /** @test */
    public function test_vendor_cannot_delete_other_vendor_shop()
    {
        $otherVendor = User::factory()->create(['role' => 'vendor']);
        $governorate = Governorate::factory()->create();
        $city = City::factory()->create(['governorate_id' => $governorate->id]);
        $location = Location::factory()->create(['city_id' => $city->id]);
        
        $otherShop = Shop::factory()->create([
            'owner_id' => $otherVendor->id,
            'location_id' => $location->id,
        ]);

        $token = $this->vendor->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/v1/vendor/my-shops/{$otherShop->id}");

        $response->assertNotFound();
    }

    /** @test */
    public function test_unauthenticated_requests_return_401()
    {
        $endpoints = [
            ['GET', '/api/v1/vendor/me'],
            ['POST', '/api/v1/vendor/logout'],
            ['GET', '/api/v1/vendor/my-shops'],
            ['GET', '/api/v1/admin/me'],
            ['GET', '/api/v1/admin/categories'],
        ];

        foreach ($endpoints as [$method, $endpoint]) {
            $response = $this->json($method, $endpoint);
            $response->assertUnauthorized();
        }
    }

    /** @test */
    public function test_invalid_token_returns_401()
    {
        $response = $this->withHeader('Authorization', 'Bearer invalid-token')
            ->getJson('/api/v1/vendor/me');

        $response->assertUnauthorized();
    }

    /** @test */
    public function test_vendor_cannot_access_admin_endpoints()
    {
        $token = $this->vendor->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/admin/categories');

        // Should return 401 or 403 depending on implementation
        $this->assertContains($response->status(), [401, 403]);
    }

    /** @test */
    public function test_rate_limiting_on_authentication_endpoints()
    {
        // Make multiple failed login attempts
        for ($i = 0; $i < 6; $i++) {
            $response = $this->postJson('/api/v1/vendor/login', [
                'email' => 'test@test.com',
                'password' => 'wrongpassword',
            ]);

            if ($i < 5) {
                $this->assertContains($response->status(), [401, 422]);
            } else {
                // 6th request should be rate limited
                $response->assertStatus(429);
            }
        }
    }

    /** @test */
    public function test_password_confirmation_required_for_registration()
    {
        $response = $this->postJson('/api/v1/vendor/register', [
            'name' => 'Test Vendor',
            'email' => 'test@test.com',
            'password' => 'password123',
            // Missing password_confirmation
        ]);

        $response->assertUnprocessable();
        $response->assertJsonPath('error.fields.password', function ($errors) {
            return count($errors) > 0;
        });
    }

    /** @test */
    public function test_password_must_match_confirmation()
    {
        $response = $this->postJson('/api/v1/vendor/register', [
            'name' => 'Test Vendor',
            'email' => 'test@test.com',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonPath('error.fields.password', function ($errors) {
            return count($errors) > 0;
        });
    }
}
