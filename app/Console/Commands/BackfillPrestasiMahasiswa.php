<?php

namespace App\Console\Commands;

use App\Models\ProgresBabak;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BackfillPrestasiMahasiswa extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prestasi:backfill {--dry-run : Tampilkan laporan perubahan tanpa menulis ke database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Normalisasi data lama progres_babak dan backfill snapshot prestasi_mahasiswa secara idempoten';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->info($dryRun
            ? '=== DRY-RUN BACKFILL PRESTASI MAHASISWA (tidak ada perubahan database) ==='
            : '=== BACKFILL PRESTASI MAHASISWA ===');

        $progresList = ProgresBabak::with([
            'pengajuan.tim.lomba',
            'pengajuan.tim.ketua',
            'pengajuan.tim.anggotaDiterima.mahasiswa',
        ])->orderBy('id_progres')->get();

        $totalProgres = $progresList->count();
        $totalPrestasiAkanDibuat = 0;
        $totalDilewati = 0;
        $unmappedList = [];

        foreach ($progresList as $progres) {
            $sebelum = sprintf(
                'babak="%s", hasil="%s", ket="%s"',
                $progres->babak,
                $progres->hasil_akhir ?? '-',
                $progres->keterangan_hasil ?? '-'
            );

            $mapped = $this->mapLegacyProgres($progres);

            if ($mapped['unmapped']) {
                $unmappedList[] = "id_progres={$progres->id_progres} ({$sebelum})";
            }

            $sesudah = sprintf(
                'babak="%s", hasil="%s", ket="%s"',
                $mapped['babak'],
                $mapped['hasil_akhir'] ?? '-',
                $mapped['keterangan_hasil'] ?? '-'
            );

            $tim = $progres->pengajuan?->tim;
            $lomba = $tim?->lomba;
            $statusTim = $tim?->status_tim ?? 'tidak_ada';

            $catatan = '';
            $jumlahBarisPrestasi = 0;

            if ($progres->status_setuju !== 'disetujui') {
                $catatan = "status_setuju={$progres->status_setuju} (tidak dihitung)";
                $totalDilewati++;
            } elseif (! $tim || $statusTim !== 'disetujui') {
                $catatan = "status_tim={$statusTim} (tidak dihitung sebagai prestasi)";
                $totalDilewati++;
            } elseif (! $lomba) {
                $catatan = 'lomba tidak ditemukan (tidak dipetakan)';
                $totalDilewati++;
            } elseif (
                $mapped['babak'] === ProgresBabak::BABAK_FINAL
                && in_array($mapped['hasil_akhir'], ProgresBabak::HASIL_PRESTASI, true)
            ) {
                $anggotaNims = collect();
                if ($tim->ketua) {
                    $anggotaNims->push($tim->ketua->nim);
                }
                foreach ($tim->anggotaDiterima as $ang) {
                    $anggotaNims->push($ang->nim);
                }
                $jumlahBarisPrestasi = $anggotaNims->unique()->count();
                $catatan = 'memenuhi syarat prestasi';
            } else {
                $catatan = 'hanya monitoring';
            }

            $totalPrestasiAkanDibuat += $jumlahBarisPrestasi;

            $this->line(sprintf(
                '[id_progres=%d | %s | status=%s] %s => %s | baris_prestasi=%d (%s)',
                $progres->id_progres,
                $tim?->nama_tim ?? '-',
                $progres->status_setuju,
                $sebelum,
                $sesudah,
                $jumlahBarisPrestasi,
                $catatan
            ));

            if (! $dryRun) {
                DB::transaction(function () use ($progres, $mapped) {
                    $progres->babak = $mapped['babak'];
                    $progres->hasil_akhir = $mapped['hasil_akhir'];
                    $progres->keterangan_hasil = $mapped['keterangan_hasil'];
                    // Tidak mengubah status_setuju
                    $progres->save();
                });
            }
        }

        if (! $dryRun) {
            Cache::forget('admin_kpi_stats');
        }

        $this->newLine();
        $this->info("Total progres diperiksa: {$totalProgres}");
        $this->info('Total baris prestasi '.($dryRun ? 'yang akan diproses' : 'disinkronkan').": {$totalPrestasiAkanDibuat}");
        $this->info("Total tidak dihitung / dilewati: {$totalDilewati}");

        if (! empty($unmappedList)) {
            $this->warn('Data yang tidak dapat dipetakan otomatis:');
            foreach ($unmappedList as $item) {
                $this->warn(" - {$item}");
            }
        }

        return self::SUCCESS;
    }

    /**
     * Petakan nilai lama babak, hasil_akhir, dan keterangan_hasil ke format standar tanpa mengubah status_setuju.
     *
     * @return array{babak: string, hasil_akhir: ?string, keterangan_hasil: ?string, unmapped: bool}
     */
    private function mapLegacyProgres(ProgresBabak $progres): array
    {
        $rawBabak = trim((string) $progres->babak);
        $rawHasil = trim((string) ($progres->hasil_akhir ?? ''));
        $rawKet = $progres->keterangan_hasil !== null ? trim((string) $progres->keterangan_hasil) : null;
        $babakLower = strtolower($rawBabak);
        $hasilLower = strtolower($rawHasil);

        // 1. Kasus khusus Final Exhibition / Penghargaan Khusus (mis. Best Prototype & Implementation)
        if (str_contains($babakLower, 'exhibition') || str_contains($hasilLower, 'best ')) {
            return [
                'babak' => ProgresBabak::BABAK_FINAL,
                'hasil_akhir' => 'Penghargaan Khusus',
                'keterangan_hasil' => $rawKet ?: ($rawHasil !== '' ? mb_substr($rawHasil, 0, 100) : null),
                'unmapped' => false,
            ];
        }

        // 2. Babak Non-Final (Penyisihan / Semifinal)
        if (str_contains($babakLower, 'penyisihan') || str_contains($babakLower, 'penyisahan')) {
            return [
                'babak' => ProgresBabak::BABAK_PENYISIHAN,
                'hasil_akhir' => $this->mapNonFinalHasil($rawHasil),
                'keterangan_hasil' => null,
                'unmapped' => false,
            ];
        }

        if (str_contains($babakLower, 'semifinal') || str_contains($babakLower, 'semi final') || str_contains($babakLower, 'semi-final')) {
            return [
                'babak' => ProgresBabak::BABAK_SEMIFINAL,
                'hasil_akhir' => $this->mapNonFinalHasil($rawHasil),
                'keterangan_hasil' => null,
                'unmapped' => false,
            ];
        }

        // 3. Babak Final
        if ($babakLower === 'final' || str_contains($babakLower, 'final')) {
            if (in_array($rawHasil, ProgresBabak::HASIL_FINAL, true)) {
                return [
                    'babak' => ProgresBabak::BABAK_FINAL,
                    'hasil_akhir' => $rawHasil,
                    'keterangan_hasil' => $rawKet,
                    'unmapped' => false,
                ];
            }

            if (preg_match('/^juara\s*([123])\b(.*)$/i', $rawHasil, $m)) {
                $peringkat = 'Juara '.$m[1];
                $sisa = trim($m[2]);
                $sisa = trim((string) preg_replace('/^(Nasional|Internasional|Regional|Kampus)\b/i', '', $sisa));

                return [
                    'babak' => ProgresBabak::BABAK_FINAL,
                    'hasil_akhir' => $peringkat,
                    'keterangan_hasil' => $rawKet ?: ($sisa !== '' ? mb_substr($sisa, 0, 100) : null),
                    'unmapped' => false,
                ];
            }

            if (str_contains($hasilLower, 'harapan') || str_contains($hasilLower, 'favorit')) {
                return [
                    'babak' => ProgresBabak::BABAK_FINAL,
                    'hasil_akhir' => 'Harapan/Favorit',
                    'keterangan_hasil' => $rawKet ?: ($rawHasil !== '' ? mb_substr($rawHasil, 0, 100) : null),
                    'unmapped' => false,
                ];
            }

            if (str_contains($hasilLower, 'finalis')) {
                return [
                    'babak' => ProgresBabak::BABAK_FINAL,
                    'hasil_akhir' => 'Finalis (tanpa penghargaan)',
                    'keterangan_hasil' => null,
                    'unmapped' => false,
                ];
            }

            return [
                'babak' => ProgresBabak::BABAK_FINAL,
                'hasil_akhir' => $rawHasil,
                'keterangan_hasil' => $rawKet,
                'unmapped' => true,
            ];
        }

        // Babak lainnya
        return [
            'babak' => $rawBabak,
            'hasil_akhir' => $this->mapNonFinalHasil($rawHasil),
            'keterangan_hasil' => null,
            'unmapped' => ! in_array($this->mapNonFinalHasil($rawHasil), ProgresBabak::HASIL_NON_FINAL, true),
        ];
    }

    private function mapNonFinalHasil(string $rawHasil): string
    {
        $lower = strtolower(trim($rawHasil));

        if (str_contains($lower, 'tidak') || str_contains($lower, 'gugur')) {
            return 'Tidak lolos';
        }

        if (str_contains($lower, 'lolos')) {
            return 'Lolos ke babak berikutnya';
        }

        return $rawHasil;
    }
}
