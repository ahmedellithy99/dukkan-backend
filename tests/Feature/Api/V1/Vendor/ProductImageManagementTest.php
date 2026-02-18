<?php

namespace Tests\Feature\Api\V1\Vendor;

use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $vendor;
    protected Shop $shop;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->vendor = User::factory()->vendor()->create();
        $this->shop = Shop::factory()->for($this->vendor, 'owner')->create();
        $this->product = Product::factory()->for($this->shop)->create();
    }

    /** @test */
    public function vendor_can_upload_main_image()
    {
        $image = UploadedFile::fake()->image('main.jpg', 800, 600);

        $response = $this->actingAs($this->vendor)
            ->postJson("/api/v1/vendor/my-shop/{$this->shop->slug}/products/{$this->product->slug}/images/main", [
                'image' => $image,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => ['id', 'file_name', 'mime_type', 'size'],
            ]);

        $this->assertNotNull($this->product->fresh()->getFirstMedia('main_image'));
    }

    /** @test */
    public function vendor_can_upload_secondary_image()
    {
        $image = UploadedFile::fake()->image('secondary.jpg', 800, 600);

        $response = $this->actingAs($this->vendor)
            ->postJson("/api/v1/vendor/my-shop/{$this->shop->slug}/products/{$this->product->slug}/images/secondary", [
                'image' => $image,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => ['id', 'file_name', 'mime_type', 'size'],
            ]);

        $this->assertNotNull($this->product->fresh()->getFirstMedia('secondary_image'));
    }

    /** @test */
    public function vendor_can_replace_main_image()
    {
        // Upload initial main image
        $initialImage = UploadedFile::fake()->image('initial.jpg');
        $this->product->addMedia($initialImage)->toMediaCollection('main_image');
        $initialMediaId = $this->product->getFirstMedia('main_image')->id;

        // Replace with new image
        $newImage = UploadedFile::fake()->image('new.jpg');
        $response = $this->actingAs($this->vendor)
            ->postJson("/api/v1/vendor/my-shop/{$this->shop->slug}/products/{$this->product->slug}/images/main", [
                'image' => $newImage,
            ]);

        $response->assertStatus(201);

        $this->product->refresh();
        $currentMedia = $this->product->getFirstMedia('main_image');
        
        $this->assertNotNull($currentMedia);
        $this->assertNotEquals($initialMediaId, $currentMedia->id);
    }

    /** @test */
    public function vendor_can_replace_secondary_image()
    {
        // Upload initial secondary image
        $initialImage = UploadedFile::fake()->image('initial.jpg');
        $this->product->addMedia($initialImage)->toMediaCollection('secondary_image');
        $initialMediaId = $this->product->getFirstMedia('secondary_image')->id;

        // Replace with new image
        $newImage = UploadedFile::fake()->image('new.jpg');
        $response = $this->actingAs($this->vendor)
            ->postJson("/api/v1/vendor/my-shop/{$this->shop->slug}/products/{$this->product->slug}/images/secondary", [
                'image' => $newImage,
            ]);

        $response->assertStatus(201);

        $this->product->refresh();
        $currentMedia = $this->product->getFirstMedia('secondary_image');
        
        $this->assertNotNull($currentMedia);
        $this->assertNotEquals($initialMediaId, $currentMedia->id);
    }

    /** @test */
    public function vendor_can_delete_main_image()
    {
        $image = UploadedFile::fake()->image('main.jpg');
        $this->product->addMedia($image)->toMediaCollection('main_image');

        $response = $this->actingAs($this->vendor)
            ->deleteJson("/api/v1/vendor/my-shop/{$this->shop->slug}/products/{$this->product->slug}/images/main");

        $response->assertStatus(204);
        $this->assertNull($this->product->fresh()->getFirstMedia('main_image'));
    }

    /** @test */
    public function vendor_can_delete_secondary_image()
    {
        $image = UploadedFile::fake()->image('secondary.jpg');
        $this->product->addMedia($image)->toMediaCollection('secondary_image');

        $response = $this->actingAs($this->vendor)
            ->deleteJson("/api/v1/vendor/my-shop/{$this->shop->slug}/products/{$this->product->slug}/images/secondary");

        $response->assertStatus(204);
        $this->assertNull($this->product->fresh()->getFirstMedia('secondary_image'));
    }

    /** @test */
    public function vendor_cannot_manage_images_for_other_vendors_products()
    {
        $otherVendor = User::factory()->vendor()->create();
        $otherShop = Shop::factory()->for($otherVendor, 'owner')->create();
        $otherProduct = Product::factory()->for($otherShop)->create();

        $image = UploadedFile::fake()->image('test.jpg');

        // Try to upload main image - should get 403 Forbidden (not 404)
        $response = $this->actingAs($this->vendor)
            ->postJson("/api/v1/vendor/my-shop/{$otherShop->slug}/products/{$otherProduct->slug}/images/main", [
                'image' => $image,
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function image_upload_requires_valid_image_file()
    {
        // Create a non-image file (text file)
        $file = UploadedFile::fake()->create('document.txt', 100);

        $response = $this->actingAs($this->vendor)
            ->postJson("/api/v1/vendor/my-shop/{$this->shop->slug}/products/{$this->product->slug}/images/main", [
                'image' => $file,
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.fields.image', fn($errors) => count($errors) > 0);
    }

    /** @test */
    public function deleting_non_existent_main_image_returns_404()
    {
        $response = $this->actingAs($this->vendor)
            ->deleteJson("/api/v1/vendor/my-shop/{$this->shop->slug}/products/{$this->product->slug}/images/main");

        $response->assertStatus(404);
    }

    /** @test */
    public function deleting_non_existent_secondary_image_returns_404()
    {
        $response = $this->actingAs($this->vendor)
            ->deleteJson("/api/v1/vendor/my-shop/{$this->shop->slug}/products/{$this->product->slug}/images/secondary");

        $response->assertStatus(404);
    }
}
