<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mahasiswa', function (Blueprint $table) {
            if (! Schema::hasColumn('mahasiswa', 'bidang_minat')) {
                $table->json('bidang_minat')->nullable();
            }

            if (! Schema::hasColumn('mahasiswa', 'portofolio')) {
                $table->json('portofolio')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mahasiswa', function (Blueprint $table) {
            if (Schema::hasColumn('mahasiswa', 'bidang_minat')) {
                $table->dropColumn('bidang_minat');
            }

            if (Schema::hasColumn('mahasiswa', 'portofolio')) {
                $table->dropColumn('portofolio');
            }
        });
    }
};
