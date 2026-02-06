<?php

namespace Tests\Feature\Api\V1\Website;

use App\Models\Category;
use App\Models\City;
use App\Models\Governorate;
use App\Models\Location;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $vendor;
    protected Shop $shop;
    protected Subcategory $subcategory;

    protected function setUp(): void
    {
        parent::setUp();

        // Create vendor user
        $this->vendor = User::factory()->create([
            'role' => 'vendor',
            'status' => 'active',
        ]);

        // Create shop for vendor
        $this->shop = $this->createShop($this->vendor);

        // Create subcategory
        $this->subcategory = $this->createSubcategory();
    }

    public function test_public_can_view_active_product()
    {
        $product = Product::factory()->create([
            'shop_id' => $this->shop->id,
            'subcategory_id' => $this->subcategory->id,
            'is_active' => true,
        ]);

        $response = $this->getJson("/api/v1/products/{$product->slug}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'api_version',
                'success',
                'data' => [
                    'id',
                    'name',
                    'slug',
                    'description',
                    'price',
                    'stock_quantity',
                    'is_in_stock',
                    'has_discount',
                    'created_at',
                    'updated_at',
                ]
            ])
            ->assertJson([
                'data' => [
                    'id' => $product->id,
                    'name' => $product->name,
                ]
            ]);
    }

    public function test_products_can_be_filtered_by_category()
    {
        $category2 = Category::factory()->create();
        $subcategory2 = Subcategory::factory()->create(['category_id' => $category2->id]);

        // Products in first category
        Product::factory()->count(2)->create([
            'shop_id' => $this->shop->id,
            'subcategory_id' => $this->subcategory->id,
            'is_active' => true,
        ]);

        // Products in second category
        Product::factory()->create([
            'shop_id' => $this->shop->id,
            'subcategory_id' => $subcategory2->id,
            'is_active' => true,
        ]);

        $response = $this->getJson("/api/v1/products?category_id={$this->subcategory->category_id}");

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    public function test_products_can_be_searched()
    {
        Product::factory()->create([
            'shop_id' => $this->shop->id,
            'subcategory_id' => $this->subcategory->id,
            'name' => 'Red T-Shirt',
            'is_active' => true,
        ]);

        Product::factory()->create([
            'shop_id' => $this->shop->id,
            'subcategory_id' => $this->subcategory->id,
            'name' => 'Blue Jeans',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/products?search=Red');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Red T-Shirt', $response->json('data.0.name'));
    }

    private function createShop(User $vendor): Shop
    {
        $location = $this->createLocation();
        
        return Shop::factory()->create([
            'owner_id' => $vendor->id,
            'location_id' => $location->id,
            'is_active' => true,
        ]);
    }

    private function createLocation(): Location
    {
        $governorate = Governorate::factory()->create();
        $city = City::factory()->create(['governorate_id' => $governorate->id]);
        
        return Location::factory()->create(['city_id' => $city->id]);
    }

    private function createSubcategory(): Subcategory
    {
        $category = Category::factory()->create();
        return Subcategory::factory()->create(['category_id' => $category->id]);
    }
}
