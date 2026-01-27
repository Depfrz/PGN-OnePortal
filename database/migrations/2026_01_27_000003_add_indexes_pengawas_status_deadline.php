<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pengawas')) {
            Schema::table('pengawas', function (Blueprint $table) {
                $table->index('status', 'pengawas_status_index');
                $table->index('deadline', 'pengawas_deadline_index');
            });
        }

        if (Schema::hasTable('pengawas_kegiatan')) {
            Schema::table('pengawas_kegiatan', function (Blueprint $table) {
                $table->index('status', 'pengawas_kegiatan_status_index');
                $table->index('deadline', 'pengawas_kegiatan_deadline_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pengawas')) {
            Schema::table('pengawas', function (Blueprint $table) {
                $table->dropIndex('pengawas_status_index');
                $table->dropIndex('pengawas_deadline_index');
            });
        }

        if (Schema::hasTable('pengawas_kegiatan')) {
            Schema::table('pengawas_kegiatan', function (Blueprint $table) {
                $table->dropIndex('pengawas_kegiatan_status_index');
                $table->dropIndex('pengawas_kegiatan_deadline_index');
            });
        }
    }
};

