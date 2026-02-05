<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Governorate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\City>
 */
class CityFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = City::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->randomElement([
            'New Cairo',
            'Old Cairo',
            'Heliopolis',
            'Maadi',
            'Zamalek',
            'Downtown Cairo',
            'Nasr City',
            'Shubra',
            'Giza City',
            '6th of October City',
            'Sheikh Zayed',
            'Alexandria Center',
            'Montaza',
            'Sidi Gaber',
            'Smouha',
            'Agami',
            'Borg El Arab',
            'Port Said Center',
            'Port Fouad',
            'Suez Center',
            'Ismailia Center',
            'Luxor Center',
            'Aswan Center'
        ]);

        // Add unique suffix to ensure slug uniqueness across test runs
        $uniqueSuffix = $this->faker->unique()->numberBetween(1000, 9999);

        return [
            'governorate_id' => Governorate::factory(),
            'name' => $name,
            'slug' => Str::slug($name) . '-' . $uniqueSuffix,
        ];
    }
}