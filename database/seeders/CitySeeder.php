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
        $cairo = Governorate::where('slug', 'cairo')->first();
        $giza = Governorate::where('slug', 'giza')->first();
        $alexandria = Governorate::where('slug', 'alexandria')->first();
        $qalyubia = Governorate::where('slug', 'qalyubia')->first();
        $sharqia = Governorate::where('slug', 'sharqia')->first();

        $cities = [
            // Cairo Governorate
            ['governorate_id' => $cairo->id, 'name' => 'Cairo', 'slug' => 'cairo'],
            ['governorate_id' => $cairo->id, 'name' => 'New Cairo', 'slug' => 'new-cairo'],
            ['governorate_id' => $cairo->id, 'name' => 'Heliopolis', 'slug' => 'heliopolis'],
            ['governorate_id' => $cairo->id, 'name' => 'Nasr City', 'slug' => 'nasr-city'],
            ['governorate_id' => $cairo->id, 'name' => 'Maadi', 'slug' => 'maadi'],
            ['governorate_id' => $cairo->id, 'name' => 'Zamalek', 'slug' => 'zamalek'],
            ['governorate_id' => $cairo->id, 'name' => 'Downtown', 'slug' => 'downtown'],

            // Giza Governorate
            ['governorate_id' => $giza->id, 'name' => 'Giza', 'slug' => 'giza'],
            ['governorate_id' => $giza->id, 'name' => 'Dokki', 'slug' => 'dokki'],
            ['governorate_id' => $giza->id, 'name' => 'Mohandessin', 'slug' => 'mohandessin'],
            ['governorate_id' => $giza->id, 'name' => '6th of October', 'slug' => '6th-of-october'],
            ['governorate_id' => $giza->id, 'name' => 'Sheikh Zayed', 'slug' => 'sheikh-zayed'],

            // Alexandria Governorate
            ['governorate_id' => $alexandria->id, 'name' => 'Alexandria', 'slug' => 'alexandria'],
            ['governorate_id' => $alexandria->id, 'name' => 'Montaza', 'slug' => 'montaza'],
            ['governorate_id' => $alexandria->id, 'name' => 'Raml Station', 'slug' => 'raml-station'],
            ['governorate_id' => $alexandria->id, 'name' => 'Sidi Gaber', 'slug' => 'sidi-gaber'],

            // Qalyubia Governorate
            ['governorate_id' => $qalyubia->id, 'name' => 'Benha', 'slug' => 'benha'],
            ['governorate_id' => $qalyubia->id, 'name' => 'Shubra El Kheima', 'slug' => 'shubra-el-kheima'],
            ['governorate_id' => $qalyubia->id, 'name' => 'Qaha', 'slug' => 'qaha'],

            // Sharqia Governorate
            ['governorate_id' => $sharqia->id, 'name' => 'Zagazig', 'slug' => 'zagazig'],
            ['governorate_id' => $sharqia->id, 'name' => 'Bilbeis', 'slug' => 'bilbeis'],
            ['governorate_id' => $sharqia->id, 'name' => '10th of Ramadan', 'slug' => '10th-of-ramadan'],
        ];

        foreach ($cities as $city) {
            City::create($city);
        }
    }
}
