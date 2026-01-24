<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengawas_keterangan', function (Blueprint $table) {
            $table->string('bukti_path')->nullable();
            $table->string('bukti_original_name')->nullable();
            $table->string('bukti_mime')->nullable();
            $table->bigInteger('bukti_size')->nullable();
            $table->timestamp('bukti_uploaded_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pengawas_keterangan', function (Blueprint $table) {
            $table->dropColumn([
                'bukti_path',
                'bukti_original_name',
                'bukti_mime',
                'bukti_size',
                'bukti_uploaded_at'
            ]);
        });
    }
};
