<?php

namespace Tests\Unit;

use App\Filters\Website\ProductFilter;
use App\Filters\Website\ShopFilter;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\City;
use App\Models\Governorate;
use App\Models\Location;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Property-Based Test for Search and Discovery Functionality
 * Feature: marketplace-platform, Property 17: Search and Discovery Functionality
 * Validates: Requirements 9.1, 9.2, 9.3, 9.4, 9.5
 */
class SearchAndFilteringPropertyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Property 17: Search and Discovery Functionality
     * For any search query or filter combination, the system should return accurate results
     * matching text, location, category, and attribute criteria.
     * **Validates: Requirements 9.1, 9.2, 9.3, 9.4, 9.5**
     */
    public function test_text_search_matches_product_names_and_descriptions()
    {
        // Run property test with 30 iterations - Requirement 9.1
        for ($i = 0; $i < 30; $i++) {
            $this->runTextSearchProperty();
        }
    }

    private function runTextSearchProperty()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = $this->createValidShop($vendor);
        $subcategory = $this->createValidSubcategory();

        $searchTerms = ['Samsung', 'iPhone', 'Laptop', 'Shoes', 'Shirt'];
        $searchTerm = $searchTerms[array_rand($searchTerms)];

        // Create product with search term in name
        $nameMatchProduct = Product::create([
            'shop_id' => $shop->id,
            'subcategory_id' => $subcategory->id,
            'name' => $searchTerm . ' Product ' . rand(1, 1000),
            'description' => 'Generic description',
            'price' => rand(50, 500),
            'stock_quantity' => rand(1, 50),
            'is_active' => true,
        ]);

        // Create product with search term in description
        $descriptionMatchProduct = Product::create([
            'shop_id' => $shop->id,
            'subcategory_id' => $subcategory->id,
            'name' => 'Generic Product ' . rand(1, 1000),
            'description' => 'This is a ' . $searchTerm . ' product',
            'price' => rand(50, 500),
            'stock_quantity' => rand(1, 50),
            'is_active' => true,
        ]);

        // Create product without search term
        $nonMatchProduct = Product::create([
            'shop_id' => $shop->id,
            'subcategory_id' => $subcategory->id,
            'name' => 'Different Product ' . rand(1, 1000),
            'description' => 'Completely different description',
            'price' => rand(50, 500),
            'stock_quantity' => rand(1, 50),
            'is_active' => true,
        ]);

        // Test search using ProductFilter
        $request = new Request(['search' => $searchTerm]);
        $filter = new ProductFilter($request);
        
        $results = Product::filter($filter)->get();

        // Requirement 9.1: Text search should match names and descriptions
        $this->assertTrue($results->contains($nameMatchProduct));
        $this->assertTrue($results->contains($descriptionMatchProduct));
        $this->assertFalse($results->contains($nonMatchProduct));
    }

    /**
     * Property: Category-based filtering returns products from selected categories
     * **Validates: Requirements 9.2**
     */
    public function test_category_filtering_returns_correct_products()
    {
        for ($i = 0; $i < 30; $i++) {
            $this->runCategoryFilteringProperty();
        }
    }

    private function runCategoryFilteringProperty()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = $this->createValidShop($vendor);

        // Create categories and subcategories
        $electronicsCategory = $this->createCategory('Electronics');
        $fashionCategory = $this->createCategory('Fashion');
        
        $phonesSubcategory = $this->createSubcategory($electronicsCategory, 'Phones');
        $shirtsSubcategory = $this->createSubcategory($fashionCategory, 'Shirts');

        // Create products in different categories
        $phoneProduct = $this->createProduct($shop, $phonesSubcategory);
        $shirtProduct = $this->createProduct($shop, $shirtsSubcategory);

        // Test category filtering
        $request = new Request(['category_id' => $electronicsCategory->id]);
        $filter = new ProductFilter($request);
        
        $results = Product::filter($filter)->get();

        // Requirement 9.2: Category filter should return products from selected category
        $this->assertTrue($results->contains($phoneProduct));
        $this->assertFalse($results->contains($shirtProduct));

        // Test subcategory filtering
        $request = new Request(['subcategory_id' => $phonesSubcategory->id]);
        $filter = new ProductFilter($request);
        
        $results = Product::filter($filter)->get();

        // Requirement 9.2: Subcategory filter should return products from selected subcategory
        $this->assertTrue($results->contains($phoneProduct));
        $this->assertFalse($results->contains($shirtProduct));
    }

    /**
     * Property: Attribute-based filtering returns products with selected attributes
     * **Validates: Requirements 9.3**
     */
    public function test_attribute_filtering_returns_matching_products()
    {
        for ($i = 0; $i < 30; $i++) {
            $this->runAttributeFilteringProperty();
        }
    }

    private function runAttributeFilteringProperty()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = $this->createValidShop($vendor);
        $subcategory = $this->createValidSubcategory();

        // Create attributes and values
        $colorAttribute = Attribute::create(['name' => 'Color_' . rand(1, 10000), 'slug' => 'color-' . rand(1, 10000)]);
        $redValue = AttributeValue::create(['attribute_id' => $colorAttribute->id, 'value' => 'Red', 'slug' => 'red-' . rand(1, 10000)]);
        $blueValue = AttributeValue::create(['attribute_id' => $colorAttribute->id, 'value' => 'Blue', 'slug' => 'blue-' . rand(1, 10000)]);

        // Create products with different attributes
        $redProduct = $this->createProduct($shop, $subcategory);
        $redProduct->attributeValues()->attach($redValue->id);

        $blueProduct = $this->createProduct($shop, $subcategory);
        $blueProduct->attributeValues()->attach($blueValue->id);

        $noAttributeProduct = $this->createProduct($shop, $subcategory);

        // Test attribute filtering
        $request = new Request(['attributes' => ['color' => [$redValue->id]]]);
        $filter = new ProductFilter($request);
        
        $results = Product::filter($filter)->get();

        // Requirement 9.3: Attribute filter should return products with selected attribute values
        $this->assertTrue($results->contains($redProduct));
        $this->assertFalse($results->contains($blueProduct));
        $this->assertFalse($results->contains($noAttributeProduct));
    }

    /**
     * Property: Multiple filters can be combined
     * **Validates: Requirements 9.4**
     */
    public function test_multiple_filters_can_be_combined()
    {
        for ($i = 0; $i < 20; $i++) {
            $this->runMultipleFiltersProperty();
        }
    }

    private function runMultipleFiltersProperty()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = $this->createValidShop($vendor);
        
        $category = $this->createCategory('Electronics');
        $subcategory = $this->createSubcategory($category, 'Phones');

        // Create products with different characteristics
        $matchingProduct = Product::create([
            'shop_id' => $shop->id,
            'subcategory_id' => $subcategory->id,
            'name' => 'Samsung Galaxy Phone',
            'description' => 'Latest smartphone',
            'price' => 500.00,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        $wrongPriceProduct = Product::create([
            'shop_id' => $shop->id,
            'subcategory_id' => $subcategory->id,
            'name' => 'Samsung Budget Phone',
            'description' => 'Affordable smartphone',
            'price' => 100.00,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        $wrongCategoryProduct = Product::create([
            'shop_id' => $shop->id,
            'subcategory_id' => $this->createValidSubcategory()->id,
            'name' => 'Samsung TV',
            'description' => 'Smart TV',
            'price' => 600.00,
            'stock_quantity' => 5,
            'is_active' => true,
        ]);

        // Test combining search, category, and price filters
        $request = new Request([
            'search' => 'Samsung',
            'subcategory_id' => $subcategory->id,
            'min_price' => 400,
            'max_price' => 700,
        ]);
        $filter = new ProductFilter($request);
        
        $results = Product::filter($filter)->get();

        // Requirement 9.4: Multiple filters should work together
        $this->assertTrue($results->contains($matchingProduct));
        $this->assertFalse($results->contains($wrongPriceProduct));
        $this->assertFalse($results->contains($wrongCategoryProduct));
    }

    /**
     * Property: Sorting options work correctly
     * **Validates: Requirements 9.4**
     */
    public function test_sorting_options_work_correctly()
    {
        for ($i = 0; $i < 20; $i++) {
            $this->runSortingProperty();
        }
    }

    private function runSortingProperty()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = $this->createValidShop($vendor);
        $subcategory = $this->createValidSubcategory();

        // Create products with different prices
        $cheapProduct = $this->createProductWithPrice($shop, $subcategory, 100.00);
        $mediumProduct = $this->createProductWithPrice($shop, $subcategory, 300.00);
        $expensiveProduct = $this->createProductWithPrice($shop, $subcategory, 500.00);

        // Test ascending price sort
        $request = new Request(['sort' => 'price']);
        $filter = new ProductFilter($request);
        
        $results = Product::where('shop_id', $shop->id)->filter($filter)->get();

        $this->assertEquals($cheapProduct->id, $results->first()->id);
        $this->assertEquals($expensiveProduct->id, $results->last()->id);

        // Test descending price sort
        $request = new Request(['sort' => '-price']);
        $filter = new ProductFilter($request);
        
        $results = Product::where('shop_id', $shop->id)->filter($filter)->get();

        $this->assertEquals($expensiveProduct->id, $results->first()->id);
        $this->assertEquals($cheapProduct->id, $results->last()->id);
    }

    /**
     * Property: Location-based filtering works correctly
     * **Validates: Requirements 9.1, 9.2**
     */
    public function test_location_based_filtering()
    {
        for ($i = 0; $i < 20; $i++) {
            $this->runLocationFilteringProperty();
        }
    }

    private function runLocationFilteringProperty()
    {
        $vendor1 = User::factory()->create(['role' => 'vendor']);
        $vendor2 = User::factory()->create(['role' => 'vendor']);

        // Create locations in different cities
        $location1 = $this->createValidLocation();
        $location2 = $this->createValidLocation();

        $shop1 = $this->createShopWithLocation($vendor1, $location1);
        $shop2 = $this->createShopWithLocation($vendor2, $location2);

        $subcategory = $this->createValidSubcategory();

        $product1 = $this->createProduct($shop1, $subcategory);
        $product2 = $this->createProduct($shop2, $subcategory);

        // Test city-based filtering
        $request = new Request(['city_id' => $location1->city_id]);
        $filter = new ProductFilter($request);
        
        $results = Product::filter($filter)->get();

        // Products should be filtered by city
        $this->assertTrue($results->contains($product1));
        $this->assertFalse($results->contains($product2));
    }

    /**
     * Property: Shop filtering works correctly
     * **Validates: Requirements 9.1, 9.2**
     */
    public function test_shop_filtering()
    {
        for ($i = 0; $i < 20; $i++) {
            $this->runShopFilteringProperty();
        }
    }

    private function runShopFilteringProperty()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        
        // Create locations in same city
        $location1 = $this->createValidLocation();
        $location2 = $this->createLocationInSameCity($location1);

        $shop1 = $this->createShopWithLocation($vendor, $location1);
        $shop2 = $this->createShopWithLocation($vendor, $location2);

        // Test shop search
        $request = new Request(['search' => $shop1->name]);
        $filter = new ShopFilter($request);
        
        $results = Shop::filter($filter)->get();

        $this->assertTrue($results->contains($shop1));
        $this->assertFalse($results->contains($shop2));

        // Test city filtering for shops
        $request = new Request(['city_id' => $location1->city_id]);
        $filter = new ShopFilter($request);
        
        $results = Shop::filter($filter)->get();

        $this->assertTrue($results->contains($shop1));
        $this->assertTrue($results->contains($shop2));
    }

    /**
     * Property: Price range filtering works correctly
     * **Validates: Requirements 9.4**
     */
    public function test_price_range_filtering()
    {
        for ($i = 0; $i < 20; $i++) {
            $this->runPriceRangeFilteringProperty();
        }
    }

    private function runPriceRangeFilteringProperty()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = $this->createValidShop($vendor);
        $subcategory = $this->createValidSubcategory();

        $cheapProduct = $this->createProductWithPrice($shop, $subcategory, 50.00);
        $mediumProduct = $this->createProductWithPrice($shop, $subcategory, 150.00);
        $expensiveProduct = $this->createProductWithPrice($shop, $subcategory, 300.00);

        // Test min price filter
        $request = new Request(['min_price' => 100]);
        $filter = new ProductFilter($request);
        
        $results = Product::filter($filter)->get();

        $this->assertFalse($results->contains($cheapProduct));
        $this->assertTrue($results->contains($mediumProduct));
        $this->assertTrue($results->contains($expensiveProduct));

        // Test max price filter
        $request = new Request(['max_price' => 200]);
        $filter = new ProductFilter($request);
        
        $results = Product::filter($filter)->get();

        $this->assertTrue($results->contains($cheapProduct));
        $this->assertTrue($results->contains($mediumProduct));
        $this->assertFalse($results->contains($expensiveProduct));

        // Test price range filter
        $request = new Request(['min_price' => 100, 'max_price' => 200]);
        $filter = new ProductFilter($request);
        
        $results = Product::filter($filter)->get();

        $this->assertFalse($results->contains($cheapProduct));
        $this->assertTrue($results->contains($mediumProduct));
        $this->assertFalse($results->contains($expensiveProduct));
    }

    /**
     * Property: Stock filtering works correctly
     * **Validates: Requirements 9.4**
     */
    public function test_stock_filtering()
    {
        for ($i = 0; $i < 20; $i++) {
            $this->runStockFilteringProperty();
        }
    }

    private function runStockFilteringProperty()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = $this->createValidShop($vendor);
        $subcategory = $this->createValidSubcategory();

        $inStockProduct = $this->createProductWithStock($shop, $subcategory, 10);
        $outOfStockProduct = $this->createProductWithStock($shop, $subcategory, 0);

        // Test in_stock filter
        $request = new Request(['in_stock' => true]);
        $filter = new ProductFilter($request);
        
        $results = Product::filter($filter)->get();

        $this->assertTrue($results->contains($inStockProduct));
        $this->assertFalse($results->contains($outOfStockProduct));

        // Test out of stock filter
        $request = new Request(['in_stock' => false]);
        $filter = new ProductFilter($request);
        
        $results = Product::filter($filter)->get();

        $this->assertFalse($results->contains($inStockProduct));
        $this->assertTrue($results->contains($outOfStockProduct));
    }

    /**
     * Property: Discount filtering works correctly
     * **Validates: Requirements 9.4**
     */
    public function test_discount_filtering()
    {
        for ($i = 0; $i < 20; $i++) {
            $this->runDiscountFilteringProperty();
        }
    }

    private function runDiscountFilteringProperty()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = $this->createValidShop($vendor);
        $subcategory = $this->createValidSubcategory();

        $discountedProduct = Product::create([
            'shop_id' => $shop->id,
            'subcategory_id' => $subcategory->id,
            'name' => 'Discounted Product ' . rand(1, 1000),
            'description' => 'Product with discount',
            'price' => 200.00,
            'discount_type' => 'percent',
            'discount_value' => 20,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        $regularProduct = Product::create([
            'shop_id' => $shop->id,
            'subcategory_id' => $subcategory->id,
            'name' => 'Regular Product ' . rand(1, 1000),
            'description' => 'Product without discount',
            'price' => 200.00,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        // Test on_discount filter
        $request = new Request(['on_discount' => true]);
        $filter = new ProductFilter($request);
        
        $results = Product::filter($filter)->get();

        $this->assertTrue($results->contains($discountedProduct));
        $this->assertFalse($results->contains($regularProduct));

        // Test not on discount filter
        $request = new Request(['on_discount' => false]);
        $filter = new ProductFilter($request);
        
        $results = Product::filter($filter)->get();

        $this->assertFalse($results->contains($discountedProduct));
        $this->assertTrue($results->contains($regularProduct));
    }

    // Helper methods

    private function createValidShop(User $vendor): Shop
    {
        $location = $this->createValidLocation();
        
        return Shop::create([
            'owner_id' => $vendor->id,
            'location_id' => $location->id,
            'name' => 'Shop ' . rand(1, 100000),
            'description' => 'Test shop description',
            'whatsapp_number' => '+201234567890',
            'phone_number' => '+201234567890',
            'is_active' => true,
        ]);
    }

    private function createShopWithLocation(User $vendor, Location $location): Shop
    {
        return Shop::create([
            'owner_id' => $vendor->id,
            'location_id' => $location->id,
            'name' => 'Shop ' . rand(1, 100000),
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
            'name' => 'Governorate ' . $timestamp . $random,
            'slug' => 'gov-' . $timestamp . '-' . $random,
        ]);

        $city = City::create([
            'governorate_id' => $governorate->id,
            'name' => 'City ' . $timestamp . $random,
            'slug' => 'city-' . $timestamp . '-' . $random,
        ]);

        return Location::create([
            'city_id' => $city->id,
            'area' => 'Area_' . $timestamp . '_' . $random,
            'latitude' => round(22 + (rand(0, 1000) / 100), 6),
            'longitude' => round(25 + (rand(0, 1000) / 100), 6),
        ]);
    }

    private function createLocationInSameCity(Location $existingLocation): Location
    {
        $timestamp = microtime(true) * 1000;
        $random = rand(100000, 999999);

        return Location::create([
            'city_id' => $existingLocation->city_id,
            'area' => 'Area_' . $timestamp . '_' . $random,
            'latitude' => round(22 + (rand(0, 1000) / 100), 6),
            'longitude' => round(25 + (rand(0, 1000) / 100), 6),
        ]);
    }

    private function createValidSubcategory(): Subcategory
    {
        $timestamp = microtime(true) * 1000;
        $random = rand(100000, 999999);

        $category = Category::create([
            'name' => 'Category ' . $timestamp . $random,
            'slug' => 'cat-' . $timestamp . '-' . $random,
        ]);

        return Subcategory::create([
            'category_id' => $category->id,
            'name' => 'Subcategory ' . $timestamp . $random,
            'slug' => 'subcat-' . $timestamp . '-' . $random,
        ]);
    }

    private function createCategory(string $name): Category
    {
        $timestamp = microtime(true) * 1000;
        $random = rand(100000, 999999);

        return Category::create([
            'name' => $name . ' ' . $timestamp . $random,
            'slug' => strtolower($name) . '-' . $timestamp . '-' . $random,
        ]);
    }

    private function createSubcategory(Category $category, string $name): Subcategory
    {
        $timestamp = microtime(true) * 1000;
        $random = rand(100000, 999999);

        return Subcategory::create([
            'category_id' => $category->id,
            'name' => $name . ' ' . $timestamp . $random,
            'slug' => strtolower($name) . '-' . $timestamp . '-' . $random,
        ]);
    }

    private function createProduct(Shop $shop, Subcategory $subcategory): Product
    {
        return Product::create([
            'shop_id' => $shop->id,
            'subcategory_id' => $subcategory->id,
            'name' => 'Product ' . rand(1, 100000),
            'description' => 'Test product description',
            'price' => rand(50, 500),
            'stock_quantity' => rand(1, 50),
            'is_active' => true,
        ]);
    }

    private function createProductWithPrice(Shop $shop, Subcategory $subcategory, float $price): Product
    {
        return Product::create([
            'shop_id' => $shop->id,
            'subcategory_id' => $subcategory->id,
            'name' => 'Product ' . rand(1, 100000),
            'description' => 'Test product description',
            'price' => $price,
            'stock_quantity' => rand(1, 50),
            'is_active' => true,
        ]);
    }

    private function createProductWithStock(Shop $shop, Subcategory $subcategory, int $stock): Product
    {
        return Product::create([
            'shop_id' => $shop->id,
            'subcategory_id' => $subcategory->id,
            'name' => 'Product ' . rand(1, 100000),
            'description' => 'Test product description',
            'price' => rand(50, 500),
            'stock_quantity' => $stock,
            'is_active' => true,
        ]);
    }
}
