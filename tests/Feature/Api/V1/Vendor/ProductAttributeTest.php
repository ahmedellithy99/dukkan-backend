<?php

namespace Tests\Feature\Api\V1\Vendor;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductAttributeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test vendor can create product with attribute values
     */
    public function test_vendor_can_create_product_with_attribute_values()
    {
        // Create vendor with shop
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = Shop::factory()->create(['owner_id' => $vendor->id]);

        // Create category and subcategory
        $category = Category::factory()->create();
        $subcategory = Subcategory::factory()->create(['category_id' => $category->id]);

        // Create attributes and attribute values
        $colorAttribute = Attribute::factory()->create(['name' => 'Color']);
        $sizeAttribute = Attribute::factory()->create(['name' => 'Size']);

        $redValue = AttributeValue::factory()->create([
            'attribute_id' => $colorAttribute->id,
            'value' => 'Red',
        ]);
        $largeValue = AttributeValue::factory()->create([
            'attribute_id' => $sizeAttribute->id,
            'value' => 'Large',
        ]);

        // Create product with attribute values
        $response = $this->actingAs($vendor, 'sanctum')
            ->postJson("/api/v1/vendor/my-shop/{$shop->slug}/products", [
                'subcategory_id' => $subcategory->id,
                'name' => 'Red Large T-Shirt',
                'description' => 'A beautiful red t-shirt',
                'price' => 29.99,
                'stock_quantity' => 100,
                'attribute_values' => [$redValue->id, $largeValue->id],
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'price',
                    'attribute_values',
                ],
            ]);

        // Verify attribute values are attached
        $product = Product::find($response->json('data.id'));
        $this->assertCount(2, $product->attributeValues);
        $this->assertTrue($product->attributeValues->contains('id', $redValue->id));
        $this->assertTrue($product->attributeValues->contains('id', $largeValue->id));
    }

    /**
     * Test vendor can create product without attribute values
     */
    public function test_vendor_can_create_product_without_attribute_values()
    {
        // Create vendor with shop
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = Shop::factory()->create(['owner_id' => $vendor->id]);

        // Create category and subcategory
        $category = Category::factory()->create();
        $subcategory = Subcategory::factory()->create(['category_id' => $category->id]);

        // Create product without attribute values
        $response = $this->actingAs($vendor, 'sanctum')
            ->postJson("/api/v1/vendor/my-shop/{$shop->slug}/products", [
                'subcategory_id' => $subcategory->id,
                'name' => 'Simple Product',
                'description' => 'A simple product',
                'price' => 19.99,
                'stock_quantity' => 50,
            ]);

        $response->assertStatus(201);

        // Verify no attribute values are attached
        $product = Product::find($response->json('data.id'));
        $this->assertCount(0, $product->attributeValues);
    }

    /**
     * Test vendor can update product attribute values
     */
    public function test_vendor_can_update_product_attribute_values()
    {
        // Create vendor with shop and product
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = Shop::factory()->create(['owner_id' => $vendor->id]);
        $product = Product::factory()->create(['shop_id' => $shop->id]);

        // Create attributes and attribute values
        $colorAttribute = Attribute::factory()->create(['name' => 'Color']);
        $sizeAttribute = Attribute::factory()->create(['name' => 'Size']);

        $redValue = AttributeValue::factory()->create([
            'attribute_id' => $colorAttribute->id,
            'value' => 'Red',
        ]);
        $blueValue = AttributeValue::factory()->create([
            'attribute_id' => $colorAttribute->id,
            'value' => 'Blue',
        ]);
        $mediumValue = AttributeValue::factory()->create([
            'attribute_id' => $sizeAttribute->id,
            'value' => 'Medium',
        ]);

        // Attach initial attribute values
        $product->attributeValues()->attach([$redValue->id]);

        // Update product with new attribute values
        $response = $this->actingAs($vendor, 'sanctum')
            ->putJson("/api/v1/vendor/my-shop/{$shop->slug}/products/{$product->slug}", [
                'attribute_values' => [$blueValue->id, $mediumValue->id],
            ]);

        $response->assertStatus(200);

        // Verify attribute values are updated
        $product->refresh();
        $this->assertCount(2, $product->attributeValues);
        $this->assertFalse($product->attributeValues->contains('id', $redValue->id));
        $this->assertTrue($product->attributeValues->contains('id', $blueValue->id));
        $this->assertTrue($product->attributeValues->contains('id', $mediumValue->id));
    }

    /**
     * Test vendor can remove all attribute values from product
     */
    public function test_vendor_can_remove_all_attribute_values_from_product()
    {
        // Create vendor with shop and product
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = Shop::factory()->create(['owner_id' => $vendor->id]);
        $product = Product::factory()->create(['shop_id' => $shop->id]);

        // Create and attach attribute values
        $colorAttribute = Attribute::factory()->create(['name' => 'Color']);
        $redValue = AttributeValue::factory()->create([
            'attribute_id' => $colorAttribute->id,
            'value' => 'Red',
        ]);
        $product->attributeValues()->attach([$redValue->id]);

        // Update product with empty attribute values array
        $response = $this->actingAs($vendor, 'sanctum')
            ->putJson("/api/v1/vendor/my-shop/{$shop->slug}/products/{$product->slug}", [
                'attribute_values' => [],
            ]);

        $response->assertStatus(200);

        // Verify all attribute values are removed
        $product->refresh();
        $this->assertCount(0, $product->attributeValues);
    }

    /**
     * Test validation fails for invalid attribute value IDs
     */
    public function test_validation_fails_for_invalid_attribute_value_ids()
    {
        // Create vendor with shop
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = Shop::factory()->create(['owner_id' => $vendor->id]);

        // Create category and subcategory
        $category = Category::factory()->create();
        $subcategory = Subcategory::factory()->create(['category_id' => $category->id]);

        // Try to create product with non-existent attribute value IDs
        $response = $this->actingAs($vendor, 'sanctum')
            ->postJson("/api/v1/vendor/my-shop/{$shop->slug}/products", [
                'subcategory_id' => $subcategory->id,
                'name' => 'Test Product',
                'price' => 29.99,
                'stock_quantity' => 100,
                'attribute_values' => [99999, 88888], // Non-existent IDs
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_FAILED',
                ],
            ])
            ->assertJsonStructure([
                'error' => [
                    'fields' => [
                        'attribute_values.0',
                        'attribute_values.1',
                    ],
                ],
            ]);
    }

    /**
     * Test product response includes attribute values with attributes
     */
    public function test_product_response_includes_attribute_values_with_attributes()
    {
        // Create vendor with shop and product
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = Shop::factory()->create(['owner_id' => $vendor->id]);
        $product = Product::factory()->create(['shop_id' => $shop->id]);

        // Create attributes and attribute values
        $colorAttribute = Attribute::factory()->create(['name' => 'Color']);
        $sizeAttribute = Attribute::factory()->create(['name' => 'Size']);

        $redValue = AttributeValue::factory()->create([
            'attribute_id' => $colorAttribute->id,
            'value' => 'Red',
        ]);
        $largeValue = AttributeValue::factory()->create([
            'attribute_id' => $sizeAttribute->id,
            'value' => 'Large',
        ]);

        // Attach attribute values
        $product->attributeValues()->attach([$redValue->id, $largeValue->id]);

        // Get product
        $response = $this->actingAs($vendor, 'sanctum')
            ->getJson("/api/v1/vendor/my-shop/{$shop->slug}/products/{$product->slug}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'attribute_values' => [
                        '*' => [
                            'id',
                            'value',
                            'attribute' => [
                                'id',
                                'name',
                            ],
                        ],
                    ],
                ],
            ]);

        // Verify attribute values are in response
        $attributeValues = $response->json('data.attribute_values');
        $this->assertCount(2, $attributeValues);
    }

    /**
     * Test vendor can update product without changing attribute values
     */
    public function test_vendor_can_update_product_without_changing_attribute_values()
    {
        // Create vendor with shop and product
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = Shop::factory()->create(['owner_id' => $vendor->id]);
        $product = Product::factory()->create(['shop_id' => $shop->id]);

        // Create and attach attribute values
        $colorAttribute = Attribute::factory()->create(['name' => 'Color']);
        $redValue = AttributeValue::factory()->create([
            'attribute_id' => $colorAttribute->id,
            'value' => 'Red',
        ]);
        $product->attributeValues()->attach([$redValue->id]);

        // Update product name without touching attribute values
        $response = $this->actingAs($vendor, 'sanctum')
            ->putJson("/api/v1/vendor/my-shop/{$shop->slug}/products/{$product->slug}", [
                'name' => 'Updated Product Name',
            ]);

        $response->assertStatus(200);

        // Verify attribute values remain unchanged
        $product->refresh();
        $this->assertCount(1, $product->attributeValues);
        $this->assertTrue($product->attributeValues->contains('id', $redValue->id));
        $this->assertEquals('Updated Product Name', $product->name);
    }
}
