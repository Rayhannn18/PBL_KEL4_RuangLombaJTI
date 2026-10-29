<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Hanya diisi untuk mahasiswa; dosen dan admin dibiarkan kosong.
            $table->string('prodi', 60)->nullable()->after('nim_nip');
            $table->unsignedSmallInteger('angkatan')->nullable()->after('prodi');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['prodi', 'angkatan']);
        });
    }
};
