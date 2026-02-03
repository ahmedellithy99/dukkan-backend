<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Location;
use App\Models\Shop;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoreSystemIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_marketplace_workflow_integration()
    {
        // 1. Test User Authentication System
        $vendor = User::factory()->create([
            'role' => 'vendor',
            'status' => 'active'
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $vendor->id,
            'role' => 'vendor'
        ]);

        // 2. Test Location Management System
        $location = Location::factory()->create([
            'area' => 'Nasr City',
            'latitude' => 30.0444,
            'longitude' => 31.2357
        ]);

        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'area' => 'Nasr City'
        ]);

        // 3. Test Category System
        $category = Category::factory()->create([
            'name' => 'Electronics'
        ]);

        $subcategory = Subcategory::factory()->create([
            'category_id' => $category->id,
            'name' => 'Smartphones'
        ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Electronics'
        ]);

        $this->assertDatabaseHas('subcategories', [
            'id' => $subcategory->id,
            'category_id' => $category->id,
            'name' => 'Smartphones'
        ]);

        // 4. Test Shop Management System
        $shop = Shop::factory()->create([
            'owner_id' => $vendor->id,
            'location_id' => $location->id,
            'name' => 'Tech Store',
            'is_active' => true
        ]);

        $this->assertDatabaseHas('shops', [
            'id' => $shop->id,
            'owner_id' => $vendor->id,
            'location_id' => $location->id,
            'name' => 'Tech Store'
        ]);

        // 5. Test Product System
        $product = Product::factory()->create([
            'shop_id' => $shop->id,
            'subcategory_id' => $subcategory->id,
            'name' => 'iPhone 15',
            'is_active' => true
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'shop_id' => $shop->id,
            'subcategory_id' => $subcategory->id,
            'name' => 'iPhone 15'
        ]);

        // 6. Test Relationships Integration
        $this->assertEquals($vendor->id, $shop->owner->id);
        $this->assertEquals($location->id, $shop->location->id);
        $this->assertEquals($shop->id, $product->shop->id);
        $this->assertEquals($subcategory->id, $product->subcategory->id);
        $this->assertEquals($category->id, $product->subcategory->category->id);
        
        // Test hierarchical location relationships
        $this->assertNotNull($location->city);
        $this->assertNotNull($location->city->governorate);

        // 7. Test API Authentication Integration
        $token = $vendor->createToken('test-token')->plainTextToken;
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->get('/api/v1/vendor/my-shops');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'location' => [
                        'area',
                        'city' => [
                            'name',
                            'governorate' => [
                                'name'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        // 8. Test Public API Integration
        $publicResponse = $this->get('/api/v1/shops');
        $publicResponse->assertStatus(200);
        $publicResponse->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'location'
                ]
            ]
        ]);

        // 9. Test Category API Integration
        $categoryResponse = $this->get('/api/v1/categories');
        $categoryResponse->assertStatus(200);
        $categoryResponse->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'slug'
                ]
            ]
        ]);
    }

    public function test_admin_system_integration()
    {
        // Create admin user separately to avoid conflicts
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active'
        ]);

        $adminToken = $admin->createToken('admin-token')->plainTextToken;

        // Test admin authentication first
        $meResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $adminToken,
            'Accept' => 'application/json'
        ])->get('/api/v1/admin/me');

        $meResponse->assertStatus(200);
        $meResponse->assertJsonPath('data.user.role', 'admin');

        // Test Admin API Integration
        $adminResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $adminToken,
            'Accept' => 'application/json'
        ])->get('/api/v1/admin/categories');

        $adminResponse->assertStatus(200);
        $adminResponse->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'slug'
                ]
            ]
        ]);
    }

    public function test_location_update_authorization_integration()
    {
        $vendor1 = User::factory()->create(['role' => 'vendor']);
        $vendor2 = User::factory()->create(['role' => 'vendor']);
        
        $location1 = Location::factory()->create();
        $location2 = Location::factory()->create();
        
        $shop1 = Shop::factory()->create([
            'owner_id' => $vendor1->id,
            'location_id' => $location1->id
        ]);
        
        $shop2 = Shop::factory()->create([
            'owner_id' => $vendor2->id,
            'location_id' => $location2->id
        ]);

        $token1 = $vendor1->createToken('test-token')->plainTextToken;

        // Test successful location update (vendor owns shop at this location)
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token1,
            'Accept' => 'application/json'
        ])->put("/api/v1/vendor/locations/{$location1->id}", [
            'area' => 'Updated Area',
            'latitude' => 30.0555,
            'longitude' => 31.2468
        ]);

        $response->assertStatus(200);

        // Test unauthorized location update (vendor doesn't own shop at this location)
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token1,
            'Accept' => 'application/json'
        ])->put("/api/v1/vendor/locations/{$location2->id}", [
            'area' => 'Unauthorized Area',
            'latitude' => 30.0666,
            'longitude' => 31.2579
        ]);

        $response->assertStatus(403);
    }

    public function test_category_hierarchy_integration()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('admin-token')->plainTextToken;

        // Create category via API
        $categoryResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->post('/api/v1/admin/categories', [
            'name' => 'Test Category'
        ]);

        $categoryResponse->assertStatus(201);
        $categorySlug = $categoryResponse->json('data.slug');

        // Create subcategory via API
        $subcategoryResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->post("/api/v1/admin/categories/{$categorySlug}/subcategories", [
            'name' => 'Test Subcategory'
        ]);

        $subcategoryResponse->assertStatus(201);
        $subcategoryId = $subcategoryResponse->json('data.id');

        // Verify hierarchy in database
        $category = Category::where('slug', $categorySlug)->first();
        $subcategory = Subcategory::find($subcategoryId);

        $this->assertEquals($category->id, $subcategory->category_id);
        $this->assertTrue($category->subcategories->contains($subcategory));

        // Test public API shows hierarchy
        $publicResponse = $this->get("/api/v1/categories/{$categorySlug}");
        $publicResponse->assertStatus(200);
        $publicResponse->assertJsonPath('data.name', 'Test Category');
    }
}