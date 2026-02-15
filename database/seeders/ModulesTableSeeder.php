<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ModulesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('modules')->delete();
        
        \DB::table('modules')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Beranda',
                'group' => 'Buku Saku',
                'slug' => 'beranda',
                'description' => 'Akses dokumen panduan, referensi, dan materi penting lainnya.',
                'url' => '/buku-saku',
                'tab_type' => 'current',
                'icon' => 'home',
                'status' => 1,
                'order' => 0,
                'created_at' => '2026-01-20 13:22:19',
                'updated_at' => '2026-01-22 05:06:05',
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'Integrasi Sistem',
                'group' => 'Web Utama',
                'slug' => 'integrasi-sistem',
                'description' => NULL,
                'url' => '/integrasi-sistem',
                'tab_type' => 'current',
                'icon' => 'database',
                'status' => 1,
                'order' => 0,
                'created_at' => '2026-01-20 13:22:19',
                'updated_at' => '2026-01-22 01:00:29',
            ),
            2 => 
            array (
                'id' => 3,
                'name' => 'Management User',
                'group' => 'Web Utama',
                'slug' => 'management-user',
                'description' => NULL,
                'url' => '/management-user',
                'tab_type' => 'current',
                'icon' => 'users',
                'status' => 1,
                'order' => 0,
                'created_at' => '2026-01-20 13:22:19',
                'updated_at' => '2026-01-22 01:00:29',
            ),
            3 => 
            array (
                'id' => 4,
                'name' => 'Data History',
                'group' => 'Web Utama',
                'slug' => 'history',
                'description' => NULL,
                'url' => '/history',
                'tab_type' => 'current',
                'icon' => 'clock',
                'status' => 1,
                'order' => 0,
                'created_at' => '2026-01-20 13:22:19',
                'updated_at' => '2026-01-22 01:00:29',
            ),
            4 => 
            array (
                'id' => 10,
                'name' => 'List Pengawasan',
                'group' => 'List Pengawasan',
                'slug' => 'list-pengawasan',
                'description' => NULL,
                'url' => '/list-pengawasan',
                'tab_type' => 'current',
                'icon' => 'clipboard',
                'status' => 1,
                'order' => 0,
                'created_at' => '2026-01-21 13:39:15',
                'updated_at' => '2026-01-22 01:00:29',
            ),
            5 => 
            array (
                'id' => 11,
                'name' => 'Dokumen Favorit',
                'group' => 'Buku Saku',
                'slug' => 'dokumen-favorit',
                'description' => NULL,
                'url' => '/buku-saku/favorites',
                'tab_type' => 'current',
                'icon' => 'star',
                'status' => 1,
                'order' => 0,
                'created_at' => '2026-01-22 01:00:19',
                'updated_at' => '2026-01-22 05:06:05',
            ),
            6 => 
            array (
                'id' => 12,
                'name' => 'Riwayat Dokumen',
                'group' => 'Buku Saku',
                'slug' => 'buku-saku-history',
                'description' => NULL,
                'url' => '/buku-saku/history',
                'tab_type' => 'current',
                'icon' => 'clock',
                'status' => 1,
                'order' => 0,
                'created_at' => '2026-01-22 01:00:19',
                'updated_at' => '2026-01-22 01:00:29',
            ),
            7 => 
            array (
                'id' => 13,
                'name' => 'Pengecekan File',
                'group' => 'Buku Saku',
                'slug' => 'pengecekan-file',
                'description' => NULL,
                'url' => '/buku-saku/approval',
                'tab_type' => 'current',
                'icon' => 'check-circle',
                'status' => 1,
                'order' => 0,
                'created_at' => '2026-01-22 01:00:19',
                'updated_at' => '2026-01-22 05:06:05',
            ),
            8 => 
            array (
                'id' => 14,
                'name' => 'Upload Dokumen',
                'group' => 'Buku Saku',
                'slug' => 'upload-dokumen',
                'description' => NULL,
                'url' => '/buku-saku/upload',
                'tab_type' => 'current',
                'icon' => 'upload',
                'status' => 1,
                'order' => 0,
                'created_at' => '2026-01-22 01:00:19',
                'updated_at' => '2026-01-22 05:06:05',
            ),
            9 => 
            array (
                'id' => 15,
                'name' => 'Buku Saku',
                'group' => 'Lainnya',
                'slug' => 'buku-saku-dashboard',
                'description' => NULL,
                'url' => '/buku-saku',
                'tab_type' => 'current',
                'icon' => 'book',
                'status' => 1,
                'order' => 0,
                'created_at' => '2026-01-22 01:37:07',
                'updated_at' => '2026-01-22 01:37:07',
            ),
            10 => 
            array (
                'id' => 16,
                'name' => 'Dokumen Kedaluwarsa',
                'group' => 'Buku Saku',
                'slug' => 'dokumen-kedaluwarsa',
                'description' => 'Akses dokumen yang telah kedaluwarsa.',
                'url' => '/buku-saku/expired',
                'tab_type' => 'current',
                'icon' => 'clock',
                'status' => 1,
                'order' => 4,
                'created_at' => '2026-02-12 12:00:00',
                'updated_at' => '2026-02-12 12:00:00',
            ),
        ));
        
        
    }
}