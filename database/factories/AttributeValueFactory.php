<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AttributeValue>
 */
class AttributeValueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $value = $this->faker->randomElement([
            'Small', 'Medium', 'Large', 'XL', 'Red', 'Blue', 'Green', 'Black', 'White',
            'Male', 'Female', 'Unisex', 'Cotton', 'Leather', 'Plastic', 'Metal',
        ]);

        return [
            'attribute_id' => \App\Models\Attribute::factory(),
            'slug' => \Illuminate\Support\Str::slug($value) . '-' . $this->faker->unique()->randomNumber(4),
            'value' => $value,
        ];
    }
}
