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
        // 1. Mahasiswa
        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->string('nim', 20)->primary();
            $table->string('nama', 150);
            $table->string('email_kampus', 150)->unique();
            $table->string('password');
            $table->string('prodi', 100);
            $table->integer('angkatan');
            $table->timestamps();
        });

        // 2. Dosen
        Schema::create('dosen', function (Blueprint $table) {
            $table->string('nidn', 20)->primary();
            $table->string('nama', 150);
            $table->string('email_kampus', 150)->unique();
            $table->string('password');
            $table->string('bidang_keahlian', 255);
            $table->integer('kuota_bimbingan')->default(5);
            $table->timestamps();
        });

        // 3. Admin
        Schema::create('admin', function (Blueprint $table) {
            $table->id('id_admin');
            $table->string('nama', 150);
            $table->string('email', 150)->unique();
            $table->string('password');
            $table->timestamps();
        });

        // 4. Lomba
        Schema::create('lomba', function (Blueprint $table) {
            $table->id('id_lomba');
            $table->string('nama_lomba', 255);
            $table->string('kategori', 100);
            $table->string('tingkat', 50); // Nasional, Internasional, Regional, Provinsi
            $table->string('penyelenggara', 150);
            $table->text('deskripsi')->nullable();
            $table->text('persyaratan')->nullable();
            $table->date('tenggat');
            $table->decimal('biaya', 12, 2)->default(0);
            $table->text('benefit')->nullable();
            $table->string('tautan_daftar', 255);
            $table->string('status_verifikasi', 30)->default('menunggu'); // terverifikasi, menunggu, ditolak
            $table->string('poster', 255)->nullable();
            $table->string('nim', 20)->nullable(); // Diajukan oleh mahasiswa
            $table->unsignedBigInteger('id_admin')->nullable(); // Diverifikasi oleh admin
            $table->timestamps();

            $table->foreign('nim')->references('nim')->on('mahasiswa')->nullOnDelete();
            $table->foreign('id_admin')->references('id_admin')->on('admin')->nullOnDelete();
        });

        // 5. Tim
        Schema::create('tim', function (Blueprint $table) {
            $table->id('id_tim');
            $table->string('nama_tim', 150);
            $table->unsignedBigInteger('id_lomba');
            $table->string('nim', 20); // Ketua tim
            $table->integer('kuota_anggota')->default(3);
            $table->string('status_tim', 30)->default('menunggu_persetujuan'); // disetujui, menunggu_persetujuan, ditolak
            $table->unsignedBigInteger('id_admin')->nullable(); // Disetujui oleh admin
            $table->timestamps();

            $table->foreign('id_lomba')->references('id_lomba')->on('lomba')->cascadeOnDelete();
            $table->foreign('nim')->references('nim')->on('mahasiswa')->cascadeOnDelete();
            $table->foreign('id_admin')->references('id_admin')->on('admin')->nullOnDelete();
        });

        // 6. Anggota Tim
        Schema::create('anggota_tim', function (Blueprint $table) {
            $table->id('id_anggota');
            $table->unsignedBigInteger('id_tim');
            $table->string('nim', 20);
            $table->string('status_gabung', 30)->default('menunggu'); // diterima, menunggu, ditolak
            $table->string('peran', 100)->default('Anggota');
            $table->timestamps();

            $table->foreign('id_tim')->references('id_tim')->on('tim')->cascadeOnDelete();
            $table->foreign('nim')->references('nim')->on('mahasiswa')->cascadeOnDelete();
        });

        // 7. Pengajuan Bimbingan
        Schema::create('pengajuan_bimbingan', function (Blueprint $table) {
            $table->id('id_pengajuan');
            $table->unsignedBigInteger('id_tim');
            $table->string('nidn', 20);
            $table->date('tgl_pengajuan');
            $table->string('status', 30)->default('menunggu'); // disetujui, menunggu, ditolak
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('id_tim')->references('id_tim')->on('tim')->cascadeOnDelete();
            $table->foreign('nidn')->references('nidn')->on('dosen')->cascadeOnDelete();
        });

        // 8. Logbook
        Schema::create('logbook', function (Blueprint $table) {
            $table->id('id_logbook');
            $table->unsignedBigInteger('id_pengajuan');
            $table->date('tanggal');
            $table->text('materi');
            $table->text('tindak_lanjut')->nullable();
            $table->string('status_validasi', 30)->default('menunggu'); // disetujui, menunggu, revisi
            $table->timestamps();

            $table->foreign('id_pengajuan')->references('id_pengajuan')->on('pengajuan_bimbingan')->cascadeOnDelete();
        });

        // 9. Progres Babak
        Schema::create('progres_babak', function (Blueprint $table) {
            $table->id('id_progres');
            $table->unsignedBigInteger('id_pengajuan');
            $table->string('babak', 50); // Persiapan, Penyisihan, Semifinal, Final
            $table->date('tanggal_update');
            $table->string('hasil_akhir', 100)->nullable(); // Lolos, Gugur, Juara 1, Juara 2, Juara 3, Juara Harapan, Finalis
            $table->string('status_setuju', 30)->default('menunggu'); // disetujui, menunggu, ditolak
            $table->timestamps();

            $table->foreign('id_pengajuan')->references('id_pengajuan')->on('pengajuan_bimbingan')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progres_babak');
        Schema::dropIfExists('logbook');
        Schema::dropIfExists('pengajuan_bimbingan');
        Schema::dropIfExists('anggota_tim');
        Schema::dropIfExists('tim');
        Schema::dropIfExists('lomba');
        Schema::dropIfExists('admin');
        Schema::dropIfExists('dosen');
        Schema::dropIfExists('mahasiswa');
    }
};
