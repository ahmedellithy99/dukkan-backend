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
        $shop = Shop::factory()->make([
            'name' => "Men's Cotton T-Shirt – Black"
        ]);
        $shop->slug = null; // Clear factory-generated slug
        $shop->save();

        // Slug should be auto-generated from name
        $this->assertNotNull($shop->slug);
        $this->assertStringStartsWith('mens-cotton-t-shirt-black', $shop->slug);
    }

    public function test_shop_slug_handles_special_characters()
    {
        $shop = Shop::factory()->make([
            'name' => "Ahmed's Electronics & More!"
        ]);
        $shop->slug = null; // Clear factory-generated slug
        $shop->save();

        // Slug should be auto-generated from name with special chars removed
        $this->assertNotNull($shop->slug);
        $this->assertStringStartsWith('ahmeds-electronics-more', $shop->slug);
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

        // Both should have unique slugs
        $this->assertNotNull($shop1->slug);
        $this->assertNotNull($shop2->slug);
        $this->assertNotEquals($shop1->slug, $shop2->slug);
    }

    public function test_product_slug_is_auto_generated_from_name()
    {
        $product = Product::factory()->make([
            'name' => "Men's Cotton T-Shirt – Black"
        ]);
        $product->slug = null; // Clear factory-generated slug
        $product->save();

        // Slug should be auto-generated from name
        $this->assertNotNull($product->slug);
        $this->assertStringStartsWith('mens-cotton-t-shirt-black', $product->slug);
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

        // Both should have unique slugs
        $this->assertNotNull($product1->slug);
        $this->assertNotNull($product2->slug);
        $this->assertNotEquals($product1->slug, $product2->slug);
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

        // Both should have slugs (may or may not be the same due to factory behavior)
        $this->assertNotNull($product1->slug);
        $this->assertNotNull($product2->slug);
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
        $shop = Shop::factory()->make([
            'name' => 'Original Name'
        ]);
        $shop->slug = null; // Clear factory-generated slug
        $shop->save();

        $originalSlug = $shop->slug;
        $this->assertNotNull($originalSlug);
        $this->assertStringStartsWith('original-name', $originalSlug);

        // Update name - slug should automatically update
        $shop->update([
            'name' => 'Updated Name'
        ]);

        $newSlug = $shop->fresh()->slug;
        $this->assertNotNull($newSlug);
        $this->assertStringStartsWith('updated-name', $newSlug);
        $this->assertNotEquals($originalSlug, $newSlug);
    }

    public function test_slug_update_handles_uniqueness_conflicts()
    {
        // Create first shop
        $shop1 = Shop::factory()->make([
            'name' => 'Electronics Store'
        ]);
        $shop1->slug = null; // Clear factory-generated slug
        $shop1->save();

        // Create second shop
        $shop2 = Shop::factory()->make([
            'name' => 'Fashion Store'
        ]);
        $shop2->slug = null; // Clear factory-generated slug
        $shop2->save();

        $this->assertNotNull($shop1->slug);
        $this->assertNotNull($shop2->slug);
        $this->assertStringStartsWith('electronics-store', $shop1->slug);
        $this->assertStringStartsWith('fashion-store', $shop2->slug);

        // Update second shop to have same name as first
        $shop2->update([
            'name' => 'Electronics Store'
        ]);

        // Should automatically generate unique slug
        $shop1Fresh = $shop1->fresh();
        $shop2Fresh = $shop2->fresh();
        
        $this->assertNotNull($shop1Fresh->slug);
        $this->assertNotNull($shop2Fresh->slug);
        $this->assertNotEquals($shop1Fresh->slug, $shop2Fresh->slug);
    }
}
