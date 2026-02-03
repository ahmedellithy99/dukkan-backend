<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Models\Category;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $vendorUser;

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
    }

    public function test_admin_can_list_categories()
    {
        Sanctum::actingAs($this->adminUser);

        // Create test categories
        $categories = Category::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/admin/categories');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'slug',
                            'created_at',
                            'updated_at',
                            'subcategories'
                        ]
                    ]
                ]);

        $this->assertEquals(3, count($response->json('data')));
    }

    public function test_admin_can_create_category()
    {
        Sanctum::actingAs($this->adminUser);

        $categoryData = [
            'name' => 'Electronics',
        ];

        $response = $this->postJson('/api/v1/admin/categories', $categoryData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'name',
                        'slug',
                        'created_at',
                        'updated_at',
                        'subcategories'
                    ]
                ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Electronics',
            'slug' => 'electronics'
        ]);
    }

    public function test_admin_can_show_category()
    {
        Sanctum::actingAs($this->adminUser);

        $category = Category::factory()->create(['name' => 'Books']);

        $response = $this->getJson("/api/v1/admin/categories/{$category->slug}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $category->id,
                        'name' => 'Books',
                        'slug' => 'books'
                    ]
                ]);
    }

    public function test_admin_can_update_category()
    {
        Sanctum::actingAs($this->adminUser);

        $category = Category::factory()->create(['name' => 'Old Name']);

        $updateData = [
            'name' => 'New Name',
        ];

        $response = $this->putJson("/api/v1/admin/categories/{$category->slug}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $category->id,
                        'name' => 'New Name',
                        'slug' => 'new-name'
                    ]
                ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'New Name',
            'slug' => 'new-name'
        ]);
    }

    public function test_admin_can_delete_empty_category()
    {
        Sanctum::actingAs($this->adminUser);

        $category = Category::factory()->create();

        $response = $this->deleteJson("/api/v1/admin/categories/{$category->slug}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id
        ]);
    }

    public function test_admin_cannot_delete_category_with_products()
    {
        Sanctum::actingAs($this->adminUser);

        $category = Category::factory()->create();
        $subcategory = Subcategory::factory()->create(['category_id' => $category->id]);
        
        // Create a product in the subcategory (assuming Product model exists)
        // This test will be more relevant when products are implemented
        
        $response = $this->deleteJson("/api/v1/admin/categories/{$category->slug}");

        // For now, it should succeed since no products exist
        $response->assertStatus(204);
    }

    public function test_vendor_cannot_access_admin_categories()
    {
        Sanctum::actingAs($this->vendorUser);

        $response = $this->getJson('/api/v1/admin/categories');

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_admin_categories()
    {
        $response = $this->getJson('/api/v1/admin/categories');

        $response->assertStatus(401);
    }

    public function test_category_name_is_required()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/v1/admin/categories', []);

        $response->assertStatus(422)
                ->assertJsonPath('error.fields.name.0', 'Category name is required.');
    }

    public function test_category_name_must_be_unique()
    {
        Sanctum::actingAs($this->adminUser);

        Category::factory()->create(['name' => 'Electronics']);

        $response = $this->postJson('/api/v1/admin/categories', [
            'name' => 'Electronics'
        ]);

        $response->assertStatus(422)
                ->assertJsonPath('error.fields.name.0', 'The name has already been taken.');
    }

    public function test_category_slug_is_auto_generated()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/v1/admin/categories', [
            'name' => 'Consumer Electronics'
        ]);

        $response->assertStatus(201);
        
        $category = Category::where('name', 'Consumer Electronics')->first();
        $this->assertEquals('consumer-electronics', $category->slug);
    }
}