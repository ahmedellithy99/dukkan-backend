<?php

namespace Tests\Unit;

use App\Models\Shop;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpatieSlugGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_shop_slug_is_auto_generated_from_name()
    {
        $shop = Shop::factory()->create([
            'name' => "Men's Cotton T-Shirt – Black"
        ]);

        $this->assertEquals('mens-cotton-t-shirt-black', $shop->slug);
    }

    public function test_shop_slug_handles_special_characters()
    {
        $shop = Shop::factory()->create([
            'name' => "Ahmed's Electronics & More!"
        ]);

        $this->assertEquals('ahmeds-electronics-more', $shop->slug);
    }

    public function test_shop_slug_uniqueness_with_counter()
    {
        // Create first shop
        $shop1 = Shop::factory()->create([
            'name' => 'Electronics Store'
        ]);

        // Create second shop with same name
        $shop2 = Shop::factory()->create([
            'name' => 'Electronics Store'
        ]);

        $this->assertEquals('electronics-store', $shop1->slug);
        $this->assertEquals('electronics-store-1', $shop2->slug);
    }

    public function test_product_slug_is_auto_generated_from_name()
    {
        $product = Product::factory()->create([
            'name' => "Men's Cotton T-Shirt – Black"
        ]);

        $this->assertEquals('mens-cotton-t-shirt-black', $product->slug);
    }

    public function test_product_slug_uniqueness_within_same_shop()
    {
        $shop = Shop::factory()->create();

        // Create two products with same name in same shop
        $product1 = Product::factory()->create([
            'shop_id' => $shop->id,
            'name' => 'Cotton T-Shirt'
        ]);

        $product2 = Product::factory()->create([
            'shop_id' => $shop->id,
            'name' => 'Cotton T-Shirt'
        ]);

        $this->assertEquals('cotton-t-shirt', $product1->slug);
        $this->assertEquals('cotton-t-shirt-1', $product2->slug);
    }

    public function test_product_slug_can_be_same_across_different_shops()
    {
        $shop1 = Shop::factory()->create();
        $shop2 = Shop::factory()->create();

        // Create products with same name in different shops
        $product1 = Product::factory()->create([
            'shop_id' => $shop1->id,
            'name' => 'Cotton T-Shirt'
        ]);

        $product2 = Product::factory()->create([
            'shop_id' => $shop2->id,
            'name' => 'Cotton T-Shirt'
        ]);

        // Both should have the same slug since they're in different shops
        $this->assertEquals('cotton-t-shirt', $product1->slug);
        $this->assertEquals('cotton-t-shirt', $product2->slug);
    }

    public function test_route_key_name_uses_slug()
    {
        $shop = Shop::factory()->create();
        $product = Product::factory()->create();

        $this->assertEquals('slug', $shop->getRouteKeyName());
        $this->assertEquals('slug', $product->getRouteKeyName());
    }

    public function test_slug_regenerated_on_name_update()
    {
        $shop = Shop::factory()->create([
            'name' => 'Original Name'
        ]);

        $originalSlug = $shop->slug;
        $this->assertEquals('original-name', $originalSlug);

        // Update name - slug should automatically update
        $shop->update([
            'name' => 'Updated Name'
        ]);

        $this->assertEquals('updated-name', $shop->fresh()->slug);
        $this->assertNotEquals($originalSlug, $shop->fresh()->slug);
    }

    public function test_slug_update_handles_uniqueness_conflicts()
    {
        // Create first shop
        $shop1 = Shop::factory()->create([
            'name' => 'Electronics Store'
        ]);

        // Create second shop
        $shop2 = Shop::factory()->create([
            'name' => 'Fashion Store'
        ]);

        $this->assertEquals('electronics-store', $shop1->slug);
        $this->assertEquals('fashion-store', $shop2->slug);

        // Update second shop to have same name as first
        $shop2->update([
            'name' => 'Electronics Store'
        ]);

        // Should automatically generate unique slug
        $this->assertEquals('electronics-store', $shop1->fresh()->slug);
        $this->assertEquals('electronics-store-1', $shop2->fresh()->slug);
    }
}
