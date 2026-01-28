<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shop>
 */
class ShopFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->company();

        return [
            'owner_id' => \App\Models\User::factory(),
            'location_id' => \App\Models\Location::factory(),
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name) . '-' . $this->faker->unique()->randomNumber(5),
            'description' => $this->faker->paragraph(),
            'whatsapp_number' => '+20'.$this->faker->numerify('##########'),
            'phone_number' => '+20'.$this->faker->numerify('##########'),
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
        ];
    }
}
