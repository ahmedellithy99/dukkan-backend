<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Models\AdCarousel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdCarouselTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        Storage::fake('public');
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_create_ad_carousel_with_auto_increment_display_order()
    {
        $image = UploadedFile::fake()->image('carousel.jpg');

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/ad-carousels', [
                'title' => 'First Carousel',
                'carousel_image' => $image,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'First Carousel')
            ->assertJsonPath('data.display_order', 0);

        // Create second carousel
        $image2 = UploadedFile::fake()->image('carousel2.jpg');
        
        $response2 = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/ad-carousels', [
                'title' => 'Second Carousel',
                'carousel_image' => $image2,
            ]);

        $response2->assertStatus(201)
            ->assertJsonPath('data.display_order', 1);
    }

    public function test_admin_can_update_carousel_image_only()
    {
        $carousel = AdCarousel::factory()->create([
            'title' => 'Original Title',
            'display_order' => 5,
        ]);

        $newImage = UploadedFile::fake()->image('new-carousel.jpg');

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/admin/ad-carousels/{$carousel->id}", [
                'carousel_image' => $newImage,
            ]);

        $response->assertStatus(200);
        
        $carousel->refresh();
        $this->assertEquals('Original Title', $carousel->title);
        $this->assertEquals(5, $carousel->display_order);
    }

    public function test_non_admin_cannot_create_ad_carousel()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $image = UploadedFile::fake()->image('carousel.jpg');

        $response = $this->actingAs($vendor, 'sanctum')
            ->postJson('/api/v1/admin/ad-carousels', [
                'title' => 'Test Carousel',
                'carousel_image' => $image,
            ]);

        $response->assertStatus(403);
    }

    public function test_image_is_required_on_store()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/ad-carousels', [
                'title' => 'Test Carousel',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.fields.carousel_image', fn($errors) => !empty($errors));
    }

    public function test_image_is_required_on_update()
    {
        $carousel = AdCarousel::factory()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/admin/ad-carousels/{$carousel->id}", []);

        $response->assertStatus(422)
            ->assertJsonPath('error.fields.carousel_image', fn($errors) => !empty($errors));
    }
}
