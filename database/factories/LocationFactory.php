<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Governorate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Location>
 */
class LocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Get or create a random city
        $city = City::inRandomOrder()->first();

        if (!$city) {
            // If no cities exist, create a governorate and city
            $governorate = Governorate::firstOrCreate([
                'slug' => 'cairo'
            ], [
                'name' => 'Cairo',
                'slug' => 'cairo',
            ]);

            $city = City::firstOrCreate([
                'governorate_id' => $governorate->id,
                'slug' => 'cairo-city'
            ], [
                'governorate_id' => $governorate->id,
                'name' => 'Cairo',
                'slug' => 'cairo-city',
            ]);
        }

        return [
            'city_id' => $city->id,
            'area' => $this->faker->optional(0.7)->streetName(), // 70% chance of having an area
            'latitude' => $this->faker->latitude(29.0, 31.5), // Egypt latitude range
            'longitude' => $this->faker->longitude(25.0, 35.0), // Egypt longitude range
        ];
    }

    /**
     * Create a city-only location (for subdomain-based cities).
     */
    public function cityOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'area' => null,
        ]);
    }

    /**
     * Create a location with area.
     */
    public function withArea(): static
    {
        return $this->state(fn (array $attributes) => [
            'area' => $this->faker->streetName(),
        ]);
    }

    /**
     * Create a location in a specific city.
     */
    public function inCity(City $city): static
    {
        return $this->state(fn (array $attributes) => [
            'city_id' => $city->id,
        ]);
    }
}
