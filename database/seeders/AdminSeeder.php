<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@dukkan.com',
            'phone' => '+201000000000',
            'password' => Hash::make('admin123456'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Create additional admin users for testing
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@dukkan.com',
            'phone' => '+201000000001',
            'password' => Hash::make('superadmin123'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Create a test admin user for development
        if (app()->environment(['local', 'testing'])) {
            User::create([
                'name' => 'Test Admin',
                'email' => 'testadmin@example.com',
                'phone' => '+201000000002',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'status' => 'active',
            ]);
        }
    }
}