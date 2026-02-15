<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting database seeding...');

        // 1. Seed hierarchical location data first
        $this->command->info('📍 Seeding locations...');
        $this->call([
            GovernorateSeeder::class,
            CitySeeder::class,
            LocationSeeder::class,
        ]);

        // 2. Seed users (admin and vendors)
        $this->command->info('👥 Seeding users...');
        $this->call([
            AdminSeeder::class,
            VendorSeeder::class,
        ]);

        // 3. Seed categories and subcategories
        $this->command->info('📂 Seeding categories...');
        $this->call([
            CategorySeeder::class,
        ]);

        // 4. Seed attributes and attribute values
        $this->command->info('🏷️  Seeding attributes...');
        $this->call([
            AttributeSeeder::class,
        ]);

        // 5. Seed shops with products
        $this->command->info('🏪 Seeding shops and products...');
        $this->call([
            ShopSeeder::class,
        ]);

        $this->command->info('✅ Database seeding completed successfully!');
    }
}
