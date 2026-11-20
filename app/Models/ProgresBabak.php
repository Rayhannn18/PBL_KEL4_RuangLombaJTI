<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class ProgresBabak extends Model
{
    use HasFactory;

    public const BABAK_PENYISIHAN = 'Penyisihan';

    public const BABAK_SEMIFINAL = 'Semifinal';

    public const BABAK_FINAL = 'Final';

    public const BABAK_LAINNYA = 'Babak Lainnya';

    public const BABAK_STANDAR = [
        self::BABAK_PENYISIHAN,
        self::BABAK_SEMIFINAL,
        self::BABAK_FINAL,
    ];

    public const BABAK_OPTIONS = [
        self::BABAK_PENYISIHAN,
        self::BABAK_SEMIFINAL,
        self::BABAK_FINAL,
        self::BABAK_LAINNYA,
    ];

    public const HASIL_NON_FINAL = [
        'Lolos ke babak berikutnya',
        'Tidak lolos',
    ];

    public const HASIL_FINAL = [
        'Juara 1',
        'Juara 2',
        'Juara 3',
        'Harapan/Favorit',
        'Penghargaan Khusus',
        'Finalis (tanpa penghargaan)',
    ];

    /**
     * Daftar hasil babak Final yang dihitung sebagai prestasi mahasiswa
     * dan wajib menyertakan unggah bukti.
     */
    public const HASIL_PRESTASI = [
        'Juara 1',
        'Juara 2',
        'Juara 3',
        'Harapan/Favorit',
        'Penghargaan Khusus',
    ];

    /**
     * Kelompok hasil Final jenis peringkat (maksimal 1 laporan aktif per tim per lomba).
     */
    public const HASIL_FINAL_PERINGKAT = [
        'Juara 1',
        'Juara 2',
        'Juara 3',
        'Harapan/Favorit',
        'Finalis (tanpa penghargaan)',
    ];

    /**
     * Hasil Final jenis penghargaan khusus (maksimal 1 laporan aktif per tim per lomba).
     */
    public const HASIL_FINAL_PENGHARGAAN_KHUSUS = 'Penghargaan Khusus';

    /**
     * Disk privat untuk berkas bukti (tidak terjangkau lewat /storage);
     * disajikan hanya melalui route bimbingan.progres.bukti dengan otorisasi.
     */
    public const DISK_BUKTI = 'local';

    protected $table = 'progres_babak';

    protected $primaryKey = 'id_progres';

    protected $fillable = [
        'id_pengajuan',
        'babak',
        'tanggal_update',
        'hasil_akhir',
        'keterangan_hasil',
        'file_bukti',
        'status_setuju',
        'catatan_dosen',
        'alasan_penolakan',
        'diverifikasi_oleh',
        'diverifikasi_pada',
    ];

    protected $casts = [
        'tanggal_update' => 'date',
        'diverifikasi_pada' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saved(function (ProgresBabak $progres): void {
            Cache::forget('admin_kpi_stats');

            if (Schema::hasTable('prestasi_mahasiswa')) {
                PrestasiMahasiswa::syncFromProgres($progres);
            }
        });

        static::deleted(function (ProgresBabak $progres): void {
            Cache::forget('admin_kpi_stats');

            if (Schema::hasTable('prestasi_mahasiswa')) {
                PrestasiMahasiswa::removeForProgres((int) $progres->id_progres);
            }
        });
    }

    public function prestasiMahasiswa()
    {
        return $this->hasMany(PrestasiMahasiswa::class, 'id_progres', 'id_progres');
    }

    public function pengajuan()
    {
        return $this->belongsTo(PengajuanBimbingan::class, 'id_pengajuan', 'id_pengajuan');
    }

    public function verifikatorDosen()
    {
        return $this->belongsTo(Dosen::class, 'diverifikasi_oleh', 'nip');
    }

    /**
     * Cek apakah hasil babak Final wajib melampirkan file bukti.
     */
    public static function isHasilWajibBukti(?string $hasil): bool
    {
        return in_array((string) $hasil, self::HASIL_PRESTASI, true);
    }

    /**
     * Tentukan kategori laporan Final ('peringkat' atau 'penghargaan_khusus')
     * untuk aturan maksimal 1 laporan aktif per jenis pada babak Final.
     */
    public static function kategoriFinal(?string $hasil): ?string
    {
        $hasilStr = trim((string) $hasil);
        if ($hasilStr === '') {
            return null;
        }

        if ($hasilStr === self::HASIL_FINAL_PENGHARGAAN_KHUSUS) {
            return 'penghargaan_khusus';
        }

        if (in_array($hasilStr, self::HASIL_FINAL_PERINGKAT, true)) {
            return 'peringkat';
        }

        $lower = strtolower($hasilStr);
        if (str_contains($lower, 'juara') || str_contains($lower, 'finalis') || str_contains($lower, 'harapan') || str_contains($lower, 'favorit')) {
            return 'peringkat';
        }

        if (str_contains($lower, 'best') || str_contains($lower, 'penghargaan khusus')) {
            return 'penghargaan_khusus';
        }

        return null;
    }

    /**
     * Ambil alasan penolakan (mendukung kolom alasan_penolakan baru maupun catatan_dosen lama).
     */
    public function getAlasanTolakAttribute(): ?string
    {
        return $this->alasan_penolakan ?: $this->catatan_dosen;
    }

    public function setujuiProgres(?string $catatan = null, ?string $diverifikasiOleh = null): self
    {
        $payload = [
            'status_setuju' => 'disetujui',
            'catatan_dosen' => $catatan,
        ];

        if (Schema::hasColumn('progres_babak', 'alasan_penolakan')) {
            $payload['alasan_penolakan'] = null;
        }

        if (Schema::hasColumn('progres_babak', 'diverifikasi_oleh')) {
            $payload['diverifikasi_oleh'] = $diverifikasiOleh;
        }

        if (Schema::hasColumn('progres_babak', 'diverifikasi_pada')) {
            $payload['diverifikasi_pada'] = now();
        }

        $this->update($payload);

        return $this;
    }

    public function tolakProgres(string $alasan, ?string $diverifikasiOleh = null): self
    {
        $alasanTrimmed = trim($alasan);

        $payload = [
            'status_setuju' => 'ditolak',
            'catatan_dosen' => $alasanTrimmed,
        ];

        if (Schema::hasColumn('progres_babak', 'alasan_penolakan')) {
            $payload['alasan_penolakan'] = $alasanTrimmed;
        }

        if (Schema::hasColumn('progres_babak', 'diverifikasi_oleh')) {
            $payload['diverifikasi_oleh'] = $diverifikasiOleh;
        }

        if (Schema::hasColumn('progres_babak', 'diverifikasi_pada')) {
            $payload['diverifikasi_pada'] = now();
        }

        $this->update($payload);

        return $this;
    }

    public function isJuara(): bool
    {
        if (! $this->hasil_akhir) {
            return false;
        }

        if (in_array($this->hasil_akhir, self::HASIL_PRESTASI, true)) {
            return true;
        }

        $lower = strtolower($this->hasil_akhir);

        return str_contains($lower, 'juara')
            || str_contains($lower, 'gold')
            || str_contains($lower, 'silver')
            || str_contains($lower, 'bronze')
            || str_contains($lower, 'best')
            || str_contains($lower, 'harapan')
            || str_contains($lower, 'favorit')
            || str_contains($lower, 'penghargaan khusus');
    }
}
