<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('template_kegiatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_jenis_pipa_id')->constrained('master_jenis_pipas')->cascadeOnDelete();
            $table->string('nama_kegiatan');
            $table->timestamps();
        });

        // Seed default data
        $pipaBaja = DB::table('master_jenis_pipas')->where('name', 'Pipa Baja')->first();
        $pipaPE = DB::table('master_jenis_pipas')->where('name', 'Pipa PE')->first();

        if ($pipaBaja) {
            $activities = [
                "Survey", "Persiapan dan Pembersihan Jalur Pipa", "Penggalian Lubang Percobaan", "Marker dan Aksesories", "Field Bending", 
                "Penanganan, Transportasi, dan Penyimpanan Pipa, Valve, Fitting dan MRS", "Pengangkutan dan Penjajaran", "Pekerjaan Galian Pipa", 
                "Pengelasan", "Pengujian Tidak Merusak (NDT)", "Field Joint Coating", "Holiday Test", "Penurunan Pipa (Lowering)", 
                "Proteksi Katodik", "Penimbunan Galian", "Pembersihan (Cleaning)", "Water Filling", "Hydrostatic Test", "Swabbing", "Drying", 
                "Nitrogen Purging", "Perbaikan Kembali (Reinstatement)", "Tie In Pipa", "Perlintasan (Crossing)", "Pemasangan Valve", 
                "Pengecatan", "Hot Tapping", "Pekerjaan Sipil", "Pekerjaan Elektrikal", "Commissiong (gas In)", "Pemasangan Grounding", "SAT MRS"
            ];
            foreach ($activities as $act) {
                DB::table('template_kegiatans')->insert([
                    'master_jenis_pipa_id' => $pipaBaja->id,
                    'nama_kegiatan' => $act,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if ($pipaPE) {
            $activities = [
                "Survey", "Penggalian Lubang Percobaan", "Persiapan dan Pembersihan Jalur Pipa", 
                "Penangan, Pengangkutan, Penyimpanan, Inspeksi Pipa dan Fitting", "Pengangkutan dan Penjajaran", "Pekerjaan Galian Pipa", 
                "Penyambungan Pipa dan Fitting", "Pemasangan Pipa Induk dan Pipa Service", "Penurunan Pipa", "Pemasangan Valve", 
                "Penimbunan Galian", "Perlintasan", "Pre-Commissioning", "Flushing", "Pneumatic Test", "Nitrogen Purging", 
                "Commissioning", "Perbaikan Kembali", "Penanda dan Aksesories", "SAT MRS"
            ];
            foreach ($activities as $act) {
                DB::table('template_kegiatans')->insert([
                    'master_jenis_pipa_id' => $pipaPE->id,
                    'nama_kegiatan' => $act,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_kegiatans');
    }
};
