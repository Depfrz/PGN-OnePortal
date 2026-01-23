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
        Schema::table('pengawas', function (Blueprint $table) {
            if (!Schema::hasColumn('pengawas', 'bukti_path')) {
                $table->string('bukti_path')->nullable();
                $table->string('bukti_original_name')->nullable();
                $table->string('bukti_mime')->nullable();
                $table->unsignedBigInteger('bukti_size')->nullable();
                $table->timestamp('bukti_uploaded_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengawas', function (Blueprint $table) {
            if (Schema::hasColumn('pengawas', 'bukti_path')) {
                $table->dropColumn(['bukti_path', 'bukti_original_name', 'bukti_mime', 'bukti_size', 'bukti_uploaded_at']);
            }
        });
    }
};
