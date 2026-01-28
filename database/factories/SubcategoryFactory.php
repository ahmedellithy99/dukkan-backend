<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subcategory>
 */
class SubcategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->randomElement([
            'T-Shirt', 'Jacket', 'Jeans', 'Dress', 'Sneakers', 'Boots',
            'Bag', 'Watch', 'Sunglasses', 'Hat', 'Scarf', 'Belt',
        ]);

        return [
            'category_id' => \App\Models\Category::factory(),
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name) . '-' . $this->faker->unique()->randomNumber(5),
        ];
    }
}
