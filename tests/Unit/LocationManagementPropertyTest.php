<?php

namespace Tests\Unit;

use App\Models\City;
use App\Models\Governorate;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Property-Based Test for Location Management Integrity
 * Feature: marketplace-platform, Property 3: Location Management Integrity
 * Validates: Requirements 2.1, 2.2, 2.3.
 */
class LocationManagementPropertyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Property 3: Location Management Integrity
     * For any location data with valid GPS coordinates, the system should store
     * the location and allow shop owners to create locations.
     */
    public function test_location_management_integrity_property()
    {
        // Run property test with 100 iterations
        for ($i = 0; $i < 100; $i++) {
            $this->runLocationManagementProperty();
        }
    }

    private function runLocationManagementProperty()
    {
        // Create governorate and city first with highly unique identifiers
        $timestamp = microtime(true) * 1000; // More precise timestamp
        $random = rand(100000, 999999);

        $governorateName = $this->generateRandomGovernorate();
        $governorate = Governorate::create([
            'name' => $governorateName,
            'slug' => strtolower(str_replace(' ', '-', $governorateName)) . '-' . $timestamp . '-' . $random,
        ]);

        $cityName = $this->generateRandomCity();
        $city = City::create([
            'governorate_id' => $governorate->id,
            'name' => $cityName,
            'slug' => strtolower(str_replace(' ', '-', $cityName)) . '-' . $timestamp . '-' . $random,
        ]);

        // Generate unique location data for each iteration
        $locationData = $this->generateUniqueLocationData($city->id);

        // Create location
        $location = Location::create($locationData);

        // Property assertions
        $this->assertInstanceOf(Location::class, $location);
        $this->assertEquals($locationData['city_id'], $location->city_id);
        $this->assertEquals($locationData['area'], $location->area);
        $this->assertEquals($locationData['latitude'], $location->latitude);
        $this->assertEquals($locationData['longitude'], $location->longitude);

        // Location should have timestamps
        $this->assertNotNull($location->created_at);
        $this->assertNotNull($location->updated_at);

        // GPS coordinates should be within valid ranges
        $this->assertGreaterThanOrEqual(-90, $location->latitude);
        $this->assertLessThanOrEqual(90, $location->latitude);
        $this->assertGreaterThanOrEqual(-180, $location->longitude);
        $this->assertLessThanOrEqual(180, $location->longitude);

        // Test relationships
        $this->assertInstanceOf(City::class, $location->city);
        $this->assertEquals($city->name, $location->city->name);
        $this->assertEquals($governorate->name, $location->city->governorate->name);
    }

    /**
     * Property: GPS coordinate validation
     * For any GPS coordinates within valid ranges, location should be created successfully.
     */
    public function test_gps_coordinate_validation_property()
    {
        for ($i = 0; $i < 50; $i++) {
            $this->runGpsCoordinateValidationProperty();
        }
    }

    private function runGpsCoordinateValidationProperty()
    {
        // Create governorate and city first with more unique identifiers
        $timestamp = microtime(true);
        $random = rand(1, 999999);

        $governorateName = $this->generateRandomGovernorate();
        $governorate = Governorate::create([
            'name' => $governorateName,
            'slug' => strtolower(str_replace(' ', '-', $governorateName)) . '-' . $timestamp . '-' . $random,
        ]);

        $cityName = $this->generateRandomCity();
        $city = City::create([
            'governorate_id' => $governorate->id,
            'name' => $cityName,
            'slug' => strtolower(str_replace(' ', '-', $cityName)) . '-' . $timestamp . '-' . $random,
        ]);

        $locationData = $this->generateUniqueLocationData($city->id);

        // Generate valid GPS coordinates
        $locationData['latitude'] = $this->generateValidLatitude();
        $locationData['longitude'] = $this->generateValidLongitude();

        $location = Location::create($locationData);

        $this->assertInstanceOf(Location::class, $location);
        $this->assertGreaterThanOrEqual(-90, $location->latitude);
        $this->assertLessThanOrEqual(90, $location->latitude);
        $this->assertGreaterThanOrEqual(-180, $location->longitude);
        $this->assertLessThanOrEqual(180, $location->longitude);
    }
    
    /**
     * Property: Multiple shops can exist in same area
     * For any location, multiple shops should be able to use the same area.
     */
    public function test_multiple_shops_same_area_property()
    {
        for ($i = 0; $i < 20; $i++) {
            $this->runMultipleShopsSameAreaProperty();
        }
    }

    private function runMultipleShopsSameAreaProperty()
    {
        // Clear database for this test
        Location::truncate();
        City::truncate();
        Governorate::truncate();

        // Create governorate and city
        $governorate = Governorate::create([
            'name' => $this->generateRandomGovernorate(),
            'slug' => strtolower(str_replace(' ', '-', $this->generateRandomGovernorate())),
        ]);

        $city = City::create([
            'governorate_id' => $governorate->id,
            'name' => $this->generateRandomCity(),
            'slug' => strtolower(str_replace(' ', '-', $this->generateRandomCity())),
        ]);

        $area = $this->generateRandomArea().'_'.time().'_'.rand(1, 10000);

        // Create multiple locations with same city-area (should succeed)
        for ($j = 0; $j < 3; $j++) {
            $locationData = $this->generateUniqueLocationData($city->id);
            $locationData['area'] = $area;

            $location = Location::create($locationData);
            $this->assertInstanceOf(Location::class, $location);
            $this->assertEquals($city->id, $location->city_id);
            $this->assertEquals($area, $location->area);
            $this->assertEquals($governorate->name, $location->city->governorate->name);
        }

        // Verify all 3 locations were created successfully
        $locationsInArea = Location::where('city_id', $city->id)
            ->where('area', $area)
            ->count();

        $this->assertEquals(3, $locationsInArea);
    }

    /**
     * Property: Basic location storage and retrieval
     * For any location, it should be stored and retrieved correctly.
     */
    public function test_basic_location_storage_property()
    {
        for ($i = 0; $i < 20; $i++) {
            $this->runBasicLocationStorageProperty();
        }
    }

    private function runBasicLocationStorageProperty()
    {
        // Clear database for this test
        Location::truncate();
        City::truncate();
        Governorate::truncate();

        // Create governorate and city
        $governorate = Governorate::create([
            'name' => $this->generateRandomGovernorate(),
            'slug' => strtolower(str_replace(' ', '-', $this->generateRandomGovernorate())),
        ]);

        $city = City::create([
            'governorate_id' => $governorate->id,
            'name' => $this->generateRandomCity(),
            'slug' => strtolower(str_replace(' ', '-', $this->generateRandomCity())),
        ]);

        // Create a reference location
        $refLocationData = $this->generateUniqueLocationData($city->id);
        $refLocation = Location::create($refLocationData);

        // Create nearby locations with different coordinates
        $nearbyLocations = [];
        for ($j = 0; $j < 2; $j++) {
            $nearbyData = $this->generateUniqueLocationData($city->id);
            $nearbyData['latitude'] = $refLocation->latitude + (rand(-100, 100) / 1000);
            $nearbyData['longitude'] = $refLocation->longitude + (rand(-100, 100) / 1000);
            $nearbyLocations[] = Location::create($nearbyData);
        }

        // Test that all locations are stored correctly
        $allLocations = Location::all();
        $this->assertGreaterThanOrEqual(3, $allLocations->count());

        // Test that we can find the reference location
        $foundLocation = Location::find($refLocation->id);
        $this->assertNotNull($foundLocation);
        $this->assertEquals($refLocation->city_id, $foundLocation->city_id);
        $this->assertEquals($refLocation->area, $foundLocation->area);
    }

    private function generateUniqueLocationData($cityId): array
    {
        $timestamp = time();
        $random = rand(1, 100000);

        return [
            'city_id' => $cityId,
            'area' => $this->generateRandomArea().'_'.$timestamp.'_'.$random,
            'latitude' => $this->generateValidLatitude(),
            'longitude' => $this->generateValidLongitude(),
        ];
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

        return $areas[array_rand($areas)].' '.rand(1, 100);
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

    /**
     * Property: Governorate filtering through city relationships
     * For any governorate filter, only locations from cities in that governorate should be returned.
     */
    public function test_governorate_filtering_property()
    {
        for ($i = 0; $i < 20; $i++) {
            $this->runGovernorateFilteringProperty();
        }
    }

    private function runGovernorateFilteringProperty()
    {
        // Clear database for this test
        Location::truncate();
        City::truncate();
        Governorate::truncate();

        // Create governorates
        $governorates = ['Cairo', 'Giza', 'Alexandria', 'Qalyubia', 'Sharqia'];
        $governorateModels = [];
        foreach ($governorates as $govName) {
            $governorateModels[] = Governorate::create([
                'name' => $govName,
                'slug' => strtolower($govName),
            ]);
        }

        $targetGovernorate = $governorateModels[array_rand($governorateModels)];

        // Create cities in different governorates
        $targetCities = [];
        $otherCities = [];

        for ($j = 0; $j < 2; $j++) {
            // Create city in target governorate
            $targetCities[] = City::create([
                'governorate_id' => $targetGovernorate->id,
                'name' => 'City_'.$j.'_'.time(),
                'slug' => 'city-'.$j.'-'.time(),
            ]);

            // Create city in other governorate
            $otherGovernorates = array_filter($governorateModels, fn($gov) => $gov->id !== $targetGovernorate->id);
            $otherGovernorate = $otherGovernorates[array_rand($otherGovernorates)];
            $otherCities[] = City::create([
                'governorate_id' => $otherGovernorate->id,
                'name' => 'OtherCity_'.$j.'_'.time(),
                'slug' => 'other-city-'.$j.'-'.time(),
            ]);
        }

        // Create locations in target governorate cities
        foreach ($targetCities as $city) {
            Location::create($this->generateUniqueLocationData($city->id));
        }

        // Create locations in other governorate cities
        foreach ($otherCities as $city) {
            Location::create($this->generateUniqueLocationData($city->id));
        }

        // Test governorate filtering through city relationships
        $targetCityIds = collect($targetCities)->pluck('id')->toArray();
        $filteredLocations = Location::whereIn('city_id', $targetCityIds)->get();

        $this->assertGreaterThanOrEqual(2, $filteredLocations->count());

        foreach ($filteredLocations as $location) {
            $this->assertEquals($targetGovernorate->id, $location->city->governorate_id);
        }
    }
}
