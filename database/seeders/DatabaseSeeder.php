<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            ModuleSeeder::class,
        ]);

        // 1. Create Default Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@pgn.co.id'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
            ]
        );
        $admin->assignRole('Admin');

        // 2. Create Supervisor
        $supervisor = User::firstOrCreate(
            ['email' => 'supervisor@pgn.co.id'],
            [
                'name' => 'Operational Supervisor',
                'password' => bcrypt('password'),
            ]
        );
        $supervisor->assignRole('Supervisor');

        // 3. Create SuperUser
        $superUser = User::firstOrCreate(
            ['email' => 'staff@pgn.co.id'],
            [
                'name' => 'Operational Staff',
                'password' => bcrypt('password'),
            ]
        );
        $superUser->assignRole('SuperUser');

        // 4. Create Standard User
        $user = User::firstOrCreate(
            ['email' => 'user@pgn.co.id'],
            [
                'name' => 'Guest User',
                'password' => bcrypt('password'),
            ]
        );
        $user->assignRole('User');
    }
}
