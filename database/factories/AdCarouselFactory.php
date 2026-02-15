<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AdCarousel>
 */
class AdCarouselFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'link_url' => $this->faker->optional(0.7)->url(), // 70% chance of having a URL
            'display_order' => $this->faker->numberBetween(0, 100),
        ];
    }
}
