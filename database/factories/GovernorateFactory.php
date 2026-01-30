<?php

namespace Database\Factories;

use App\Models\Governorate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Governorate>
 */
class GovernorateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Governorate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->randomElement([
            'Cairo',
            'Alexandria',
            'Giza',
            'Qalyubia',
            'Port Said',
            'Suez',
            'Luxor',
            'Aswan',
            'Asyut',
            'Beheira',
            'Beni Suef',
            'Dakahlia',
            'Damietta',
            'Fayyum',
            'Gharbia',
            'Ismailia',
            'Kafr el-Sheikh',
            'Matrouh',
            'Minya',
            'Monufia',
            'New Valley',
            'North Sinai',
            'Qena',
            'Red Sea',
            'Sharqia',
            'Sohag',
            'South Sinai'
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}