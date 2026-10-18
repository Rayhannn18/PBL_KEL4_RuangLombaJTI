<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {

        Schema::create('lomba_divisi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_lomba');
            $table->string('nama_divisi', 150);
            $table->string('kode_icon', 50)->nullable();
            $table->timestamps();

            $table->foreign('id_lomba')->references('id_lomba')->on('lomba')->cascadeOnDelete();
        });

        Schema::create('lomba_persyaratan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_lomba');
            $table->text('persyaratan');
            $table->integer('urutan')->default(1);
            $table->timestamps();

            $table->foreign('id_lomba')->references('id_lomba')->on('lomba')->cascadeOnDelete();
        });

        Schema::create('lomba_jadwal', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_lomba');
            $table->string('nama_tahap', 150);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->integer('urutan')->default(1);
            $table->timestamps();

            $table->foreign('id_lomba')->references('id_lomba')->on('lomba')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lomba_jadwal');
        Schema::dropIfExists('lomba_persyaratan');
        Schema::dropIfExists('lomba_divisi');
    }
};
