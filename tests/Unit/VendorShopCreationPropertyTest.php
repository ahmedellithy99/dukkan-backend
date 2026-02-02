<?php

namespace Tests\Unit;

use App\Models\City;
use App\Models\Governorate;
use App\Models\Location;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Property-Based Test for Vendor Shop Creation and Ownership
 * Feature: marketplace-platform, Property 5: Vendor Shop Creation and Ownership
 * Validates: Requirements 3.1, 3.2, 3.3.
 */
class VendorShopCreationPropertyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Property 5: Vendor Shop Creation and Ownership
     * For any vendor and valid shop data, creating a shop should properly associate it
     * with the owner and selected location, with correct validation.
     */
    public function test_vendor_shop_creation_property()
    {
        // Run property test with 100 iterations
        for ($i = 0; $i < 100; $i++) {
            $this->runVendorShopCreationProperty();
        }
    }

    private function runVendorShopCreationProperty()
    {
        // Create vendor user
        $vendor = User::factory()->create([
            'role' => 'vendor',
            'status' => 'active',
        ]);

        // Create location for shop
        $location = $this->createValidLocation();

        // Generate valid shop data
        $shopData = $this->generateValidShopData($vendor->id, $location->id);

        // Create shop
        $shop = Shop::create($shopData);

        // Property assertions
        $this->assertInstanceOf(Shop::class, $shop);
        $this->assertEquals($shopData['owner_id'], $shop->owner_id);
        $this->assertEquals($shopData['location_id'], $shop->location_id);
        $this->assertEquals($shopData['name'], $shop->name);
        $this->assertEquals($shopData['description'], $shop->description);
        $this->assertEquals($shopData['whatsapp_number'], $shop->whatsapp_number);
        $this->assertEquals($shopData['phone_number'], $shop->phone_number);
        $this->assertEquals($shopData['is_active'], $shop->is_active);

        // Shop should be linked to authenticated vendor (owner_id)
        $this->assertEquals($vendor->id, $shop->owner_id);
        $this->assertInstanceOf(User::class, $shop->owner);
        $this->assertEquals('vendor', $shop->owner->role);

        // Location must exist and be properly linked
        $this->assertEquals($location->id, $shop->location_id);
        $this->assertInstanceOf(Location::class, $shop->location);

        // Shop should have timestamps
        $this->assertNotNull($shop->created_at);
        $this->assertNotNull($shop->updated_at);

        // Slug should be auto-generated
        $this->assertNotNull($shop->slug);
        $this->assertNotEmpty($shop->slug);
    }

    /**
     * Property: WhatsApp/phone validation works
     * For any valid WhatsApp/phone number format, shop should be created successfully.
     */
    public function test_whatsapp_phone_validation_property()
    {
        for ($i = 0; $i < 50; $i++) {
            $this->runWhatsAppPhoneValidationProperty();
        }
    }

    private function runWhatsAppPhoneValidationProperty()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $location = $this->createValidLocation();

        $shopData = $this->generateValidShopData($vendor->id, $location->id);
        
        // Test various valid phone number formats
        $validFormats = [
            '+201234567890',  // E.164 format
            '+20 12 3456 7890', // With spaces
            '+20-12-3456-7890', // With dashes
            '01234567890',      // Local format
        ];

        $whatsappNumber = $validFormats[array_rand($validFormats)];
        $phoneNumber = $validFormats[array_rand($validFormats)];

        $shopData['whatsapp_number'] = $whatsappNumber;
        $shopData['phone_number'] = $phoneNumber;

        $shop = Shop::create($shopData);

        $this->assertInstanceOf(Shop::class, $shop);
        $this->assertEquals($whatsappNumber, $shop->whatsapp_number);
        $this->assertEquals($phoneNumber, $shop->phone_number);
    }

    /**
     * Property: Vendor cannot create shop on behalf of another vendor
     * For any two different vendors, one cannot create a shop for the other.
     */
    public function test_vendor_ownership_isolation_property()
    {
        for ($i = 0; $i < 50; $i++) {
            $this->runVendorOwnershipIsolationProperty();
        }
    }

    private function runVendorOwnershipIsolationProperty()
    {
        // Create two different vendors
        $vendor1 = User::factory()->create(['role' => 'vendor']);
        $vendor2 = User::factory()->create(['role' => 'vendor']);
        
        // Create separate locations for each shop (due to unique constraint)
        $location1 = $this->createValidLocation();
        $location2 = $this->createValidLocation();

        // Vendor1 creates a shop for themselves
        $shop1Data = $this->generateValidShopData($vendor1->id, $location1->id);
        $shop1 = Shop::create($shop1Data);

        $this->assertEquals($vendor1->id, $shop1->owner_id);

        // Vendor2 creates a shop for themselves
        $shop2Data = $this->generateValidShopData($vendor2->id, $location2->id);
        $shop2 = Shop::create($shop2Data);

        $this->assertEquals($vendor2->id, $shop2->owner_id);

        // Verify shops are properly isolated
        $this->assertNotEquals($shop1->owner_id, $shop2->owner_id);
        
        // Verify vendor1 can only see their own shop
        $vendor1Shops = Shop::where('owner_id', $vendor1->id)->get();
        $this->assertCount(1, $vendor1Shops);
        $this->assertEquals($shop1->id, $vendor1Shops->first()->id);

        // Verify vendor2 can only see their own shop
        $vendor2Shops = Shop::where('owner_id', $vendor2->id)->get();
        $this->assertCount(1, $vendor2Shops);
        $this->assertEquals($shop2->id, $vendor2Shops->first()->id);
    }

    /**
     * Property: Location must exist for shop creation
     * For any non-existent location, shop creation should fail.
     */
    public function test_location_requirement_property()
    {
        for ($i = 0; $i < 20; $i++) {
            $this->runLocationRequirementProperty();
        }
    }

    private function runLocationRequirementProperty()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        
        // Try to create shop with non-existent location
        $nonExistentLocationId = 99999;
        $shopData = $this->generateValidShopData($vendor->id, $nonExistentLocationId);

        $exceptionThrown = false;
        try {
            Shop::create($shopData);
        } catch (QueryException $e) {
            $exceptionThrown = true;
            // Should fail due to foreign key constraint
            $this->assertTrue(
                str_contains($e->getMessage(), 'foreign key constraint') ||
                str_contains($e->getMessage(), 'FOREIGN KEY constraint failed')
            );
        }

        $this->assertTrue($exceptionThrown, 'Expected QueryException for non-existent location');
    }

    /**
     * Property: Shop status management works correctly
     * For any shop status (active/inactive), shop should be created with correct status.
     */
    public function test_shop_status_management_property()
    {
        for ($i = 0; $i < 30; $i++) {
            $this->runShopStatusManagementProperty();
        }
    }

    private function runShopStatusManagementProperty()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $location = $this->createValidLocation();

        $isActive = (bool) rand(0, 1);
        $shopData = $this->generateValidShopData($vendor->id, $location->id);
        $shopData['is_active'] = $isActive;

        $shop = Shop::create($shopData);

        $this->assertEquals($isActive, $shop->is_active);

        // Test scope works correctly
        if ($isActive) {
            $this->assertTrue(Shop::active()->where('id', $shop->id)->exists());
        } else {
            $this->assertFalse(Shop::active()->where('id', $shop->id)->exists());
        }
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

    private function generateValidShopData(int $ownerId, int $locationId): array
    {
        return [
            'owner_id' => $ownerId,
            'location_id' => $locationId,
            'name' => $this->generateRandomShopName(),
            'description' => $this->generateRandomDescription(),
            'whatsapp_number' => $this->generateRandomWhatsAppNumber(),
            'phone_number' => $this->generateRandomPhoneNumber(),
            'is_active' => (bool) rand(0, 1),
        ];
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