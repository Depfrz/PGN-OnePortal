<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('permissions')->delete();
        
        \DB::table('permissions')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'view dashboard',
                'guard_name' => 'web',
                'created_at' => '2026-01-20 13:22:17',
                'updated_at' => '2026-01-20 13:22:17',
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'manage systems',
                'guard_name' => 'web',
                'created_at' => '2026-01-20 13:22:17',
                'updated_at' => '2026-01-20 13:22:17',
            ),
            2 => 
            array (
                'id' => 3,
                'name' => 'manage users',
                'guard_name' => 'web',
                'created_at' => '2026-01-20 13:22:17',
                'updated_at' => '2026-01-20 13:22:17',
            ),
            3 => 
            array (
                'id' => 4,
                'name' => 'view module dashboard',
                'guard_name' => 'web',
                'created_at' => '2026-01-20 13:22:19',
                'updated_at' => '2026-01-20 13:22:19',
            ),
            4 => 
            array (
                'id' => 5,
                'name' => 'view module integrasi-sistem',
                'guard_name' => 'web',
                'created_at' => '2026-01-20 13:22:19',
                'updated_at' => '2026-01-20 13:22:19',
            ),
            5 => 
            array (
                'id' => 6,
                'name' => 'view module management-user',
                'guard_name' => 'web',
                'created_at' => '2026-01-20 13:22:19',
                'updated_at' => '2026-01-20 13:22:19',
            ),
            6 => 
            array (
                'id' => 7,
                'name' => 'view module history',
                'guard_name' => 'web',
                'created_at' => '2026-01-20 13:22:19',
                'updated_at' => '2026-01-20 13:22:19',
            ),
            7 => 
            array (
                'id' => 8,
                'name' => 'view module hcm-sip-pgn',
                'guard_name' => 'web',
                'created_at' => '2026-01-20 13:22:19',
                'updated_at' => '2026-01-20 13:22:19',
            ),
            8 => 
            array (
                'id' => 9,
                'name' => 'view module pmo',
                'guard_name' => 'web',
                'created_at' => '2026-01-20 13:22:19',
                'updated_at' => '2026-01-20 13:22:19',
            ),
            9 => 
            array (
                'id' => 10,
                'name' => 'view module procurement',
                'guard_name' => 'web',
                'created_at' => '2026-01-20 13:22:19',
                'updated_at' => '2026-01-20 13:22:19',
            ),
        ));
        
        
    }
}