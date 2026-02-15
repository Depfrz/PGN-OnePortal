<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class BukuSakuTagsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('buku_saku_tags')->delete();
        
        \DB::table('buku_saku_tags')->insert(array (
            0 => 
            array (
                'id' => 2,
                'name' => 'welder',
                'created_at' => '2026-01-22 08:27:47',
                'updated_at' => '2026-01-22 08:27:47',
            ),
            1 => 
            array (
                'id' => 3,
                'name' => 'penggalian',
                'created_at' => '2026-01-22 08:27:47',
                'updated_at' => '2026-01-22 08:27:47',
            ),
            2 => 
            array (
                'id' => 4,
                'name' => 'pemasangan',
                'created_at' => '2026-01-23 01:36:45',
                'updated_at' => '2026-01-23 01:36:45',
            ),
            3 => 
            array (
                'id' => 5,
                'name' => 'intruksi',
                'created_at' => '2026-01-23 01:36:52',
                'updated_at' => '2026-01-23 01:36:52',
            ),
            4 => 
            array (
                'id' => 6,
                'name' => 'PMO',
                'created_at' => '2026-01-23 01:37:18',
                'updated_at' => '2026-01-23 01:37:18',
            ),
        ));
        
        
    }
}