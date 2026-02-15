<?php

namespace Database\Seeders;

use App\Models\AttributeValue;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductStats;
use App\Models\Shop;
use App\Models\Subcategory;
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
        $subcategories = Subcategory::all();

        if ($vendors->isEmpty()) {
            $this->command->warn('No vendors found. Skipping shop seeding.');
            return;
        }

        if ($subcategories->isEmpty()) {
            $this->command->warn('No subcategories found. Skipping shop seeding.');
            return;
        }

        // Get attribute values for products
        $sizes = AttributeValue::whereHas('attribute', fn($q) => $q->where('name', 'Size'))->pluck('id');
        $colors = AttributeValue::whereHas('attribute', fn($q) => $q->where('name', 'Color'))->pluck('id');
        $genders = AttributeValue::whereHas('attribute', fn($q) => $q->where('name', 'Gender'))->pluck('id');
        $materials = AttributeValue::whereHas('attribute', fn($q) => $q->where('name', 'Material'))->pluck('id');
        $conditions = AttributeValue::whereHas('attribute', fn($q) => $q->where('name', 'Condition'))->pluck('id');

        $shops = [
            [
                'name' => 'Fashion Hub',
                'description' => 'Your one-stop shop for trendy clothes and accessories',
                'whatsapp_number' => '+201001234567',
                'phone_number' => '+201001234567',
                'is_active' => true,
                'products_count' => 25,
            ],
            [
                'name' => 'Shoe Palace',
                'description' => 'Premium footwear for all occasions',
                'whatsapp_number' => '+201003456789',
                'phone_number' => '+201003456789',
                'is_active' => true,
                'products_count' => 20,
            ],
            [
                'name' => 'Sports Corner',
                'description' => 'Sports equipment and athletic wear',
                'whatsapp_number' => '+201005678901',
                'phone_number' => '+201005678901',
                'is_active' => true,
                'products_count' => 30,
            ],
        ];

        foreach ($shops as $shopData) {
            $shop = Shop::create([
                'owner_id' => $vendors->random()->id,
                'location_id' => Location::factory()->create(['city_id' => 1])->id,
                'name' => $shopData['name'],
                'slug' => \Illuminate\Support\Str::slug($shopData['name']),
                'description' => $shopData['description'],
                'whatsapp_number' => $shopData['whatsapp_number'],
                'phone_number' => $shopData['phone_number'],
                'is_active' => $shopData['is_active'],
            ]);

            $this->command->info("Creating {$shopData['products_count']} products for {$shop->name}...");

            // Create products for this shop
            for ($i = 1; $i <= $shopData['products_count']; $i++) {
                $price = rand(50, 2000);
                $hasDiscount = rand(1, 100) <= 40; // 40% chance of discount
                $productName = fake()->words(rand(2, 4), true) . ' ' . $shop->name . ' ' . $i;
                
                $product = Product::create([
                    'shop_id' => $shop->id,
                    'subcategory_id' => $subcategories->random()->id,
                    'name' => $productName,
                    'slug' => \Illuminate\Support\Str::slug($productName) . '-' . uniqid(),
                    'description' => fake()->sentence(rand(10, 20)),
                    'price' => $price,
                    'stock_quantity' => rand(0, 100),
                    'is_active' => rand(1, 100) <= 90, // 90% active
                    'discount_type' => $hasDiscount ? (rand(0, 1) ? 'percent' : 'amount') : null,
                    'discount_value' => $hasDiscount ? ($hasDiscount && rand(0, 1) ? rand(5, 50) : rand(10, 200)) : null,
                ]);

                // Attach random attributes
                $attributeIds = collect([
                    $sizes->isNotEmpty() ? $sizes->random() : null,
                    $colors->isNotEmpty() ? $colors->random() : null,
                    $genders->isNotEmpty() && rand(0, 1) ? $genders->random() : null,
                    $materials->isNotEmpty() && rand(0, 1) ? $materials->random() : null,
                    $conditions->isNotEmpty() && rand(0, 1) ? $conditions->random() : null,
                ])->filter()->unique()->values();

                if ($attributeIds->isNotEmpty()) {
                    $product->attributeValues()->attach($attributeIds);
                }

                // Create product stats
                ProductStats::create([
                    'product_id' => $product->id,
                    'views_count' => rand(0, 500),
                    'whatsapp_clicks' => rand(0, 50),
                    'favorites_count' => rand(0, 100),
                    'last_viewed_at' => rand(0, 1) ? now()->subDays(rand(0, 30)) : null,
                ]);
            }
        }

        $this->command->info('✅ Shops and products created successfully!');
    }
}
