<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AddExpiredDocumentsModuleSeeder extends Seeder
{
    public function run()
    {
        // 1. Add "Dokumen Kedaluwarsa" module if it doesn't exist
        $expiredModule = DB::table('modules')->where('slug', 'dokumen-kedaluwarsa')->first();
        
        if (!$expiredModule) {
            DB::table('modules')->insert([
                'name' => 'Dokumen Kedaluwarsa',
                'group' => 'Buku Saku',
                'slug' => 'dokumen-kedaluwarsa',
                'description' => 'Akses dokumen yang telah kedaluwarsa.',
                'url' => '/buku-saku/expired',
                'tab_type' => 'current',
                'icon' => 'clock', // Using clock icon similar to history
                'status' => 1,
                'order' => 4, // Intended order
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        } else {
            // Update if exists to ensure order
            DB::table('modules')->where('id', $expiredModule->id)->update([
                'order' => 4,
                'updated_at' => Carbon::now(),
            ]);
        }

        // 2. Update order for other modules
        $orders = [
            'beranda' => 1,
            'dokumen-favorit' => 2,
            'pengecekan-file' => 3,
            'dokumen-kedaluwarsa' => 4, // Already handled, but good for completeness in array
            'buku-saku-history' => 5, // Riwayat Dokumen
            'upload-dokumen' => 6,
        ];

        foreach ($orders as $slug => $order) {
            DB::table('modules')->where('slug', $slug)->update(['order' => $order]);
        }
    }
}
