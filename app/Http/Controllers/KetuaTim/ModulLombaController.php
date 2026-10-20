<?php

namespace App\Http\Controllers\KetuaTim;

use App\Http\Controllers\Controller;
use App\Models\LombaDivisi;
use App\Models\LombaJadwal;
use App\Models\LombaPersyaratan;
use App\Models\Mahasiswa;
use App\Models\Tim;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ModulLombaController extends Controller
{

    public function index(Request $request)
    {

        $nim = session('auth_user.id');

        if (! $nim) {
            return redirect()->route('login')->with('error', 'Silakan masuk terlebih dahulu untuk mengakses Modul Lomba.');
        }

        $mahasiswa = Mahasiswa::where('nim', $nim)->first();
        if (! $mahasiswa) {
            return redirect()->route('login')->with('error', 'Akun mahasiswa tidak ditemukan dalam basis data.');
        }

        $currentYear = (int) date('Y');
        $currentMonth = (int) date('n');
        $tingkat = ($currentMonth >= 8)
            ? ($currentYear - (int) $mahasiswa->angkatan + 1)
            : ($currentYear - (int) $mahasiswa->angkatan);
        $tingkat = max(1, min(4, $tingkat)); // Batasi maksimal tingkat 4 (D-IV)

        $akun = [
            'nama'            => $mahasiswa->nama,
            'nim'             => $mahasiswa->nim,
            'prodi'           => $mahasiswa->prodi,
            'tingkat'         => $tingkat,
            'angkatan'        => $mahasiswa->angkatan,
            'status_siakad'   => 'SIAKAD Aktif',
            'jenis_mahasiswa' => 'Ketua Tim',
        ];

        $tim = Tim::with([
            'lomba',
            'anggotaDiterima',
            'bimbinganDisetujui.dosen',
        ])->where('nim', $mahasiswa->nim)->latest()->first();

        if (! $tim || ! $tim->lomba) {
            return view('ketua-tim.modul-lomba', [
                'akun'    => $akun,
                'tim'     => $tim,
                'lomba'   => null,
                'isEmpty' => true,
            ]);
        }

        $lombaModel = $tim->lomba;

        $divisiList = collect();
        if (Schema::hasTable('lomba_divisi')) {
            try {
                $divisiList = LombaDivisi::where('id_lomba', $lombaModel->id_lomba)->get();
            } catch (\Throwable $e) {
                $divisiList = collect();
            }
        }

        $persyaratanList = collect();
        if (Schema::hasTable('lomba_persyaratan')) {
            try {
                $persyaratanList = LombaPersyaratan::where('id_lomba', $lombaModel->id_lomba)->orderBy('urutan')->get();
            } catch (\Throwable $e) {
                $persyaratanList = collect();
            }
        }

        $jadwalList = collect();
        if (Schema::hasTable('lomba_jadwal')) {
            try {
                $jadwalList = LombaJadwal::where('id_lomba', $lombaModel->id_lomba)->orderBy('urutan')->get();
            } catch (\Throwable $e) {
                $jadwalList = collect();
            }
        }

        $splitLines = fn ($text) => array_values(array_filter(array_map(
            fn ($l) => trim(preg_replace('/^\d+[\.\)]\s*/', '', $l)),
            preg_split('/\\\\n|\r\n|\r|\n/', trim((string) $text))
        )));

        if ($persyaratanList->isNotEmpty()) {
            $persyaratanData = $persyaratanList
                ->flatMap(fn ($p) => $splitLines($p->persyaratan))
                ->values()->all();
        } elseif (! empty($lombaModel->persyaratan)) {
            $persyaratanData = $splitLines($lombaModel->persyaratan);
        } else {
            $persyaratanData = [
                'Mahasiswa aktif terdaftar di PDDikti dan memiliki akun SIAKAD aktif.',
                'Peserta mendaftar sebagai tim sesuai ketentuan jumlah anggota pada buku pedoman.',
                'Karya orisinal dan belum pernah dipublikasikan atau dilombakan.',
                'Melampirkan identitas dan surat keterangan mahasiswa aktif.',
                'Peserta menyiapkan perangkat dan koneksi untuk pelaksanaan daring.',
            ];
        }

        $divisiTerpilihNama = 'Pengembangan Perangkat Lunak';
        if (isset($tim->id_divisi) && $tim->id_divisi) {
            $divisiModel = $divisiList->firstWhere('id', $tim->id_divisi);
            if ($divisiModel) {
                $divisiTerpilihNama = $divisiModel->nama_divisi;
            }
        } elseif ($divisiList->isNotEmpty()) {
            $divisiTerpilihNama = $divisiList->first()->nama_divisi;
        }

        $divisiData = [];
        if ($divisiList->isNotEmpty()) {
            foreach ($divisiList as $d) {
                $divisiData[] = [
                    'id'          => $d->kode_icon ?: 'ppl',
                    'nama'        => $d->nama_divisi,
                    'is_selected' => ($d->nama_divisi === $divisiTerpilihNama),
                ];
            }
        } else {
 
            $divisiData = [
                ['id' => 'laptop',  'nama' => 'Pengembangan Perangkat Lunak', 'is_selected' => true],
                ['id' => 'chart',   'nama' => 'Data Mining',                  'is_selected' => false],
                ['id' => 'shield',  'nama' => 'Keamanan Siber',              'is_selected' => false],
                ['id' => 'palette', 'nama' => 'UX Design',                   'is_selected' => false],
                ['id' => 'city',    'nama' => 'Smart City',                  'is_selected' => false],
                ['id' => 'game',    'nama' => 'Game Development',            'is_selected' => false],
            ];
        }

        $jadwalData = [];
        $tahapNomor = 1;
        $tahapSaatIni = 'Pendaftaran';

        if ($jadwalList->isNotEmpty()) {
            foreach ($jadwalList as $j) {
                $jadwalData[] = [
                    'nomor'  => $j->urutan,
                    'tahap'  => $j->nama_tahap,
                    'waktu'  => $j->waktu_formatted,
                    'status' => $j->status_label,
                    'theme'  => match ($j->status) {
                        'sedang_berjalan' => 'amber',
                        'selesai'         => 'green',
                        default           => 'blue',
                    },
                ];
            }

            $tahapBerjalan = $jadwalList->first(fn ($item) => $item->status === 'sedang_berjalan');
            if ($tahapBerjalan) {
                $tahapNomor = $tahapBerjalan->urutan;
                $tahapSaatIni = $tahapBerjalan->nama_tahap;
            } else {
                $tahapAkanDatang = $jadwalList->first(fn ($item) => $item->status === 'akan_datang');
                if ($tahapAkanDatang) {
                    $tahapNomor = max(1, $tahapAkanDatang->urutan);
                    $tahapSaatIni = $tahapAkanDatang->nama_tahap;
                } else {
                    $tahapNomor = $jadwalList->count();
                    $tahapSaatIni = $jadwalList->last()->nama_tahap;
                }
            }
            $tahapTotal = $jadwalList->count();
        } else {

            $tahapTotal = 4;
            $jadwalData = [
                ['nomor' => 1, 'tahap' => 'Pendaftaran & unggah berkas',        'waktu' => '1 – 30 Juni 2026', 'status' => 'sedang berjalan', 'theme' => 'amber'],
                ['nomor' => 2, 'tahap' => 'Pengumpulan karya babak penyisihan', 'waktu' => 'Juli 2026',        'status' => null,              'theme' => 'blue'],
                ['nomor' => 3, 'tahap' => 'Pengumuman finalis',                 'waktu' => 'Agustus 2026',     'status' => null,              'theme' => 'blue'],
                ['nomor' => 4, 'tahap' => 'Final & penjurian',                  'waktu' => 'Oktober 2026',     'status' => null,              'theme' => 'green'],
            ];
        }

        $totalAnggota = $tim->anggotaDiterima->count();
        if (! $tim->anggotaDiterima->contains('nim', $mahasiswa->nim)) {
            $totalAnggota += 1;
        }
        $kuotaAnggotaStr = "{$totalAnggota} / {$tim->kuota_anggota}";

        $dosenStr = ($tim->bimbinganDisetujui && $tim->bimbinganDisetujui->dosen)
            ? '1 Dosen'
            : 'Belum Ada';

        $pengusul = 'Admin JTI';
        if ($lombaModel->nim) {
            $pengusul = 'Mahasiswa';
        }

        $statusVerifikasi = ($lombaModel->status_verifikasi === 'terverifikasi')
            ? 'Terverifikasi Admin'
            : ucfirst($lombaModel->status_verifikasi);

        $pathPedoman = $lombaModel->path_pedoman ?? 'pedoman/gemastik-2026.pdf';

        $biayaRaw  = $lombaModel->biaya_pendaftaran ?? 0;
        $biayaText = ((int) $biayaRaw) > 0 ? 'Rp ' . number_format($biayaRaw, 0, ',', '.') : 'Gratis';

        $batas = ! empty($lombaModel->batas_pendaftaran)
            ? Carbon::parse($lombaModel->batas_pendaftaran)->startOfDay()
            : null;

        $statusWaktu = ['label' => '-', 'theme' => 'gray'];
        if ($batas) {
            $sisa = (int) Carbon::today()->diffInDays($batas, false);
            if ($sisa < 0)       { $statusWaktu = ['label' => 'Tutup', 'theme' => 'gray']; }
            elseif ($sisa === 0) { $statusWaktu = ['label' => 'Berakhir Hari Ini', 'theme' => 'red']; }
            elseif ($sisa <= 7)  { $statusWaktu = ['label' => "Sisa {$sisa} Hari", 'theme' => 'red']; }
            else                 { $statusWaktu = ['label' => 'Buka', 'theme' => 'green']; }
        }

        $lomba = [
            'nama'                => $lombaModel->nama_lomba,
            'badge_role'          => 'Modul Lomba • Ketua Tim',
            'deskripsi_hero'      => 'Pagelaran Mahasiswa Nasional Bidang TIK. Semua informasi lomba tim Anda, dari persyaratan sampai buku pedoman, ada di satu halaman.',
            'tingkat'             => $lombaModel->tingkat,
            'status_verifikasi'   => $statusVerifikasi,
            'penyelenggara'       => $lombaModel->penyelenggara,
            'kategori'            => $lombaModel->kategori,
            'biaya'               => $biayaText,
            'tipe_peserta'        => $lombaModel->tipe_peserta ?? ($tim->kuota_anggota > 1 ? 'Kelompok' : 'Individu'),
            'mode'                => $lombaModel->mode_pelaksanaan ?? 'Online',
            'batas_tenggat'       => $batas ? $batas->format('d M Y') : '-',
            'status_waktu'        => $statusWaktu,
            'ukuran_tim'          => $tim->kuota_anggota . ' Anggota (termasuk ketua)',
            'pengusul'            => $pengusul,
            'deskripsi_lengkap'   => $lombaModel->deskripsi ?: 'Pagelaran Mahasiswa Nasional Bidang Teknologi Informasi dan Komunikasi (GEMASTIK) adalah ajang kompetisi tahunan tingkat nasional bagi mahasiswa perguruan tinggi di Indonesia pada berbagai divisi bidang TIK.',
            'tahap_saat_ini'      => $tahapSaatIni,
            'tahap_nomor'         => $tahapNomor,
            'tahap_total'         => $tahapTotal,
            'kuota_anggota'       => $kuotaAnggotaStr,
            'dosen_pembimbing'    => $dosenStr,
            'divisi_terpilih'     => $divisiTerpilihNama,
            'divisi'              => $divisiData,
            'persyaratan'         => $persyaratanData,
            'jadwal'              => $jadwalData,
            'url_pendaftaran'     => $lombaModel->tautan_daftar ?: 'https://gemastik.kemdikbud.go.id',
            'panduan_pendaftaran' => [
                'Buat akun tim di portal resmi.',
                'Isi data anggota & pilih divisi.',
                'Unggah berkas persyaratan lalu kirim.',
            ],
            'pedoman'             => [
                'judul'     => 'Buku Pedoman ' . $lombaModel->nama_lomba,
                'deskripsi' => 'Berisi ketentuan umum, aturan tiap divisi, kriteria penilaian, dan format berkas. Diunggah oleh admin JTI.',
                'path_pdf'  => $pathPedoman,
            ],
        ];

        return view('ketua-tim.modul-lomba', [
            'akun'    => $akun,
            'tim'     => $tim,
            'lomba'   => $lomba,
            'isEmpty' => false,
        ]);
    }
}