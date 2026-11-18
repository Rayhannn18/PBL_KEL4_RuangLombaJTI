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
            if (! Schema::hasColumn('lomba', 'alasan_penolakan')) {
                $table->text('alasan_penolakan')->nullable()->after('status_verifikasi');
            }

            if (! Schema::hasColumn('lomba', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('id_admin');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lomba', function (Blueprint $table) {
            if (Schema::hasColumn('lomba', 'verified_at')) {
                $table->dropColumn('verified_at');
            }

            if (Schema::hasColumn('lomba', 'alasan_penolakan')) {
                $table->dropColumn('alasan_penolakan');
            }
        });
    }
};
