<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Media>
 */
class MediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'model_type' => $this->faker->randomElement(['App\\Models\\Shop', 'App\\Models\\Product']),
            'model_id' => $this->faker->numberBetween(1, 100),
            'type' => $this->faker->randomElement(['logo', 'banner', 'product_image']),
            'path' => 'images/'.$this->faker->uuid().'.jpg',
            'alt_text' => $this->faker->sentence(),
            'display_order' => $this->faker->numberBetween(0, 10),
        ];
    }
}
