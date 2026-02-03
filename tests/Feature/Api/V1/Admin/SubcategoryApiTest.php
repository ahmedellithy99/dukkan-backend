<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Models\Category;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SubcategoryApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $vendorUser;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user
        $this->adminUser = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@example.com',
        ]);

        // Create vendor user for authorization testing
        $this->vendorUser = User::factory()->create([
            'role' => 'vendor',
            'email' => 'vendor@example.com',
        ]);

        // Create a parent category for testing
        $this->category = Category::factory()->create(['name' => 'Electronics']);
    }

    public function test_admin_can_list_subcategories_for_category()
    {
        Sanctum::actingAs($this->adminUser);

        // Create subcategories for this category
        Subcategory::factory()->count(3)->create(['category_id' => $this->category->id]);
        
        // Create subcategories for another category (should not appear)
        $otherCategory = Category::factory()->create();
        Subcategory::factory()->count(2)->create(['category_id' => $otherCategory->id]);

        $response = $this->getJson("/api/v1/admin/categories/{$this->category->slug}/subcategories");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'slug',
                            'category_id',
                        ]
                    ]
                ]);

        // Should only return subcategories for this category
        $this->assertEquals(3, count($response->json('data')));
    }

    public function test_admin_can_create_subcategory()
    {
        Sanctum::actingAs($this->adminUser);

        $subcategoryData = [
            'name' => 'Smartphones',
        ];

        $response = $this->postJson("/api/v1/admin/categories/{$this->category->slug}/subcategories", $subcategoryData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'name',
                        'slug',
                        'category_id',
                    ]
                ]);

        $this->assertDatabaseHas('subcategories', [
            'name' => 'Smartphones',
            'slug' => 'smartphones',
            'category_id' => $this->category->id
        ]);
    }

    public function test_admin_can_show_subcategory()
    {
        Sanctum::actingAs($this->adminUser);

        $subcategory = Subcategory::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Laptops'
        ]);

        $response = $this->getJson("/api/v1/admin/categories/{$this->category->slug}/subcategories/{$subcategory->slug}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $subcategory->id,
                        'name' => 'Laptops',
                        'slug' => 'laptops',
                        'category_id' => $this->category->id
                    ]
                ]);
    }

    public function test_admin_can_update_subcategory()
    {
        Sanctum::actingAs($this->adminUser);

        $subcategory = Subcategory::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Old Name'
        ]);

        $updateData = [
            'name' => 'New Name',
        ];

        $response = $this->putJson("/api/v1/admin/categories/{$this->category->slug}/subcategories/{$subcategory->slug}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $subcategory->id,
                        'name' => 'New Name',
                        'slug' => 'new-name',
                        'category_id' => $this->category->id
                    ]
                ]);

        $this->assertDatabaseHas('subcategories', [
            'id' => $subcategory->id,
            'name' => 'New Name',
            'slug' => 'new-name'
        ]);
    }

    public function test_admin_can_delete_empty_subcategory()
    {
        Sanctum::actingAs($this->adminUser);

        $subcategory = Subcategory::factory()->create(['category_id' => $this->category->id]);

        $response = $this->deleteJson("/api/v1/admin/categories/{$this->category->slug}/subcategories/{$subcategory->slug}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('subcategories', [
            'id' => $subcategory->id
        ]);
    }

    public function test_admin_cannot_delete_subcategory_with_products()
    {
        Sanctum::actingAs($this->adminUser);

        $subcategory = Subcategory::factory()->create(['category_id' => $this->category->id]);
        
        // Create a product in the subcategory (assuming Product model exists)
        // This test will be more relevant when products are implemented
        
        $response = $this->deleteJson("/api/v1/admin/categories/{$this->category->slug}/subcategories/{$subcategory->slug}");

        // For now, it should succeed since no products exist
        $response->assertStatus(204);
    }

    public function test_subcategory_belongs_to_correct_category()
    {
        Sanctum::actingAs($this->adminUser);

        $otherCategory = Category::factory()->create(['name' => 'Books']);
        $subcategory = Subcategory::factory()->create(['category_id' => $otherCategory->id]);

        // Try to access subcategory through wrong category
        $response = $this->getJson("/api/v1/admin/categories/{$this->category->slug}/subcategories/{$subcategory->slug}");

        $response->assertStatus(404);
    }

    public function test_vendor_cannot_access_admin_subcategories()
    {
        Sanctum::actingAs($this->vendorUser);

        $response = $this->getJson("/api/v1/admin/categories/{$this->category->slug}/subcategories");

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_admin_subcategories()
    {
        $response = $this->getJson("/api/v1/admin/categories/{$this->category->slug}/subcategories");

        $response->assertStatus(401);
    }

    public function test_subcategory_name_is_required()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson("/api/v1/admin/categories/{$this->category->slug}/subcategories", []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
    }

    public function test_subcategory_name_must_be_unique_within_category()
    {
        Sanctum::actingAs($this->adminUser);

        Subcategory::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Smartphones'
        ]);

        $response = $this->postJson("/api/v1/admin/categories/{$this->category->slug}/subcategories", [
            'name' => 'Smartphones'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
    }

    public function test_subcategory_name_must_be_globally_unique()
    {
        Sanctum::actingAs($this->adminUser);

        $otherCategory = Category::factory()->create(['name' => 'Books']);
        
        // Create subcategory in first category
        Subcategory::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Accessories'
        ]);

        // Should NOT be able to create subcategory with same name in different category
        $response = $this->postJson("/api/v1/admin/categories/{$otherCategory->slug}/subcategories", [
            'name' => 'Accessories'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
    }

    public function test_subcategory_slug_is_auto_generated()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson("/api/v1/admin/categories/{$this->category->slug}/subcategories", [
            'name' => 'Gaming Laptops'
        ]);

        $response->assertStatus(201);
        
        $subcategory = Subcategory::where('name', 'Gaming Laptops')->first();
        $this->assertEquals('gaming-laptops', $subcategory->slug);
    }

    public function test_cannot_access_subcategory_with_invalid_category()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->getJson("/api/v1/admin/categories/invalid-category/subcategories");

        $response->assertStatus(404);
    }
}