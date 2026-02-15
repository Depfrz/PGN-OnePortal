<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PengawasKeteranganTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('pengawas_keterangan')->delete();
        
        \DB::table('pengawas_keterangan')->insert(array (
            0 => 
            array (
                'id' => 3,
                'pengawas_id' => 1,
                'keterangan_option_id' => 4,
                'created_at' => '2026-01-22 07:27:22',
                'updated_at' => '2026-01-22 07:27:22',
            ),
            1 => 
            array (
                'id' => 4,
                'pengawas_id' => 1,
                'keterangan_option_id' => 3,
                'created_at' => '2026-01-22 07:27:22',
                'updated_at' => '2026-01-22 07:27:22',
            ),
        ));
        
        
    }
}