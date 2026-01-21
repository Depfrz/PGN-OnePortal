<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengawas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('tanggal')->nullable();
            $table->string('status')->default('Active');
            $table->timestamps();
        });

        Schema::create('keterangan_options', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('pengawas_keterangan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pengawas_id');
            $table->unsignedBigInteger('keterangan_option_id');
            $table->timestamps();

            $table->unique(['pengawas_id', 'keterangan_option_id']);
            $table->foreign('pengawas_id')->references('id')->on('pengawas')->onDelete('cascade');
            $table->foreign('keterangan_option_id')->references('id')->on('keterangan_options')->onDelete('cascade');
        });

        // Seed default keterangan options if not exist
        $defaults = [
            'Izin (WIR)',
            'Permit to Work ( PTW )',
            'Pekerja',
            'Alat Pelindung Diri',
        ];
        foreach ($defaults as $name) {
            if (!DB::table('keterangan_options')->where('name', $name)->exists()) {
                DB::table('keterangan_options')->insert([
                    'name' => $name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pengawas_keterangan');
        Schema::dropIfExists('keterangan_options');
        Schema::dropIfExists('pengawas');
    }
};

