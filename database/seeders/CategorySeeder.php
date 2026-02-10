<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Clothes' => [
                'T-Shirt',
                'Shirt',
                'Jacket',
                'Jeans',
                'Pants',
                'Dress',
                'Skirt',
                'Sweater',
                'Hoodie',
            ],
            'Shoes' => [
                'Sneakers',
                'Boots',
                'Sandals',
                'Formal Shoes',
                'Sports Shoes',
                'Slippers',
            ],
            'Accessories' => [
                'Bag',
                'Backpack',
                'Watch',
                'Sunglasses',
                'Hat',
                'Scarf',
                'Belt',
                'Wallet',
                'Jewelry',
            ],
            'Electronics' => [
                'Phone',
                'Laptop',
                'Tablet',
                'Headphones',
                'Camera',
                'Smart Watch',
                'Charger',
                'Speaker',
            ],
            'Home & Garden' => [
                'Furniture',
                'Decoration',
                'Kitchen',
                'Bedding',
                'Lighting',
                'Garden Tools',
                'Plants',
            ],
            'Sports' => [
                'Gym Equipment',
                'Sports Wear',
                'Balls',
                'Yoga',
                'Swimming',
                'Cycling',
            ],
            'Beauty' => [
                'Makeup',
                'Skincare',
                'Haircare',
                'Perfume',
                'Nail Care',
                'Body Care',
            ],
            'Books' => [
                'Fiction',
                'Non-Fiction',
                'Educational',
                'Comics',
                'Magazines',
            ],
        ];

        foreach ($categories as $categoryName => $subcategories) {
            $category = new Category();
            $category->name = $categoryName;
            $category->slug = \Illuminate\Support\Str::slug($categoryName);
            $category->save();

            foreach ($subcategories as $subcategoryName) {
                $subcategory = new Subcategory();
                $subcategory->category_id = $category->id;
                $subcategory->name = $subcategoryName;
                $subcategory->slug = \Illuminate\Support\Str::slug($subcategoryName);
                $subcategory->save();
            }
        }
    }
}
