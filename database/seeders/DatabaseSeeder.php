<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. CREATE ROLES
        $adminRole = Role::create(['name' => 'admin']);
        $staffRole = Role::create(['name' => 'staff']);
        $citizenRole = Role::create(['name' => 'citizen']);

        // 2. CREATE ADMIN USER
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name'  => 'User',
            'email'      => 'admin@example.com',
            'password'   => Hash::make('password'),
            'status'     => 'active'
        ]);

        // attach admin role
        $admin->roles()->attach($adminRole->id);

        // 3. CREATE TEST CITIZEN USER
        $user = User::create([
            'first_name' => 'Test',
            'last_name'  => 'User',
            'email'      => 'user@example.com',
            'password'   => Hash::make('password'),
            'status'     => 'active'
        ]);

        $user->roles()->attach($citizenRole->id);
    }
}