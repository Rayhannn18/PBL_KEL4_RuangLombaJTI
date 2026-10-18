<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\AnggotaTim;
use App\Models\Dosen;
use App\Models\Lomba;
use App\Models\LombaDivisi;
use App\Models\LombaJadwal;
use App\Models\LombaPersyaratan;
use App\Models\Mahasiswa;
use App\Models\PengajuanBimbingan;
use App\Models\Tim;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GemastikSeeder extends Seeder
{

    public function run(): void
    {
        $admin = Admin::first();

        $mahasiswa = Mahasiswa::where('nim', '244107060147')->first();
        if (!$mahasiswa) {
            $mahasiswa = Mahasiswa::create([
                'nim'          => '244107060147',
                'nama'         => 'Nadya Syantika',
                'email_kampus' => 'nadya.syantika@polinema.ac.id',
                'password'     => Hash::make('password123'),
                'prodi'        => 'D-IV Sistem Informasi Bisnis',
                'angkatan'     => 2022,
            ]);
        }

        $lomba = Lomba::updateOrCreate(
            ['nama_lomba' => 'GEMASTIK XIX 2026'],
            [
                'kategori'          => 'Teknologi Informasi & Komunikasi',
                'tingkat'           => 'Nasional',
                'penyelenggara'     => 'Kemdikbudristek RI (Puspresnas)',
                'deskripsi'         => 'Pagelaran Mahasiswa Nasional Bidang Teknologi Informasi dan Komunikasi (GEMASTIK) adalah ajang kompetisi tahunan tingkat nasional bagi mahasiswa perguruan tinggi di Indonesia pada berbagai divisi bidang TIK.',
                'persyaratan'       => 'Mahasiswa aktif jenjang D3/D4/S1 terdaftar di PDDikti.',
                'tenggat'           => Carbon::create(2026, 6, 30),
                'biaya'             => 0,
                'benefit'           => 'Medali Puspresnas, Sertifikat Nasional, Uang Pembinaan.',
                'tautan_daftar'     => 'https://gemastik.kemdikbud.go.id',
                'status_verifikasi' => 'terverifikasi',
                'poster'            => null,
                'path_pedoman'      => 'pedoman/gemastik-2026.pdf',
                'nim'               => $mahasiswa->nim,
                'id_admin'          => $admin ? $admin->id_admin : null,
            ]
        );

        $divisiList = [
            ['nama_divisi' => 'Pengembangan Perangkat Lunak', 'kode_icon' => 'laptop'],
            ['nama_divisi' => 'Data Mining',                  'kode_icon' => 'chart'],
            ['nama_divisi' => 'Keamanan Siber',              'kode_icon' => 'shield'],
            ['nama_divisi' => 'UX Design',                   'kode_icon' => 'palette'],
            ['nama_divisi' => 'Smart City',                  'kode_icon' => 'city'],
            ['nama_divisi' => 'Game Development',            'kode_icon' => 'game'],
        ];

        foreach ($divisiList as $item) {
            LombaDivisi::updateOrCreate(
                [
                    'id_lomba'    => $lomba->id_lomba,
                    'nama_divisi' => $item['nama_divisi'],
                ],
                [
                    'kode_icon'   => $item['kode_icon'],
                ]
            );
        }

        $persyaratanList = [
            'Mahasiswa aktif jenjang D3/D4/S1 terdaftar di PDDikti.',
            'Anggota tim berasal dari satu perguruan tinggi sesuai ketentuan divisi.',
            'Didampingi satu dosen pembimbing.',
            'Karya orisinal dan belum pernah dipublikasikan di lomba lain.',
            'Melampirkan surat keterangan mahasiswa aktif dan kartu identitas.',
        ];

        LombaPersyaratan::where('id_lomba', $lomba->id_lomba)->delete();
        foreach ($persyaratanList as $index => $text) {
            LombaPersyaratan::create([
                'id_lomba'    => $lomba->id_lomba,
                'persyaratan' => $text,
                'urutan'      => $index + 1,
            ]);
        }

        // Jadwal Tahapan Lomba (Dihitung dinamis relatif terhadap waktu berjalan)
        // Tahap 1 diatur mencakup hari ini agar status dinamisnya otomatis 'sedang berjalan'
        $today = Carbon::today();
        $jadwalList = [
            [
                'nama_tahap'      => 'Pendaftaran & unggah berkas',
                'tanggal_mulai'   => $today->copy()->subDays(10),
                'tanggal_selesai' => $today->copy()->addDays(20),
                'urutan'          => 1,
            ],
            [
                'nama_tahap'      => 'Pengumpulan karya babak penyisihan',
                'tanggal_mulai'   => $today->copy()->addDays(21),
                'tanggal_selesai' => $today->copy()->addDays(50),
                'urutan'          => 2,
            ],
            [
                'nama_tahap'      => 'Pengumuman finalis',
                'tanggal_mulai'   => $today->copy()->addDays(51),
                'tanggal_selesai' => $today->copy()->addDays(75),
                'urutan'          => 3,
            ],
            [
                'nama_tahap'      => 'Final & penjurian',
                'tanggal_mulai'   => $today->copy()->addDays(76),
                'tanggal_selesai' => $today->copy()->addDays(100),
                'urutan'          => 4,
            ],
        ];

        LombaJadwal::where('id_lomba', $lomba->id_lomba)->delete();
        foreach ($jadwalList as $j) {
            LombaJadwal::create([
                'id_lomba'        => $lomba->id_lomba,
                'nama_tahap'      => $j['nama_tahap'],
                'tanggal_mulai'   => $j['tanggal_mulai'],
                'tanggal_selesai' => $j['tanggal_selesai'],
                'urutan'          => $j['urutan'],
            ]);
        }

        $divisiPPL = LombaDivisi::where('id_lomba', $lomba->id_lomba)
            ->where('nama_divisi', 'Pengembangan Perangkat Lunak')
            ->first();

        $tim = Tim::updateOrCreate(
            [
                'id_lomba' => $lomba->id_lomba,
                'nim'      => $mahasiswa->nim,
            ],
            [
                'nama_tim'      => 'Tim Gemastik Polinema',
                'kuota_anggota' => 3,
                'status_tim'    => 'disetujui',
                'id_admin'      => $admin ? $admin->id_admin : null,
                'id_divisi'     => $divisiPPL ? $divisiPPL->id : null,
            ]
        );

        $dosen = Dosen::first();
        if ($dosen) {
            PengajuanBimbingan::updateOrCreate(
                ['id_tim' => $tim->id_tim],
                [
                    'nidn'          => $dosen->nidn,
                    'tgl_pengajuan' => Carbon::now()->subDays(5),
                    'status'        => 'disetujui',
                    'catatan'       => 'Bimbingan GEMASTIK XIX 2026 disetujui.',
                ]
            );
        }

        AnggotaTim::updateOrCreate(
            ['id_tim' => $tim->id_tim, 'nim' => $mahasiswa->nim],
            ['status_gabung' => 'diterima', 'peran' => 'Ketua Tim']
        );

        $otherMahasiswa = Mahasiswa::where('nim', '!=', $mahasiswa->nim)->take(2)->get();
        foreach ($otherMahasiswa as $idx => $other) {
            AnggotaTim::updateOrCreate(
                ['id_tim' => $tim->id_tim, 'nim' => $other->nim],
                ['status_gabung' => 'diterima', 'peran' => $idx === 0 ? 'Frontend Developer' : 'UI/UX Designer']
            );
        }
    }
}