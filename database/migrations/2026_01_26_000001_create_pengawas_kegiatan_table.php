<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pengawas_kegiatan')) {
            Schema::create('pengawas_kegiatan', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pengawas_id')->constrained('pengawas')->onDelete('cascade');
                $table->string('nama_kegiatan');
                $table->date('tanggal_mulai')->nullable();
                $table->date('deadline')->nullable();
                $table->string('status')->default('Belum Dimulai');
                $table->text('deskripsi')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pengawas_kegiatan');
    }
};
