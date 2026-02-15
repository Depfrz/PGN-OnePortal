<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RoleHasPermissionsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('role_has_permissions')->delete();
        
        \DB::table('role_has_permissions')->insert(array (
            0 => 
            array (
                'permission_id' => 1,
                'role_id' => 1,
            ),
            1 => 
            array (
                'permission_id' => 1,
                'role_id' => 2,
            ),
            2 => 
            array (
                'permission_id' => 1,
                'role_id' => 3,
            ),
            3 => 
            array (
                'permission_id' => 1,
                'role_id' => 4,
            ),
            4 => 
            array (
                'permission_id' => 2,
                'role_id' => 3,
            ),
            5 => 
            array (
                'permission_id' => 2,
                'role_id' => 4,
            ),
            6 => 
            array (
                'permission_id' => 3,
                'role_id' => 4,
            ),
            7 => 
            array (
                'permission_id' => 4,
                'role_id' => 1,
            ),
            8 => 
            array (
                'permission_id' => 4,
                'role_id' => 2,
            ),
            9 => 
            array (
                'permission_id' => 4,
                'role_id' => 3,
            ),
            10 => 
            array (
                'permission_id' => 4,
                'role_id' => 4,
            ),
            11 => 
            array (
                'permission_id' => 5,
                'role_id' => 2,
            ),
            12 => 
            array (
                'permission_id' => 5,
                'role_id' => 3,
            ),
            13 => 
            array (
                'permission_id' => 5,
                'role_id' => 4,
            ),
            14 => 
            array (
                'permission_id' => 6,
                'role_id' => 4,
            ),
            15 => 
            array (
                'permission_id' => 7,
                'role_id' => 1,
            ),
            16 => 
            array (
                'permission_id' => 7,
                'role_id' => 2,
            ),
            17 => 
            array (
                'permission_id' => 7,
                'role_id' => 3,
            ),
            18 => 
            array (
                'permission_id' => 7,
                'role_id' => 4,
            ),
            19 => 
            array (
                'permission_id' => 8,
                'role_id' => 3,
            ),
            20 => 
            array (
                'permission_id' => 8,
                'role_id' => 4,
            ),
            21 => 
            array (
                'permission_id' => 9,
                'role_id' => 3,
            ),
            22 => 
            array (
                'permission_id' => 9,
                'role_id' => 4,
            ),
            23 => 
            array (
                'permission_id' => 10,
                'role_id' => 3,
            ),
            24 => 
            array (
                'permission_id' => 10,
                'role_id' => 4,
            ),
        ));
        
        
    }
}