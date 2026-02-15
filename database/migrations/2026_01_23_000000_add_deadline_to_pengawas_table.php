<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengawas', function (Blueprint $table) {
            if (!Schema::hasColumn('pengawas', 'deadline')) {
                $table->date('deadline')->nullable()->after('tanggal');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pengawas', function (Blueprint $table) {
            if (Schema::hasColumn('pengawas', 'deadline')) {
                $table->dropColumn('deadline');
            }
        });
    }
};
