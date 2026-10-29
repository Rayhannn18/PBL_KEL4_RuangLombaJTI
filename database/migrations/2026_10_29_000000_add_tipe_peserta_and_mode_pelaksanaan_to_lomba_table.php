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
            if (! Schema::hasColumn('lomba', 'tipe_peserta')) {
                $table->string('tipe_peserta', 50)->default('Kelompok');
            }

            if (! Schema::hasColumn('lomba', 'mode_pelaksanaan')) {
                $table->string('mode_pelaksanaan', 50)->default('Online');
            }

            if (Schema::hasColumn('lomba', 'biaya')) {
                $table->bigInteger('biaya')->default(0)->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lomba', function (Blueprint $table) {
            if (Schema::hasColumn('lomba', 'tipe_peserta')) {
                $table->dropColumn('tipe_peserta');
            }

            if (Schema::hasColumn('lomba', 'mode_pelaksanaan')) {
                $table->dropColumn('mode_pelaksanaan');
            }

            if (Schema::hasColumn('lomba', 'biaya')) {
                $table->decimal('biaya', 12, 2)->default(0)->change();
            }
        });
    }
};
