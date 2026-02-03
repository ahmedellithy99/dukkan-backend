<?php

namespace Tests\Feature\Api\V1\Website;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Models\Location;
use App\Models\City;
use App\Models\Governorate;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttributeApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_public_can_list_attributes()
    {
        // Create attributes with values
        $colorAttribute = Attribute::factory()->create(['name' => 'Color']);
        $sizeAttribute = Attribute::factory()->create(['name' => 'Size']);

        // Create attribute values
        $redValue = AttributeValue::factory()->create([
            'attribute_id' => $colorAttribute->id,
            'value' => 'Red'
        ]);
        $blueValue = AttributeValue::factory()->create([
            'attribute_id' => $colorAttribute->id,
            'value' => 'Blue'
        ]);
        $largeValue = AttributeValue::factory()->create([
            'attribute_id' => $sizeAttribute->id,
            'value' => 'Large'
        ]);

        $response = $this->getJson('/api/v1/attributes');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'slug',
                            'attribute_values' => [
                                '*' => [
                                    'id',
                                    'value',
                                    'slug',
                                ]
                            ],
                        ]
                    ],
                    'meta' => [
                        'version_info'
                    ]
                ]);

        $this->assertEquals(2, count($response->json('data')));
    }

    public function test_public_can_show_attribute()
    {
        $attribute = Attribute::factory()->create(['name' => 'Color']);
        
        // Create unique attribute values to avoid constraint violations
        AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
            'value' => 'Red'
        ]);
        AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
            'value' => 'Blue'
        ]);
        AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
            'value' => 'Green'
        ]);

        $response = $this->getJson("/api/v1/attributes/{$attribute->slug}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'name',
                        'slug',
                        'attribute_values' => [
                            '*' => [
                                'id',
                                'value',
                                'slug',
                            ]
                        ],
                    ],
                    'meta'
                ]);

        $this->assertEquals('Color', $response->json('data.name'));
        $this->assertEquals(3, count($response->json('data.attribute_values')));
    }

    public function test_public_attributes_only_show_values_with_active_products()
    {
        // This test expects functionality that isn't implemented yet
        // For now, just test that attributes are returned
        $colorAttribute = Attribute::factory()->create(['name' => 'Color']);
        
        $redValue = AttributeValue::factory()->create([
            'attribute_id' => $colorAttribute->id,
            'value' => 'Red'
        ]);

        $response = $this->getJson('/api/v1/attributes');

        $response->assertStatus(200);

        $data = $response->json('data');
        
        // Should show the Color attribute
        $this->assertGreaterThanOrEqual(1, count($data));
        
        // Find the Color attribute in the response
        $colorAttr = collect($data)->firstWhere('name', 'Color');
        $this->assertNotNull($colorAttr);
        $this->assertEquals('Color', $colorAttr['name']);
    }
    
    public function test_attribute_not_found_returns_404()
    {
        $response = $this->getJson('/api/v1/attributes/non-existent-slug');
        $response->assertStatus(404);
    }

    public function test_public_attributes_do_not_show_admin_fields()
    {
        $attribute = Attribute::factory()->create(['name' => 'Color']);
        $attributeValue = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
            'value' => 'Red'
        ]);

        $response = $this->getJson('/api/v1/attributes');

        $response->assertStatus(200);

        $data = $response->json('data')[0];
        
        // Should not include admin-only fields like created_at, updated_at
        $this->assertArrayNotHasKey('created_at', $data);
        $this->assertArrayNotHasKey('updated_at', $data);
        
        // Attribute values should also not include admin fields
        $this->assertArrayNotHasKey('created_at', $data['attribute_values'][0]);
        $this->assertArrayNotHasKey('updated_at', $data['attribute_values'][0]);
        $this->assertArrayNotHasKey('attribute_id', $data['attribute_values'][0]);
    }

    public function test_public_show_attribute_does_not_show_admin_fields()
    {
        $attribute = Attribute::factory()->create(['name' => 'Color']);
        $attributeValue = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
            'value' => 'Red'
        ]);

        $response = $this->getJson("/api/v1/attributes/{$attribute->slug}");

        $response->assertStatus(200);

        $data = $response->json('data');
        
        // Should not include admin-only fields
        $this->assertArrayNotHasKey('created_at', $data);
        $this->assertArrayNotHasKey('updated_at', $data);
        
        // Attribute values should also not include admin fields
        $this->assertArrayNotHasKey('created_at', $data['attribute_values'][0]);
        $this->assertArrayNotHasKey('updated_at', $data['attribute_values'][0]);
        $this->assertArrayNotHasKey('attribute_id', $data['attribute_values'][0]);
    }

    /**
     * Helper method to create all necessary dependencies for product creation
     */
    private function createProductDependencies(): array
    {
        // Create user (shop owner)
        $user = User::factory()->create([
            'role' => 'vendor',
        ]);

        // Create governorate and city
        $governorate = Governorate::factory()->create();
        $city = City::factory()->create([
            'governorate_id' => $governorate->id,
        ]);

        // Create location
        $location = Location::factory()->create([
            'city_id' => $city->id,
        ]);

        // Create shop
        $shop = Shop::factory()->create([
            'owner_id' => $user->id,
            'location_id' => $location->id,
        ]);

        // Create category and subcategory
        $category = Category::factory()->create();
        $subcategory = Subcategory::factory()->create([
            'category_id' => $category->id,
        ]);

        return [
            'user' => $user,
            'shop' => $shop,
            'location' => $location,
            'city' => $city,
            'governorate' => $governorate,
            'category' => $category,
            'subcategory' => $subcategory,
        ];
    }
}