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
        Schema::table('progres_babak', function (Blueprint $table) {
            if (! Schema::hasColumn('progres_babak', 'keterangan_hasil')) {
                $table->string('keterangan_hasil', 100)->nullable()->after('hasil_akhir');
            }

            if (! Schema::hasColumn('progres_babak', 'file_bukti')) {
                $table->string('file_bukti', 255)->nullable()->after('keterangan_hasil');
            }

            if (! Schema::hasColumn('progres_babak', 'alasan_penolakan')) {
                $table->text('alasan_penolakan')->nullable()->after('catatan_dosen');
            }

            if (! Schema::hasColumn('progres_babak', 'diverifikasi_oleh')) {
                $table->string('diverifikasi_oleh', 20)->nullable()->after('alasan_penolakan');
            }

            if (! Schema::hasColumn('progres_babak', 'diverifikasi_pada')) {
                $table->timestamp('diverifikasi_pada')->nullable()->after('diverifikasi_oleh');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('progres_babak', function (Blueprint $table) {
            $columnsToDrop = [];

            foreach (['diverifikasi_pada', 'diverifikasi_oleh', 'alasan_penolakan', 'file_bukti', 'keterangan_hasil'] as $column) {
                if (Schema::hasColumn('progres_babak', $column)) {
                    $columnsToDrop[] = $column;
                }
            }

            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
