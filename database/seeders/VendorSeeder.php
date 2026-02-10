<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class VendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vendors = [
            [
                'name' => 'Ahmed Mohamed',
                'email' => 'ahmed.vendor@dukkan.com',
                'phone' => '+201001234567',
                'password' => Hash::make('vendor123'),
                'role' => 'vendor',
                'status' => 'active',
            ],
            [
                'name' => 'Fatma Hassan',
                'email' => 'fatma.vendor@dukkan.com',
                'phone' => '+201002345678',
                'password' => Hash::make('vendor123'),
                'role' => 'vendor',
                'status' => 'active',
            ],
            [
                'name' => 'Mohamed Ali',
                'email' => 'mohamed.vendor@dukkan.com',
                'phone' => '+201003456789',
                'password' => Hash::make('vendor123'),
                'role' => 'vendor',
                'status' => 'active',
            ],
            [
                'name' => 'Sara Ibrahim',
                'email' => 'sara.vendor@dukkan.com',
                'phone' => '+201004567890',
                'password' => Hash::make('vendor123'),
                'role' => 'vendor',
                'status' => 'active',
            ],
            [
                'name' => 'Khaled Mahmoud',
                'email' => 'khaled.vendor@dukkan.com',
                'phone' => '+201005678901',
                'password' => Hash::make('vendor123'),
                'role' => 'vendor',
                'status' => 'active',
            ],
        ];

        foreach ($vendors as $vendor) {
            User::create($vendor);
        }

        // Create additional test vendors in local/testing environments
        if (app()->environment(['local', 'testing'])) {
            User::factory()->count(5)->create([
                'role' => 'vendor',
                'status' => 'active',
            ]);
        }
    }
}
