<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengawas_kegiatan', function (Blueprint $table) {
            $table->string('bukti_path')->nullable();
            $table->string('bukti_original_name')->nullable();
            $table->string('bukti_mime')->nullable();
            $table->unsignedBigInteger('bukti_size')->nullable();
            $table->timestamp('bukti_uploaded_at')->nullable();
        });

        Schema::create('pengawas_kegiatan_keterangan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengawas_kegiatan_id')->constrained('pengawas_kegiatan')->onDelete('cascade');
            $table->foreignId('keterangan_option_id')->constrained('keterangan_options')->onDelete('cascade');
            $table->string('bukti_path')->nullable();
            $table->string('bukti_original_name')->nullable();
            $table->string('bukti_mime')->nullable();
            $table->unsignedBigInteger('bukti_size')->nullable();
            $table->timestamp('bukti_uploaded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengawas_kegiatan_keterangan');
        Schema::table('pengawas_kegiatan', function (Blueprint $table) {
            $table->dropColumn(['bukti_path', 'bukti_original_name', 'bukti_mime', 'bukti_size', 'bukti_uploaded_at']);
        });
    }
};
