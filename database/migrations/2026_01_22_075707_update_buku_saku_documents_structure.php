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
            // Drop categories if exists
            if (Schema::hasColumn('buku_saku_documents', 'categories')) {
                $table->dropColumn('categories');
            }
            
            // Add valid_until date
            if (!Schema::hasColumn('buku_saku_documents', 'valid_until')) {
                $table->date('valid_until')->nullable()->after('file_size');
            }

            // Change default status to 'approved'
            $table->string('status')->default('approved')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buku_saku_documents', function (Blueprint $table) {
            $table->json('categories')->nullable();
            $table->dropColumn('valid_until');
            $table->string('status')->default('pending')->change();
        });
    }
};
