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
        $this->call([
            RoleSeeder::class,
            ModuleSeeder::class,
        ]);

        // Create Default Admin User if not exists
        $admin = User::firstOrCreate(
            ['email' => 'admin@pgn.co.id'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'), // Change this in production
            ]
        );
        $admin->assignRole('Admin');

        // Create Supervisor
        $supervisor = User::firstOrCreate(
            ['email' => 'supervisor@pgn.co.id'],
            [
                'name' => 'Operational Supervisor',
                'password' => bcrypt('password'),
            ]
        );
        $supervisor->assignRole('Supervisor');

        // Create SuperUser
        $superUser = User::firstOrCreate(
            ['email' => 'staff@pgn.co.id'],
            [
                'name' => 'Operational Staff',
                'password' => bcrypt('password'),
            ]
        );
        $superUser->assignRole('SuperUser');

<<<<<<< HEAD
        // Create Standard User
        $user = User::firstOrCreate(
            ['email' => 'user@pgn.co.id'],
            [
                'name' => 'Guest User',
=======
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
>>>>>>> 97571af1c27ffb016b6c4bcf16b211a49a06893b
                'password' => bcrypt('password'),
            ]
        );
        $user->assignRole('User');
<<<<<<< HEAD
=======

        // Seed Modules and Access
        $this->call(ModuleSeeder::class);
>>>>>>> 97571af1c27ffb016b6c4bcf16b211a49a06893b
    }
}
