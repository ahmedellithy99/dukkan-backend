<?php

namespace Tests\Feature\Api\V1\Vendor;

use App\Models\Product;
use App\Models\Shop;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\TestCase;

class ProductImageManagementTest extends TestCase
{
    // use RefreshDatabase;

    // protected function setUp(): void
    // {
    //     parent::setUp();
    //     Storage::fake('public');
    // }

    // public function test_vendor_can_upload_product_images()
    // {
    //     $vendor = User::factory()->create(['role' => 'vendor']);
    //     $shop = Shop::factory()->create(['owner_id' => $vendor->id]);
    //     $subcategory = Subcategory::factory()->create();
    //     $product = Product::factory()->create([
    //         'shop_id' => $shop->id,
    //         'subcategory_id' => $subcategory->id,
    //     ]);

    //     $images = [
    //         UploadedFile::fake()->image('product1.jpg'),
    //         UploadedFile::fake()->image('product2.jpg'),
    //     ];

    //     $response = $this->actingAs($vendor, 'sanctum')
    //         ->postJson("/api/v1/vendor/my-shop/{$shop->slug}/products/{$product->slug}/images", [
    //             'images' => $images,
    //         ]);

    //     $response->assertStatus(201)
    //         ->assertJson([
    //             'success' => true,
    //             'message' => 'Images uploaded successfully',
    //         ]);

    //     $this->assertEquals(2, $product->fresh()->getMedia('product_images')->count());
    // }

    // public function test_vendor_cannot_upload_images_to_another_vendors_product()
    // {
    //     $vendor1 = User::factory()->create(['role' => 'vendor']);
    //     $vendor2 = User::factory()->create(['role' => 'vendor']);
        
    //     $shop = Shop::factory()->create(['owner_id' => $vendor1->id]);
    //     $subcategory = Subcategory::factory()->create();
    //     $product = Product::factory()->create([
    //         'shop_id' => $shop->id,
    //         'subcategory_id' => $subcategory->id,
    //     ]);

    //     $images = [
    //         UploadedFile::fake()->image('product1.jpg'),
    //     ];

    //     $response = $this->actingAs($vendor2, 'sanctum')
    //         ->postJson("/api/v1/vendor/my-shop/{$shop->slug}/products/{$product->slug}/images", [
    //             'images' => $images,
    //         ]);

    //     $response->assertStatus(403);
    // }

    // public function test_image_upload_validates_file_types()
    // {
    //     $vendor = User::factory()->create(['role' => 'vendor']);
    //     $shop = Shop::factory()->create(['owner_id' => $vendor->id]);
    //     $subcategory = Subcategory::factory()->create();
    //     $product = Product::factory()->create([
    //         'shop_id' => $shop->id,
    //         'subcategory_id' => $subcategory->id,
    //     ]);

    //     $invalidFile = UploadedFile::fake()->create('document.pdf', 100);

    //     $response = $this->actingAs($vendor, 'sanctum')
    //         ->post("/api/v1/vendor/my-shop/{$shop->slug}/products/{$product->slug}/images", [
    //             'images' => [$invalidFile],
    //         ]);

    //     $response->assertStatus(422);
    // }

    // public function test_vendor_can_delete_product_image()
    // {
    //     $vendor = User::factory()->create(['role' => 'vendor']);
    //     $shop = Shop::factory()->create(['owner_id' => $vendor->id]);
    //     $subcategory = Subcategory::factory()->create();
    //     $product = Product::factory()->create([
    //         'shop_id' => $shop->id,
    //         'subcategory_id' => $subcategory->id,
    //     ]);

    //     $file = UploadedFile::fake()->image('product.jpg');
    //     $media = $product->addMedia($file)->toMediaCollection('product_images');

    //     $response = $this->actingAs($vendor, 'sanctum')
    //         ->deleteJson("/api/v1/vendor/my-shop/{$shop->slug}/products/{$product->slug}/images/{$media->id}");

    //     $response->assertStatus(200)
    //         ->assertJson([
    //             'success' => true,
    //             'message' => 'Image deleted successfully',
    //         ]);

    //     $this->assertEquals(0, $product->fresh()->getMedia('product_images')->count());
    //     $this->assertDatabaseMissing('media', ['id' => $media->id]);
    // }

    // public function test_vendor_cannot_delete_another_products_image()
    // {
    //     $vendor = User::factory()->create(['role' => 'vendor']);
    //     $shop = Shop::factory()->create(['owner_id' => $vendor->id]);
    //     $subcategory = Subcategory::factory()->create();
        
    //     $product1 = Product::factory()->create([
    //         'shop_id' => $shop->id,
    //         'subcategory_id' => $subcategory->id,
    //     ]);
        
    //     $product2 = Product::factory()->create([
    //         'shop_id' => $shop->id,
    //         'subcategory_id' => $subcategory->id,
    //     ]);

    //     $file = UploadedFile::fake()->image('product.jpg');
    //     $media = $product2->addMedia($file)->toMediaCollection('product_images');

    //     $response = $this->actingAs($vendor, 'sanctum')
    //         ->deleteJson("/api/v1/vendor/my-shop/{$shop->slug}/products/{$product1->slug}/images/{$media->id}");

    //     $response->assertStatus(404);
    //     $this->assertEquals(1, $product2->fresh()->getMedia('product_images')->count());
    // }

    // public function test_vendor_can_reorder_product_images()
    // {
    //     $vendor = User::factory()->create(['role' => 'vendor']);
    //     $shop = Shop::factory()->create(['owner_id' => $vendor->id]);
    //     $subcategory = Subcategory::factory()->create();
    //     $product = Product::factory()->create([
    //         'shop_id' => $shop->id,
    //         'subcategory_id' => $subcategory->id,
    //     ]);

    //     // Upload 3 images
    //     $media1 = $product->addMedia(UploadedFile::fake()->image('img1.jpg'))
    //         ->withCustomProperties(['order' => 0])
    //         ->toMediaCollection('product_images');
        
    //     $media2 = $product->addMedia(UploadedFile::fake()->image('img2.jpg'))
    //         ->withCustomProperties(['order' => 1])
    //         ->toMediaCollection('product_images');
        
    //     $media3 = $product->addMedia(UploadedFile::fake()->image('img3.jpg'))
    //         ->withCustomProperties(['order' => 2])
    //         ->toMediaCollection('product_images');

    //     // Reorder: swap first and last
    //     $response = $this->actingAs($vendor, 'sanctum')
    //         ->putJson("/api/v1/vendor/my-shop/{$shop->slug}/products/{$product->slug}/images/reorder", [
    //             'order' => [
    //                 ['id' => $media3->id, 'order' => 0],
    //                 ['id' => $media2->id, 'order' => 1],
    //                 ['id' => $media1->id, 'order' => 2],
    //             ],
    //         ]);

    //     $response->assertStatus(200)
    //         ->assertJson([
    //             'success' => true,
    //             'message' => 'Images reordered successfully',
    //         ]);

    //     // Verify new order
    //     $product->refresh();
    //     $reorderedMedia1 = Media::find($media1->id);
    //     $reorderedMedia3 = Media::find($media3->id);
        
    //     $this->assertEquals(2, $reorderedMedia1->getCustomProperty('order'));
    //     $this->assertEquals(0, $reorderedMedia3->getCustomProperty('order'));
    // }

    // public function test_reorder_validates_required_fields()
    // {
    //     $vendor = User::factory()->create(['role' => 'vendor']);
    //     $shop = Shop::factory()->create(['owner_id' => $vendor->id]);
    //     $subcategory = Subcategory::factory()->create();
    //     $product = Product::factory()->create([
    //         'shop_id' => $shop->id,
    //         'subcategory_id' => $subcategory->id,
    //     ]);

    //     $response = $this->actingAs($vendor, 'sanctum')
    //         ->putJson("/api/v1/vendor/my-shop/{$shop->slug}/products/{$product->slug}/images/reorder", [
    //             'order' => [],
    //         ]);

    //     $response->assertStatus(422);
    // }

    // public function test_upload_validates_maximum_images()
    // {
    //     $vendor = User::factory()->create(['role' => 'vendor']);
    //     $shop = Shop::factory()->create(['owner_id' => $vendor->id]);
    //     $subcategory = Subcategory::factory()->create();
    //     $product = Product::factory()->create([
    //         'shop_id' => $shop->id,
    //         'subcategory_id' => $subcategory->id,
    //     ]);

    //     // Try to upload 11 images (max is 10)
    //     $images = [];
    //     for ($i = 0; $i < 11; $i++) {
    //         $images[] = UploadedFile::fake()->image("product{$i}.jpg");
    //     }

    //     $response = $this->actingAs($vendor, 'sanctum')
    //         ->post("/api/v1/vendor/my-shop/{$shop->slug}/products/{$product->slug}/images", [
    //             'images' => $images,
    //         ]);

    //     $response->assertStatus(422);
    // }
}
