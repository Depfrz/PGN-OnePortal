<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;
use App\Models\User;
use App\Models\ModuleAccess;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Daftar Semua Modul (Sistem + Dummy)
        $modules = [
            // System Modules
            [
                'name' => 'Buku Saku',
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
            [
                'name' => 'List Pengawasan',
                'slug' => 'list-pengawasan',
                'url' => '/list-pengawasan',
                'icon' => 'clipboard',
                'status' => true,
            ],
            // Business Modules
            [
                'name' => 'HCM SIP-PGN',
                'slug' => 'hcm-sip-pgn',
                'url' => '#',
                'icon' => 'briefcase',
                'status' => true,
            ],
            [
                'name' => 'Project Management Office',
                'slug' => 'pmo',
                'url' => '#',
                'icon' => 'clipboard',
                'status' => true,
            ],
            [
                'name' => 'Procurement System',
                'slug' => 'procurement',
                'url' => '#',
                'icon' => 'shopping-cart',
                'status' => true,
            ],
        ];

        // 2. Create Modules and Permissions
        foreach ($modules as $moduleData) {
            $module = Module::firstOrCreate(
                ['slug' => $moduleData['slug']],
                $moduleData
            );

            // Create Permission for this Module (Legacy Spatie Logic)
            $permissionName = 'view module ' . $module->slug;
            Permission::firstOrCreate(['name' => $permissionName]);
        }

        // 3. Assign Permissions to Roles (Append, don't overwrite)
        // Use givePermissionTo instead of syncPermissions to preserve RoleSeeder permissions
        
        $allModulePermissions = Permission::where('name', 'like', 'view module %')->get();

        // Admin: Access All
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $adminRole->givePermissionTo($allModulePermissions);

        // Supervisor: Access All except management-user
        $supervisorRole = Role::firstOrCreate(['name' => 'Supervisor']);
        $supervisorPermissions = $allModulePermissions->reject(function ($permission) {
            return $permission->name === 'view module management-user';
        });
        $supervisorRole->givePermissionTo($supervisorPermissions);

        // User: Access Dashboard & History
        $userRole = Role::firstOrCreate(['name' => 'User']);
        $userPermissions = $allModulePermissions->filter(function ($permission) {
             return in_array($permission->name, ['view module dashboard', 'view module history']);
        });
        $userRole->givePermissionTo($userPermissions);

        // SuperUser: Access Dashboard, History, Integrasi
        $superUserRole = Role::firstOrCreate(['name' => 'SuperUser']);
        $superUserPermissions = $allModulePermissions->filter(function ($permission) {
             return in_array($permission->name, ['view module dashboard', 'view module history', 'view module integrasi-sistem']);
        });
        $superUserRole->givePermissionTo($superUserPermissions);

        // 4. Assign ModuleAccess for Specific Users (Legacy/Hybrid support)
        
        // Example: Assign Dashboard to all users in ModuleAccess table
        $allUsers = User::all();
        $dashboardModule = Module::where('slug', 'dashboard')->first();

        if ($dashboardModule) {
            foreach ($allUsers as $user) {
                ModuleAccess::updateOrCreate(
                    ['user_id' => $user->id, 'module_id' => $dashboardModule->id],
                    [
                        'can_read' => true,
                        'can_write' => false,
                        'can_delete' => false,
                    ]
                );
            }
        }
    }
}
