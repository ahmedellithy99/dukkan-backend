<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Governorate;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get governorates
        $beheira = Governorate::where('slug', 'beheira')->first();
        $alexandria = Governorate::where('slug', 'alexandria')->first();

        $cities = [
            // Cairo Governorate
            ['governorate_id' => $beheira->id, 'name' => 'Abo-Hommos', 'slug' => 'abo-hommos'],

            ['governorate_id' => $alexandria->id, 'name' => 'Alexandria', 'slug' => 'alexandria'],
            ['governorate_id' => $alexandria->id, 'name' => 'Montaza', 'slug' => 'montaza'],
            ['governorate_id' => $alexandria->id, 'name' => 'Raml Station', 'slug' => 'raml-station'],
            ['governorate_id' => $alexandria->id, 'name' => 'Sidi Gaber', 'slug' => 'sidi-gaber'],

        ];

        foreach ($cities as $city) {
            City::create($city);
        }
    }
}
