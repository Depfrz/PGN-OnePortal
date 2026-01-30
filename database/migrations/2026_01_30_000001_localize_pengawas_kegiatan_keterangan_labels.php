<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengawas_kegiatan_keterangan', function (Blueprint $table) {
            $table->string('label')->nullable()->after('pengawas_kegiatan_id');
        });

        $rows = DB::table('pengawas_kegiatan_keterangan')
            ->join('keterangan_options', 'keterangan_options.id', '=', 'pengawas_kegiatan_keterangan.keterangan_option_id')
            ->whereNull('pengawas_kegiatan_keterangan.label')
            ->select('pengawas_kegiatan_keterangan.id', 'keterangan_options.name as label')
            ->get();

        foreach ($rows as $row) {
            DB::table('pengawas_kegiatan_keterangan')
                ->where('id', $row->id)
                ->update(['label' => $row->label]);
        }

        Schema::table('pengawas_kegiatan_keterangan', function (Blueprint $table) {
            $table->dropConstrainedForeignId('keterangan_option_id');
            $table->index(['pengawas_kegiatan_id', 'label']);
        });
    }

    public function down(): void
    {
        Schema::table('pengawas_kegiatan_keterangan', function (Blueprint $table) {
            $table->dropIndex(['pengawas_kegiatan_id', 'label']);
            $table->foreignId('keterangan_option_id')
                ->nullable()
                ->constrained('keterangan_options')
                ->cascadeOnDelete()
                ->after('pengawas_kegiatan_id');
            $table->dropColumn('label');
        });
    }
};
