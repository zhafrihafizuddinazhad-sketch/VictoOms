<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Seed the application's roles.
     */
    public function run(): void
{
    Role::firstOrCreate(['name' => 'owner']);

    Role::firstOrCreate(['name' => 'admin']);

    Role::firstOrCreate(['name' => 'designer']);

    Role::firstOrCreate(['name' => 'cameraman']);
}
}