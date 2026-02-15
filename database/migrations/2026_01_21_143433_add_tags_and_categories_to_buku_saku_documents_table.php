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
            $table->string('tags')->nullable()->after('description');
            $table->json('categories')->nullable()->after('tags');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buku_saku_documents', function (Blueprint $table) {
            $table->dropColumn(['tags', 'categories']);
        });
    }
};
