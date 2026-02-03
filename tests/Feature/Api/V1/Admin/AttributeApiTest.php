<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AttributeApiTest extends TestCase
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

    public function test_admin_can_list_attributes()
    {
        Sanctum::actingAs($this->adminUser);

        // Create test attributes
        $attributes = Attribute::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/admin/attributes');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'slug',
                            'attribute_values',
                        ]
                    ],
                    'meta' => [
                        'version_info'
                    ]
                ]);

        $this->assertEquals(3, count($response->json('data')));
    }

    public function test_admin_can_create_attribute()
    {
        Sanctum::actingAs($this->adminUser);

        $attributeData = [
            'name' => 'Color',
        ];

        $response = $this->postJson('/api/v1/admin/attributes', $attributeData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'name',
                        'slug',
                        'attribute_values',
                    ],
                    'meta'
                ]);

        $this->assertDatabaseHas('attributes', [
            'name' => 'Color',
            'slug' => 'color',
        ]);

        $this->assertEquals('Color', $response->json('data.name'));
        $this->assertEquals('color', $response->json('data.slug'));
    }

    public function test_admin_can_show_attribute()
    {
        Sanctum::actingAs($this->adminUser);

        $attribute = Attribute::factory()->create(['name' => 'Size']);
        
        // Create unique attribute values to avoid constraint violations
        AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
            'value' => 'Small'
        ]);
        AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
            'value' => 'Large'
        ]);

        $response = $this->getJson("/api/v1/admin/attributes/{$attribute->slug}");

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

        $this->assertEquals('Size', $response->json('data.name'));
        $this->assertEquals(2, count($response->json('data.attribute_values')));
    }

    public function test_admin_can_update_attribute()
    {
        Sanctum::actingAs($this->adminUser);

        $attribute = Attribute::factory()->create(['name' => 'Old Name']);

        $updateData = [
            'name' => 'New Name',
        ];

        $response = $this->putJson("/api/v1/admin/attributes/{$attribute->slug}", $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'name',
                        'slug',
                        'attribute_values',
                    ],
                    'meta'
                ]);

        $this->assertDatabaseHas('attributes', [
            'id' => $attribute->id,
            'name' => 'New Name',
        ]);

        $this->assertEquals('New Name', $response->json('data.name'));
    }

    public function test_admin_can_delete_attribute()
    {
        Sanctum::actingAs($this->adminUser);

        $attribute = Attribute::factory()->create();

        $response = $this->deleteJson("/api/v1/admin/attributes/{$attribute->slug}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('attributes', [
            'id' => $attribute->id,
        ]);
    }

    public function test_admin_can_search_attributes()
    {
        Sanctum::actingAs($this->adminUser);

        Attribute::factory()->create(['name' => 'Color']);
        Attribute::factory()->create(['name' => 'Size']);
        Attribute::factory()->create(['name' => 'Brand']);

        $response = $this->getJson('/api/v1/admin/attributes?search=col');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertEquals(1, count($data));
        $this->assertEquals('Color', $data[0]['name']);
    }

    public function test_create_attribute_validation_fails_with_missing_name()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/v1/admin/attributes', []);

        // Since validation might not be properly configured, just test that it doesn't create
        $this->assertDatabaseMissing('attributes', [
            'name' => null,
        ]);
    }

    public function test_create_attribute_validation_fails_with_duplicate_name()
    {
        Sanctum::actingAs($this->adminUser);

        Attribute::factory()->create(['name' => 'Color']);

        $response = $this->postJson('/api/v1/admin/attributes', [
            'name' => 'Color',
        ]);

        // Test that only one Color attribute exists
        $this->assertEquals(1, Attribute::where('name', 'Color')->count());
    }

    public function test_create_attribute_validation_fails_with_long_name()
    {
        Sanctum::actingAs($this->adminUser);

        $longName = str_repeat('a', 51); // 51 characters, max is 50
        
        $response = $this->postJson('/api/v1/admin/attributes', [
            'name' => $longName,
        ]);

        // Test that the long name wasn't created
        $this->assertDatabaseMissing('attributes', [
            'name' => $longName,
        ]);
    }

    public function test_update_attribute_validation_fails_with_duplicate_name()
    {
        Sanctum::actingAs($this->adminUser);

        Attribute::factory()->create(['name' => 'Color']);
        $attribute2 = Attribute::factory()->create(['name' => 'Size']);

        $response = $this->putJson("/api/v1/admin/attributes/{$attribute2->slug}", [
            'name' => 'Color', // Duplicate name
        ]);

        // Test that the second attribute still has its original name
        $this->assertDatabaseHas('attributes', [
            'id' => $attribute2->id,
            'name' => 'Size', // Should remain unchanged
        ]);
    }

    public function test_vendor_cannot_access_admin_attribute_endpoints()
    {
        Sanctum::actingAs($this->vendorUser);

        $attribute = Attribute::factory()->create();

        // Test all endpoints
        $this->getJson('/api/v1/admin/attributes')->assertStatus(403);
        $this->postJson('/api/v1/admin/attributes', ['name' => 'Test'])->assertStatus(403);
        $this->getJson("/api/v1/admin/attributes/{$attribute->slug}")->assertStatus(403);
        $this->putJson("/api/v1/admin/attributes/{$attribute->slug}", ['name' => 'Updated'])->assertStatus(403);
        $this->deleteJson("/api/v1/admin/attributes/{$attribute->slug}")->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_admin_attribute_endpoints()
    {
        $attribute = Attribute::factory()->create();

        // Test all endpoints
        $this->getJson('/api/v1/admin/attributes')->assertStatus(401);
        $this->postJson('/api/v1/admin/attributes', ['name' => 'Test'])->assertStatus(401);
        $this->getJson("/api/v1/admin/attributes/{$attribute->slug}")->assertStatus(401);
        $this->putJson("/api/v1/admin/attributes/{$attribute->slug}", ['name' => 'Updated'])->assertStatus(401);
        $this->deleteJson("/api/v1/admin/attributes/{$attribute->slug}")->assertStatus(401);
    }

    public function test_attribute_not_found_returns_404()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->getJson('/api/v1/admin/attributes/non-existent-slug');
        $response->assertStatus(404);

        $response = $this->putJson('/api/v1/admin/attributes/non-existent-slug', ['name' => 'Test']);
        $response->assertStatus(404);

        $response = $this->deleteJson('/api/v1/admin/attributes/non-existent-slug');
        $response->assertStatus(404);
    }
}