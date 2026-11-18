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
        // 1. Referensi Tarif Standar Biaya Masukan (SBM) PMK 32/2025
        Schema::create('sbm_tarif', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 60);
            $table->string('uraian', 255);
            $table->string('provinsi', 100)->nullable();
            $table->string('satuan', 30);
            $table->decimal('tarif', 14, 2)->default(0);
            $table->enum('sifat', ['batas_tertinggi', 'dapat_dilampaui', 'at_cost']);
            $table->unsignedSmallInteger('tahun_anggaran')->default(2026);
            $table->string('sumber', 100)->default('PMK 32/2025');
            $table->timestamps();

            $table->index(['kode', 'provinsi', 'tahun_anggaran']);
        });

        // 2. Pengajuan Rencana Anggaran Biaya (RAB) per Tim & Babak
        Schema::create('rab_pengajuan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tim_id');
            $table->unsignedBigInteger('lomba_id');
            $table->enum('babak', ['semi_final', 'final']);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('kota_tujuan', 100);
            $table->string('provinsi_tujuan', 100);
            $table->unsignedSmallInteger('jumlah_peserta')->default(1);
            $table->decimal('total_anggaran', 15, 2)->default(0);
            $table->enum('status', ['draft', 'menunggu_validasi', 'perlu_revisi', 'disetujui'])->default('draft');
            $table->string('link_dokumen', 500)->nullable();
            $table->text('catatan_dosen')->nullable();
            $table->string('diisi_terakhir_oleh', 20)->nullable();
            $table->string('divalidasi_oleh', 20)->nullable();
            $table->timestamp('divalidasi_pada')->nullable();
            $table->timestamps();

            $table->unique(['tim_id', 'babak']);
            $table->index('status');

            $table->foreign('tim_id')->references('id_tim')->on('tim')->cascadeOnDelete();
            $table->foreign('lomba_id')->references('id_lomba')->on('lomba')->cascadeOnDelete();
            $table->foreign('diisi_terakhir_oleh')->references('nim')->on('mahasiswa')->nullOnDelete();
            $table->foreign('divalidasi_oleh')->references('nidn')->on('dosen')->nullOnDelete();
        });

        // 3. Rincian Komponen Biaya RAB
        Schema::create('rab_item', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rab_pengajuan_id');
            $table->enum('kategori', [
                'uang_harian_luar_kota',
                'uang_harian_dalam_kota',
                'penginapan',
                'tiket_antar_kota',
                'transport_terminal',
                'transport_lokal',
                'biaya_pendaftaran',
                'lain_lain',
            ]);
            $table->string('uraian', 255);
            $table->string('satuan', 30);
            $table->unsignedSmallInteger('jumlah_orang')->default(1);
            $table->unsignedSmallInteger('frekuensi')->default(1);
            $table->decimal('tarif_satuan', 14, 2)->default(0);
            $table->enum('sifat', ['batas_tertinggi', 'dapat_dilampaui', 'at_cost']);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->text('alasan_melebihi')->nullable();
            $table->string('bukti_link', 500)->nullable();
            $table->timestamps();

            $table->index('rab_pengajuan_id');
            $table->foreign('rab_pengajuan_id')->references('id')->on('rab_pengajuan')->cascadeOnDelete();
        });

        // 4. Riwayat Log Perubahan Status RAB (Ringan)
        Schema::create('rab_riwayat', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rab_pengajuan_id');
            $table->enum('aksi', ['dikirim', 'direvisi_dosen', 'dikirim_ulang', 'disetujui']);
            $table->string('status_dari', 30)->nullable();
            $table->string('status_ke', 30);
            $table->text('catatan')->nullable();
            $table->string('user_id', 30);
            $table->decimal('total_anggaran', 15, 2)->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->index('rab_pengajuan_id');
            $table->foreign('rab_pengajuan_id')->references('id')->on('rab_pengajuan')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rab_riwayat');
        Schema::dropIfExists('rab_item');
        Schema::dropIfExists('rab_pengajuan');
        Schema::dropIfExists('sbm_tarif');
    }
};
