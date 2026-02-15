<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengawas_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengawas_id')->constrained('pengawas')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['pengawas_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengawas_users');
    }
};
