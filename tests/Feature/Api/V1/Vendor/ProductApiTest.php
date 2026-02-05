<?php

namespace Tests\Feature\Api\V1\Vendor;

use App\Models\Category;
use App\Models\City;
use App\Models\Governorate;
use App\Models\Location;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
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

    public function test_vendor_can_list_their_products()
    {
        Sanctum::actingAs($this->vendor);

        // Create products for this vendor
        $product1 = Product::factory()->create([
            'shop_id' => $this->shop->id,
            'subcategory_id' => $this->subcategory->id,
        ]);

        $product2 = Product::factory()->create([
            'shop_id' => $this->shop->id,
            'subcategory_id' => $this->subcategory->id,
        ]);

        // Create product for another vendor (should not appear)
        $otherVendor = User::factory()->create(['role' => 'vendor']);
        $otherShop = $this->createShop($otherVendor);
        Product::factory()->create([
            'shop_id' => $otherShop->id,
            'subcategory_id' => $this->subcategory->id,
        ]);

    $response = $this->getJson("/api/v1/vendor/my-shop/{$this->shop->slug}/products");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'api_version',
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'description',
                        'price',
                        'stock_quantity',
                        'is_active',
                        'created_at',
                        'updated_at',
                    ]
                ],
                'meta' => [
                    'pagination',
                    'version_info',
                ]
            ]);

        // Should only see own products
        $this->assertCount(2, $response->json('data'));
    }

    public function test_vendor_can_create_product()
    {
        Sanctum::actingAs($this->vendor);

        $productData = [
            'shop_id' => $this->shop->id,
            'subcategory_id' => $this->subcategory->id,
            'name' => 'Test Product',
            'description' => 'Test product description',
            'price' => 99.99,
            'stock_quantity' => 10,
            'is_active' => true,
        ];

        $response = $this->postJson("/api/v1/vendor/my-shop/{$this->shop->slug}/products", $productData);

        $response->assertStatus(201)
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
                    'is_active',
                    'created_at',
                    'updated_at',
                ]
            ]);

        $this->assertDatabaseHas('products', [
            'shop_id' => $this->shop->id,
            'subcategory_id' => $this->subcategory->id,
            'name' => 'Test Product',
            'price' => 99.99,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);
    }

    public function test_vendor_can_view_their_product()
    {
        Sanctum::actingAs($this->vendor);

        $product = Product::factory()->create([
            'shop_id' => $this->shop->id,
            'subcategory_id' => $this->subcategory->id,
        ]);

        $response = $this->getJson("/api/v1/vendor/my-shop/{$this->shop->slug}/products/{$product->slug}");

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
                    'is_active',
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

    public function test_vendor_can_update_their_product()
    {
        Sanctum::actingAs($this->vendor);

        $product = Product::factory()->create([
            'shop_id' => $this->shop->id,
            'subcategory_id' => $this->subcategory->id,
            'name' => 'Original Name',
            'price' => 50.00,
        ]);

        $updateData = [
            'name' => 'Updated Product Name',
            'price' => 75.00,
            'description' => 'Updated description',
        ];

        $response = $this->putJson("/api/v1/vendor/my-shop/{$this->shop->slug}/products/{$product->slug}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $product->id,
                    'name' => 'Updated Product Name',
                    'price' => '75.00',
                    'description' => 'Updated description',
                ]
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product Name',
            'price' => 75.00,
            'description' => 'Updated description',
        ]);
    }

    public function test_vendor_can_delete_their_product()
    {
        Sanctum::actingAs($this->vendor);

        $product = Product::factory()->create([
            'shop_id' => $this->shop->id,
            'subcategory_id' => $this->subcategory->id,
        ]);

        $response = $this->deleteJson("/api/v1/vendor/my-shop/{$this->shop->slug}/products/{$product->slug}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    public function test_vendor_cannot_access_other_vendor_products()
    {
        Sanctum::actingAs($this->vendor);

        // Create product for another vendor
        $otherVendor = User::factory()->create(['role' => 'vendor']);
        $otherShop = $this->createShop($otherVendor);
        $otherProduct = Product::factory()->create([
            'shop_id' => $otherShop->id,
            'subcategory_id' => $this->subcategory->id,
        ]);

        // Try to view other vendor's product
        $response = $this->getJson("/api/v1/vendor/my-shop/{$otherShop->slug}/products/{$otherProduct->slug}");
        $response->assertStatus(403);

        // Try to update other vendor's product
        $response = $this->putJson("/api/v1/vendor/my-shop/{$otherShop->slug}/products/{$otherProduct->slug}", [
            'name' => 'Hacked Name'
        ]);
        $response->assertStatus(403);

        // Try to delete other vendor's product
        $response = $this->deleteJson("/api/v1/vendor/my-shop/{$otherShop->slug}/products/{$otherProduct->slug}");
        $response->assertStatus(403);
    }

    public function test_vendor_can_toggle_product_status()
    {
        Sanctum::actingAs($this->vendor);

        $product = Product::factory()->create([
            'shop_id' => $this->shop->id,
            'subcategory_id' => $this->subcategory->id,
            'is_active' => true,
        ]);

        $response = $this->putJson("/api/v1/vendor/my-shop/{$this->shop->slug}/products/{$product->slug}/toggle-status");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $product->id,
                    'is_active' => false,
                ]
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'is_active' => false,
        ]);
    }

    public function test_vendor_can_update_product_stock()
    {
        Sanctum::actingAs($this->vendor);

        $product = Product::factory()->create([
            'shop_id' => $this->shop->id,
            'subcategory_id' => $this->subcategory->id,
            'stock_quantity' => 10,
        ]);

        $response = $this->putJson("/api/v1/vendor/my-shop/{$this->shop->slug}/products/{$product->slug}/stock", [
            'stock_quantity' => 25,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $product->id,
                    'stock_quantity' => 25,
                ]
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 25,
        ]);
    }

    public function test_vendor_can_apply_discount_to_product()
    {
        Sanctum::actingAs($this->vendor);

        $product = Product::factory()->create([
            'shop_id' => $this->shop->id,
            'subcategory_id' => $this->subcategory->id,
            'price' => 100.00,
        ]);

        $response = $this->putJson("/api/v1/vendor/my-shop/{$this->shop->slug}/products/{$product->slug}/apply-discount", [
            'discount_type' => 'percent',
            'discount_value' => 20,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $product->id,
                    'discount_type' => 'percent',
                    'discount_value' => '20.00',
                    'has_discount' => true,
                    'discounted_price' => 80.00,
                    'savings_amount' => 20.00,
                ]
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'discount_type' => 'percent',
            'discount_value' => 20.00,
        ]);
    }

    public function test_vendor_can_remove_discount_from_product()
    {
        Sanctum::actingAs($this->vendor);

        $product = Product::factory()->create([
            'shop_id' => $this->shop->id,
            'subcategory_id' => $this->subcategory->id,
            'price' => 100.00,
            'discount_type' => 'percent',
            'discount_value' => 20,
        ]);

        $response = $this->putJson("/api/v1/vendor/my-shop/{$this->shop->slug}/products/{$product->slug}/remove-discount");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $product->id,
                    'discount_type' => null,
                    'discount_value' => null,
                    'has_discount' => false,
                ]
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'discount_type' => null,
            'discount_value' => null,
        ]);
    }

    private function createShop(User $vendor): Shop
    {
        $location = $this->createLocation();
        
        return Shop::factory()->create([
            'owner_id' => $vendor->id,
            'location_id' => $location->id,
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