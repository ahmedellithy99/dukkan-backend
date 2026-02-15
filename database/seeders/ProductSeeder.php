<?php

namespace Database\Seeders;

use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductStats;
use App\Models\Shop;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shops = Shop::all();
        $subcategories = Subcategory::all();

        if ($shops->isEmpty() || $subcategories->isEmpty()) {
            $this->command->warn('No shops or subcategories found. Skipping product seeding.');
            return;
        }

        // Get attribute values for assignment
        $sizes = AttributeValue::whereHas('attribute', fn($q) => $q->where('name', 'Size'))->pluck('id');
        $colors = AttributeValue::whereHas('attribute', fn($q) => $q->where('name', 'Color'))->pluck('id');
        $genders = AttributeValue::whereHas('attribute', fn($q) => $q->where('name', 'Gender'))->pluck('id');
        $materials = AttributeValue::whereHas('attribute', fn($q) => $q->where('name', 'Material'))->pluck('id');
        $conditions = AttributeValue::whereHas('attribute', fn($q) => $q->where('name', 'Condition'))->pluck('id');

        $products = [
            [
                'name' => 'Classic White T-Shirt',
                'description' => 'Comfortable cotton t-shirt perfect for everyday wear',
                'price' => 150.00,
                'is_active' => true,
                'attributes' => [$sizes->random(), $colors->random(), $genders->random(), $materials->random()],
            ],
            [
                'name' => 'Denim Jeans',
                'description' => 'Stylish blue jeans with modern fit',
                'price' => 450.00,
                'is_active' => true,
                'attributes' => [$sizes->random(), $colors->random(), $genders->random()],
            ],
            [
                'name' => 'Running Sneakers',
                'description' => 'Lightweight sneakers for running and sports',
                'price' => 800.00,
                'is_active' => true,
                'attributes' => [$sizes->random(), $colors->random()],
            ],
            [
                'name' => 'Leather Jacket',
                'description' => 'Premium leather jacket for cold weather',
                'price' => 1200.00,
                'is_active' => true,
                'attributes' => [$sizes->random(), $colors->random(), $materials->random()],
            ],
            [
                'name' => 'Summer Dress',
                'description' => 'Light and breezy dress for summer days',
                'price' => 350.00,
                'is_active' => true,
                'attributes' => [$sizes->random(), $colors->random(), $genders->random()],
            ],
            [
                'name' => 'Wireless Headphones',
                'description' => 'High-quality wireless headphones with noise cancellation',
                'price' => 600.00,
                'is_active' => true,
                'attributes' => [$colors->random(), $conditions->random()],
            ],
            [
                'name' => 'Smart Watch',
                'description' => 'Feature-rich smartwatch with fitness tracking',
                'price' => 1500.00,
                'is_active' => true,
                'attributes' => [$colors->random(), $conditions->random()],
            ],
            [
                'name' => 'Backpack',
                'description' => 'Spacious backpack for daily use',
                'price' => 250.00,
                'is_active' => true,
                'attributes' => [$colors->random(), $materials->random()],
            ],
            [
                'name' => 'Sunglasses',
                'description' => 'UV protection sunglasses with stylish design',
                'price' => 200.00,
                'is_active' => true,
                'attributes' => [$colors->random(), $genders->random()],
            ],
            [
                'name' => 'Winter Boots',
                'description' => 'Warm and comfortable boots for winter',
                'price' => 700.00,
                'is_active' => true,
                'attributes' => [$sizes->random(), $colors->random(), $materials->random()],
            ],
        ];

        foreach ($products as $productData) {
            $product = Product::create([
                'shop_id' => $shops->random()->id,
                'subcategory_id' => $subcategories->random()->id,
                'name' => $productData['name'],
                'slug' => \Illuminate\Support\Str::slug($productData['name']),
                'description' => $productData['description'],
                'price' => $productData['price'],
                'is_active' => $productData['is_active'],
            ]);

            // Attach attributes
            if (!empty($productData['attributes'])) {
                $product->attributeValues()->attach($productData['attributes']);
            }

            // Create product stats
            ProductStats::create([
                'product_id' => $product->id,
                'views_count' => rand(0, 500),
                'whatsapp_clicks' => rand(0, 50),
                'favorites_count' => rand(0, 100),
                'last_viewed_at' => now()->subDays(rand(0, 30)),
            ]);
        }

        // Create additional random products in local/testing environments
        // if (app()->environment(['local', 'testing'])) {
        //     $additionalProducts = Product::factory()->count(40)->create();
            
        //     // Attach random attributes to additional products
        //     foreach ($additionalProducts as $product) {
        //         $attributeIds = collect([
        //             $sizes->random(),
        //             $colors->random(),
        //             $genders->random(),
        //         ])->filter()->unique()->values();
                
        //         if ($attributeIds->isNotEmpty()) {
        //             $product->attributeValues()->attach($attributeIds);
        //         }

        //         // Create stats for additional products
        //         ProductStats::create([
        //             'product_id' => $product->id,
        //             'views_count' => rand(0, 1000),
        //             'whatsapp_clicks' => rand(0, 100),
        //             'favorites_count' => rand(0, 200),
        //             'last_viewed_at' => now()->subDays(rand(0, 60)),
        //         ]);
        //     }
        // }
    }
}
