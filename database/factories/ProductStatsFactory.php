<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductStats>
 */
class ProductStatsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => \App\Models\Product::factory(),
            'views_count' => $this->faker->numberBetween(0, 1000),
            'whatsapp_clicks' => $this->faker->numberBetween(0, 100),
            'favorites_count' => $this->faker->numberBetween(0, 50),
            'last_viewed_at' => $this->faker->optional(0.7)->dateTimeBetween('-1 month', 'now'),
            'updated_at' => now(),
        ];
    }
}
