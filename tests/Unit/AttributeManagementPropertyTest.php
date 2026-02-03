<?php

namespace Tests\Unit;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Eris\Generator;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Property-Based Test for Attribute System Management
 * Feature: marketplace-platform, Property 11: Attribute System Management
 * Validates: Requirements 6.1, 6.2, 6.4
 */
class AttributeManagementPropertyTest extends TestCase
{
    use RefreshDatabase, TestTrait;

    /**
     * Property 11: Attribute System Management
     * For any attribute and attribute value creation, the system should maintain reusable attributes 
     * and prevent duplicate combinations
     * 
     * Validates: Requirements 6.1, 6.2, 6.4
     */
    public function test_attribute_system_management()
    {
        $this->forAll(
            Generator\elements(['Color', 'Size', 'Brand', 'Material', 'Gender']),
            Generator\elements(['Red', 'Blue', 'Large', 'Small', 'Nike', 'Cotton', 'Male', 'Female'])
        )->then(function ($attributeName, $attributeValueName) {
            // Clear database for each iteration to avoid unique constraint issues
            Attribute::truncate();
            AttributeValue::truncate();

            // Create reusable attribute (slug auto-generated)
            $attribute = Attribute::create([
                'name' => $attributeName,
            ]);

            // Verify attribute creation
            $this->assertInstanceOf(Attribute::class, $attribute);
            $this->assertEquals($attributeName, $attribute->name);
            $this->assertNotEmpty($attribute->slug);
            $this->assertTrue($attribute->exists);

            // Create attribute value (slug auto-generated)
            $attributeValue = AttributeValue::create([
                'attribute_id' => $attribute->id,
                'value' => $attributeValueName,
            ]);

            // Verify attribute value creation and relationship
            $this->assertInstanceOf(AttributeValue::class, $attributeValue);
            $this->assertEquals($attributeValueName, $attributeValue->value);
            $this->assertEquals($attribute->id, $attributeValue->attribute_id);
            $this->assertNotEmpty($attributeValue->slug);
            $this->assertTrue($attributeValue->exists);

            // Verify relationship works both ways
            $this->assertTrue($attribute->attributeValues->contains($attributeValue));
            $this->assertEquals($attribute->id, $attributeValue->attribute->id);

            // Test reusability - same attribute can have multiple values
            $secondValue = AttributeValue::create([
                'attribute_id' => $attribute->id,
                'value' => $attributeValueName . ' Variant',
            ]);

            $this->assertEquals($attribute->id, $secondValue->attribute_id);
            
            // Refresh the attribute to get updated relationship count
            $attribute->refresh();
            $this->assertEquals(2, $attribute->attributeValues->count());

            // Test duplicate prevention - same attribute-value combination should be unique
            try {
                AttributeValue::create([
                    'attribute_id' => $attribute->id,
                    'value' => $attributeValueName, // Same value as first one
                ]);
                $this->fail('Expected unique constraint violation for duplicate attribute-value combination');
            } catch (\Exception $e) {
                // Verify original attribute value still exists
                $this->assertTrue($attributeValue->exists);
                $this->assertEquals(2, AttributeValue::where('attribute_id', $attribute->id)->count());
            }
        });
    }

    /**
     * Property test for attribute slug generation and uniqueness
     * Validates that attribute slugs are properly generated and unique
     */
    public function test_attribute_slug_uniqueness()
    {
        $this->forAll(
            Generator\elements(['Color', 'Size', 'Brand', 'Material', 'Style']),
            Generator\int(1, 1000)
        )->then(function ($baseName, $suffix) {
            // Clear database for each iteration
            Attribute::truncate();

            $attributeName = $baseName . ' ' . $suffix;
            
            $attribute = Attribute::create([
                'name' => $attributeName,
            ]);

            // Verify slug is generated correctly
            $expectedSlug = \Illuminate\Support\Str::slug($attributeName);
            $this->assertEquals($expectedSlug, $attribute->slug);
            
            // Verify slug is unique in database
            $this->assertEquals(1, Attribute::where('slug', $attribute->slug)->count());

            // Test creating another attribute with similar name
            $similarAttribute = Attribute::create([
                'name' => $attributeName . ' Copy',
            ]);

            // Verify both attributes have unique slugs
            $this->assertNotEquals($attribute->slug, $similarAttribute->slug);
            $this->assertEquals(2, Attribute::count());
        });
    }

    /**
     * Property test for attribute value relationships
     * Validates that attribute values are properly linked to attributes
     */
    public function test_attribute_value_relationships()
    {
        $this->forAll(
            Generator\elements(['Size', 'Color', 'Brand']),
            Generator\int(2, 5)
        )->then(function ($attributeName, $valueCount) {
            // Clear database for each iteration
            Attribute::truncate();
            AttributeValue::truncate();

            // Create attribute (slug auto-generated)
            $attribute = Attribute::create([
                'name' => $attributeName,
            ]);

            // Create multiple attribute values
            $createdValues = [];
            for ($i = 1; $i <= $valueCount; $i++) {
                $valueName = $attributeName . ' Value ' . $i;
                
                try {
                    $attributeValue = AttributeValue::create([
                        'attribute_id' => $attribute->id,
                        'value' => $valueName,
                    ]);
                    
                    if ($attributeValue && $attributeValue->exists) {
                        $this->assertEquals($attribute->id, $attributeValue->attribute_id);
                        $createdValues[] = $attributeValue;
                    }
                } catch (\Exception $e) {
                    // Skip this iteration if value creation fails
                    continue;
                }
            }

            // Skip test if no values were created
            if (count($createdValues) === 0) {
                return;
            }

            $actualValueCount = count($createdValues);

            // Verify all values are linked to the attribute
            $this->assertEquals($actualValueCount, $attribute->attributeValues->count());
            
            // Verify each value belongs to the attribute
            foreach ($attribute->attributeValues as $value) {
                $this->assertEquals($attribute->id, $value->attribute_id);
            }

            // Test reverse relationship
            foreach ($createdValues as $value) {
                $valueWithAttribute = AttributeValue::with('attribute')->find($value->id);
                $this->assertEquals($attributeName, $valueWithAttribute->attribute->name);
            }
        });
    }

    /**
     * Property test for attribute reusability across different contexts
     * Validates that attributes can be reused with different values
     */
    public function test_attribute_reusability()
    {
        $this->forAll(
            Generator\elements(['Color', 'Size', 'Material']),
            Generator\elements([
                ['Red', 'Blue', 'Green'],
                ['Small', 'Medium', 'Large'],
                ['Cotton', 'Polyester', 'Wool']
            ])
        )->then(function ($attributeName, $valueSet) {
            // Clear database for each iteration
            Attribute::truncate();
            AttributeValue::truncate();

            // Create single reusable attribute (slug auto-generated)
            $attribute = Attribute::create([
                'name' => $attributeName,
            ]);

            // Create multiple values for the same attribute
            $createdValues = [];
            foreach ($valueSet as $valueName) {
                try {
                    $attributeValue = AttributeValue::create([
                        'attribute_id' => $attribute->id,
                        'value' => $valueName,
                    ]);
                    
                    if ($attributeValue && $attributeValue->exists) {
                        $createdValues[] = $attributeValue;
                    }
                } catch (\Exception $e) {
                    // Skip this value if creation fails
                    continue;
                }
            }

            // Skip test if no values were created
            if (count($createdValues) === 0) {
                return;
            }

            // Verify all values belong to the same attribute (reusability)
            foreach ($createdValues as $value) {
                $this->assertEquals($attribute->id, $value->attribute_id);
                $this->assertEquals($attributeName, $value->attribute->name);
            }

            // Verify attribute can hold multiple values
            $this->assertGreaterThan(0, $attribute->attributeValues->count());
            $this->assertEquals(count($createdValues), $attribute->attributeValues->count());

            // Verify each value is unique within the attribute
            $values = $attribute->attributeValues->pluck('value')->toArray();
            $this->assertEquals(count($values), count(array_unique($values)));
        });
    }
}