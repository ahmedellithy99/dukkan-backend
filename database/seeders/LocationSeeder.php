<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some cities
        $abo_hommos = City::where('slug', 'abo-hommos')->first();
        $alexandria = City::where('slug', 'alexandria')->first();

        $locations = [
            // Beheira locations
            ['city_id' => $abo_hommos?->id, 'latitude' => 31.1008,  'longitude' => 30.3159],
            
            // Alexandria locations
            ['city_id' => $alexandria?->id, 'latitude' => 31.2420, 'longitude' => 29.9617],
            ['city_id' => $alexandria?->id, 'latitude' => 31.2156, 'longitude' => 29.9467],
            ['city_id' => $alexandria?->id, 'latitude' => 31.2656, 'longitude' => 29.9869],
        ];

        foreach ($locations as $location) {
            if ($location['city_id']) {
                Location::create($location);
            }
        }
    }
}
