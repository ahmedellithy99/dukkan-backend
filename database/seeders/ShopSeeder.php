<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Seeder;

class ShopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vendors = User::where('role', 'vendor')->get();
        $locations = Location::all();

        if ($vendors->isEmpty() || $locations->isEmpty()) {
            $this->command->warn('No vendors or locations found. Skipping shop seeding.');
            return;
        }

        $shops = [
            [
                'name' => 'Fashion Hub',
                'description' => 'Your one-stop shop for trendy clothes and accessories',
                'whatsapp_number' => '+201001234567',
                'phone_number' => '+201001234567',
                'is_active' => true,
            ],
            [
                'name' => 'Tech Store',
                'description' => 'Latest electronics and gadgets at competitive prices',
                'whatsapp_number' => '+201002345678',
                'phone_number' => '+201002345678',
                'is_active' => true,
            ],
            [
                'name' => 'Shoe Palace',
                'description' => 'Premium footwear for all occasions',
                'whatsapp_number' => '+201003456789',
                'phone_number' => '+201003456789',
                'is_active' => true,
            ],
            [
                'name' => 'Home Essentials',
                'description' => 'Everything you need for your home',
                'whatsapp_number' => '+201004567890',
                'phone_number' => '+201004567890',
                'is_active' => true,
            ],
            [
                'name' => 'Sports Corner',
                'description' => 'Sports equipment and athletic wear',
                'whatsapp_number' => '+201005678901',
                'phone_number' => '+201005678901',
                'is_active' => true,
            ],
            [
                'name' => 'Beauty Boutique',
                'description' => 'Premium beauty and skincare products',
                'whatsapp_number' => '+201006789012',
                'phone_number' => '+201006789012',
                'is_active' => true,
            ],
            [
                'name' => 'Book Haven',
                'description' => 'Wide selection of books and magazines',
                'whatsapp_number' => '+201007890123',
                'phone_number' => '+201007890123',
                'is_active' => true,
            ],
            [
                'name' => 'Accessory World',
                'description' => 'Stylish accessories for every style',
                'whatsapp_number' => '+201008901234',
                'phone_number' => '+201008901234',
                'is_active' => true,
            ],
        ];

        $availableLocations = $locations->shuffle();
        $locationIndex = 0;

        foreach ($shops as $index => $shopData) {
            // Ensure we have enough locations
            if ($locationIndex >= $availableLocations->count()) {
                $this->command->warn('Not enough locations for all shops. Creating additional locations...');
                break;
            }

            Shop::create([
                'owner_id' => $vendors->random()->id,
                'location_id' => $availableLocations[$locationIndex]->id,
                'name' => $shopData['name'],
                'slug' => \Illuminate\Support\Str::slug($shopData['name']),
                'description' => $shopData['description'],
                'whatsapp_number' => $shopData['whatsapp_number'],
                'phone_number' => $shopData['phone_number'],
                'is_active' => $shopData['is_active'],
            ]);

            $locationIndex++;
        }

        // Create additional random shops in local/testing environments
        if (app()->environment(['local', 'testing'])) {
            Shop::factory()->count(12)->create();
        }
    }
}
