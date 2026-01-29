<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Property-Based Test for User Registration and Authentication
 * Feature: marketplace-platform, Property 1: User Registration and Authentication
 * Validates: Requirements 1.1, 1.2, 1.5.
 */
class UserRegistrationPropertyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Property 1: User Registration and Authentication
     * For any valid user registration data, creating a user account should result in
     * a user with the correct role, hashed password, and unique email constraint enforcement.
     */
    public function test_user_registration_property()
    {
        // Run property test with 100 iterations
        for ($i = 0; $i < 100; $i++) {
            $this->runUserRegistrationProperty();
        }
    }

    private function runUserRegistrationProperty()
    {
        // Generate random valid user data
        $userData = $this->generateValidUserData();

        // Create user
        $user = User::create($userData);

        // Property assertions
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($userData['name'], $user->name);
        $this->assertEquals($userData['email'], $user->email);
        $this->assertEquals($userData['phone'], $user->phone);
        $this->assertEquals($userData['role'], $user->role);
        $this->assertEquals($userData['status'], $user->status);

        // Password should be hashed
        $this->assertTrue(Hash::check($userData['password'], $user->password));
        $this->assertNotEquals($userData['password'], $user->password);

        // User should have timestamps
        $this->assertNotNull($user->created_at);
        $this->assertNotNull($user->updated_at);

        // Test unique email constraint
        $duplicateUserData = $this->generateValidUserData();
        $duplicateUserData['email'] = $userData['email']; // Same email

        $this->expectException(QueryException::class);
        User::create($duplicateUserData);
    }

    /**
     * Property: Email uniqueness constraint
     * For any two users with the same email, the second creation should fail.
     */
    public function test_email_uniqueness_property()
    {
        for ($i = 0; $i < 50; $i++) {
            $this->runEmailUniquenessProperty();
        }
    }

    private function runEmailUniquenessProperty()
    {
        $email = $this->generateRandomEmail();

        // Create first user
        $userData1 = $this->generateValidUserData();
        $userData1['email'] = $email;
        $user1 = User::create($userData1);

        $this->assertInstanceOf(User::class, $user1);

        // Try to create second user with same email
        $userData2 = $this->generateValidUserData();
        $userData2['email'] = $email;

        $exceptionThrown = false;
        try {
            User::create($userData2);
        } catch (QueryException $e) {
            $exceptionThrown = true;
            // SQLite uses different error message than MySQL
            $this->assertTrue(
                str_contains($e->getMessage(), 'Duplicate entry') ||
                str_contains($e->getMessage(), 'UNIQUE constraint failed')
            );
        }

        $this->assertTrue($exceptionThrown, 'Expected QueryException for duplicate email');
    }

    /**
     * Property: Role-based user creation
     * For any valid role, user should be created with that role.
     */
    public function test_role_assignment_property()
    {
        $roles = ['shop_owner', 'admin'];

        for ($i = 0; $i < 50; $i++) {
            $role = $roles[array_rand($roles)];
            $userData = $this->generateValidUserData();
            $userData['role'] = $role;

            $user = User::create($userData);

            $this->assertEquals($role, $user->role);

            // Test scopes work correctly
            if ($role === 'shop_owner') {
                $this->assertTrue(User::shopOwners()->where('id', $user->id)->exists());
            }
        }
    }

    /**
     * Property: Status management
     * For any valid status, user should be created with that status.
     */
    public function test_status_assignment_property()
    {
        $statuses = ['active', 'suspended'];

        for ($i = 0; $i < 50; $i++) {
            $status = $statuses[array_rand($statuses)];
            $userData = $this->generateValidUserData();
            $userData['status'] = $status;

            $user = User::create($userData);

            $this->assertEquals($status, $user->status);

            // Test scopes work correctly
            if ($status === 'active') {
                $this->assertTrue(User::active()->where('id', $user->id)->exists());
            }
        }
    }

    private function generateValidUserData(): array
    {
        return [
            'name' => $this->generateRandomName(),
            'email' => $this->generateRandomEmail(),
            'phone' => $this->generateRandomPhone(),
            'password' => $this->generateRandomPassword(),
            'role' => $this->generateRandomRole(),
            'status' => $this->generateRandomStatus(),
        ];
    }

    private function generateRandomName(): string
    {
        $names = ['Ahmed Ali', 'Fatma Hassan', 'Mohamed Omar', 'Nour Mahmoud', 'Sara Ahmed'];

        return $names[array_rand($names)].' '.rand(1, 1000);
    }

    private function generateRandomEmail(): string
    {
        $domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com'];

        return 'user'.rand(1, 100000).'@'.$domains[array_rand($domains)];
    }

    private function generateRandomPhone(): string
    {
        return '+20'.rand(1000000000, 1999999999);
    }

    private function generateRandomPassword(): string
    {
        return 'password'.rand(1, 1000);
    }

    private function generateRandomRole(): string
    {
        $roles = ['shop_owner', 'admin'];

        return $roles[array_rand($roles)];
    }

    private function generateRandomStatus(): string
    {
        $statuses = ['active', 'suspended'];

        return $statuses[array_rand($statuses)];
    }
}
