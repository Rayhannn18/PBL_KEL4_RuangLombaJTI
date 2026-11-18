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
        if (Schema::hasTable('prestasi_mahasiswa')) {
            return;
        }

        Schema::create('prestasi_mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->string('nim', 20);
            $table->unsignedBigInteger('id_tim');
            $table->unsignedBigInteger('id_lomba');
            $table->unsignedBigInteger('id_progres');
            $table->string('jenis_prestasi', 30); // 'peringkat' atau 'penghargaan_khusus'
            $table->string('hasil', 50);
            $table->smallInteger('peringkat_skor')->default(1);
            $table->string('keterangan_hasil', 100)->nullable();
            $table->string('tingkat_lomba', 50);
            $table->integer('angkatan');
            $table->string('prodi', 100);
            $table->date('tanggal_capaian')->nullable();
            $table->timestamps();

            $table->foreign('nim')->references('nim')->on('mahasiswa')->cascadeOnDelete();
            $table->foreign('id_tim')->references('id_tim')->on('tim')->cascadeOnDelete();
            $table->foreign('id_lomba')->references('id_lomba')->on('lomba')->cascadeOnDelete();
            $table->foreign('id_progres')->references('id_progres')->on('progres_babak')->cascadeOnDelete();

            $table->unique(['nim', 'id_lomba', 'jenis_prestasi'], 'prestasi_mhs_nim_lomba_jenis_unique');
            $table->index('angkatan');
            $table->index('prodi');
            $table->index('id_progres');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prestasi_mahasiswa');
    }
};
