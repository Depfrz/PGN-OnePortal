<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('buku_saku_documents', function (Blueprint $table) {
            if (!Schema::hasColumn('buku_saku_documents', 'valid_until')) {
                $table->date('valid_until')->nullable()->after('approved_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buku_saku_documents', function (Blueprint $table) {
            if (Schema::hasColumn('buku_saku_documents', 'valid_until')) {
                $table->dropColumn('valid_until');
            }
        });
    }
};
