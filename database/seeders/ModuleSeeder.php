<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Daftar Modul Sistem
        $modules = [
            [
                'name' => 'Dashboard',
                'slug' => 'dashboard',
                'url' => '/dashboard',
                'icon' => 'home',
                'status' => true,
            ],
            [
                'name' => 'Integrasi Sistem',
                'slug' => 'integrasi-sistem',
                'url' => '/integrasi-sistem',
                'icon' => 'database',
                'status' => true,
            ],
            [
                'name' => 'Management User',
                'slug' => 'management-user',
                'url' => '/management-user',
                'icon' => 'users',
                'status' => true,
            ],
            [
                'name' => 'Data History',
                'slug' => 'history',
                'url' => '/history',
                'icon' => 'clock',
                'status' => true,
            ],
        ];

        foreach ($modules as $moduleData) {
            // Create Module in DB
            $module = Module::firstOrCreate(
                ['slug' => $moduleData['slug']],
                $moduleData
            );

            // Create Permission for this Module
            $permissionName = 'view module ' . $module->slug;
            Permission::firstOrCreate(['name' => $permissionName]);
        }

        // 2. Assign Default Permissions to Roles
        
        // Admin: Access All
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $allModulePermissions = Permission::where('name', 'like', 'view module %')->get();
        $adminRole->syncPermissions($allModulePermissions);

        // Supervisor: Access All except management-user
        $supervisorRole = Role::firstOrCreate(['name' => 'Supervisor']);
        $supervisorPermissions = $allModulePermissions->reject(function ($permission) {
            return $permission->name === 'view module management-user';
        });
        $supervisorRole->syncPermissions($supervisorPermissions);

        // User/Staff: Access Dashboard & History only
        $userRole = Role::firstOrCreate(['name' => 'User']);
        $userRole->syncPermissions(['view module dashboard', 'view module history']);

        // SuperUser: Access Dashboard, History, Integrasi
        $superUserRole = Role::firstOrCreate(['name' => 'SuperUser']);
        $superUserRole->syncPermissions([
            'view module dashboard', 
            'view module history', 
            'view module integrasi-sistem'
        ]);
    }
}
