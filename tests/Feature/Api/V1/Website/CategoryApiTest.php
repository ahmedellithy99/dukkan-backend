<?php

namespace Tests\Feature\Api\V1\Website;

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_list_categories()
    {
        // Create test categories
        $categories = Category::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'slug',
                            'subcategories'
                        ]
                    ]
                ]);

        $this->assertEquals(3, count($response->json('data')));
    }

    public function test_public_can_show_category()
    {
        $category = Category::factory()->create(['name' => 'Electronics']);
        
        // Create some subcategories
        Subcategory::factory()->count(2)->create(['category_id' => $category->id]);

        $response = $this->getJson("/api/v1/categories/{$category->slug}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $category->id,
                        'name' => 'Electronics',
                        'slug' => 'electronics'
                    ]
                ])
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'name',
                        'slug',
                        'subcategories' => [
                            '*' => [
                                'id',
                                'name',
                                'slug',
                                'category_id'
                            ]
                        ]
                    ]
                ]);

        // Should include subcategories
        $this->assertEquals(2, count($response->json('data.subcategories')));
    }

    public function test_public_cannot_access_nonexistent_category()
    {
        $response = $this->getJson('/api/v1/categories/nonexistent-category');

        $response->assertStatus(404);
    }

    public function test_public_endpoints_do_not_require_authentication()
    {
        $category = Category::factory()->create();

        // Test list endpoint
        $response = $this->getJson('/api/v1/categories');
        $response->assertStatus(200);

        // Test show endpoint
        $response = $this->getJson("/api/v1/categories/{$category->slug}");
        $response->assertStatus(200);
    }
}