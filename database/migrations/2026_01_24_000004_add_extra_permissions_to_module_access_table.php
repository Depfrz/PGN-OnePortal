<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('module_access', function (Blueprint $table) {
            $table->json('extra_permissions')->nullable()->after('show_on_dashboard');
        });
    }

    public function down(): void
    {
        Schema::table('module_access', function (Blueprint $table) {
            $table->dropColumn('extra_permissions');
        });
    }
};

