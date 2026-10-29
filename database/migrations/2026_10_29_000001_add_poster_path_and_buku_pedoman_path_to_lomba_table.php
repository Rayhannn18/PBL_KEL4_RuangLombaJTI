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
        Schema::table('lomba', function (Blueprint $table) {
            if (! Schema::hasColumn('lomba', 'poster_path')) {
                $table->string('poster_path', 255)->nullable()->after('poster');
            }

            if (! Schema::hasColumn('lomba', 'buku_pedoman_path')) {
                $table->string('buku_pedoman_path', 255)->nullable()->after('poster_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lomba', function (Blueprint $table) {
            if (Schema::hasColumn('lomba', 'buku_pedoman_path')) {
                $table->dropColumn('buku_pedoman_path');
            }

            if (Schema::hasColumn('lomba', 'poster_path')) {
                $table->dropColumn('poster_path');
            }
        });
    }
};
