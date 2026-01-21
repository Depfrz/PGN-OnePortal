<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('modules')->where('slug', 'list-pengawasan')->exists();

        if (!$exists) {
            DB::table('modules')->insert([
                'name' => 'List Pengawasan',
                'slug' => 'list-pengawasan',
                'url' => '/list-pengawasan',
                'icon' => 'clipboard',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('modules')->where('slug', 'list-pengawasan')->delete();
    }
};

