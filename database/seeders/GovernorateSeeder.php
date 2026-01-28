<?php

namespace Database\Seeders;

use App\Models\Governorate;
use Illuminate\Database\Seeder;

class GovernorateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $governorates = [
            ['name' => 'Cairo', 'slug' => 'cairo'],
            ['name' => 'Giza', 'slug' => 'giza'],
            ['name' => 'Alexandria', 'slug' => 'alexandria'],
            ['name' => 'Qalyubia', 'slug' => 'qalyubia'],
            ['name' => 'Sharqia', 'slug' => 'sharqia'],
            ['name' => 'Dakahlia', 'slug' => 'dakahlia'],
            ['name' => 'Beheira', 'slug' => 'beheira'],
            ['name' => 'Kafr El Sheikh', 'slug' => 'kafr-el-sheikh'],
            ['name' => 'Gharbia', 'slug' => 'gharbia'],
            ['name' => 'Monufia', 'slug' => 'monufia'],
            ['name' => 'Damietta', 'slug' => 'damietta'],
            ['name' => 'Port Said', 'slug' => 'port-said'],
            ['name' => 'Ismailia', 'slug' => 'ismailia'],
            ['name' => 'Suez', 'slug' => 'suez'],
            ['name' => 'North Sinai', 'slug' => 'north-sinai'],
            ['name' => 'South Sinai', 'slug' => 'south-sinai'],
            ['name' => 'Red Sea', 'slug' => 'red-sea'],
            ['name' => 'Luxor', 'slug' => 'luxor'],
            ['name' => 'Aswan', 'slug' => 'aswan'],
            ['name' => 'Qena', 'slug' => 'qena'],
            ['name' => 'Sohag', 'slug' => 'sohag'],
            ['name' => 'Asyut', 'slug' => 'asyut'],
            ['name' => 'Minya', 'slug' => 'minya'],
            ['name' => 'Beni Suef', 'slug' => 'beni-suef'],
            ['name' => 'Fayoum', 'slug' => 'fayoum'],
            ['name' => 'New Valley', 'slug' => 'new-valley'],
            ['name' => 'Matrouh', 'slug' => 'matrouh'],
        ];

        foreach ($governorates as $governorate) {
            Governorate::create($governorate);
        }
    }
}
