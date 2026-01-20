<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        // Create Default Admin User if not exists
        $admin = User::firstOrCreate(
            ['email' => 'admin@pgn.co.id'],
            [
                'name' => 'IT Administrator',
                'password' => bcrypt('password'), // Change this in production
            ]
        );
        $admin->assignRole('Admin');

        // Create a Test SuperUser
        $superUser = User::firstOrCreate(
            ['email' => 'staff@pgn.co.id'],
            [
                'name' => 'Operational Staff',
                'password' => bcrypt('password'),
            ]
        );
        $superUser->assignRole('SuperUser');

        // Create a Test Supervisor
        $supervisor = User::firstOrCreate(
            ['email' => 'supervisor@pgn.co.id'],
            [
                'name' => 'Project Manager',
                'password' => bcrypt('password'),
            ]
        );
        $supervisor->assignRole('Supervisor');

        // Create a Test User (Auditor/Guest)
        $user = User::firstOrCreate(
            ['email' => 'user@pgn.co.id'],
            [
                'name' => 'Guest Auditor',
                'password' => bcrypt('password'),
            ]
        );
        $user->assignRole('User');
    }
}
