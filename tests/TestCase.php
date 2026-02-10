<?php

namespace Tests;

use App\Models\City;
use App\Models\Governorate;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Get JSON with default X-City header for website endpoints
     */
    protected function getJsonWithCity(string $uri, array $headers = []): \Illuminate\Testing\TestResponse
    {
        // Ensure we have a default city in the database
        if (!isset($headers['X-City'])) {
            $city = City::where('slug', 'cairo')->first();
            
            if (!$city) {
                // Create a default city for testing
                $governorate = Governorate::firstOrCreate(
                    ['slug' => 'cairo'],
                    ['name' => 'Cairo']
                );
                
                $city = City::firstOrCreate(
                    ['slug' => 'cairo'],
                    ['governorate_id' => $governorate->id, 'name' => 'Cairo']
                );
            }
            
            $headers['X-City'] = 'cairo';
        }
        
        return $this->getJson($uri, $headers);
    }
    
    /**
     * Post JSON with default X-City header for website endpoints
     */
    protected function postJsonWithCity(string $uri, array $data = [], array $headers = []): \Illuminate\Testing\TestResponse
    {
        if (!isset($headers['X-City'])) {
            $headers['X-City'] = 'cairo';
        }
        
        return $this->postJson($uri, $data, $headers);
    }
}
