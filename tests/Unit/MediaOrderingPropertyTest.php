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
 * Property-Based Test for Media Ordering and Cleanup
 * Feature: marketplace-platform, Property 14: Media Ordering and Cleanup
 * Validates: Requirements 7.3, 7.5
 */
class MediaOrderingPropertyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /**
     * Property 14: Media Ordering and Cleanup
     * For any multiple media uploads or entity deletion, the system should maintain display order and handle cascade cleanup.
     * Validates: Requirements 7.3, 7.5
     */
    public function test_media_ordering_property()
    {
        // Run property test with 10 iterations
        for ($i = 0; $i < 10; $i++) {
            $this->runMediaOrderingProperty();
        }
    }

    private function runMediaOrderingProperty()
    {
        // Clear any existing media
        \Spatie\MediaLibrary\MediaCollections\Models\Media::truncate();
        
        $user = User::factory()->create(['role' => 'vendor']);
        $location = Location::factory()->create();
        $shop = Shop::factory()->create([
            'owner_id' => $user->id,
            'location_id' => $location->id,
        ]);

        $category = Category::factory()->create();
        $subcategory = Subcategory::factory()->create(['category_id' => $category->id]);
        $product = Product::factory()->create([
            'shop_id' => $shop->id,
            'subcategory_id' => $subcategory->id,
        ]);

        // Add multiple images with explicit order
        $imageCount = rand(2, 5);
        $addedMedia = [];
        
        for ($j = 0; $j < $imageCount; $j++) {
            $file = $this->generateRandomImage("image_{$j}");
            $media = $product->addMedia($file)
                ->withCustomProperties(['order' => $j])
                ->toMediaCollection('images');
            $addedMedia[] = $media;
        }

        // Refresh product to get updated media
        $product->refresh();

        // Verify all media was added
        $allMedia = $product->getMedia('images');
        $this->assertEquals($imageCount, $allMedia->count());

        // Verify order is maintained through custom properties
        foreach ($addedMedia as $index => $media) {
            $retrievedMedia = $product->getMedia('images')->where('id', $media->id)->first();
            $this->assertNotNull($retrievedMedia);
            $this->assertEquals($index, $retrievedMedia->getCustomProperty('order'));
        }
    }

    /**
     * Property: Media cleanup on entity deletion
     * For any entity deletion, associated media should be cleaned up.
     * Validates: Requirement 7.5
     */
    public function test_media_cleanup_on_deletion_property()
    {
        for ($i = 0; $i < 10; $i++) {
            $this->runMediaCleanupProperty();
        }
    }

    private function runMediaCleanupProperty()
    {
        // Clear any existing media
        \Spatie\MediaLibrary\MediaCollections\Models\Media::truncate();
        
        $user = User::factory()->create(['role' => 'vendor']);
        $location = Location::factory()->create();
        $shop = Shop::factory()->create([
            'owner_id' => $user->id,
            'location_id' => $location->id,
        ]);

        // Add media to shop
        $logoFile = $this->generateRandomImage('logo');
        $shop->addMedia($logoFile)->toMediaCollection('logo');
        
        $shop->refresh();
        $shopMediaId = $shop->getFirstMedia('logo')->id;

        // Verify media exists
        $this->assertDatabaseHas('media', ['id' => $shopMediaId]);

        // Soft delete shop (Shop uses SoftDeletes)
        $shopId = $shop->id;
        $shop->delete();

        // Verify shop is soft deleted (still in database but deleted_at is set)
        $this->assertDatabaseHas('shops', ['id' => $shopId]);
        $this->assertSoftDeleted('shops', ['id' => $shopId]);

        // Force delete to test media cleanup
        $shop->forceDelete();

        // Verify shop is permanently deleted
        $this->assertDatabaseMissing('shops', ['id' => $shopId]);
        
        // Verify media is cleaned up after force delete
        $this->assertDatabaseMissing('media', ['id' => $shopMediaId]);
    }

    /**
     * Property: Product media cleanup on deletion
     * For any product deletion, associated media should be cleaned up.
     * Validates: Requirement 7.5
     */
    public function test_product_media_cleanup_property()
    {
        for ($i = 0; $i < 10; $i++) {
            $this->runProductMediaCleanupProperty();
        }
    }

    private function runProductMediaCleanupProperty()
    {
        // Clear any existing media
        \Spatie\MediaLibrary\MediaCollections\Models\Media::truncate();
        
        $user = User::factory()->create(['role' => 'vendor']);
        $location = Location::factory()->create();
        $shop = Shop::factory()->create([
            'owner_id' => $user->id,
            'location_id' => $location->id,
        ]);

        $category = Category::factory()->create();
        $subcategory = Subcategory::factory()->create(['category_id' => $category->id]);
        $product = Product::factory()->create([
            'shop_id' => $shop->id,
            'subcategory_id' => $subcategory->id,
        ]);

        // Add multiple media items
        $mainImageFile = $this->generateRandomImage('main');
        $product->addMedia($mainImageFile)->toMediaCollection('main_image');
        
        $secondaryImageFile = $this->generateRandomImage('secondary');
        $product->addMedia($secondaryImageFile)->toMediaCollection('secondary_image');

        $product->refresh();
        
        $mainMediaId = $product->getFirstMedia('main_image')->id;
        $secondaryMediaId = $product->getFirstMedia('secondary_image')->id;

        // Verify media exists
        $this->assertDatabaseHas('media', ['id' => $mainMediaId]);
        $this->assertDatabaseHas('media', ['id' => $secondaryMediaId]);

        // Delete product
        $product->delete();

        // Verify all media is cleaned up
        $this->assertDatabaseMissing('media', ['id' => $mainMediaId]);
        $this->assertDatabaseMissing('media', ['id' => $secondaryMediaId]);
    }

    /**
     * Property: Media ordering with custom properties
     * For any media with custom order properties, the order should be retrievable.
     * Validates: Requirement 7.3
     */
    public function test_custom_order_property()
    {
        for ($i = 0; $i < 10; $i++) {
            $this->runCustomOrderProperty();
        }
    }

    private function runCustomOrderProperty()
    {
        // Clear any existing media
        \Spatie\MediaLibrary\MediaCollections\Models\Media::truncate();
        
        $user = User::factory()->create(['role' => 'vendor']);
        $location = Location::factory()->create();
        $shop = Shop::factory()->create([
            'owner_id' => $user->id,
            'location_id' => $location->id,
        ]);

        $category = Category::factory()->create();
        $subcategory = Subcategory::factory()->create(['category_id' => $category->id]);
        $product = Product::factory()->create([
            'shop_id' => $shop->id,
            'subcategory_id' => $subcategory->id,
        ]);

        // Add media with random order values
        $orderValues = [3, 1, 4, 2];
        $mediaItems = [];
        
        foreach ($orderValues as $order) {
            $file = $this->generateRandomImage("image_order_{$order}");
            $media = $product->addMedia($file)
                ->withCustomProperties(['display_order' => $order])
                ->toMediaCollection('images');
            $mediaItems[] = ['media' => $media, 'order' => $order];
        }

        $product->refresh();

        // Retrieve media and verify custom order properties
        $allMedia = $product->getMedia('images');
        $this->assertEquals(count($orderValues), $allMedia->count());

        foreach ($mediaItems as $item) {
            $retrievedMedia = $allMedia->where('id', $item['media']->id)->first();
            $this->assertNotNull($retrievedMedia);
            $this->assertEquals($item['order'], $retrievedMedia->getCustomProperty('display_order'));
        }

        // Verify we can sort by custom property
        $sortedMedia = $allMedia->sortBy(function ($media) {
            return $media->getCustomProperty('display_order');
        });

        $sortedOrders = $sortedMedia->map(function ($media) {
            return $media->getCustomProperty('display_order');
        })->values()->toArray();

        $this->assertEquals([1, 2, 3, 4], $sortedOrders);
    }

    /**
     * Property: Cascade deletion from shop to products
     * When a shop is deleted, products should be cascade deleted and media should be cleaned up.
     * Validates: Requirement 7.5
     */
    public function test_cascade_deletion_property()
    {
        for ($i = 0; $i < 10; $i++) {
            $this->runCascadeDeletionProperty();
        }
    }

    private function runCascadeDeletionProperty()
    {
        // Clear any existing media
        \Spatie\MediaLibrary\MediaCollections\Models\Media::truncate();
        
        $user = User::factory()->create(['role' => 'vendor']);
        $location = Location::factory()->create();
        $shop = Shop::factory()->create([
            'owner_id' => $user->id,
            'location_id' => $location->id,
        ]);

        // Add shop logo
        $logoFile = $this->generateRandomImage('shop_logo');
        $shop->addMedia($logoFile)->toMediaCollection('logo');
        
        $shop->refresh();
        $shopMediaId = $shop->getFirstMedia('logo')->id;

        // Create products with media
        $category = Category::factory()->create();
        $subcategory = Subcategory::factory()->create(['category_id' => $category->id]);
        
        $productCount = rand(2, 4);
        $products = [];
        $productMediaIds = [];
        $productIds = [];
        
        for ($j = 0; $j < $productCount; $j++) {
            $product = Product::factory()->create([
                'shop_id' => $shop->id,
                'subcategory_id' => $subcategory->id,
            ]);

            $mainImageFile = $this->generateRandomImage("product_{$j}_main");
            $product->addMedia($mainImageFile)->toMediaCollection('main_image');
            
            $product->refresh();
            $products[] = $product;
            $productIds[] = $product->id;
            $productMediaIds[] = $product->getFirstMedia('main_image')->id;
        }

        // Verify all media exists
        $this->assertDatabaseHas('media', ['id' => $shopMediaId]);
        foreach ($productMediaIds as $mediaId) {
            $this->assertDatabaseHas('media', ['id' => $mediaId]);
        }

        // Manually delete products first (to trigger media cleanup)
        foreach ($products as $product) {
            $product->delete();
        }

        // Verify products are deleted
        foreach ($productIds as $productId) {
            $this->assertDatabaseMissing('products', ['id' => $productId]);
        }
        
        // Verify product media is cleaned up
        foreach ($productMediaIds as $mediaId) {
            $this->assertDatabaseMissing('media', ['id' => $mediaId]);
        }

        // Now delete shop
        $shopId = $shop->id;
        $shop->delete();

        // Verify shop is soft deleted
        $this->assertSoftDeleted('shops', ['id' => $shopId]);

        // Force delete shop
        $shop->forceDelete();

        // Verify shop is permanently deleted
        $this->assertDatabaseMissing('shops', ['id' => $shopId]);

        // Verify shop media is cleaned up
        $this->assertDatabaseMissing('media', ['id' => $shopMediaId]);
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
