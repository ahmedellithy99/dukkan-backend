<?php

namespace Tests\Feature\Api\V1\Website;

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubcategoryApiTest extends TestCase
{
    use RefreshDatabase;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a parent category for testing
        $this->category = Category::factory()->create(['name' => 'Electronics']);
    }

    public function test_public_can_list_subcategories_for_category()
    {
        // Create subcategories for this category
        Subcategory::factory()->count(3)->create(['category_id' => $this->category->id]);
        
        // Create subcategories for another category (should not appear)
        $otherCategory = Category::factory()->create();
        Subcategory::factory()->count(2)->create(['category_id' => $otherCategory->id]);

        $response = $this->getJson("/api/v1/categories/{$this->category->slug}/subcategories");

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
        
        // Verify all subcategories belong to the correct category
        foreach ($response->json('data') as $subcategory) {
            $this->assertEquals($this->category->id, $subcategory['category_id']);
        }
    }

    public function test_public_can_show_subcategory()
    {
        $subcategory = Subcategory::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Smartphones'
        ]);

        $response = $this->getJson("/api/v1/categories/{$this->category->slug}/subcategories/{$subcategory->slug}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $subcategory->id,
                        'name' => 'Smartphones',
                        'slug' => 'smartphones',
                        'category_id' => $this->category->id
                    ]
                ]);
    }

    public function test_subcategory_belongs_to_correct_category()
    {
        $otherCategory = Category::factory()->create(['name' => 'Books']);
        $subcategory = Subcategory::factory()->create(['category_id' => $otherCategory->id]);

        // Try to access subcategory through wrong category
        $response = $this->getJson("/api/v1/categories/{$this->category->slug}/subcategories/{$subcategory->slug}");

        $response->assertStatus(404);
    }

    public function test_public_cannot_access_nonexistent_subcategory()
    {
        $response = $this->getJson("/api/v1/categories/{$this->category->slug}/subcategories/nonexistent-subcategory");

        $response->assertStatus(404);
    }

    public function test_public_cannot_access_subcategories_with_invalid_category()
    {
        $response = $this->getJson("/api/v1/categories/invalid-category/subcategories");

        $response->assertStatus(404);
    }

    public function test_public_endpoints_do_not_require_authentication()
    {
        $subcategory = Subcategory::factory()->create(['category_id' => $this->category->id]);

        // Test list endpoint
        $response = $this->getJson("/api/v1/categories/{$this->category->slug}/subcategories");
        $response->assertStatus(200);

        // Test show endpoint
        $response = $this->getJson("/api/v1/categories/{$this->category->slug}/subcategories/{$subcategory->slug}");
        $response->assertStatus(200);
    }

    public function test_empty_category_returns_empty_subcategories_list()
    {
        $emptyCategory = Category::factory()->create(['name' => 'Empty Category']);

        $response = $this->getJson("/api/v1/categories/{$emptyCategory->slug}/subcategories");

        $response->assertStatus(200);
        
        $subcategories = $response->json('data');
        $this->assertEquals(0, count($subcategories));
    }

    public function test_subcategories_list_only_shows_subcategories_from_specified_category()
    {
        // Create subcategories for our test category
        $subcategory1 = Subcategory::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Electronics Sub 1'
        ]);
        $subcategory2 = Subcategory::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Electronics Sub 2'
        ]);

        // Create another category with subcategories
        $booksCategory = Category::factory()->create(['name' => 'Books']);
        $bookSubcategory = Subcategory::factory()->create([
            'category_id' => $booksCategory->id,
            'name' => 'Fiction'
        ]);

        $response = $this->getJson("/api/v1/categories/{$this->category->slug}/subcategories");

        $response->assertStatus(200);
        
        $subcategories = $response->json('data');
        $this->assertEquals(2, count($subcategories));
        
        // Verify only electronics subcategories are returned
        $subcategoryNames = array_column($subcategories, 'name');
        $this->assertContains('Electronics Sub 1', $subcategoryNames);
        $this->assertContains('Electronics Sub 2', $subcategoryNames);
        $this->assertNotContains('Fiction', $subcategoryNames);
    }
}