<?php

namespace Tests\Unit;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDiscountTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_without_discount()
    {
        $product = Product::factory()->create([
            'price' => 100.00,
            'discount_type' => null,
            'discount_value' => null,
        ]);

        $this->assertFalse($product->hasDiscount());
        $this->assertEquals(100.00, $product->getDiscountedPrice());
        $this->assertNull($product->getSavingsAmount());
    }

    public function test_product_with_percentage_discount()
    {
        $product = Product::factory()->create([
            'price' => 100.00,
            'discount_type' => 'percent',
            'discount_value' => 20.00,
        ]);

        $this->assertTrue($product->hasDiscount());
        $this->assertEquals(80.00, $product->getDiscountedPrice());
        $this->assertEquals(20.00, $product->getSavingsAmount());
    }

    public function test_product_with_amount_discount()
    {
        $product = Product::factory()->create([
            'price' => 100.00,
            'discount_type' => 'amount',
            'discount_value' => 25.00,
        ]);

        $this->assertTrue($product->hasDiscount());
        $this->assertEquals(75.00, $product->getDiscountedPrice());
        $this->assertEquals(25.00, $product->getSavingsAmount());
    }

    public function test_product_with_both_discounts_amount_takes_precedence()
    {
        // This test is no longer valid with the new structure since we can only have one discount type
        // Let's test that amount discount works correctly
        $product = Product::factory()->create([
            'price' => 100.00,
            'discount_type' => 'amount',
            'discount_value' => 30.00,
        ]);

        $this->assertTrue($product->hasDiscount());
        $this->assertEquals(70.00, $product->getDiscountedPrice());
        $this->assertEquals(30.00, $product->getSavingsAmount());
    }

    public function test_discount_cannot_go_below_zero()
    {
        $product = Product::factory()->create([
            'price' => 50.00,
            'discount_type' => 'amount',
            'discount_value' => 75.00, // More than the price
        ]);

        $this->assertTrue($product->hasDiscount());
        $this->assertEquals(0.00, $product->getDiscountedPrice());
        $this->assertEquals(50.00, $product->getSavingsAmount());
    }

    public function test_on_discount_scope()
    {
        // Create products with and without discounts
        Product::factory()->create([
            'price' => 100.00,
            'discount_type' => 'percent',
            'discount_value' => 20.00,
        ]);

        Product::factory()->create([
            'price' => 100.00,
            'discount_type' => 'amount',
            'discount_value' => 25.00,
        ]);

        Product::factory()->create([
            'price' => 100.00,
            'discount_type' => null,
            'discount_value' => null,
        ]);

        $discountedProducts = Product::onDiscount()->count();
        $this->assertEquals(2, $discountedProducts);
    }
}
