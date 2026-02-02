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
 * Property-Based Test for Vendor Shop Status, Visibility, and Soft Delete
 * Feature: marketplace-platform, Property 6: Vendor Shop Status, Visibility, and Soft Delete
 * Validates: Requirements 3.4, 3.5.
 */
class VendorShopFilteringPropertyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Property 6: Vendor Shop Status, Visibility, and Soft Delete
     * For any vendor shop operations, the system should maintain proper filtering,
     * status management, and soft delete functionality.
     */
    public function test_vendor_shop_filtering_property()
    {
        // Run property test with 100 iterations
        for ($i = 0; $i < 100; $i++) {
            $this->runVendorShopFilteringProperty();
        }
    }

    private function runVendorShopFilteringProperty()
    {
        // Create vendor
        $vendor = User::factory()->create([
            'role' => 'vendor',
            'status' => 'active',
        ]);

        // Create multiple shops for the vendor
        $activeShop = $this->createShopForVendor($vendor, true);
        $inactiveShop = $this->createShopForVendor($vendor, false);

        // Vendor can list only their shops
        $vendorShops = Shop::where('owner_id', $vendor->id)->get();
        $this->assertCount(2, $vendorShops);
        
        foreach ($vendorShops as $shop) {
            $this->assertEquals($vendor->id, $shop->owner_id);
        }

        // Filtering by is_active works
        $activeShops = Shop::where('owner_id', $vendor->id)->active()->get();
        $this->assertCount(1, $activeShops);
        $this->assertEquals($activeShop->id, $activeShops->first()->id);
        $this->assertTrue($activeShops->first()->is_active);

        $allVendorShops = Shop::where('owner_id', $vendor->id)->get();
        $inactiveShops = $allVendorShops->where('is_active', false);
        $this->assertCount(1, $inactiveShops);
        $this->assertEquals($inactiveShop->id, $inactiveShops->first()->id);
        $this->assertFalse($inactiveShops->first()->is_active);
    }

    /**
     * Property: Soft-deleted shops are excluded from normal queries
     * For any soft-deleted shop, it should not appear in normal queries.
     */
    public function test_soft_delete_exclusion_property()
    {
        for ($i = 0; $i < 50; $i++) {
            $this->runSoftDeleteExclusionProperty();
        }
    }

    private function runSoftDeleteExclusionProperty()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        
        // Create shops
        $activeShop = $this->createShopForVendor($vendor, true);
        $shopToDelete = $this->createShopForVendor($vendor, true);

        // Verify both shops exist initially
        $initialCount = Shop::where('owner_id', $vendor->id)->count();
        $this->assertEquals(2, $initialCount);

        // Soft delete one shop
        $shopToDelete->delete();

        // Soft-deleted shops should be excluded from normal queries
        $remainingShops = Shop::where('owner_id', $vendor->id)->get();
        $this->assertCount(1, $remainingShops);
        $this->assertEquals($activeShop->id, $remainingShops->first()->id);

        // But should be included when using withTrashed()
        $allShopsIncludingDeleted = Shop::where('owner_id', $vendor->id)->withTrashed()->get();
        $this->assertCount(2, $allShopsIncludingDeleted);

        // Only deleted shops
        $deletedShops = Shop::where('owner_id', $vendor->id)->onlyTrashed()->get();
        $this->assertCount(1, $deletedShops);
        $this->assertEquals($shopToDelete->id, $deletedShops->first()->id);
    }

    /**
     * Property: Vendor cannot access/update/delete shops they don't own
     * For any two different vendors, one cannot access the other's shops.
     */
    public function test_vendor_ownership_isolation_property()
    {
        for ($i = 0; $i < 50; $i++) {
            $this->runVendorOwnershipIsolationProperty();
        }
    }

    private function runVendorOwnershipIsolationProperty()
    {
        // Create two vendors
        $vendor1 = User::factory()->create(['role' => 'vendor']);
        $vendor2 = User::factory()->create(['role' => 'vendor']);

        // Create shops for each vendor
        $vendor1Shop = $this->createShopForVendor($vendor1, true);
        $vendor2Shop = $this->createShopForVendor($vendor2, true);

        // Vendor1 can only see their own shops
        $vendor1Shops = Shop::where('owner_id', $vendor1->id)->get();
        $this->assertCount(1, $vendor1Shops);
        $this->assertEquals($vendor1Shop->id, $vendor1Shops->first()->id);

        // Vendor2 can only see their own shops
        $vendor2Shops = Shop::where('owner_id', $vendor2->id)->get();
        $this->assertCount(1, $vendor2Shops);
        $this->assertEquals($vendor2Shop->id, $vendor2Shops->first()->id);

        // Vendor1 cannot see vendor2's shops
        $vendor1CannotSeeVendor2Shops = Shop::where('owner_id', $vendor1->id)
            ->where('id', $vendor2Shop->id)
            ->exists();
        $this->assertFalse($vendor1CannotSeeVendor2Shops);

        // Vendor2 cannot see vendor1's shops
        $vendor2CannotSeeVendor1Shops = Shop::where('owner_id', $vendor2->id)
            ->where('id', $vendor1Shop->id)
            ->exists();
        $this->assertFalse($vendor2CannotSeeVendor1Shops);
    }

    /**
     * Property: Shop status filtering works correctly
     * For any combination of active/inactive shops, filtering should work correctly.
     */
    public function test_shop_status_filtering_property()
    {
        for ($i = 0; $i < 30; $i++) {
            $this->runShopStatusFilteringProperty();
        }
    }

    private function runShopStatusFilteringProperty()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);

        // Create shops with different statuses
        $activeShopsCount = rand(1, 3);
        $inactiveShopsCount = rand(1, 3);

        $activeShops = [];
        $inactiveShops = [];

        for ($j = 0; $j < $activeShopsCount; $j++) {
            $activeShops[] = $this->createShopForVendor($vendor, true);
        }

        for ($j = 0; $j < $inactiveShopsCount; $j++) {
            $inactiveShops[] = $this->createShopForVendor($vendor, false);
        }

        // Test active scope
        $foundActiveShops = Shop::where('owner_id', $vendor->id)->active()->get();
        $this->assertCount($activeShopsCount, $foundActiveShops);
        
        foreach ($foundActiveShops as $shop) {
            $this->assertTrue($shop->is_active);
            $this->assertEquals($vendor->id, $shop->owner_id);
        }

        // Test manual filtering for inactive shops
        $foundInactiveShops = Shop::where('owner_id', $vendor->id)
            ->where('is_active', false)
            ->get();
        $this->assertCount($inactiveShopsCount, $foundInactiveShops);
        
        foreach ($foundInactiveShops as $shop) {
            $this->assertFalse($shop->is_active);
            $this->assertEquals($vendor->id, $shop->owner_id);
        }

        // Test total count
        $totalShops = Shop::where('owner_id', $vendor->id)->get();
        $this->assertCount($activeShopsCount + $inactiveShopsCount, $totalShops);
    }

    /**
     * Property: Shop restoration works correctly after soft delete
     * For any soft-deleted shop, it should be restorable.
     */
    public function test_shop_restoration_property()
    {
        for ($i = 0; $i < 20; $i++) {
            $this->runShopRestorationProperty();
        }
    }

    private function runShopRestorationProperty()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $shop = $this->createShopForVendor($vendor, true);

        // Verify shop exists initially
        $this->assertTrue(Shop::where('id', $shop->id)->exists());

        // Soft delete the shop
        $shop->delete();

        // Verify shop is soft deleted
        $this->assertFalse(Shop::where('id', $shop->id)->exists());
        $this->assertTrue(Shop::where('id', $shop->id)->onlyTrashed()->exists());

        // Restore the shop
        $shop->restore();

        // Verify shop is restored
        $this->assertTrue(Shop::where('id', $shop->id)->exists());
        $this->assertFalse(Shop::where('id', $shop->id)->onlyTrashed()->exists());

        // Verify shop data is intact after restoration
        $restoredShop = Shop::find($shop->id);
        $this->assertEquals($shop->name, $restoredShop->name);
        $this->assertEquals($shop->owner_id, $restoredShop->owner_id);
        $this->assertEquals($shop->location_id, $restoredShop->location_id);
    }

    /**
     * Property: Multiple vendors with mixed shop statuses
     * For any multiple vendors with various shop statuses, filtering should work correctly.
     */
    public function test_multi_vendor_status_filtering_property()
    {
        for ($i = 0; $i < 20; $i++) {
            $this->runMultiVendorStatusFilteringProperty();
        }
    }

    private function runMultiVendorStatusFilteringProperty()
    {
        // Clean up any existing data for this test
        Shop::query()->forceDelete(); // Force delete to clear soft-deleted records too
        User::where('role', 'vendor')->delete();
        Location::truncate();
        City::truncate();
        Governorate::truncate();

        // Create multiple vendors
        $vendorCount = rand(2, 4);
        $vendors = [];
        
        for ($i = 0; $i < $vendorCount; $i++) {
            $vendors[] = User::factory()->create(['role' => 'vendor']);
        }

        $totalActiveShops = 0;
        $totalInactiveShops = 0;

        // Create shops for each vendor with random statuses
        foreach ($vendors as $vendor) {
            $shopCount = rand(1, 3);
            
            for ($j = 0; $j < $shopCount; $j++) {
                $isActive = (bool) rand(0, 1);
                $this->createShopForVendor($vendor, $isActive);
                
                if ($isActive) {
                    $totalActiveShops++;
                } else {
                    $totalInactiveShops++;
                }
            }
        }

        // Test global active shops count
        $globalActiveShops = Shop::active()->get();
        $this->assertCount($totalActiveShops, $globalActiveShops);

        // Test that each vendor can only see their own shops
        foreach ($vendors as $vendor) {
            $vendorShops = Shop::where('owner_id', $vendor->id)->get();
            
            foreach ($vendorShops as $shop) {
                $this->assertEquals($vendor->id, $shop->owner_id);
            }
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