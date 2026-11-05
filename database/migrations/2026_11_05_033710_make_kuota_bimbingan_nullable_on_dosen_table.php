<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jadikan kuota_bimbingan nullable dengan default 5.
     * Admin tidak lagi mengisi kuota saat menambah dosen baru —
     * dosen mengatur sendiri kuotanya lewat profil mereka.
     */
    public function up(): void
    {
        Schema::table('dosen', function (Blueprint $table) {
            $table->integer('kuota_bimbingan')->nullable()->default(5)->change();
        });
    }

    public function down(): void
    {
        Schema::table('dosen', function (Blueprint $table) {
            $table->integer('kuota_bimbingan')->nullable(false)->default(5)->change();
        });
    }
};
