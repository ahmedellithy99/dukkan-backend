<?php

namespace Tests\Unit;

use App\Models\User;
use Eris\Generator;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Property-Based Test using Eris library
 * Testing User Registration with proper property-based testing.
 */
class ErisPropertyTest extends TestCase
{
    use RefreshDatabase, TestTrait;

    /**
     * Property test for user registration using Eris.
     */
    public function test_user_registration_with_eris()
    {
        $this->forAll(
            Generator\string(),
            Generator\int(),
            Generator\string(),
            Generator\elements(['shop_owner', 'admin']),
            Generator\elements(['active', 'suspended'])
        )->then(function ($name, $emailSeed, $password, $role, $status) {
            // Skip empty strings
            if (empty($name) || empty($password)) {
                return;
            }

            // Clear database for each iteration to avoid unique constraint issues
            User::truncate();

            $email = 'user'.abs($emailSeed).time().rand(1, 10000).'@example.com';

            $userData = [
                'name' => $name,
                'email' => $email,
                'phone' => '+201234567890',
                'password' => $password,
                'role' => $role,
                'status' => $status,
            ];

            $user = User::create($userData);

            $this->assertInstanceOf(User::class, $user);
            $this->assertEquals($name, $user->name);
            $this->assertEquals($email, $user->email);
            $this->assertEquals($role, $user->role);
            $this->assertEquals($status, $user->status);
        });
    }
}
