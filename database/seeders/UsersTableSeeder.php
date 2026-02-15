<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('users')->delete();
        
        \DB::table('users')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Admin',
                'email' => 'admin@pgn.co.id',
                'profile_photo_path' => NULL,
                'instansi' => 'PGN',
                'jabatan' => 'Admin',
                'email_verified_at' => NULL,
                'password' => '$2y$12$D3SonVaPA/75JkyylAJ/1eWkvhw0CUjjWiQm2EBwIc/rv1gSDAw7i',
                'remember_token' => NULL,
                'created_at' => '2026-01-20 13:22:17',
                'updated_at' => '2026-01-20 13:22:17',
            ),
            1 => 
            array (
                'id' => 5,
                'name' => 'Test Viewer',
                'email' => 'viewer@test.com',
                'profile_photo_path' => NULL,
                'instansi' => NULL,
                'jabatan' => NULL,
                'email_verified_at' => NULL,
                'password' => '$2y$12$sfUADccPPe70QoQiAEBZkuNdMnsm41uLSrBapD6zTxsC0F9fbU.2i',
                'remember_token' => NULL,
                'created_at' => '2026-01-22 05:06:04',
                'updated_at' => '2026-01-22 05:06:04',
            ),
            2 => 
            array (
                'id' => 6,
                'name' => 'Test Checker',
                'email' => 'checker@test.com',
                'profile_photo_path' => NULL,
                'instansi' => NULL,
                'jabatan' => NULL,
                'email_verified_at' => NULL,
                'password' => '$2y$12$d1V/buImwwgJlDmG5.DJBu1SQsKQlsYeKJgLaIEEGE.DGPZTzgHrK',
                'remember_token' => NULL,
                'created_at' => '2026-01-22 05:06:04',
                'updated_at' => '2026-01-22 05:06:04',
            ),
            3 => 
            array (
                'id' => 7,
                'name' => 'Test Admin',
                'email' => 'admin@test.com',
                'profile_photo_path' => NULL,
                'instansi' => NULL,
                'jabatan' => NULL,
                'email_verified_at' => NULL,
                'password' => '$2y$12$to31yZoCFHO1rW8q7YNhxuY1DcaveTDlu6saOfEJ/y/1u/d.tdwsO',
                'remember_token' => NULL,
                'created_at' => '2026-01-22 05:06:04',
                'updated_at' => '2026-01-22 05:06:04',
            ),
            4 => 
            array (
                'id' => 8,
                'name' => 'pak agung',
                'email' => 'pakagung@gmail.com',
                'profile_photo_path' => NULL,
                'instansi' => 'pgn',
                'jabatan' => 'manager',
                'email_verified_at' => NULL,
                'password' => '$2y$12$K.8lYJ/y19.KM88VGAMdkegbPvnmF5w8.Q1oUX29nT.LbCak/kAJe',
                'remember_token' => NULL,
                'created_at' => '2026-01-22 07:00:12',
                'updated_at' => '2026-01-22 07:00:12',
            ),
        ));
        
        
    }
}