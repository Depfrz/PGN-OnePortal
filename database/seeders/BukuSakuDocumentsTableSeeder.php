<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class BukuSakuDocumentsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('buku_saku_documents')->delete();
        
        \DB::table('buku_saku_documents')->insert(array (
            0 => 
            array (
                'id' => 1,
                'user_id' => 1,
                'title' => 'tata cara welder',
                'description' => NULL,
                'tags' => 'welder, pengeboman',
                'file_path' => 'buku-saku/hN92xv7Bg2IAZ2POgP5jIqMlCWJIncN3qTPPSw89.pdf',
                'file_type' => 'pdf',
                'file_size' => '1.95 MB',
                'valid_until' => NULL,
                'status' => 'approved',
                'rejected_reason' => NULL,
                'approved_by' => NULL,
                'approved_at' => NULL,
                'created_at' => '2026-01-21 14:55:10',
                'updated_at' => '2026-01-21 14:56:08',
            ),
            1 => 
            array (
                'id' => 2,
                'user_id' => 1,
                'title' => 'pemasangan pipa',
                'description' => NULL,
                'tags' => 'pemasangan, welder, penguncian',
                'file_path' => 'buku-saku/h0OySW7qMViXLpTe6Ids32AgW479KajxYpZ7hMdo.pdf',
                'file_type' => 'pdf',
                'file_size' => '402.42 KB',
                'valid_until' => NULL,
                'status' => 'approved',
                'rejected_reason' => NULL,
                'approved_by' => 1,
                'approved_at' => '2026-01-21 15:27:55',
                'created_at' => '2026-01-21 15:27:49',
                'updated_at' => '2026-01-21 15:27:55',
            ),
            2 => 
            array (
                'id' => 3,
                'user_id' => 1,
                'title' => 'IK _HSSE PMO_No_001-03_05-2024_Pemeriksaan Peralatan Kerja_Controlled Copy_compressed.pdf',
                'description' => 'pemeriksa peralatan kerja',
                'tags' => 'penggalian, boring',
                'file_path' => 'buku-saku/aGWdHfCQMKqp78ZWVsExfWEOPKA6YDzKB2SyFPRM.pdf',
                'file_type' => 'pdf',
                'file_size' => '6.38 MB',
                'valid_until' => NULL,
                'status' => 'approved',
                'rejected_reason' => NULL,
                'approved_by' => 1,
                'approved_at' => '2026-01-22 07:10:03',
                'created_at' => '2026-01-22 07:09:39',
                'updated_at' => '2026-01-22 07:10:03',
            ),
            3 => 
            array (
                'id' => 4,
                'user_id' => 1,
                'title' => 'Panduan_Pengelolaan_Insiden_HSSE_PGN_Rev Mei 2024.pdf',
                'description' => NULL,
                'tags' => 'penggalian, welder',
                'file_path' => 'buku-saku/OQX6TE5xmQ7uOey8lD7BHioqaycACak16b9KlMVS.pdf',
                'file_type' => 'pdf',
                'file_size' => '2.11 MB',
                'valid_until' => NULL,
                'status' => 'approved',
                'rejected_reason' => NULL,
                'approved_by' => 1,
                'approved_at' => '2026-01-22 07:12:14',
                'created_at' => '2026-01-22 07:12:09',
                'updated_at' => '2026-01-22 07:12:14',
            ),
            4 => 
            array (
                'id' => 5,
                'user_id' => 1,
                'title' => 'IK  HSSE PMO No 001 03 05 2024 Pemeriksaan Peralatan Kerja Controlled Copy compressed.pdf',
                'description' => NULL,
                'tags' => 'welder,penggalian,intruksi,PMO',
                'file_path' => 'buku-saku/deVdyaV6OwwLZVoPfFBgGvO9Brj6zUEZ8X5nSIQO.pdf',
                'file_type' => 'pdf',
                'file_size' => '1.38 KB',
                'valid_until' => '2029-08-23',
                'status' => 'approved',
                'rejected_reason' => NULL,
                'approved_by' => 1,
                'approved_at' => '2026-01-23 01:56:02',
                'created_at' => '2026-01-23 01:56:02',
                'updated_at' => '2026-01-23 02:25:48',
            ),
            5 => 
            array (
                'id' => 7,
                'user_id' => 1,
                'title' => 'tata cara penggalian',
                'description' => NULL,
                'tags' => 'penggalian,intruksi',
                'file_path' => 'buku-saku/RV2nEuFKpPSIkDqNJoKCcYZVduvKCLtM4n17HQTJ.pdf',
                'file_type' => 'pdf',
                'file_size' => '402.42 KB',
                'valid_until' => '2029-04-26',
                'status' => 'approved',
                'rejected_reason' => NULL,
                'approved_by' => 1,
                'approved_at' => '2026-01-23 02:24:55',
                'created_at' => '2026-01-23 02:24:55',
                'updated_at' => '2026-01-23 02:24:55',
            ),
            6 => 
            array (
                'id' => 8,
                'user_id' => 1,
                'title' => 'test exp',
                'description' => NULL,
                'tags' => 'welder,intruksi',
                'file_path' => 'buku-saku/4du2CcZo5Fo1e6FlgJZxJwKRCJU5mDkGOkfsy8tH.pdf',
                'file_type' => 'pdf',
                'file_size' => '1.95 MB',
                'valid_until' => '2023-08-20',
                'status' => 'approved',
                'rejected_reason' => NULL,
                'approved_by' => 1,
                'approved_at' => '2026-01-23 02:36:11',
                'created_at' => '2026-01-23 02:36:11',
                'updated_at' => '2026-01-23 02:36:11',
            ),
            7 => 
            array (
                'id' => 9,
                'user_id' => 1,
                'title' => 'tata cara welder',
                'description' => NULL,
                'tags' => 'penggalian,PMO',
                'file_path' => 'buku-saku/E7NEz5udvElrjbzbRmRVf8vsMWeNp8nfFjakpw8W.pdf',
                'file_type' => 'pdf',
                'file_size' => '1.95 MB',
                'valid_until' => '2025-12-01',
                'status' => 'approved',
                'rejected_reason' => NULL,
                'approved_by' => 1,
                'approved_at' => '2026-01-23 02:37:44',
                'created_at' => '2026-01-23 02:37:44',
                'updated_at' => '2026-01-23 02:37:44',
            ),
            8 => 
            array (
                'id' => 10,
                'user_id' => 1,
                'title' => 'pc',
                'description' => NULL,
                'tags' => 'penggalian,PMO',
                'file_path' => 'buku-saku/YIcuiByMWlcZHcwwQgSbZ45VW1c6RHSbuouY5N3o.pdf',
                'file_type' => 'pdf',
                'file_size' => '1.95 MB',
                'valid_until' => '2026-12-23',
                'status' => 'approved',
                'rejected_reason' => NULL,
                'approved_by' => 1,
                'approved_at' => '2026-01-23 02:38:21',
                'created_at' => '2026-01-23 02:38:21',
                'updated_at' => '2026-01-23 02:38:21',
            ),
        ));
        
        
    }
}