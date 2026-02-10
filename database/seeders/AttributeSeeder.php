<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attributes = [
            'Size' => ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL'],
            'Color' => ['Red', 'Blue', 'Green', 'Black', 'White', 'Yellow', 'Orange', 'Purple', 'Pink', 'Brown', 'Gray', 'Beige'],
            'Gender' => ['Male', 'Female', 'Unisex'],
            'Material' => ['Cotton', 'Polyester', 'Leather', 'Wool', 'Silk', 'Denim', 'Plastic', 'Metal', 'Wood', 'Glass'],
            'Brand' => ['Nike', 'Adidas', 'Puma', 'Zara', 'H&M', 'Gucci', 'Samsung', 'Apple', 'Sony', 'LG', 'Generic'],
            'Condition' => ['New', 'Like New', 'Good', 'Fair', 'Used'],
            'Style' => ['Casual', 'Formal', 'Sport', 'Vintage', 'Modern', 'Classic'],
        ];

        foreach ($attributes as $attributeName => $values) {
            $attribute = new Attribute();
            $attribute->name = $attributeName;
            $attribute->slug = \Illuminate\Support\Str::slug($attributeName);
            $attribute->save();

            foreach ($values as $value) {
                $attributeValue = new AttributeValue();
                $attributeValue->attribute_id = $attribute->id;
                $attributeValue->value = $value;
                $attributeValue->slug = \Illuminate\Support\Str::slug($value);
                $attributeValue->save();
            }
        }
    }
}
