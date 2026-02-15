<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ModuleAccessTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('module_access')->delete();
        
        \DB::table('module_access')->insert(array (
            0 => 
            array (
                'id' => 153,
                'user_id' => 5,
                'module_id' => 1,
                'can_read' => 1,
                'can_write' => 0,
                'can_delete' => 0,
                'created_at' => '2026-01-22 05:06:05',
                'updated_at' => '2026-01-22 05:06:05',
                'show_on_dashboard' => 1,
            ),
            1 => 
            array (
                'id' => 154,
                'user_id' => 5,
                'module_id' => 11,
                'can_read' => 1,
                'can_write' => 0,
                'can_delete' => 0,
                'created_at' => '2026-01-22 05:06:05',
                'updated_at' => '2026-01-22 05:06:05',
                'show_on_dashboard' => 1,
            ),
            2 => 
            array (
                'id' => 155,
                'user_id' => 6,
                'module_id' => 1,
                'can_read' => 1,
                'can_write' => 0,
                'can_delete' => 0,
                'created_at' => '2026-01-22 05:06:05',
                'updated_at' => '2026-01-22 05:06:05',
                'show_on_dashboard' => 1,
            ),
            3 => 
            array (
                'id' => 156,
                'user_id' => 6,
                'module_id' => 13,
                'can_read' => 1,
                'can_write' => 0,
                'can_delete' => 0,
                'created_at' => '2026-01-22 05:06:05',
                'updated_at' => '2026-01-22 05:06:05',
                'show_on_dashboard' => 1,
            ),
            4 => 
            array (
                'id' => 157,
                'user_id' => 1,
                'module_id' => 1,
                'can_read' => 1,
                'can_write' => 1,
                'can_delete' => 1,
                'created_at' => '2026-01-22 06:37:04',
                'updated_at' => '2026-01-22 06:37:04',
                'show_on_dashboard' => 1,
            ),
            5 => 
            array (
                'id' => 158,
                'user_id' => 1,
                'module_id' => 2,
                'can_read' => 1,
                'can_write' => 1,
                'can_delete' => 1,
                'created_at' => '2026-01-22 06:37:04',
                'updated_at' => '2026-01-22 06:37:04',
                'show_on_dashboard' => 1,
            ),
            6 => 
            array (
                'id' => 159,
                'user_id' => 1,
                'module_id' => 3,
                'can_read' => 1,
                'can_write' => 1,
                'can_delete' => 1,
                'created_at' => '2026-01-22 06:37:04',
                'updated_at' => '2026-01-22 06:37:04',
                'show_on_dashboard' => 1,
            ),
            7 => 
            array (
                'id' => 160,
                'user_id' => 1,
                'module_id' => 4,
                'can_read' => 1,
                'can_write' => 1,
                'can_delete' => 1,
                'created_at' => '2026-01-22 06:37:04',
                'updated_at' => '2026-01-22 06:37:04',
                'show_on_dashboard' => 1,
            ),
            8 => 
            array (
                'id' => 161,
                'user_id' => 1,
                'module_id' => 10,
                'can_read' => 1,
                'can_write' => 1,
                'can_delete' => 1,
                'created_at' => '2026-01-22 06:37:04',
                'updated_at' => '2026-01-22 06:37:04',
                'show_on_dashboard' => 1,
            ),
            9 => 
            array (
                'id' => 162,
                'user_id' => 1,
                'module_id' => 11,
                'can_read' => 1,
                'can_write' => 1,
                'can_delete' => 1,
                'created_at' => '2026-01-22 06:37:04',
                'updated_at' => '2026-01-22 06:37:04',
                'show_on_dashboard' => 1,
            ),
            10 => 
            array (
                'id' => 163,
                'user_id' => 1,
                'module_id' => 12,
                'can_read' => 1,
                'can_write' => 1,
                'can_delete' => 1,
                'created_at' => '2026-01-22 06:37:04',
                'updated_at' => '2026-01-22 06:37:04',
                'show_on_dashboard' => 1,
            ),
            11 => 
            array (
                'id' => 164,
                'user_id' => 1,
                'module_id' => 13,
                'can_read' => 1,
                'can_write' => 1,
                'can_delete' => 1,
                'created_at' => '2026-01-22 06:37:04',
                'updated_at' => '2026-01-22 06:37:04',
                'show_on_dashboard' => 1,
            ),
            12 => 
            array (
                'id' => 165,
                'user_id' => 1,
                'module_id' => 14,
                'can_read' => 1,
                'can_write' => 1,
                'can_delete' => 1,
                'created_at' => '2026-01-22 06:37:04',
                'updated_at' => '2026-01-22 06:37:04',
                'show_on_dashboard' => 1,
            ),
            13 => 
            array (
                'id' => 166,
                'user_id' => 1,
                'module_id' => 15,
                'can_read' => 1,
                'can_write' => 1,
                'can_delete' => 1,
                'created_at' => '2026-01-22 06:37:04',
                'updated_at' => '2026-01-22 06:37:04',
                'show_on_dashboard' => 1,
            ),
            14 => 
            array (
                'id' => 167,
                'user_id' => 8,
                'module_id' => 1,
                'can_read' => 1,
                'can_write' => 1,
                'can_delete' => 1,
                'created_at' => '2026-01-22 07:02:06',
                'updated_at' => '2026-01-22 07:02:06',
                'show_on_dashboard' => 1,
            ),
            15 => 
            array (
                'id' => 168,
                'user_id' => 8,
                'module_id' => 2,
                'can_read' => 1,
                'can_write' => 1,
                'can_delete' => 1,
                'created_at' => '2026-01-22 07:02:06',
                'updated_at' => '2026-01-22 07:02:06',
                'show_on_dashboard' => 1,
            ),
            16 => 
            array (
                'id' => 169,
                'user_id' => 8,
                'module_id' => 4,
                'can_read' => 1,
                'can_write' => 1,
                'can_delete' => 1,
                'created_at' => '2026-01-22 07:02:06',
                'updated_at' => '2026-01-22 07:02:06',
                'show_on_dashboard' => 1,
            ),
            17 => 
            array (
                'id' => 170,
                'user_id' => 8,
                'module_id' => 11,
                'can_read' => 1,
                'can_write' => 1,
                'can_delete' => 1,
                'created_at' => '2026-01-22 07:02:06',
                'updated_at' => '2026-01-22 07:02:06',
                'show_on_dashboard' => 1,
            ),
            18 => 
            array (
                'id' => 171,
                'user_id' => 8,
                'module_id' => 12,
                'can_read' => 1,
                'can_write' => 1,
                'can_delete' => 1,
                'created_at' => '2026-01-22 07:02:06',
                'updated_at' => '2026-01-22 07:02:06',
                'show_on_dashboard' => 1,
            ),
            19 => 
            array (
                'id' => 172,
                'user_id' => 8,
                'module_id' => 13,
                'can_read' => 1,
                'can_write' => 1,
                'can_delete' => 1,
                'created_at' => '2026-01-22 07:02:06',
                'updated_at' => '2026-01-22 07:02:06',
                'show_on_dashboard' => 1,
            ),
            20 => 
            array (
                'id' => 173,
                'user_id' => 8,
                'module_id' => 15,
                'can_read' => 1,
                'can_write' => 1,
                'can_delete' => 1,
                'created_at' => '2026-01-22 07:02:06',
                'updated_at' => '2026-01-22 07:02:06',
                'show_on_dashboard' => 1,
            ),
        ));
        
        
    }
}