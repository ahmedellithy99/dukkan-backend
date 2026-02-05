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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Property-Based Test for Product Status and Filtering
 * Feature: marketplace-platform, Property 10: Product Status and Filtering
 * Validates: Requirements 5.3, 5.4.
 */
class ProductFilteringPropertyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Property 10: Product Status and Filtering
     * For any product status change or complex filtering request, the system should
     * maintain status integrity and support multi-criteria filtering.
     * **Validates: Requirements 5.3, 5.4**
     */
    public function test_product_status_and_filtering_property()
    {
        // Run property test with 100 iterations
        for ($i = 0; $i < 100; $i++) {
            $this->runProductStatusAndFilteringProperty();
        }
    }

    private function runProductStatusAndFilteringProperty()
    {
        // Create test data
        $vendor = User::factory()->create(['role' => 'vendor', 'status' => 'active']);
        $shop = $this->createValidShop($vendor);
        $subcategory = $this->createValidSubcategory();

        // Create products with different statuses
        $activeProduct = $this->createProductWithStatus($shop, $subcategory, true);
        $inactiveProduct = $this->createProductWithStatus($shop, $subcategory, false);

        // Test active product filtering - Requirement 5.3
        $activeProducts = Product::active()->get();
        $this->assertTrue($activeProducts->contains($activeProduct));
        $this->assertFalse($activeProducts->contains($inactiveProduct));

        // Test inactive product filtering
        $inactiveProducts = Product::where('is_active', false)->get();
        $this->assertTrue($inactiveProducts->contains($inactiveProduct));
        $this->assertFalse($inactiveProducts->contains($activeProduct));

        // Test shop-based filtering - Requirement 5.4
        $shopProducts = Product::where('shop_id', $shop->id)->get();
        $this->assertTrue($shopProducts->contains($activeProduct));
        $this->assertTrue($shopProducts->contains($inactiveProduct));

        // Test subcategory-based filtering - Requirement 5.4
        $subcategoryProducts = Product::where('subcategory_id', $subcategory->id)->get();
        $this->assertTrue($subcategoryProducts->contains($activeProduct));
        $this->assertTrue($subcategoryProducts->contains($inactiveProduct));

        // Test combined filtering (active products in specific shop)
        $activeShopProducts = Product::active()
            ->where('shop_id', $shop->id)
            ->get();
        $this->assertTrue($activeShopProducts->contains($activeProduct));
        $this->assertFalse($activeShopProducts->contains($inactiveProduct));
    }

    /**
     * Property: Product stock-based filtering
     * For any product stock status, filtering should work correctly.
     * **Validates: Requirements 5.3, 5.4**
     */
    public function test_product_stock_filtering_property()
    {
        for ($i = 0; $i < 50; $i++) {
            $this->runProductStockFilteringProperty();
        }
    }

    private function runProductStockFilteringProperty()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = $this->createValidShop($vendor);
        $subcategory = $this->createValidSubcategory();

        // Create products with different stock levels
        $inStockProduct = $this->createProductWithStock($shop, $subcategory, rand(1, 100));
        $outOfStockProduct = $this->createProductWithStock($shop, $subcategory, 0);

        // Test in-stock filtering
        $inStockProducts = Product::inStock()->get();
        $this->assertTrue($inStockProducts->contains($inStockProduct));
        $this->assertFalse($inStockProducts->contains($outOfStockProduct));

        // Test out-of-stock filtering
        $outOfStockProducts = Product::outOfStock()->get();
        $this->assertTrue($outOfStockProducts->contains($outOfStockProduct));
        $this->assertFalse($outOfStockProducts->contains($inStockProduct));

        // Test stock status methods
        $this->assertTrue($inStockProduct->isInStock());
        $this->assertFalse($inStockProduct->isOutOfStock());
        $this->assertFalse($outOfStockProduct->isInStock());
        $this->assertTrue($outOfStockProduct->isOutOfStock());
    }

    /**
     * Property: Product discount-based filtering
     * For any product with or without discount, filtering should work correctly.
     * **Validates: Requirements 5.3, 5.4**
     */
    public function test_product_discount_filtering_property()
    {
        for ($i = 0; $i < 50; $i++) {
            $this->runProductDiscountFilteringProperty();
        }
    }

    private function runProductDiscountFilteringProperty()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = $this->createValidShop($vendor);
        $subcategory = $this->createValidSubcategory();

        // Create product with discount
        $discountedProduct = $this->createProductWithDiscount($shop, $subcategory);
        
        // Create product without discount
        $regularProduct = $this->createProductWithoutDiscount($shop, $subcategory);

        // Test discount filtering using scope
        $discountedProducts = Product::onDiscount()->get();
        $this->assertTrue($discountedProducts->contains($discountedProduct));
        $this->assertFalse($discountedProducts->contains($regularProduct));

        // Test discount detection methods
        $this->assertTrue($discountedProduct->hasDiscount());
        $this->assertFalse($regularProduct->hasDiscount());

        // Test discounted price calculation
        $discountedPrice = $discountedProduct->getDiscountedPrice();
        $this->assertLessThan($discountedProduct->price, $discountedPrice);

        $regularPrice = $regularProduct->getDiscountedPrice();
        $this->assertEquals($regularProduct->price, $regularPrice);
    }

    /**
     * Property: Multi-criteria product filtering
     * For any combination of filters, the system should return accurate results.
     * **Validates: Requirements 5.3, 5.4**
     */
    public function test_multi_criteria_filtering_property()
    {
        for ($i = 0; $i < 30; $i++) {
            $this->runMultiCriteriaFilteringProperty();
        }
    }

    private function runMultiCriteriaFilteringProperty()
    {
        // Create multiple vendors and shops
        $vendor1 = User::factory()->create(['role' => 'vendor']);
        $vendor2 = User::factory()->create(['role' => 'vendor']);
        $shop1 = $this->createValidShop($vendor1);
        $shop2 = $this->createValidShop($vendor2);

        // Create multiple subcategories
        $subcategory1 = $this->createValidSubcategory();
        $subcategory2 = $this->createValidSubcategory();

        // Create products with different combinations
        $product1 = $this->createSpecificProduct($shop1, $subcategory1, true, 50, 100.00);
        $product2 = $this->createSpecificProduct($shop1, $subcategory2, false, 0, 200.00);
        $product3 = $this->createSpecificProduct($shop2, $subcategory1, true, 25, 150.00);
        $product4 = $this->createSpecificProduct($shop2, $subcategory2, true, 75, 50.00);

        // Test filtering by shop and status
        $shop1ActiveProducts = Product::where('shop_id', $shop1->id)
            ->active()
            ->get();
        $this->assertTrue($shop1ActiveProducts->contains($product1));
        $this->assertFalse($shop1ActiveProducts->contains($product2));
        $this->assertFalse($shop1ActiveProducts->contains($product3));
        $this->assertFalse($shop1ActiveProducts->contains($product4));

        // Test filtering by subcategory and stock
        $subcategory1InStockProducts = Product::where('subcategory_id', $subcategory1->id)
            ->inStock()
            ->get();
        $this->assertTrue($subcategory1InStockProducts->contains($product1));
        $this->assertTrue($subcategory1InStockProducts->contains($product3));
        $this->assertFalse($subcategory1InStockProducts->contains($product2));
        $this->assertFalse($subcategory1InStockProducts->contains($product4));

        // Test filtering by active status and in stock
        $activeInStockProducts = Product::active()
            ->inStock()
            ->get();
        $this->assertTrue($activeInStockProducts->contains($product1));
        $this->assertTrue($activeInStockProducts->contains($product3));
        $this->assertTrue($activeInStockProducts->contains($product4));
        $this->assertFalse($activeInStockProducts->contains($product2));
    }

    /**
     * Property: Product ordering and sorting
     * For any sorting criteria, products should be ordered correctly.
     * **Validates: Requirements 5.4**
     */
    public function test_product_ordering_property()
    {
        for ($i = 0; $i < 5; $i++) {
            $this->runProductOrderingProperty();
        }
    }

    private function runProductOrderingProperty()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = $this->createValidShop($vendor);
        $subcategory = $this->createValidSubcategory();

        // Create products with different prices and creation times
        $expensiveProduct = $this->createSpecificProduct($shop, $subcategory, true, 50, 500.00);
        sleep(1); // Ensure different timestamps
        $cheapProduct = $this->createSpecificProduct($shop, $subcategory, true, 30, 100.00);
        sleep(1);
        $mediumProduct = $this->createSpecificProduct($shop, $subcategory, true, 40, 300.00);

        // Test price ordering (ascending)
        $productsByPriceAsc = Product::where('shop_id', $shop->id)
            ->orderBy('price', 'asc')
            ->get();
        
        $this->assertEquals($cheapProduct->id, $productsByPriceAsc->first()->id);
        $this->assertEquals($expensiveProduct->id, $productsByPriceAsc->last()->id);

        // Test price ordering (descending)
        $productsByPriceDesc = Product::where('shop_id', $shop->id)
            ->orderBy('price', 'desc')
            ->get();
        
        $this->assertEquals($expensiveProduct->id, $productsByPriceDesc->first()->id);
        $this->assertEquals($cheapProduct->id, $productsByPriceDesc->last()->id);

        // Test creation time ordering (newest first)
        $productsByNewest = Product::where('shop_id', $shop->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $this->assertEquals($mediumProduct->id, $productsByNewest->first()->id);
        $this->assertEquals($expensiveProduct->id, $productsByNewest->last()->id);

        // Test stock quantity ordering
        $productsByStock = Product::where('shop_id', $shop->id)
            ->orderBy('stock_quantity', 'desc')
            ->get();
        
        $this->assertEquals($expensiveProduct->id, $productsByStock->first()->id);
        $this->assertEquals($cheapProduct->id, $productsByStock->last()->id);
    }

    /**
     * Property: Product search functionality
     * For any search term, products should be filtered by name and description.
     * **Validates: Requirements 5.4**
     */
    public function test_product_search_property()
    {
        for ($i = 0; $i < 30; $i++) {
            $this->runProductSearchProperty();
        }
    }

    private function runProductSearchProperty()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = $this->createValidShop($vendor);
        $subcategory = $this->createValidSubcategory();

        $searchTerm = 'Samsung';
        
        // Create products with and without search term
        $matchingProduct = Product::create([
            'shop_id' => $shop->id,
            'subcategory_id' => $subcategory->id,
            'name' => 'Samsung Galaxy Phone',
            'description' => 'Latest smartphone technology',
            'price' => 800.00,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        $nonMatchingProduct = Product::create([
            'shop_id' => $shop->id,
            'subcategory_id' => $subcategory->id,
            'name' => 'iPhone 15',
            'description' => 'Apple smartphone',
            'price' => 900.00,
            'stock_quantity' => 5,
            'is_active' => true,
        ]);

        $descriptionMatchingProduct = Product::create([
            'shop_id' => $shop->id,
            'subcategory_id' => $subcategory->id,
            'name' => 'Android Phone',
            'description' => 'Samsung technology inside',
            'price' => 600.00,
            'stock_quantity' => 15,
            'is_active' => true,
        ]);

        // Test name-based search
        $nameSearchResults = Product::where('name', 'LIKE', "%{$searchTerm}%")->get();
        $this->assertTrue($nameSearchResults->contains($matchingProduct));
        $this->assertFalse($nameSearchResults->contains($nonMatchingProduct));
        $this->assertFalse($nameSearchResults->contains($descriptionMatchingProduct));

        // Test description-based search
        $descriptionSearchResults = Product::where('description', 'LIKE', "%{$searchTerm}%")->get();
        $this->assertTrue($descriptionSearchResults->contains($descriptionMatchingProduct));
        $this->assertFalse($descriptionSearchResults->contains($matchingProduct));
        $this->assertFalse($descriptionSearchResults->contains($nonMatchingProduct));

        // Test combined search (name OR description)
        $combinedSearchResults = Product::where(function ($query) use ($searchTerm) {
            $query->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%");
        })->get();
        
        $this->assertTrue($combinedSearchResults->contains($matchingProduct));
        $this->assertTrue($combinedSearchResults->contains($descriptionMatchingProduct));
        $this->assertFalse($combinedSearchResults->contains($nonMatchingProduct));
    }

    /**
     * Property: Product category hierarchy filtering
     * For any category or subcategory filter, products should be filtered correctly.
     * **Validates: Requirements 5.4**
     */
    public function test_category_hierarchy_filtering_property()
    {
        for ($i = 0; $i < 30; $i++) {
            $this->runCategoryHierarchyFilteringProperty();
        }
    }

    private function runCategoryHierarchyFilteringProperty()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = $this->createValidShop($vendor);

        // Create category hierarchy
        $electronicsCategory = $this->createCategory('Electronics');
        $fashionCategory = $this->createCategory('Fashion');
        
        $phonesSubcategory = $this->createSubcategory($electronicsCategory, 'Phones');
        $laptopsSubcategory = $this->createSubcategory($electronicsCategory, 'Laptops');
        $shirtsSubcategory = $this->createSubcategory($fashionCategory, 'Shirts');

        // Create products in different subcategories
        $phoneProduct = $this->createSpecificProduct($shop, $phonesSubcategory, true, 10, 800.00);
        $laptopProduct = $this->createSpecificProduct($shop, $laptopsSubcategory, true, 5, 1200.00);
        $shirtProduct = $this->createSpecificProduct($shop, $shirtsSubcategory, true, 20, 50.00);

        // Test subcategory filtering
        $phoneProducts = Product::where('subcategory_id', $phonesSubcategory->id)->get();
        $this->assertTrue($phoneProducts->contains($phoneProduct));
        $this->assertFalse($phoneProducts->contains($laptopProduct));
        $this->assertFalse($phoneProducts->contains($shirtProduct));

        // Test category filtering through subcategory relationship
        $electronicsProducts = Product::whereHas('subcategory', function ($query) use ($electronicsCategory) {
            $query->where('category_id', $electronicsCategory->id);
        })->get();
        
        $this->assertTrue($electronicsProducts->contains($phoneProduct));
        $this->assertTrue($electronicsProducts->contains($laptopProduct));
        $this->assertFalse($electronicsProducts->contains($shirtProduct));

        // Test fashion category filtering
        $fashionProducts = Product::whereHas('subcategory', function ($query) use ($fashionCategory) {
            $query->where('category_id', $fashionCategory->id);
        })->get();
        
        $this->assertTrue($fashionProducts->contains($shirtProduct));
        $this->assertFalse($fashionProducts->contains($phoneProduct));
        $this->assertFalse($fashionProducts->contains($laptopProduct));
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

    private function createCategory(string $name): Category
    {
        $timestamp = microtime(true) * 1000;
        $random = rand(100000, 999999);

        return Category::create([
            'name' => $name,
            'slug' => strtolower($name) . '-' . $timestamp . '-' . $random,
        ]);
    }

    private function createSubcategory(Category $category, string $name): Subcategory
    {
        $timestamp = microtime(true) * 1000;
        $random = rand(100000, 999999);

        return Subcategory::create([
            'category_id' => $category->id,
            'name' => $name,
            'slug' => strtolower($name) . '-' . $timestamp . '-' . $random,
        ]);
    }

    private function createProductWithStatus(Shop $shop, Subcategory $subcategory, bool $isActive): Product
    {
        return Product::create([
            'shop_id' => $shop->id,
            'subcategory_id' => $subcategory->id,
            'name' => $this->generateRandomProductName(),
            'description' => 'Test product description',
            'price' => rand(50, 500),
            'stock_quantity' => rand(1, 50),
            'is_active' => $isActive,
        ]);
    }

    private function createProductWithStock(Shop $shop, Subcategory $subcategory, int $stockQuantity): Product
    {
        return Product::create([
            'shop_id' => $shop->id,
            'subcategory_id' => $subcategory->id,
            'name' => $this->generateRandomProductName(),
            'description' => 'Test product description',
            'price' => rand(50, 500),
            'stock_quantity' => $stockQuantity,
            'is_active' => true,
        ]);
    }

    private function createProductWithDiscount(Shop $shop, Subcategory $subcategory): Product
    {
        return Product::create([
            'shop_id' => $shop->id,
            'subcategory_id' => $subcategory->id,
            'name' => $this->generateRandomProductName(),
            'description' => 'Test product description',
            'price' => 200.00,
            'discount_type' => 'percent',
            'discount_value' => 20,
            'stock_quantity' => rand(1, 50),
            'is_active' => true,
        ]);
    }

    private function createProductWithoutDiscount(Shop $shop, Subcategory $subcategory): Product
    {
        return Product::create([
            'shop_id' => $shop->id,
            'subcategory_id' => $subcategory->id,
            'name' => $this->generateRandomProductName(),
            'description' => 'Test product description',
            'price' => 150.00,
            'stock_quantity' => rand(1, 50),
            'is_active' => true,
        ]);
    }

    private function createSpecificProduct(Shop $shop, Subcategory $subcategory, bool $isActive, int $stock, float $price): Product
    {
        return Product::create([
            'shop_id' => $shop->id,
            'subcategory_id' => $subcategory->id,
            'name' => $this->generateRandomProductName(),
            'description' => 'Test product description',
            'price' => $price,
            'stock_quantity' => $stock,
            'is_active' => $isActive,
        ]);
    }

    private function generateRandomProductName(): string
    {
        $products = [
            'Samsung Galaxy',
            'iPhone Pro',
            'MacBook Air',
            'Dell Laptop',
            'Sony Headphones',
            'Nike Shoes',
            'Adidas Shirt',
            'Canon Camera',
        ];

        return $products[array_rand($products)] . ' ' . rand(1, 1000);
    }

    private function generateRandomShopName(): string
    {
        $names = [
            'Tech Store',
            'Fashion Hub',
            'Electronics World',
            'Sports Corner',
            'Book Shop',
        ];

        return $names[array_rand($names)] . ' ' . rand(1, 1000);
    }

    private function generateRandomCategory(): string
    {
        $categories = ['Electronics', 'Fashion', 'Sports', 'Books', 'Home'];
        return $categories[array_rand($categories)];
    }

    private function generateRandomSubcategory(): string
    {
        $subcategories = ['Phones', 'Laptops', 'Shoes', 'Shirts', 'Bags'];
        return $subcategories[array_rand($subcategories)];
    }

    private function generateRandomGovernorate(): string
    {
        $governorates = ['Cairo', 'Giza', 'Alexandria', 'Qalyubia'];
        return $governorates[array_rand($governorates)];
    }

    private function generateRandomCity(): string
    {
        $cities = ['Cairo', 'Alexandria', 'Giza', 'Mansoura'];
        return $cities[array_rand($cities)];
    }

    private function generateRandomArea(): string
    {
        $areas = ['Downtown', 'Nasr City', 'Maadi', 'Zamalek'];
        return $areas[array_rand($areas)];
    }

    private function generateValidLatitude(): float
    {
        return round(22 + (rand(0, 1000) / 100), 6);
    }

    private function generateValidLongitude(): float
    {
        return round(25 + (rand(0, 1000) / 100), 6);
    }
}