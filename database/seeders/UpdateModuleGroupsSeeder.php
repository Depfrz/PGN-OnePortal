<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;

class UpdateModuleGroupsSeeder extends Seeder
{
    public function run()
    {
        // 1. Group 'Web Utama'
        Module::whereIn('name', [
            'Management User', 
            'Data History', 
            'Integrasi Sistem'
        ])->update(['group' => 'Web Utama']);

        // 2. Group 'Buku Saku'
        // Includes the new sub-modules created in previous step
        Module::whereIn('name', [
            'Beranda', // Was 'Buku Saku'
            'Dokumen Favorit',
            'Riwayat Dokumen',
            'Pengecekan File',
            'Upload Dokumen'
        ])->update(['group' => 'Buku Saku']);

        // 3. Group 'List Pengawasan' (or put in Web Utama?)
        // User listed it separately, so let's give it its own group or put in 'Lainnya' if intended as separate
        // "dibawahnya ... - list pengawasan". 
        // Let's create a group 'List Pengawasan' for now to match the user's bullet point structure.
        Module::where('name', 'List Pengawasan')->update(['group' => 'List Pengawasan']);
        
        // Ensure legacy 'Buku Saku' (if any missed) is handled
        Module::where('name', 'Buku Saku')->update(['group' => 'Buku Saku']);
    }
}
