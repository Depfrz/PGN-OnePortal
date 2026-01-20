<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;
use App\Models\User;
use App\Models\ModuleAccess;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Daftar Semua Modul (Sistem + Dummy)
        $modules = [
            // System Modules
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

        foreach ($modules as $moduleData) {
            Module::firstOrCreate(
                ['slug' => $moduleData['slug']],
                $moduleData
            );
        }

        // 2. Assign Default Access for Testing (Optional)
        // We can assign access to existing users if needed, or rely on Management User UI
        
        // Example: Assign Dashboard to everyone
        $allUsers = User::all();
        $dashboardModule = Module::where('slug', 'dashboard')->first();

        if ($dashboardModule) {
            foreach ($allUsers as $user) {
                ModuleAccess::firstOrCreate([
                    'user_id' => $user->id,
                    'module_id' => $dashboardModule->id
                ], [
                    'can_read' => true,
                    'can_write' => false,
                    'can_delete' => false,
                ]);
            }
        }
    }
}
