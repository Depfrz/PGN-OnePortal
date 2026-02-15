<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('roles')->delete();
        
        \DB::table('roles')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'User',
                'guard_name' => 'web',
                'created_at' => '2026-01-20 13:22:17',
                'updated_at' => '2026-01-20 13:22:17',
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'SuperUser',
                'guard_name' => 'web',
                'created_at' => '2026-01-20 13:22:17',
                'updated_at' => '2026-01-20 13:22:17',
            ),
            2 => 
            array (
                'id' => 3,
                'name' => 'Supervisor',
                'guard_name' => 'web',
                'created_at' => '2026-01-20 13:22:17',
                'updated_at' => '2026-01-20 13:22:17',
            ),
            3 => 
            array (
                'id' => 4,
                'name' => 'Admin',
                'guard_name' => 'web',
                'created_at' => '2026-01-20 13:22:17',
                'updated_at' => '2026-01-20 13:22:17',
            ),
        ));
        
        
    }
}