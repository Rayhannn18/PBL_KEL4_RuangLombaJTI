<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('mahasiswa')->after('email')->index();
            // NIM untuk mahasiswa, NIP/NIDN untuk dosen.
            $table->string('nim_nip', 20)->nullable()->unique()->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['nim_nip']);
            $table->dropIndex(['role']);
            $table->dropColumn(['role', 'nim_nip']);
        });
    }
};