<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PrestasiMahasiswa extends Model
{
    use HasFactory;

    protected $table = 'prestasi_mahasiswa';

    protected $fillable = [
        'nim',
        'id_tim',
        'id_lomba',
        'id_progres',
        'jenis_prestasi',
        'hasil',
        'peringkat_skor',
        'keterangan_hasil',
        'tingkat_lomba',
        'angkatan',
        'prodi',
        'tanggal_capaian',
    ];

    protected $casts = [
        'peringkat_skor' => 'integer',
        'angkatan' => 'integer',
        'tanggal_capaian' => 'date',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'nim', 'nim');
    }

    public function tim()
    {
        return $this->belongsTo(Tim::class, 'id_tim', 'id_tim');
    }

    public function lomba()
    {
        return $this->belongsTo(Lomba::class, 'id_lomba', 'id_lomba');
    }

    public function progresBabak()
    {
        return $this->belongsTo(ProgresBabak::class, 'id_progres', 'id_progres');
    }

    /**
     * Skor urutan prestasi untuk mempertahankan capaian tertinggi saat anti-ganda.
     */
    public static function skorHasil(?string $hasil): int
    {
        return match ((string) $hasil) {
            'Juara 1' => 4,
            'Juara 2' => 3,
            'Juara 3' => 2,
            'Harapan/Favorit', 'Penghargaan Khusus' => 1,
            default => 0,
        };
    }

    /**
     * Hapus seluruh baris prestasi yang berasal dari laporan progres tertentu.
     */
    public static function removeForProgres(int $idProgres): void
    {
        static::where('id_progres', $idProgres)->delete();
        Cache::forget('admin_kpi_stats');
    }

    /**
     * Sinkronisasi baris prestasi per mahasiswa dari satu laporan progres babak.
     * Hanya laporan berbabak Final, berstatus disetujui, dengan hasil di HASIL_PRESTASI,
     * dan berasal dari tim berstatus disetujui yang menghasilkan snapshot prestasi.
     */
    public static function syncFromProgres(ProgresBabak $progres): int
    {
        if (
            $progres->status_setuju !== 'disetujui'
            || $progres->babak !== ProgresBabak::BABAK_FINAL
            || ! in_array($progres->hasil_akhir, ProgresBabak::HASIL_PRESTASI, true)
        ) {
            static::removeForProgres((int) $progres->id_progres);

            return 0;
        }

        $progres->loadMissing([
            'pengajuan.tim.lomba',
            'pengajuan.tim.ketua',
            'pengajuan.tim.anggotaDiterima.mahasiswa',
        ]);

        $tim = $progres->pengajuan?->tim;
        $lomba = $tim?->lomba;

        if (! $tim || $tim->status_tim !== 'disetujui' || ! $lomba) {
            static::removeForProgres((int) $progres->id_progres);

            return 0;
        }

        $jenisPrestasi = ProgresBabak::kategoriFinal($progres->hasil_akhir) ?? 'peringkat';
        $skorBaru = static::skorHasil($progres->hasil_akhir);

        // Kumpulkan semua anggota aktif tim (ketua + anggota diterima, unik per NIM)
        $anggotaAktif = collect();
        if ($tim->ketua) {
            $anggotaAktif->put($tim->ketua->nim, $tim->ketua);
        }
        foreach ($tim->anggotaDiterima as $anggota) {
            if ($anggota->mahasiswa) {
                $anggotaAktif->put($anggota->mahasiswa->nim, $anggota->mahasiswa);
            }
        }

        if ($anggotaAktif->isEmpty()) {
            static::removeForProgres((int) $progres->id_progres);

            return 0;
        }

        $affected = 0;

        DB::transaction(function () use ($progres, $tim, $lomba, $jenisPrestasi, $skorBaru, $anggotaAktif, &$affected) {
            // Bersihkan baris lama dari progres ini yang tidak lagi sesuai jenis/anggota
            static::where('id_progres', $progres->id_progres)
                ->where(function ($q) use ($jenisPrestasi, $anggotaAktif) {
                    $q->where('jenis_prestasi', '!=', $jenisPrestasi)
                        ->orWhereNotIn('nim', $anggotaAktif->keys()->all());
                })
                ->delete();

            foreach ($anggotaAktif as $mhs) {
                $existing = static::where('nim', $mhs->nim)
                    ->where('id_lomba', $lomba->id_lomba)
                    ->where('jenis_prestasi', $jenisPrestasi)
                    ->first();

                $payload = [
                    'nim' => $mhs->nim,
                    'id_tim' => $tim->id_tim,
                    'id_lomba' => $lomba->id_lomba,
                    'id_progres' => $progres->id_progres,
                    'jenis_prestasi' => $jenisPrestasi,
                    'hasil' => $progres->hasil_akhir,
                    'peringkat_skor' => $skorBaru,
                    'keterangan_hasil' => $progres->keterangan_hasil,
                    'tingkat_lomba' => $lomba->tingkat ?? 'Nasional',
                    'angkatan' => (int) $mhs->angkatan,
                    'prodi' => (string) $mhs->prodi,
                    'tanggal_capaian' => $progres->tanggal_update,
                ];

                if (! $existing) {
                    static::create($payload);
                    $affected++;
                } elseif ((int) $existing->id_progres === (int) $progres->id_progres || $skorBaru > (int) $existing->peringkat_skor) {
                    $existing->update($payload);
                    $affected++;
                }
            }
        });

        Cache::forget('admin_kpi_stats');

        return $affected;
    }
}
