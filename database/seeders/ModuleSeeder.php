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
            // Group: Buku Saku
            [
                'name' => 'Buku Saku',
                'slug' => 'buku-saku',
                'url' => '/buku-saku',
                'icon' => 'home',
                'group' => 'Buku Saku',
                'status' => true,
            ],
            [
                'name' => 'Dokumen Favorit',
                'slug' => 'buku-saku-favorites',
                'url' => '/buku-saku/favorites',
                'icon' => 'star',
                'group' => 'Buku Saku',
                'status' => true,
            ],
            [
                'name' => 'Riwayat Dokumen',
                'slug' => 'buku-saku-history',
                'url' => '/buku-saku/history',
                'icon' => 'clock',
                'group' => 'Buku Saku',
                'status' => true,
            ],
            [
                'name' => 'Pengecekan File',
                'slug' => 'buku-saku-approval',
                'url' => '/buku-saku/approval',
                'icon' => 'check-circle',
                'group' => 'Buku Saku',
                'status' => true,
            ],
            [
                'name' => 'Upload Dokumen',
                'slug' => 'buku-saku-upload',
                'url' => '/buku-saku/upload',
                'icon' => 'upload',
                'group' => 'Buku Saku',
                'status' => true,
            ],
            [
                'name' => 'Dokumen Kedaluwarsa',
                'slug' => 'dokumen-kedaluwarsa',
                'url' => '/buku-saku/expired',
                'icon' => 'clock', // or alert-triangle
                'group' => 'Buku Saku',
                'status' => true,
            ],

            // Group: Web Utama
            [
                'name' => 'Integrasi Sistem',
                'slug' => 'integrasi-sistem',
                'url' => '/integrasi-sistem',
                'icon' => 'database',
                'group' => 'Web Utama',
                'status' => true,
            ],
            [
                'name' => 'Management User',
                'slug' => 'management-user',
                'url' => '/management-user',
                'icon' => 'users',
                'group' => 'Web Utama',
                'status' => true,
            ],
            [
                'name' => 'Data History',
                'slug' => 'history',
                'url' => '/history',
                'icon' => 'clock',
                'group' => 'Web Utama',
                'status' => true,
            ],

            // Group: List Pengawasan
            [
                'name' => 'List Pengawasan',
                'slug' => 'list-pengawasan',
                'url' => '/list-pengawasan',
                'icon' => 'clipboard',
                'group' => 'List Pengawasan',
                'status' => true,
            ],

            // Group: Lainnya (Business Modules)
            [
                'name' => 'HCM SIP-PGN',
                'slug' => 'hcm-sip-pgn',
                'url' => '#',
                'icon' => 'briefcase',
                'group' => 'Lainnya',
                'status' => true,
            ],
            [
                'name' => 'Project Management Office',
                'slug' => 'pmo',
                'url' => '#',
                'icon' => 'clipboard',
                'group' => 'Lainnya',
                'status' => true,
            ],
            [
                'name' => 'Procurement System',
                'slug' => 'procurement',
                'url' => '#',
                'icon' => 'shopping-cart',
                'group' => 'Lainnya',
                'status' => true,
            ],
        ];

        // 2. Create Modules and Permissions
        foreach ($modules as $moduleData) {
            $module = Module::firstOrCreate(
                ['slug' => $moduleData['slug']],
                $moduleData
            );
            // Ensure group is updated if module existed
            $module->update($moduleData);

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
    }
}
