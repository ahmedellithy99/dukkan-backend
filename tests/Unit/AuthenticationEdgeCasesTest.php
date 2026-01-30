<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Unit Tests for Authentication Edge Cases
 * Test invalid credentials, expired tokens, role permissions
 * **Validates: Requirements 1.3, 1.4**
 */
class AuthenticationEdgeCasesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test registration with invalid data
     */
    public function test_registration_with_invalid_data()
    {
        // Test missing required fields
        $response = $this->postJson('/api/v1/vendor/register', []);
        
        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'error' => [
                'code' => 'VALIDATION_ERROR',
                'message' => 'Validation failed'
            ]
        ]);
        $response->assertJsonStructure([
            'error' => [
                'fields' => [
                    'name',
                    'email',
                    'phone',
                    'password'
                ]
            ]
        ]);
    }

    /**
     * Test registration with duplicate email
     */
    public function test_registration_with_duplicate_email()
    {
        // Create existing user
        User::create([
            'name' => 'Existing User',
            'email' => 'test@example.com',
            'phone' => '+201234567890',
            'password' => Hash::make('password123'),
            'role' => 'vendor',
            'status' => 'active',
        ]);

        // Try to register with same email
        $response = $this->postJson('/api/v1/vendor/register', [
            'name' => 'New User',
            'email' => 'test@example.com',
            'phone' => '+201234567891',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'error' => [
                'code' => 'VALIDATION_ERROR'
            ]
        ]);
        $response->assertJsonPath('error.fields.email.0', 'The email has already been taken.');
    }

    /**
     * Test login with invalid credentials
     */
    public function test_login_with_invalid_credentials()
    {
        // Create user
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+201234567890',
            'password' => Hash::make('correct_password'),
            'role' => 'vendor',
            'status' => 'active',
        ]);

        // Test wrong password
        $response = $this->postJson('/api/v1/vendor/login', [
            'email' => 'test@example.com',
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'error' => [
                'code' => 'AUTHENTICATION_ERROR',
                'message' => 'Invalid credentials'
            ]
        ]);
    }

    /**
     * Test login with non-existent email
     */
    public function test_login_with_nonexistent_email()
    {
        $response = $this->postJson('/api/v1/vendor/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'error' => [
                'code' => 'AUTHENTICATION_ERROR',
                'message' => 'Invalid credentials'
            ]
        ]);
    }

    /**
     * Test login with suspended account
     */
    public function test_login_with_suspended_account()
    {
        // Create suspended user
        User::create([
            'name' => 'Suspended User',
            'email' => 'suspended@example.com',
            'phone' => '+201234567890',
            'password' => Hash::make('password123'),
            'role' => 'vendor',
            'status' => 'suspended',
        ]);

        $response = $this->postJson('/api/v1/vendor/login', [
            'email' => 'suspended@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'error' => [
                'code' => 'ACCOUNT_SUSPENDED',
                'message' => 'Account is suspended'
            ]
        ]);
    }

    /**
     * Test accessing protected route without token
     */
    public function test_accessing_protected_route_without_token()
    {
        $response = $this->getJson('/api/v1/vendor/me');

        $response->assertStatus(401);
    }

    /**
     * Test accessing protected route with invalid token
     */
    public function test_accessing_protected_route_with_invalid_token()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid_token_here',
        ])->getJson('/api/v1/vendor/me');

        $response->assertStatus(401);
    }

    /**
     * Test password validation requirements
     */
    public function test_password_validation_requirements()
    {
        // Test short password
        $response = $this->postJson('/api/v1/vendor/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+201234567890',
            'password' => '123',
            'password_confirmation' => '123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('error.fields.password.0', 'The password field must be at least 8 characters.');
    }

    /**
     * Test password confirmation mismatch
     */
    public function test_password_confirmation_mismatch()
    {
        $response = $this->postJson('/api/v1/vendor/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+201234567890',
            'password' => 'password123',
            'password_confirmation' => 'different_password',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('error.fields.password.0', 'The password field confirmation does not match.');
    }

    /**
     * Test email format validation
     */
    public function test_email_format_validation()
    {
        $response = $this->postJson('/api/v1/vendor/register', [
            'name' => 'Test User',
            'email' => 'invalid_email_format',
            'phone' => '+201234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('error.fields.email.0', 'The email field must be a valid email address.');
    }

    /**
     * Test successful authentication flow
     */
    public function test_successful_authentication_flow()
    {
        // Register user
        $registerResponse = $this->postJson('/api/v1/vendor/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+201234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $registerResponse->assertStatus(201);
        $registerResponse->assertJsonStructure([
            'api_version',
            'success',
            'data' => [
                'user' => ['id', 'name', 'email', 'phone', 'role', 'status'],
                'token',
                'token_type'
            ]
        ]);

        $token = $registerResponse->json('data.token');

        // Test accessing protected route with valid token
        $meResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/vendor/me');

        $meResponse->assertStatus(200);
        $meResponse->assertJson([
            'success' => true,
            'data' => [
                'user' => [
                    'email' => 'test@example.com',
                    'role' => 'vendor',
                    'status' => 'active'
                ]
            ]
        ]);

        // Test logout
        $logoutResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/vendor/logout');

        $logoutResponse->assertStatus(200);
        $logoutResponse->assertJson([
            'success' => true,
            'data' => [
                'message' => 'Successfully logged out'
            ]
        ]);
    }

    /**
     * Test role assignment on registration
     */
    public function test_role_assignment_on_registration()
    {
        $response = $this->postJson('/api/v1/vendor/register', [
            'name' => 'Test Vendor',
            'email' => 'vendor@example.com',
            'phone' => '+201234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.user.role', 'vendor');
        
        // Verify in database
        $user = User::where('email', 'vendor@example.com')->first();
        $this->assertEquals('vendor', $user->role);
        $this->assertEquals('active', $user->status);
    }
}