<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Property-Based Test for Authentication Token Management
 * Feature: marketplace-platform, Property 2: Authentication Token Management
 * **Validates: Requirements 1.3**
 */
class AuthPropertyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Property 2: Authentication Token Management
     * For any valid user credentials, authentication should provide access tokens, 
     * and invalid credentials should be rejected
     * **Validates: Requirements 1.3**
     */
    public function test_authentication_token_management_property()
    {
        // Run property test with 100 iterations (minimum for property-based testing)
        for ($i = 0; $i < 100; $i++) {
            // Clear any previous authentication state
            $this->app['auth']->forgetGuards();
            
            // Generate ALL random values ONCE at the start of each iteration
            $seed = $i * 1000; // Use iteration-based seed for consistency
            mt_srand($seed);
            
            $name = $this->generateRandomString(5, 20, $seed);
            $password = $this->generateRandomString(8, 20, $seed + 1);
            $role = $this->randomChoice(['vendor', 'admin'], $seed + 2);
            $status = $this->randomChoice(['active', 'suspended'], $seed + 3);
            $randomSuffix = mt_rand(10000, 99999);
            $email = "test_auth_{$i}_{$randomSuffix}@example.com";
            $wrongPassword = 'wrong_password_' . $randomSuffix;
            $invalidEmail = 'nonexistent_auth_' . $i . '_' . $randomSuffix . '@example.com';

            // Create user with known credentials
            User::create([
                'name' => $name,
                'email' => $email,
                'phone' => '+201234567890',
                'password' => Hash::make($password),
                'role' => $role,
                'status' => $status,
            ]);

            // Test 1: Valid credentials should authenticate successfully
            if ($role === 'vendor') {
                $loginResponse = $this->postJson('/api/v1/vendor/login', [
                    'email' => $email,
                    'password' => $password,
                ]);
            } else { // admin
                $loginResponse = $this->postJson('/api/v1/admin/login', [
                    'email' => $email,
                    'password' => $password,
                ]);
            }

            if ($status === 'active') {
                // Active users should be able to login
                $loginResponse->assertStatus(200);
                $loginResponse->assertJsonStructure([
                    'api_version',
                    'success',
                    'data' => [
                        'user' => ['id', 'name', 'email', 'phone', 'role', 'status'],
                        'token',
                        'token_type'
                    ]
                ]);
                
                $responseData = $loginResponse->json();
                $this->assertTrue($responseData['success']);
                $this->assertEquals($email, $responseData['data']['user']['email']);
                $this->assertEquals($role, $responseData['data']['user']['role']);
                $this->assertNotEmpty($responseData['data']['token']);
                $this->assertEquals('Bearer', $responseData['data']['token_type']);
                
                // Test token works for authenticated endpoints
                $token = $responseData['data']['token'];
                if ($role === 'vendor') {
                    $meResponse = $this->withHeaders([
                        'Authorization' => 'Bearer ' . $token,
                    ])->getJson('/api/v1/vendor/me');
                } else { // admin
                    $meResponse = $this->withHeaders([
                        'Authorization' => 'Bearer ' . $token,
                    ])->getJson('/api/v1/admin/me');
                }
                
                $meResponse->assertStatus(200);
                $meResponse->assertJsonStructure([
                    'success',
                    'data' => [
                        'user' => ['id', 'name', 'email', 'phone', 'role', 'status']
                    ]
                ]);
                
                $meResponseData = $meResponse->json();
                $this->assertTrue($meResponseData['success']);
                $this->assertEquals($email, $meResponseData['data']['user']['email']);
                $this->assertEquals($role, $meResponseData['data']['user']['role']);
                $this->assertEquals($status, $meResponseData['data']['user']['status']);
            } else {
                // Suspended users should be rejected
                $loginResponse->assertStatus(403);
                $loginResponse->assertJson([
                    'success' => false,
                    'error' => [
                        'code' => 'ACCOUNT_SUSPENDED'
                    ]
                ]);
            }

            // Test 2: Invalid credentials should be rejected
            if ($role === 'vendor') {
                $invalidLoginResponse = $this->postJson('/api/v1/vendor/login', [
                    'email' => $email,
                    'password' => $wrongPassword,
                ]);
            } else { // admin
                $invalidLoginResponse = $this->postJson('/api/v1/admin/login', [
                    'email' => $email,
                    'password' => $wrongPassword,
                ]);
            }

            $invalidLoginResponse->assertStatus(401);
            $invalidLoginResponse->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'AUTHENTICATION_ERROR'
                ]
            ]);

            // Test 3: Invalid email should be rejected
            if ($role === 'vendor') {
                $invalidEmailResponse = $this->postJson('/api/v1/vendor/login', [
                    'email' => $invalidEmail,
                    'password' => $password,
                ]);
            } else { // admin
                $invalidEmailResponse = $this->postJson('/api/v1/admin/login', [
                    'email' => $invalidEmail,
                    'password' => $password,
                ]);
            }

            $invalidEmailResponse->assertStatus(401);
            $invalidEmailResponse->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'AUTHENTICATION_ERROR'
                ]
            ]);
        }
    }

    /**
     * Property test for token logout functionality
     * For any authenticated user, logout should successfully complete
     */
    public function test_token_logout_property()
    {
        // Run property test with 50 iterations
        for ($i = 0; $i < 50; $i++) {
            // Clear any previous authentication state
            $this->app['auth']->forgetGuards();
            
            // Generate ALL random values ONCE at the start of each iteration
            $seed = $i * 1000 + 50000; // Use iteration-based seed for consistency
            mt_srand($seed);
            
            $name = $this->generateRandomString(5, 20, $seed);
            $password = $this->generateRandomString(8, 20, $seed + 1);
            $randomSuffix = mt_rand(10000, 99999);
            $email = "test_logout_{$i}_{$randomSuffix}@example.com";

            // Create active user
            User::create([
                'name' => $name,
                'email' => $email,
                'phone' => '+201234567890',
                'password' => Hash::make($password),
                'role' => 'vendor',
                'status' => 'active',
            ]);

            // Login to get token
            $loginResponse = $this->postJson('/api/v1/vendor/login', [
                'email' => $email,
                'password' => $password,
            ]);

            $loginResponse->assertStatus(200);
            $token = $loginResponse->json('data.token');

            // Verify token works
            $meResponse = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->getJson('/api/v1/vendor/me');
            $meResponse->assertStatus(200);

            // Logout should succeed
            $logoutResponse = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->postJson('/api/v1/vendor/logout');

            $logoutResponse->assertStatus(200);
            $logoutResponse->assertJsonStructure([
                'success',
                'data' => ['message']
            ]);
            
            $logoutData = $logoutResponse->json();
            $this->assertTrue($logoutData['success']);
            $this->assertEquals('Successfully logged out', $logoutData['data']['message']);
        }
    }

    /**
     * Generate a random string of specified length range using a fixed seed
     */
    private function generateRandomString(int $minLength, int $maxLength, ?int $seed = null): string
    {
        if ($seed !== null) {
            mt_srand($seed);
        }
        
        $length = mt_rand($minLength, $maxLength);
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $string = '';
        
        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[mt_rand(0, strlen($characters) - 1)];
        }
        
        return $string;
    }

    /**
     * Choose a random element from an array using a fixed seed
     */
    private function randomChoice(array $choices, ?int $seed = null)
    {
        if ($seed !== null) {
            mt_srand($seed);
        }
        return $choices[array_rand($choices)];
    }
}