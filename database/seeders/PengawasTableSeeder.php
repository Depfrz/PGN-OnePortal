<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PengawasTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('pengawas')->delete();
        
        \DB::table('pengawas')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Depe',
                'divisi' => 'PMO QA/QC',
                'tanggal' => NULL,
                'status' => 'Selesai',
                'created_at' => '2026-01-22 07:22:55',
                'updated_at' => '2026-01-22 07:23:51',
            ),
        ));
        
        
    }
}