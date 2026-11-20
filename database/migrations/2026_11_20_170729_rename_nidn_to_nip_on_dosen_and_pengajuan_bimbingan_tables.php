<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ubah nama kolom nidn menjadi nip pada tabel dosen dan pengajuan_bimbingan.
     */
    public function up(): void
    {
        if (Schema::hasColumn('dosen', 'nidn') && ! Schema::hasColumn('dosen', 'nip')) {
            Schema::table('dosen', function (Blueprint $table) {
                $table->renameColumn('nidn', 'nip');
            });
        }

        if (Schema::hasColumn('pengajuan_bimbingan', 'nidn') && ! Schema::hasColumn('pengajuan_bimbingan', 'nip')) {
            Schema::table('pengajuan_bimbingan', function (Blueprint $table) {
                $table->renameColumn('nidn', 'nip');
            });
        }
    }

    /**
     * Kembalikan nama kolom nip menjadi nidn pada tabel dosen dan pengajuan_bimbingan.
     */
    public function down(): void
    {
        if (Schema::hasColumn('pengajuan_bimbingan', 'nip') && ! Schema::hasColumn('pengajuan_bimbingan', 'nidn')) {
            Schema::table('pengajuan_bimbingan', function (Blueprint $table) {
                $table->renameColumn('nip', 'nidn');
            });
        }

        if (Schema::hasColumn('dosen', 'nip') && ! Schema::hasColumn('dosen', 'nidn')) {
            Schema::table('dosen', function (Blueprint $table) {
                $table->renameColumn('nip', 'nidn');
            });
        }
    }
};
