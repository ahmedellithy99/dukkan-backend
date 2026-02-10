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
        $cairo = City::where('slug', 'cairo')->first();
        $newCairo = City::where('slug', 'new-cairo')->first();
        $nasr = City::where('slug', 'nasr-city')->first();
        $maadi = City::where('slug', 'maadi')->first();
        $giza = City::where('slug', 'giza')->first();
        $mohandessin = City::where('slug', 'mohandessin')->first();
        $alexandria = City::where('slug', 'alexandria')->first();

        $locations = [
            // Cairo locations
            ['city_id' => $cairo?->id, 'area' => 'Downtown', 'latitude' => 30.0444, 'longitude' => 31.2357],
            ['city_id' => $cairo?->id, 'area' => 'Zamalek', 'latitude' => 30.0626, 'longitude' => 31.2197],
            ['city_id' => $newCairo?->id, 'area' => 'Fifth Settlement', 'latitude' => 30.0272, 'longitude' => 31.4913],
            ['city_id' => $newCairo?->id, 'area' => 'First Settlement', 'latitude' => 30.0330, 'longitude' => 31.4765],
            ['city_id' => $nasr?->id, 'area' => 'Nasr City Center', 'latitude' => 30.0594, 'longitude' => 31.3381],
            ['city_id' => $nasr?->id, 'area' => 'Abbas El Akkad', 'latitude' => 30.0561, 'longitude' => 31.3373],
            ['city_id' => $maadi?->id, 'area' => 'Maadi Degla', 'latitude' => 29.9602, 'longitude' => 31.3214],
            ['city_id' => $maadi?->id, 'area' => 'Maadi Sarayat', 'latitude' => 29.9597, 'longitude' => 31.2564],
            
            // Giza locations
            ['city_id' => $giza?->id, 'area' => 'Pyramids', 'latitude' => 29.9792, 'longitude' => 31.1342],
            ['city_id' => $giza?->id, 'area' => 'Dokki', 'latitude' => 30.0382, 'longitude' => 31.2125],
            ['city_id' => $mohandessin?->id, 'area' => 'Mohandessin Center', 'latitude' => 30.0618, 'longitude' => 31.2001],
            ['city_id' => $mohandessin?->id, 'area' => 'Arab League', 'latitude' => 30.0644, 'longitude' => 31.2089],
            
            // Alexandria locations
            ['city_id' => $alexandria?->id, 'area' => 'Stanley', 'latitude' => 31.2420, 'longitude' => 29.9617],
            ['city_id' => $alexandria?->id, 'area' => 'Smouha', 'latitude' => 31.2156, 'longitude' => 29.9467],
            ['city_id' => $alexandria?->id, 'area' => 'Miami', 'latitude' => 31.2656, 'longitude' => 29.9869],
        ];

        foreach ($locations as $location) {
            if ($location['city_id']) {
                Location::create($location);
            }
        }
    }
}
