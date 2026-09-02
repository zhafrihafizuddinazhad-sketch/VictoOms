<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's users.
     */
    public function run(): void
{
    // Owner
    $owner = User::firstOrCreate(
        ['email' => 'owner@victo.com'],
        [
            'name' => 'Owner',
            'password' => Hash::make('password123'),
        ]
    );

    $owner->assignRole('owner');

    // Admin
    $admin = User::firstOrCreate(
        ['email' => 'admin@victo.com'],
        [
            'name' => 'Admin',
            'password' => Hash::make('password123'),
        ]
    );

    $admin->assignRole('admin');

    // Designer
    $designer = User::firstOrCreate(
        ['email' => 'designer@victo.com'],
        [
            'name' => 'Designer',
            'password' => Hash::make('password123'),
        ]
    );

    $designer->assignRole('designer');

    // Cameraman
    $cameraman = User::firstOrCreate(
        ['email' => 'camera@victo.com'],
        [
            'name' => 'Cameraman',
            'password' => Hash::make('password123'),
        ]
    );

    $cameraman->assignRole('cameraman');
}
}