<?php

namespace Tests\Feature\Api\V1\Vendor;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShopLogoManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_vendor_can_upload_shop_logo()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = Shop::factory()->create(['owner_id' => $vendor->id]);

        $logo = UploadedFile::fake()->image('logo.jpg', 300, 300);

        $response = $this->actingAs($vendor, 'sanctum')
            ->post("/api/v1/vendor/my-shops/{$shop->slug}/logo", [
                'logo' => $logo,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'file_name',
                    'mime_type',
                    'size',
                ],
            ]);

        $this->assertEquals(1, $shop->fresh()->getMedia('logo')->count());
    }

    public function test_vendor_can_replace_existing_logo()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = Shop::factory()->create(['owner_id' => $vendor->id]);

        // Upload first logo
        $firstLogo = UploadedFile::fake()->image('logo1.jpg', 300, 300);
        $shop->addMedia($firstLogo)->toMediaCollection('logo');

        $this->assertEquals(1, $shop->getMedia('logo')->count());
        $firstMediaId = $shop->getFirstMedia('logo')->id;

        // Upload second logo (should replace first)
        $secondLogo = UploadedFile::fake()->image('logo2.jpg', 300, 300);

        $response = $this->actingAs($vendor, 'sanctum')
            ->post("/api/v1/vendor/my-shops/{$shop->slug}/logo", [
                'logo' => $secondLogo,
            ]);

        $response->assertStatus(201);

        // Should still have only 1 logo
        $shop->refresh();
        $this->assertEquals(1, $shop->getMedia('logo')->count());
        
        // Should be a different media item
        $this->assertNotEquals($firstMediaId, $shop->getFirstMedia('logo')->id);
    }

    public function test_vendor_cannot_upload_logo_to_another_vendors_shop()
    {
        $vendor1 = User::factory()->create(['role' => 'vendor']);
        $vendor2 = User::factory()->create(['role' => 'vendor']);
        
        $shop = Shop::factory()->create(['owner_id' => $vendor1->id]);

        $logo = UploadedFile::fake()->image('logo.jpg', 300, 300);

        $response = $this->actingAs($vendor2, 'sanctum')
            ->post("/api/v1/vendor/my-shops/{$shop->slug}/logo", [
                'logo' => $logo,
            ]);

        $response->assertStatus(403);
    }

    public function test_logo_upload_validates_file_type()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = Shop::factory()->create(['owner_id' => $vendor->id]);

        $invalidFile = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($vendor, 'sanctum')
            ->post("/api/v1/vendor/my-shops/{$shop->slug}/logo", [
                'logo' => $invalidFile,
            ]);

        $response->assertStatus(422);
    }

    public function test_logo_upload_validates_file_size()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = Shop::factory()->create(['owner_id' => $vendor->id]);

        // Create a file larger than 2MB
        $largeLogo = UploadedFile::fake()->create('logo.jpg', 3000);

        $response = $this->actingAs($vendor, 'sanctum')
            ->post("/api/v1/vendor/my-shops/{$shop->slug}/logo", [
                'logo' => $largeLogo,
            ]);

        $response->assertStatus(422);
    }

    public function test_logo_upload_validates_dimensions()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = Shop::factory()->create(['owner_id' => $vendor->id]);

        // Create an image that's too small
        $tinyLogo = UploadedFile::fake()->image('logo.jpg', 50, 50);

        $response = $this->actingAs($vendor, 'sanctum')
            ->post("/api/v1/vendor/my-shops/{$shop->slug}/logo", [
                'logo' => $tinyLogo,
            ]);

        $response->assertStatus(422);
    }

    public function test_vendor_can_delete_shop_logo()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = Shop::factory()->create(['owner_id' => $vendor->id]);

        $logo = UploadedFile::fake()->image('logo.jpg', 300, 300);
        $media = $shop->addMedia($logo)->toMediaCollection('logo');

        $response = $this->actingAs($vendor, 'sanctum')
            ->delete("/api/v1/vendor/my-shops/{$shop->slug}/logo");

        $response->assertStatus(204);

        $this->assertEquals(0, $shop->fresh()->getMedia('logo')->count());
        $this->assertDatabaseMissing('media', ['id' => $media->id]);
    }

    public function test_vendor_cannot_delete_another_vendors_shop_logo()
    {
        $vendor1 = User::factory()->create(['role' => 'vendor']);
        $vendor2 = User::factory()->create(['role' => 'vendor']);
        
        $shop = Shop::factory()->create(['owner_id' => $vendor1->id]);

        $logo = UploadedFile::fake()->image('logo.jpg', 300, 300);
        $shop->addMedia($logo)->toMediaCollection('logo');

        $response = $this->actingAs($vendor2, 'sanctum')
            ->delete("/api/v1/vendor/my-shops/{$shop->slug}/logo");

        $response->assertStatus(403);
        $this->assertEquals(1, $shop->fresh()->getMedia('logo')->count());
    }

    public function test_deleting_nonexistent_logo_returns_success()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = Shop::factory()->create(['owner_id' => $vendor->id]);

        // No logo uploaded

        $response = $this->actingAs($vendor, 'sanctum')
            ->delete("/api/v1/vendor/my-shops/{$shop->slug}/logo");

        $response->assertStatus(204);
    }
}
