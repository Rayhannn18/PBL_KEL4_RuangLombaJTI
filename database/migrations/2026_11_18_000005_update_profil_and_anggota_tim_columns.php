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
        if (Schema::hasColumn('mahasiswa', 'bidang_minat')) {
            Schema::table('mahasiswa', function (Blueprint $table) {
                $table->dropColumn('bidang_minat');
            });
        }

        Schema::table('anggota_tim', function (Blueprint $table) {
            if (! Schema::hasColumn('anggota_tim', 'posisi_dilamar')) {
                $table->string('posisi_dilamar', 60)->nullable()->after('peran');
            }

            if (! Schema::hasColumn('anggota_tim', 'pesan')) {
                $table->string('pesan', 300)->nullable()->after('posisi_dilamar');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('anggota_tim', function (Blueprint $table) {
            if (Schema::hasColumn('anggota_tim', 'pesan')) {
                $table->dropColumn('pesan');
            }

            if (Schema::hasColumn('anggota_tim', 'posisi_dilamar')) {
                $table->dropColumn('posisi_dilamar');
            }
        });

        if (! Schema::hasColumn('mahasiswa', 'bidang_minat')) {
            Schema::table('mahasiswa', function (Blueprint $table) {
                $table->json('bidang_minat')->nullable();
            });
        }
    }
};
