<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\City;
use App\Models\Governorate;
use App\Models\Location;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Property-Based Test for Product Creation and Management
 * Feature: marketplace-platform, Property 9: Product Creation and Management
 * Validates: Requirements 5.1, 5.2, 5.5.
 */
class ProductManagementPropertyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Property 9: Product Creation and Management
     * For any product data from a shop owner, the system should create products
     * with proper relationships and validate all required fields.
     * **Validates: Requirements 5.1, 5.2, 5.5**
     */
    public function test_product_creation_and_management_property()
    {
        // Run property test with 100 iterations
        for ($i = 0; $i < 100; $i++) {
            $this->runProductCreationProperty();
        }
    }

    private function runProductCreationProperty()
    {
        // Create vendor and shop
        $vendor = User::factory()->create(['role' => 'vendor', 'status' => 'active']);
        $shop = $this->createValidShop($vendor);
        $subcategory = $this->createValidSubcategory();

        // Generate valid product data
        $productData = $this->generateValidProductData($shop->id, $subcategory->id);

        // Create product
        $product = Product::create($productData);

        // Property assertions - Requirements 5.1, 5.2
        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals($productData['shop_id'], $product->shop_id);
        $this->assertEquals($productData['subcategory_id'], $product->subcategory_id);
        $this->assertEquals($productData['name'], $product->name);
        $this->assertEquals($productData['description'], $product->description);
        $this->assertEquals($productData['price'], $product->price);
        $this->assertEquals($productData['is_active'], $product->is_active);

        // Product should be associated with shop and subcategory - Requirement 5.1
        $this->assertInstanceOf(Shop::class, $product->shop);
        $this->assertEquals($shop->id, $product->shop->id);
        $this->assertInstanceOf(Subcategory::class, $product->subcategory);
        $this->assertEquals($subcategory->id, $product->subcategory->id);

        // Product should have timestamps - Requirement 5.5
        $this->assertNotNull($product->created_at);
        $this->assertNotNull($product->updated_at);

        // Slug should be auto-generated and unique within shop
        $this->assertNotNull($product->slug);
        $this->assertNotEmpty($product->slug);

        // Test stock quantity handling
        if (isset($productData['stock_quantity'])) {
            $this->assertEquals($productData['stock_quantity'], $product->stock_quantity);
        }

        // Test discount handling
        if (isset($productData['discount_type']) && isset($productData['discount_value'])) {
            $this->assertEquals($productData['discount_type'], $product->discount_type);
            $this->assertEquals($productData['discount_value'], $product->discount_value);
            $this->assertTrue($product->hasDiscount());
        }
    }

    /**
     * Property: Product-subcategory relationship validation
     * For any product creation, subcategory must exist and be properly linked.
     * **Validates: Requirements 5.1, 5.2**
     */
    public function test_product_subcategory_relationship_property()
    {
        for ($i = 0; $i < 50; $i++) {
            $this->runProductSubcategoryRelationshipProperty();
        }
    }

    private function runProductSubcategoryRelationshipProperty()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = $this->createValidShop($vendor);
        $subcategory = $this->createValidSubcategory();

        $productData = $this->generateValidProductData($shop->id, $subcategory->id);
        $product = Product::create($productData);

        // Verify subcategory relationship
        $this->assertEquals($subcategory->id, $product->subcategory_id);
        $this->assertInstanceOf(Subcategory::class, $product->subcategory);
        $this->assertEquals($subcategory->name, $product->subcategory->name);

        // Verify category through subcategory
        $category = $product->subcategory->category;
        $this->assertInstanceOf(Category::class, $category);
    }

    /**
     * Property: Product shop ownership validation
     * For any product, it must belong to a valid shop.
     * **Validates: Requirements 5.1**
     */
    public function test_product_shop_ownership_property()
    {
        for ($i = 0; $i < 50; $i++) {
            $this->runProductShopOwnershipProperty();
        }
    }

    private function runProductShopOwnershipProperty()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = $this->createValidShop($vendor);
        $subcategory = $this->createValidSubcategory();

        $productData = $this->generateValidProductData($shop->id, $subcategory->id);
        $product = Product::create($productData);

        // Verify shop relationship
        $this->assertEquals($shop->id, $product->shop_id);
        $this->assertInstanceOf(Shop::class, $product->shop);
        $this->assertEquals($shop->name, $product->shop->name);
        $this->assertEquals($vendor->id, $product->shop->owner_id);
    }

    /**
     * Property: Product price handling validation
     * For any product with price, the system should handle decimal values correctly.
     * **Validates: Requirements 5.2**
     */
    public function test_product_price_handling_property()
    {
        for ($i = 0; $i < 50; $i++) {
            $this->runProductPriceHandlingProperty();
        }
    }

    private function runProductPriceHandlingProperty()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = $this->createValidShop($vendor);
        $subcategory = $this->createValidSubcategory();

        $price = $this->generateRandomPrice();
        $productData = $this->generateValidProductData($shop->id, $subcategory->id);
        $productData['price'] = $price;

        $product = Product::create($productData);

        // Verify price is stored correctly as decimal (Laravel casts decimal to string)
        $this->assertEquals($price, $product->price);
        $this->assertIsString($product->price); // Laravel decimal casting returns string

        // Test price can be null (optional)
        $productData2 = $this->generateValidProductData($shop->id, $subcategory->id);
        $productData2['price'] = null;
        $productData2['name'] = $productData2['name'] . '_no_price';

        $product2 = Product::create($productData2);
        $this->assertNull($product2->price);
    }

    /**
     * Property: Product status management
     * For any product status change, the system should maintain status integrity.
     * **Validates: Requirements 5.2**
     */
    public function test_product_status_management_property()
    {
        for ($i = 0; $i < 50; $i++) {
            $this->runProductStatusManagementProperty();
        }
    }

    private function runProductStatusManagementProperty()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = $this->createValidShop($vendor);
        $subcategory = $this->createValidSubcategory();

        $isActive = (bool) rand(0, 1);
        $productData = $this->generateValidProductData($shop->id, $subcategory->id);
        $productData['is_active'] = $isActive;

        $product = Product::create($productData);

        $this->assertEquals($isActive, $product->is_active);

        // Test scope works correctly
        if ($isActive) {
            $this->assertTrue(Product::active()->where('id', $product->id)->exists());
        } else {
            $this->assertFalse(Product::active()->where('id', $product->id)->exists());
        }
    }

    /**
     * Property: Product discount system validation
     * For any product with discount, the system should calculate discounted prices correctly.
     * **Validates: Requirements 5.2**
     */
    public function test_product_discount_system_property()
    {
        for ($i = 0; $i < 50; $i++) {
            $this->runProductDiscountSystemProperty();
        }
    }

    private function runProductDiscountSystemProperty()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = $this->createValidShop($vendor);
        $subcategory = $this->createValidSubcategory();

        $price = $this->generateRandomPrice();
        $discountType = rand(0, 1) ? 'percent' : 'amount';
        $discountValue = $discountType === 'percent' ? rand(5, 50) : rand(10, 100);

        $productData = $this->generateValidProductData($shop->id, $subcategory->id);
        $productData['price'] = $price;
        $productData['discount_type'] = $discountType;
        $productData['discount_value'] = $discountValue;

        $product = Product::create($productData);

        // Verify discount properties
        $this->assertEquals($discountType, $product->discount_type);
        $this->assertEquals($discountValue, $product->discount_value);
        $this->assertTrue($product->hasDiscount());

        // Verify discount calculation
        $discountedPrice = $product->getDiscountedPrice();
        $this->assertIsFloat($discountedPrice);
        $this->assertLessThanOrEqual($price, $discountedPrice);

        if ($discountType === 'percent') {
            $expectedPrice = round($price * (1 - ($discountValue / 100)), 2);
            $this->assertEquals($expectedPrice, $discountedPrice);
        } else {
            $expectedPrice = round(max(0, $price - $discountValue), 2);
            $this->assertEquals($expectedPrice, $discountedPrice);
        }

        // Verify savings calculation
        $savings = $product->getSavingsAmount();
        $this->assertIsFloat($savings);
        $this->assertEquals(round($price - $discountedPrice, 2), $savings);
    }

    /**
     * Property: Product stock management
     * For any product with stock tracking, the system should handle stock operations correctly.
     * **Validates: Requirements 5.2**
     */
    public function test_product_stock_management_property()
    {
        for ($i = 0; $i < 50; $i++) {
            $this->runProductStockManagementProperty();
        }
    }

    private function runProductStockManagementProperty()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = $this->createValidShop($vendor);
        $subcategory = $this->createValidSubcategory();

        $stockQuantity = rand(0, 100);
        $productData = $this->generateValidProductData($shop->id, $subcategory->id);
        $productData['stock_quantity'] = $stockQuantity;

        $product = Product::create($productData);

        $this->assertEquals($stockQuantity, $product->stock_quantity);

        // Test stock status methods
        if ($stockQuantity > 0) {
            $this->assertTrue($product->isInStock());
            $this->assertFalse($product->isOutOfStock());
            $this->assertTrue(Product::inStock()->where('id', $product->id)->exists());
        } else {
            $this->assertFalse($product->isInStock());
            $this->assertTrue($product->isOutOfStock());
            $this->assertTrue(Product::outOfStock()->where('id', $product->id)->exists());
        }

        // Test low stock detection
        if ($stockQuantity <= 5 && $stockQuantity > 0) {
            $this->assertTrue($product->isLowStock());
        }

        // Test stock operations
        if ($stockQuantity > 0) {
            $decrementAmount = min($stockQuantity, rand(1, 5));
            $originalStock = $product->stock_quantity;
            $result = $product->decrementStock($decrementAmount);
            
            $this->assertTrue($result);
            $product->refresh();
            $this->assertEquals($originalStock - $decrementAmount, $product->stock_quantity);

            // Test increment
            $incrementAmount = rand(1, 10);
            $currentStock = $product->stock_quantity;
            $product->incrementStock($incrementAmount);
            $product->refresh();
            $this->assertEquals($currentStock + $incrementAmount, $product->stock_quantity);
        }
    }

    /**
     * Property: Product slug uniqueness within shop
     * For any two products in the same shop, they should have unique slugs.
     * **Validates: Requirements 5.2**
     */
    public function test_product_slug_uniqueness_property()
    {
        for ($i = 0; $i < 30; $i++) {
            $this->runProductSlugUniquenessProperty();
        }
    }

    private function runProductSlugUniquenessProperty()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = $this->createValidShop($vendor);
        $subcategory = $this->createValidSubcategory();

        // Create first product
        $productData1 = $this->generateValidProductData($shop->id, $subcategory->id);
        $product1 = Product::create($productData1);

        // Create second product with same name (should get different slug)
        $productData2 = $this->generateValidProductData($shop->id, $subcategory->id);
        $productData2['name'] = $productData1['name']; // Same name
        $product2 = Product::create($productData2);

        // Slugs should be different
        $this->assertNotEquals($product1->slug, $product2->slug);

        // Both products should exist
        $this->assertInstanceOf(Product::class, $product1);
        $this->assertInstanceOf(Product::class, $product2);
    }

    /**
     * Property: Foreign key constraints validation
     * For any invalid shop_id or subcategory_id, product creation should fail.
     * **Validates: Requirements 5.1**
     */
    public function test_foreign_key_constraints_property()
    {
        for ($i = 0; $i < 20; $i++) {
            $this->runForeignKeyConstraintsProperty();
        }
    }

    private function runForeignKeyConstraintsProperty()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = $this->createValidShop($vendor);
        $subcategory = $this->createValidSubcategory();

        // Test invalid shop_id
        $productData = $this->generateValidProductData(99999, $subcategory->id);
        
        $exceptionThrown = false;
        try {
            Product::create($productData);
        } catch (QueryException $e) {
            $exceptionThrown = true;
            $this->assertTrue(
                str_contains($e->getMessage(), 'foreign key constraint') ||
                str_contains($e->getMessage(), 'FOREIGN KEY constraint failed')
            );
        }
        $this->assertTrue($exceptionThrown, 'Expected QueryException for invalid shop_id');

        // Test invalid subcategory_id
        $productData2 = $this->generateValidProductData($shop->id, 99999);
        
        $exceptionThrown2 = false;
        try {
            Product::create($productData2);
        } catch (QueryException $e) {
            $exceptionThrown2 = true;
            $this->assertTrue(
                str_contains($e->getMessage(), 'foreign key constraint') ||
                str_contains($e->getMessage(), 'FOREIGN KEY constraint failed')
            );
        }
        $this->assertTrue($exceptionThrown2, 'Expected QueryException for invalid subcategory_id');
    }

    private function createValidShop(User $vendor): Shop
    {
        $location = $this->createValidLocation();
        
        return Shop::create([
            'owner_id' => $vendor->id,
            'location_id' => $location->id,
            'name' => $this->generateRandomShopName(),
            'description' => 'Test shop description',
            'whatsapp_number' => '+201234567890',
            'phone_number' => '+201234567890',
            'is_active' => true,
        ]);
    }

    private function createValidLocation(): Location
    {
        $timestamp = microtime(true) * 1000;
        $random = rand(100000, 999999);

        $governorate = Governorate::create([
            'name' => $this->generateRandomGovernorate(),
            'slug' => 'gov-' . $timestamp . '-' . $random,
        ]);

        $city = City::create([
            'governorate_id' => $governorate->id,
            'name' => $this->generateRandomCity(),
            'slug' => 'city-' . $timestamp . '-' . $random,
        ]);

        return Location::create([
            'city_id' => $city->id,
            'area' => $this->generateRandomArea() . '_' . $timestamp . '_' . $random,
            'latitude' => $this->generateValidLatitude(),
            'longitude' => $this->generateValidLongitude(),
        ]);
    }

    private function createValidSubcategory(): Subcategory
    {
        $timestamp = microtime(true) * 1000;
        $random = rand(100000, 999999);

        $category = Category::create([
            'name' => $this->generateRandomCategory(),
            'slug' => 'cat-' . $timestamp . '-' . $random,
        ]);

        return Subcategory::create([
            'category_id' => $category->id,
            'name' => $this->generateRandomSubcategory(),
            'slug' => 'subcat-' . $timestamp . '-' . $random,
        ]);
    }

    private function generateValidProductData(int $shopId, int $subcategoryId): array
    {
        return [
            'shop_id' => $shopId,
            'subcategory_id' => $subcategoryId,
            'name' => $this->generateRandomProductName(),
            'description' => $this->generateRandomDescription(),
            'price' => $this->generateRandomPrice(),
            'stock_quantity' => rand(0, 100),
            'is_active' => (bool) rand(0, 1),
        ];
    }

    private function generateRandomProductName(): string
    {
        $products = [
            'Samsung Galaxy Phone',
            'Nike Running Shoes',
            'Apple MacBook Pro',
            'Sony Headphones',
            'Adidas T-Shirt',
            'Canon Camera',
            'Dell Laptop',
            'iPhone Case',
            'Wireless Mouse',
            'Gaming Keyboard',
        ];

        return $products[array_rand($products)] . ' ' . rand(1, 1000);
    }

    private function generateRandomDescription(): string
    {
        $descriptions = [
            'High quality product with excellent features',
            'Premium product with warranty included',
            'Best value for money with fast shipping',
            'Top-rated product with customer satisfaction',
            'Professional grade product for daily use',
        ];

        return $descriptions[array_rand($descriptions)];
    }

    private function generateRandomPrice(): float
    {
        return round(rand(10, 5000) + (rand(0, 99) / 100), 2);
    }

    private function generateRandomShopName(): string
    {
        $names = [
            'Tech Store',
            'Fashion Hub',
            'Electronics World',
            'Sports Corner',
            'Book Shop',
            'Mobile Center',
        ];

        return $names[array_rand($names)] . ' ' . rand(1, 1000);
    }

    private function generateRandomCategory(): string
    {
        $categories = ['Electronics', 'Fashion', 'Sports', 'Books', 'Home', 'Beauty'];
        return $categories[array_rand($categories)];
    }

    private function generateRandomSubcategory(): string
    {
        $subcategories = ['Phones', 'Laptops', 'Shoes', 'Shirts', 'Bags', 'Accessories'];
        return $subcategories[array_rand($subcategories)];
    }

    private function generateRandomGovernorate(): string
    {
        $governorates = ['Cairo', 'Giza', 'Alexandria', 'Qalyubia', 'Sharqia'];
        return $governorates[array_rand($governorates)];
    }

    private function generateRandomCity(): string
    {
        $cities = ['Cairo', 'Alexandria', 'Giza', 'Mansoura', 'Tanta'];
        return $cities[array_rand($cities)];
    }

    private function generateRandomArea(): string
    {
        $areas = ['Downtown', 'Nasr City', 'Maadi', 'Zamalek', 'Heliopolis'];
        return $areas[array_rand($areas)];
    }

    private function generateValidLatitude(): float
    {
        // Egypt latitude range: approximately 22° to 32° N
        return round(22 + (rand(0, 1000) / 100), 6);
    }

    private function generateValidLongitude(): float
    {
        // Egypt longitude range: approximately 25° to 35° E
        return round(25 + (rand(0, 1000) / 100), 6);
    }
}