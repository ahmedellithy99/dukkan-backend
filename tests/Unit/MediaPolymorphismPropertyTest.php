<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Models\Location;
use App\Models\Subcategory;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Property-Based Test for Media Management and Polymorphism
 * Feature: marketplace-platform, Property 13: Media Management and Polymorphism
 * Validates: Requirements 7.1, 7.2, 7.4
 */
class MediaPolymorphismPropertyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /**
     * Property 13: Media Management and Polymorphism
     * For any media upload, the system should store files correctly, maintain polymorphic relationships, and validate file types.
     * Validates: Requirements 7.1, 7.2, 7.4
     */
    public function test_media_polymorphism_property()
    {
        // Run property test with 10 iterations for initial testing
        for ($i = 0; $i < 10; $i++) {
            $this->runMediaPolymorphismProperty();
        }
    }

    private function runMediaPolymorphismProperty()
    {
        // Create necessary entities
        $user = User::factory()->create(['role' => 'vendor']);
        $location = Location::factory()->create();
        $shop = Shop::factory()->create([
            'owner_id' => $user->id,
            'location_id' => $location->id,
        ]);

        // Test shop media (logo)
        $logoFile = $this->generateRandomImage('logo');
        $shop->addMedia($logoFile)->toMediaCollection('logo');

        // Verify shop media was stored
        $this->assertEquals(1, $shop->getMedia('logo')->count());
        $shopMedia = $shop->getFirstMedia('logo');
        $this->assertNotNull($shopMedia);
        $this->assertEquals('logo', $shopMedia->collection_name);
        $this->assertEquals(Shop::class, $shopMedia->model_type);
        $this->assertEquals($shop->id, $shopMedia->model_id);

        // Test product media
        $category = Category::factory()->create();
        $subcategory = Subcategory::factory()->create(['category_id' => $category->id]);
        $product = Product::factory()->create([
            'shop_id' => $shop->id,
            'subcategory_id' => $subcategory->id,
        ]);

        $mainImage = $this->generateRandomImage('product_main');
        $product->addMedia($mainImage)->toMediaCollection('main_image');

        $secondaryImage = $this->generateRandomImage('product_secondary');
        $product->addMedia($secondaryImage)->toMediaCollection('secondary_image');

        // Verify product media was stored
        $this->assertEquals(1, $product->getMedia('main_image')->count());
        $this->assertEquals(1, $product->getMedia('secondary_image')->count());

        $productMainMedia = $product->getFirstMedia('main_image');
        $this->assertNotNull($productMainMedia);
        $this->assertEquals('main_image', $productMainMedia->collection_name);
        $this->assertEquals(Product::class, $productMainMedia->model_type);
        $this->assertEquals($product->id, $productMainMedia->model_id);

        // Verify polymorphic relationships work correctly
        $this->assertInstanceOf(Shop::class, $shopMedia->model);
        $this->assertInstanceOf(Product::class, $productMainMedia->model);
    }

    /**
     * Property: Media type categorization
     * For any media type (logo, banner, product_image), the system should categorize correctly.
     * Validates: Requirement 7.2
     */
    public function test_media_type_categorization_property()
    {
        for ($i = 0; $i < 10; $i++) {
            $this->runMediaTypeCategorization();
        }
    }

    private function runMediaTypeCategorization()
    {
        // Clear any existing media
        \Spatie\MediaLibrary\MediaCollections\Models\Media::truncate();
        
        $user = User::factory()->create(['role' => 'vendor']);
        $location = Location::factory()->create();
        $shop = Shop::factory()->create([
            'owner_id' => $user->id,
            'location_id' => $location->id,
        ]);

        // Test shop media collection (logo)
        $file = $this->generateRandomImage('logo');
        $shop->addMedia($file)->toMediaCollection('logo');
        
        // Refresh the shop to get updated media
        $shop->refresh();
        
        $media = $shop->getFirstMedia('logo');
        $this->assertNotNull($media, 'Shop logo media should not be null');
        $this->assertEquals('logo', $media->collection_name);

        // Test product media collections - create separate products for each collection
        $category = Category::factory()->create();
        $subcategory = Subcategory::factory()->create(['category_id' => $category->id]);
        
        // Test main_image collection
        $product1 = Product::factory()->create([
            'shop_id' => $shop->id,
            'subcategory_id' => $subcategory->id,
        ]);
        
        $mainImageFile = $this->generateRandomImage('main_image');
        $product1->addMedia($mainImageFile)->toMediaCollection('main_image');
        
        // Refresh the product to get updated media
        $product1->refresh();
        
        $mainMedia = $product1->getFirstMedia('main_image');
        $this->assertNotNull($mainMedia, 'Product main_image media should not be null');
        $this->assertEquals('main_image', $mainMedia->collection_name);
        
        // Test secondary_image collection
        $product2 = Product::factory()->create([
            'shop_id' => $shop->id,
            'subcategory_id' => $subcategory->id,
        ]);
        
        $secondaryImageFile = $this->generateRandomImage('secondary_image');
        $product2->addMedia($secondaryImageFile)->toMediaCollection('secondary_image');
        
        // Refresh the product to get updated media
        $product2->refresh();
        
        $secondaryMedia = $product2->getFirstMedia('secondary_image');
        $this->assertNotNull($secondaryMedia, 'Product secondary_image media should not be null');
        $this->assertEquals('secondary_image', $secondaryMedia->collection_name);
    }

    /**
     * Property: File type validation
     * For any valid image file type, the system should accept it.
     * Validates: Requirement 7.4
     */
    public function test_file_type_validation_property()
    {
        for ($i = 0; $i < 10; $i++) {
            $this->runFileTypeValidation();
        }
    }

    private function runFileTypeValidation()
    {
        $user = User::factory()->create(['role' => 'vendor']);
        $location = Location::factory()->create();
        $shop = Shop::factory()->create([
            'owner_id' => $user->id,
            'location_id' => $location->id,
        ]);

        // Test valid image types
        $validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        $mimeType = $validTypes[array_rand($validTypes)];
        
        $extension = match($mimeType) {
            'image/jpeg', 'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        };

        $file = UploadedFile::fake()->image('test.'.$extension)->mimeType($mimeType);
        
        // Should not throw exception for valid types
        $shop->addMedia($file)->toMediaCollection('logo');
        
        $media = $shop->getFirstMedia('logo');
        $this->assertNotNull($media);
        $this->assertContains($media->mime_type, $validTypes);
    }

    /**
     * Property: File size validation
     * For any file within size limits, the system should accept it.
     * Validates: Requirement 7.4
     */
    public function test_file_size_validation_property()
    {
        for ($i = 0; $i < 10; $i++) {
            $this->runFileSizeValidation();
        }
    }

    private function runFileSizeValidation()
    {
        $user = User::factory()->create(['role' => 'vendor']);
        $location = Location::factory()->create();
        $shop = Shop::factory()->create([
            'owner_id' => $user->id,
            'location_id' => $location->id,
        ]);

        // Generate file with random size within limits (1KB to 5MB)
        $sizeInKB = rand(1, 5000);
        $file = UploadedFile::fake()->image('test.jpg')->size($sizeInKB);
        
        // Should not throw exception for valid sizes
        $shop->addMedia($file)->toMediaCollection('logo');
        
        $media = $shop->getFirstMedia('logo');
        $this->assertNotNull($media);
        $this->assertGreaterThan(0, $media->size);
    }

    /**
     * Property: Single file collection constraint
     * For single file collections, adding a new file should replace the old one.
     * Validates: Requirement 7.1
     */
    public function test_single_file_collection_property()
    {
        for ($i = 0; $i < 10; $i++) {
            $this->runSingleFileCollection();
        }
    }

    private function runSingleFileCollection()
    {
        $user = User::factory()->create(['role' => 'vendor']);
        $location = Location::factory()->create();
        $shop = Shop::factory()->create([
            'owner_id' => $user->id,
            'location_id' => $location->id,
        ]);

        // Add first logo
        $firstLogo = $this->generateRandomImage('logo1');
        $shop->addMedia($firstLogo)->toMediaCollection('logo');
        
        $this->assertEquals(1, $shop->getMedia('logo')->count());
        $firstMediaId = $shop->getFirstMedia('logo')->id;

        // Add second logo (should replace first)
        $secondLogo = $this->generateRandomImage('logo2');
        $shop->addMedia($secondLogo)->toMediaCollection('logo');
        
        // Should still have only 1 logo
        $this->assertEquals(1, $shop->getMedia('logo')->count());
        
        // Should be a different media item
        $secondMediaId = $shop->getFirstMedia('logo')->id;
        $this->assertNotEquals($firstMediaId, $secondMediaId);
    }

    /**
     * Helper method to generate random test images
     */
    private function generateRandomImage(string $name): UploadedFile
    {
        $extensions = ['jpg', 'png', 'webp'];
        $extension = $extensions[array_rand($extensions)];
        
        $width = rand(100, 2000);
        $height = rand(100, 2000);
        
        return UploadedFile::fake()->image($name.'.'.$extension, $width, $height);
    }
}
