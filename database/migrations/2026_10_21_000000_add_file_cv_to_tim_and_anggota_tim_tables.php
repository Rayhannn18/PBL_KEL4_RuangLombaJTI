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
        if (! Schema::hasColumn('tim', 'file_cv')) {
            Schema::table('tim', function (Blueprint $table) {
                $table->string('file_cv', 255)->nullable();
            });
        }

        if (! Schema::hasColumn('anggota_tim', 'file_cv')) {
            Schema::table('anggota_tim', function (Blueprint $table) {
                $table->string('file_cv', 255)->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('tim', 'file_cv')) {
            Schema::table('tim', function (Blueprint $table) {
                $table->dropColumn('file_cv');
            });
        }

        if (Schema::hasColumn('anggota_tim', 'file_cv')) {
            Schema::table('anggota_tim', function (Blueprint $table) {
                $table->dropColumn('file_cv');
            });
        }
    }
};
