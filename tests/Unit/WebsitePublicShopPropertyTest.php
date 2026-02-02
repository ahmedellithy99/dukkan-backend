<?php

namespace Tests\Unit;

use App\Models\City;
use App\Models\Governorate;
use App\Models\Location;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Property-Based Test for Public Shop Discovery
 * Feature: marketplace-platform, Property 7: Public Shop Discovery
 * Validates: Requirements 3.4, 3.5.
 */
class WebsitePublicShopPropertyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Property 7: Public Shop Discovery
     * For any public shop queries, the system should return only active,
     * non-deleted shops and handle visibility correctly.
     */
    public function test_public_shop_discovery_property()
    {
        // Run property test with 100 iterations
        for ($i = 0; $i < 100; $i++) {
            $this->runPublicShopDiscoveryProperty();
        }
    }

    private function runPublicShopDiscoveryProperty()
    {
        // Clean up for isolated test
        Shop::query()->forceDelete();
        User::where('role', 'vendor')->delete();
        Location::truncate();
        City::truncate();
        Governorate::truncate();

        // Create vendors
        $vendor1 = User::factory()->create(['role' => 'vendor']);
        $vendor2 = User::factory()->create(['role' => 'vendor']);

        // Create shops with different statuses (each needs unique location)
        $activeShop = $this->createShopForVendor($vendor1, true);
        $inactiveShop = $this->createShopForVendor($vendor2, false);

        // Public queries should return only active, non-deleted shops
        $publicShops = Shop::active()->get();
        
        // Should contain only the active shop
        $this->assertCount(1, $publicShops);
        $this->assertEquals($activeShop->id, $publicShops->first()->id);
        $this->assertTrue($publicShops->first()->is_active);

        // Inactive shops should not be visible publicly
        $publicShopsIncludingInactive = Shop::all();
        $this->assertCount(2, $publicShopsIncludingInactive); // Both exist in DB
        
        $inactiveShopsInPublic = $publicShopsIncludingInactive->where('is_active', false);
        $this->assertCount(1, $inactiveShopsInPublic);
        
        // But public API should filter them out
        $publicActiveOnly = Shop::active()->get();
        $this->assertCount(1, $publicActiveOnly);
    }

    /**
     * Property: Public shop details are accessible when active
     * For any active shop, public access should work correctly.
     */
    public function test_public_shop_details_access_property()
    {
        for ($i = 0; $i < 50; $i++) {
            $this->runPublicShopDetailsAccessProperty();
        }
    }

    private function runPublicShopDetailsAccessProperty()
    {
        // Clean up for isolated test
        Shop::query()->forceDelete();
        User::where('role', 'vendor')->delete();
        Location::truncate();
        City::truncate();
        Governorate::truncate();

        $vendor = User::factory()->create(['role' => 'vendor']);
        
        // Create active shop
        $activeShop = $this->createShopForVendor($vendor, true);
        
        // Public should be able to access active shop details
        $foundShop = Shop::active()->where('id', $activeShop->id)->first();
        $this->assertNotNull($foundShop);
        $this->assertEquals($activeShop->id, $foundShop->id);
        $this->assertEquals($activeShop->name, $foundShop->name);
        $this->assertTrue($foundShop->is_active);

        // Test by slug (route key)
        $foundBySlug = Shop::active()->where('slug', $activeShop->slug)->first();
        $this->assertNotNull($foundBySlug);
        $this->assertEquals($activeShop->id, $foundBySlug->id);
    }

    /**
     * Property: Inactive shops are not visible publicly
     * For any inactive shop, public queries should not return it.
     */
    public function test_inactive_shop_public_invisibility_property()
    {
        for ($i = 0; $i < 50; $i++) {
            $this->runInactiveShopPublicInvisibilityProperty();
        }
    }

    private function runInactiveShopPublicInvisibilityProperty()
    {
        // Clean up for isolated test
        Shop::query()->forceDelete();
        User::where('role', 'vendor')->delete();
        Location::truncate();
        City::truncate();
        Governorate::truncate();

        $vendor = User::factory()->create(['role' => 'vendor']);
        
        // Create inactive shop
        $inactiveShop = $this->createShopForVendor($vendor, false);
        
        // Public queries should not return inactive shops
        $foundShop = Shop::active()->where('id', $inactiveShop->id)->first();
        $this->assertNull($foundShop);

        // Even direct queries with active scope should not return it
        $foundBySlug = Shop::active()->where('slug', $inactiveShop->slug)->first();
        $this->assertNull($foundBySlug);

        // But it should exist in the database
        $existsInDb = Shop::where('id', $inactiveShop->id)->exists();
        $this->assertTrue($existsInDb);
    }

    /**
     * Property: Soft-deleted shops are not visible publicly
     * For any soft-deleted shop, public queries should not return it.
     */
    public function test_soft_deleted_shop_public_invisibility_property()
    {
        for ($i = 0; $i < 50; $i++) {
            $this->runSoftDeletedShopPublicInvisibilityProperty();
        }
    }

    private function runSoftDeletedShopPublicInvisibilityProperty()
    {
        // Clean up for isolated test
        Shop::query()->forceDelete();
        User::where('role', 'vendor')->delete();
        Location::truncate();
        City::truncate();
        Governorate::truncate();

        $vendor = User::factory()->create(['role' => 'vendor']);
        
        // Create active shop then soft delete it
        $shop = $this->createShopForVendor($vendor, true);
        $shopId = $shop->id;
        $shopSlug = $shop->slug;
        
        // Verify it's initially visible
        $initiallyVisible = Shop::active()->where('id', $shopId)->exists();
        $this->assertTrue($initiallyVisible);

        // Soft delete the shop
        $shop->delete();

        // Public queries should not return soft-deleted shops
        $foundAfterDelete = Shop::active()->where('id', $shopId)->first();
        $this->assertNull($foundAfterDelete);

        $foundBySlugAfterDelete = Shop::active()->where('slug', $shopSlug)->first();
        $this->assertNull($foundBySlugAfterDelete);

        // Even without active scope, normal queries shouldn't return it
        $foundWithoutActiveScope = Shop::where('id', $shopId)->first();
        $this->assertNull($foundWithoutActiveScope);

        // But it should exist in soft-deleted records
        $existsInTrashed = Shop::onlyTrashed()->where('id', $shopId)->exists();
        $this->assertTrue($existsInTrashed);
    }

    /**
     * Property: Public shop listing filters correctly
     * For any mix of active, inactive, and deleted shops, public listing should be correct.
     */
    public function test_public_shop_listing_filtering_property()
    {
        for ($i = 0; $i < 30; $i++) {
            $this->runPublicShopListingFilteringProperty();
        }
    }

    private function runPublicShopListingFilteringProperty()
    {
        // Clean up for isolated test
        Shop::query()->forceDelete();
        User::where('role', 'vendor')->delete();
        Location::truncate();
        City::truncate();
        Governorate::truncate();

        $vendors = [];
        for ($j = 0; $j < 3; $j++) {
            $vendors[] = User::factory()->create(['role' => 'vendor']);
        }

        $activeShopsCount = 0;
        $inactiveShopsCount = 0;
        $deletedShopsCount = 0;

        // Create various shops
        foreach ($vendors as $vendor) {
            // Create active shop
            $activeShop = $this->createShopForVendor($vendor, true);
            $activeShopsCount++;

            // Create inactive shop
            $inactiveShop = $this->createShopForVendor($vendor, false);
            $inactiveShopsCount++;

            // Create shop to be deleted
            $shopToDelete = $this->createShopForVendor($vendor, true);
            $shopToDelete->delete();
            $deletedShopsCount++;
        }

        // Public listing should only return active, non-deleted shops
        $publicShops = Shop::active()->get();
        $this->assertCount($activeShopsCount, $publicShops);

        foreach ($publicShops as $shop) {
            $this->assertTrue($shop->is_active);
            $this->assertNull($shop->deleted_at);
        }

        // Total shops in database (including inactive and soft-deleted)
        $totalShopsIncludingDeleted = Shop::withTrashed()->count();
        $this->assertEquals($activeShopsCount + $inactiveShopsCount + $deletedShopsCount, $totalShopsIncludingDeleted);

        // Only active shops should be publicly visible
        $allNonDeletedShops = Shop::all();
        $this->assertCount($activeShopsCount + $inactiveShopsCount, $allNonDeletedShops);
    }

    /**
     * Property: Public shop filtering by location works correctly
     * For any location-based filtering, only active shops in that location should be returned.
     */
    public function test_public_shop_location_filtering_property()
    {
        for ($i = 0; $i < 20; $i++) {
            $this->runPublicShopLocationFilteringProperty();
        }
    }

    private function runPublicShopLocationFilteringProperty()
    {
        // Clean up for isolated test
        Shop::query()->forceDelete();
        User::where('role', 'vendor')->delete();
        Location::truncate();
        City::truncate();
        Governorate::truncate();

        $vendor = User::factory()->create(['role' => 'vendor']);

        // Create locations in different cities
        $location1 = $this->createValidLocationInCity('Cairo');
        $location2 = $this->createValidLocationInCity('Alexandria');

        // Create active shops in different locations
        $cairoShop = $this->createShopForVendorAtLocation($vendor, $location1, true);
        $alexandriaShop = $this->createShopForVendorAtLocation($vendor, $location2, true);

        // Create inactive shop in a separate location (due to unique constraint)
        $location3 = $this->createValidLocationInCity('Cairo');
        $inactiveCairoShop = $this->createShopForVendorAtLocation($vendor, $location3, false);

        // Public filtering by Cairo location should return only active Cairo shops
        $cairoActiveShops = Shop::active()
            ->where('location_id', $location1->id)
            ->get();
        
        $this->assertCount(1, $cairoActiveShops);
        $this->assertEquals($cairoShop->id, $cairoActiveShops->first()->id);

        // Public filtering by Alexandria location should return only active Alexandria shops
        $alexandriaActiveShops = Shop::active()
            ->where('location_id', $location2->id)
            ->get();
        
        $this->assertCount(1, $alexandriaActiveShops);
        $this->assertEquals($alexandriaShop->id, $alexandriaActiveShops->first()->id);

        // All public shops should be active
        $allPublicShops = Shop::active()->get();
        $this->assertCount(2, $allPublicShops); // Only the 2 active shops
        
        foreach ($allPublicShops as $shop) {
            $this->assertTrue($shop->is_active);
        }
    }

    private function createShopForVendor(User $vendor, bool $isActive): Shop
    {
        $location = $this->createValidLocation();
        
        return Shop::create([
            'owner_id' => $vendor->id,
            'location_id' => $location->id,
            'name' => $this->generateRandomShopName(),
            'description' => $this->generateRandomDescription(),
            'whatsapp_number' => $this->generateRandomWhatsAppNumber(),
            'phone_number' => $this->generateRandomPhoneNumber(),
            'is_active' => $isActive,
        ]);
    }

    private function createShopForVendorAtLocation(User $vendor, Location $location, bool $isActive): Shop
    {
        return Shop::create([
            'owner_id' => $vendor->id,
            'location_id' => $location->id,
            'name' => $this->generateRandomShopName(),
            'description' => $this->generateRandomDescription(),
            'whatsapp_number' => $this->generateRandomWhatsAppNumber(),
            'phone_number' => $this->generateRandomPhoneNumber(),
            'is_active' => $isActive,
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

    private function createValidLocationInCity(string $cityName): Location
    {
        $timestamp = microtime(true) * 1000;
        $random = rand(100000, 999999);

        $governorate = Governorate::create([
            'name' => $this->generateRandomGovernorate(),
            'slug' => 'gov-' . $timestamp . '-' . $random,
        ]);

        $city = City::create([
            'governorate_id' => $governorate->id,
            'name' => $cityName,
            'slug' => strtolower($cityName) . '-' . $timestamp . '-' . $random,
        ]);

        return Location::create([
            'city_id' => $city->id,
            'area' => $this->generateRandomArea() . '_' . $timestamp . '_' . $random,
            'latitude' => $this->generateValidLatitude(),
            'longitude' => $this->generateValidLongitude(),
        ]);
    }

    private function generateRandomShopName(): string
    {
        $names = [
            'Ahmed Electronics',
            'Cairo Fashion Store',
            'Modern Furniture',
            'Tech Solutions',
            'Beauty Corner',
            'Sports World',
            'Book Haven',
            'Coffee Corner',
        ];

        return $names[array_rand($names)] . ' ' . rand(1, 1000);
    }

    private function generateRandomDescription(): string
    {
        $descriptions = [
            'High quality products at affordable prices',
            'Your trusted local store for all needs',
            'Premium products with excellent service',
            'Best deals in town with fast delivery',
            'Quality guaranteed with customer satisfaction',
        ];

        return $descriptions[array_rand($descriptions)];
    }

    private function generateRandomWhatsAppNumber(): string
    {
        $formats = [
            '+201' . rand(10000000, 99999999),
            '+2012' . rand(1000000, 9999999),
            '+2010' . rand(1000000, 9999999),
            '+2011' . rand(1000000, 9999999),
        ];

        return $formats[array_rand($formats)];
    }

    private function generateRandomPhoneNumber(): string
    {
        return $this->generateRandomWhatsAppNumber();
    }

    private function generateRandomGovernorate(): string
    {
        $governorates = ['Cairo', 'Giza', 'Alexandria', 'Qalyubia', 'Sharqia', 'Dakahlia', 'Beheira'];
        return $governorates[array_rand($governorates)];
    }

    private function generateRandomCity(): string
    {
        $cities = ['Cairo', 'Alexandria', 'Giza', 'Luxor', 'Aswan', 'Mansoura', 'Tanta'];
        return $cities[array_rand($cities)];
    }

    private function generateRandomArea(): string
    {
        $areas = ['Downtown', 'Nasr City', 'Maadi', 'Zamalek', 'Heliopolis', 'Dokki', 'Mohandessin'];
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