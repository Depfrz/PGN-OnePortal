<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;
use App\Models\User;
use App\Models\ModuleAccess;

class UpdateBukuSakuModulesSeeder extends Seeder
{
    public function run()
    {
        // 1. Rename existing 'Buku Saku' to 'Beranda' if it exists
        $bukuSaku = Module::where('name', 'Buku Saku')->first();
        if ($bukuSaku) {
            $bukuSaku->update([
                'name' => 'Beranda',
                'slug' => 'buku-saku-beranda',
                'url' => '/buku-saku',
                'icon' => 'home',
            ]);
        } else {
            // Create if it doesn't exist
            $bukuSaku = Module::create([
                'name' => 'Beranda',
                'slug' => 'buku-saku-beranda',
                'url' => '/buku-saku',
                'status' => true,
                'icon' => 'home',
            ]);
        }

        // 2. Create other Buku Saku sub-modules
        $newModules = [
            [
                'name' => 'Dokumen Favorit',
                'slug' => 'buku-saku-favorites',
                'url' => '/buku-saku/favorites',
                'icon' => 'star',
            ],
            [
                'name' => 'Riwayat Dokumen',
                'slug' => 'buku-saku-history',
                'url' => '/buku-saku/history',
                'icon' => 'clock',
            ],
            [
                'name' => 'Pengecekan File',
                'slug' => 'buku-saku-approval',
                'url' => '/buku-saku/approval',
                'icon' => 'check-circle',
            ],
            [
                'name' => 'Upload Dokumen',
                'slug' => 'buku-saku-upload',
                'url' => '/buku-saku/upload',
                'icon' => 'upload',
            ],
        ];

        foreach ($newModules as $mod) {
            $m = Module::firstOrCreate(
                ['name' => $mod['name']],
                array_merge($mod, ['status' => true])
            );
            
            // Assign default access to Admin
            $admins = User::role('Admin')->get();
            foreach ($admins as $admin) {
                ModuleAccess::firstOrCreate(
                    ['user_id' => $admin->id, 'module_id' => $m->id],
                    ['can_read' => true, 'can_write' => true, 'can_delete' => true]
                );
            }
        }
    }
}
