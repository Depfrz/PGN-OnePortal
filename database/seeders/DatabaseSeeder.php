<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Core Auth & Permissions (Generated from current DB)
        $this->call(PermissionsTableSeeder::class);
        $this->call(RolesTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(RoleHasPermissionsTableSeeder::class);
        $this->call(ModelHasRolesTableSeeder::class);

        // 2. Application Modules
        $this->call(ModulesTableSeeder::class);
        $this->call(ModuleAccessTableSeeder::class);
        
        // 3. Buku Saku Data
        $this->call(BukuSakuTagsTableSeeder::class);
        $this->call(BukuSakuDocumentsTableSeeder::class);
        $this->call(BukuSakuFavoritesTableSeeder::class);
        
        // 4. Pengawasan Data
        $this->call(PengawasTableSeeder::class);
        $this->call(PengawasKeteranganTableSeeder::class);
        
        // 5. System Data
        $this->call(NotificationsTableSeeder::class);
    }
}
