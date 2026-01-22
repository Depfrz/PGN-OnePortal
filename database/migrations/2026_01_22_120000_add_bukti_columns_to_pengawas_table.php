<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengawas', function (Blueprint $table) {
            if (!Schema::hasColumn('pengawas', 'bukti_path')) {
                $table->string('bukti_path')->nullable()->after('status');
            }
            if (!Schema::hasColumn('pengawas', 'bukti_original_name')) {
                $table->string('bukti_original_name')->nullable()->after('bukti_path');
            }
            if (!Schema::hasColumn('pengawas', 'bukti_mime')) {
                $table->string('bukti_mime')->nullable()->after('bukti_original_name');
            }
            if (!Schema::hasColumn('pengawas', 'bukti_size')) {
                $table->unsignedBigInteger('bukti_size')->nullable()->after('bukti_mime');
            }
            if (!Schema::hasColumn('pengawas', 'bukti_uploaded_at')) {
                $table->timestamp('bukti_uploaded_at')->nullable()->after('bukti_size');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pengawas', function (Blueprint $table) {
            if (Schema::hasColumn('pengawas', 'bukti_uploaded_at')) {
                $table->dropColumn('bukti_uploaded_at');
            }
            if (Schema::hasColumn('pengawas', 'bukti_size')) {
                $table->dropColumn('bukti_size');
            }
            if (Schema::hasColumn('pengawas', 'bukti_mime')) {
                $table->dropColumn('bukti_mime');
            }
            if (Schema::hasColumn('pengawas', 'bukti_original_name')) {
                $table->dropColumn('bukti_original_name');
            }
            if (Schema::hasColumn('pengawas', 'bukti_path')) {
                $table->dropColumn('bukti_path');
            }
        });
    }
};

