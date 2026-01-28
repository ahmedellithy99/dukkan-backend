<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hasDiscount = $this->faker->boolean(30); // 30% chance of having a discount
        $discountType = $hasDiscount ? $this->faker->randomElement(['percent', 'amount']) : null;
        $trackStock = $this->faker->boolean(85); // 85% chance of tracking stock
        $name = $this->faker->words(3, true);

        return [
            'shop_id' => \App\Models\Shop::factory(),
            'subcategory_id' => \App\Models\Subcategory::factory(),
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name) . '-' . $this->faker->unique()->randomNumber(5),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->optional(0.8)->randomFloat(2, 10, 1000), // 80% chance of having a price
            'discount_type' => $discountType,
            'discount_value' => $hasDiscount
                ? ($discountType === 'percent'
                    ? $this->faker->randomFloat(2, 5, 50)    // 5% to 50% discount
                    : $this->faker->randomFloat(2, 10, 200)) // 10 to 200 EGP discount
                : null,
            'stock_quantity' => $trackStock ? $this->faker->numberBetween(0, 100) : 0,
            'track_stock' => $trackStock,
            'is_active' => $this->faker->boolean(85), // 85% chance of being active
        ];
    }
}
