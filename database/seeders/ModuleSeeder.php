<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;
<<<<<<< HEAD
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
=======
use App\Models\User;
use App\Models\ModuleAccess;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Dummy Modules
        $modules = [
            [
                'name' => 'HCM SIP-PGN',
                'slug' => 'hcm-sip-pgn',
                'description' => 'Sistem manajemen sumber daya manusia.',
                'url' => '#',
                'icon' => null,
            ],
            [
                'name' => 'Project Management Office',
                'slug' => 'pmo',
                'description' => 'Sistem pemantauan proyek.',
                'url' => '#',
                'icon' => null,
            ],
            [
                'name' => 'Procurement System',
                'slug' => 'procurement',
                'description' => 'Sistem pengadaan barang dan jasa.',
                'url' => '#',
                'icon' => null,
            ],
        ];

        foreach ($modules as $mod) {
            Module::firstOrCreate(['slug' => $mod['slug']], $mod);
        }

        // 2. Assign Access to Dummy Users (if they exist)
        
        // User (Read Only, Specific Module)
        $user = User::where('email', 'user@pgn.co.id')->first();
        $hcmModule = Module::where('slug', 'hcm-sip-pgn')->first();
        
        if ($user && $hcmModule) {
            ModuleAccess::updateOrCreate(
                ['user_id' => $user->id, 'module_id' => $hcmModule->id],
                [
                    'can_read' => true,
                    'can_write' => false,
                    'can_delete' => false,
                ]
            );
        }

        // SuperUser (Read/Write, Specific Module)
        $superUser = User::where('email', 'staff@pgn.co.id')->first();
        $pmoModule = Module::where('slug', 'pmo')->first();

        if ($superUser && $pmoModule) {
            ModuleAccess::updateOrCreate(
                ['user_id' => $superUser->id, 'module_id' => $pmoModule->id],
                [
                    'can_read' => true,
                    'can_write' => true, // Can input
                    'can_delete' => false,
                ]
            );
        }
>>>>>>> 97571af1c27ffb016b6c4bcf16b211a49a06893b
    }
}
