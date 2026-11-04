<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambah kolom catatan (deskripsi tim / posisi yang dicari) ke tabel tim.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('tim', 'catatan')) {
            Schema::table('tim', function (Blueprint $table) {
                $table->text('catatan')->nullable()->after('file_cv');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('tim', 'catatan')) {
            Schema::table('tim', function (Blueprint $table) {
                $table->dropColumn('catatan');
            });
        }
    }
};
