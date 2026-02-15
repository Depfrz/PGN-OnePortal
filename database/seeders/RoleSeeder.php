<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Define Permissions
        // We focus on broad permissions here. Specific module access is handled by ModuleAccess table.
        $permissions = [
            'view dashboard',
            'manage systems', // Create/Edit/Delete Modules (Global)
            'manage users',   // CRUD Users
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 2. Define Roles and Assign Permissions

        // Role: User
        // Constraint: Login enabled. Only see assigned modules. No edit/delete.
        $userRole = Role::firstOrCreate(['name' => 'User']);
        $userRole->syncPermissions([
            'view dashboard',
            // Specific module permissions will be checked via ModuleAccess, not global Spatie permissions
        ]);

        // Role: SuperUser
        // Constraint: Login enabled. See assigned modules. Input data (write) on assigned modules. No delete.
        $superUserRole = Role::firstOrCreate(['name' => 'SuperUser']);
        $superUserRole->syncPermissions([
            'view dashboard',
        ]);

        // Role: Supervisor
        // Constraint: Full access to all modules (Read/Write/Delete).
        $supervisorRole = Role::firstOrCreate(['name' => 'Supervisor']);
        $supervisorRole->syncPermissions([
            'view dashboard',
            'manage systems', // Implies full module control
        ]);

        // Role: Admin
        // Constraint: Supervisor rights + User Management.
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $adminRole->syncPermissions([
            'view dashboard',
            'manage systems',
            'manage users',
        ]);
    }
}
