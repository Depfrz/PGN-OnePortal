<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class BukuSakuFavoritesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('buku_saku_favorites')->delete();
        
        
        
    }
}