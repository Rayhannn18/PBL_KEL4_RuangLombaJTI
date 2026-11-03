<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AnggotaTim;
use App\Models\Dosen;
use App\Models\Lomba;
use App\Models\Mahasiswa;
use App\Models\PengajuanBimbingan;
use App\Models\ProgresBabak;
use App\Models\Tim;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DosenDetailTimTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        Storage::fake('public');
    }

    private function actingAsDosen(Dosen $d): User
    {
        $user = User::firstOrCreate(
            ['email' => $d->email_kampus],
            [
                'name' => $d->nama,
                'nim_nip' => $d->nidn,
                'role' => UserRole::Dosen,
                'password' => Hash::make('password123'),
            ]
        );

        $this->actingAs($user);
        session([
            'auth_user' => [
                'id' => $d->nidn,
                'nama' => $d->nama,
                'email' => $d->email_kampus,
                'role' => 'dosen',
                'extra' => $d->bidang_keahlian,
            ],
        ]);

        return $user;
    }

    private function createTimWithMembers(Dosen $dosen, array $overrides = []): PengajuanBimbingan
    {
        $ketua = Mahasiswa::first();
        $anggotaMhs = Mahasiswa::where('nim', '!=', $ketua->nim)->first();

        $lomba = Lomba::create([
            'nama_lomba' => 'Gemastik UX Design 2026',
            'kategori' => 'UI/UX Design',
            'tingkat' => 'Nasional',
            'penyelenggara' => 'Kemendikbudristek',
            'tenggat' => now()->addDays(30),
            'tautan_daftar' => 'https://gemastik.kemdikbud.go.id',
            'status_verifikasi' => 'disetujui',
        ]);

        $tim = Tim::create([
            'nama_tim' => 'Tim UIUX Inovatif',
            'id_lomba' => $lomba->id_lomba,
            'nim' => $ketua->nim,
            'kuota_anggota' => 3,
            'status' => 'disetujui',
            'file_cv' => 'cv/ketua_test.pdf',
        ]);

        // Simpan file dummy CV ketua di storage
        Storage::disk('public')->put('cv/ketua_test.pdf', '%PDF-1.4 dummy pdf ketua');

        // Buat anggota tim diterima dengan CV
        if ($anggotaMhs) {
            Storage::disk('public')->put('cv/anggota_test.pdf', '%PDF-1.4 dummy pdf anggota');
            AnggotaTim::create([
                'id_tim' => $tim->id_tim,
                'nim' => $anggotaMhs->nim,
                'status_gabung' => 'diterima',
                'peran' => 'UI Designer',
                'file_cv' => 'cv/anggota_test.pdf',
            ]);
        }

        $pengajuan = PengajuanBimbingan::create([
            'id_tim' => $tim->id_tim,
            'nidn' => $dosen->nidn,
            'tgl_pengajuan' => now(),
            'status' => 'disetujui',
            'catatan' => 'Fokus pengembangan mobile health app',
        ]);

        // Buat progres babak
        ProgresBabak::create([
            'id_pengajuan' => $pengajuan->id_pengajuan,
            'babak' => 'Babak Penyisihan',
            'tanggal_update' => now()->subDays(5),
            'hasil_akhir' => 'Lolos Tahap 1',
            'status_setuju' => 'disetujui',
        ]);

        ProgresBabak::create([
            'id_pengajuan' => $pengajuan->id_pengajuan,
            'babak' => 'Babak Semifinal',
            'tanggal_update' => now(),
            'hasil_akhir' => 'Juara Harapan',
            'status_setuju' => 'disetujui',
        ]);

        return $pengajuan;
    }

    /**
     * Dosen dapat melihat detail info lomba tim pada kartu bimbingan
     */
    public function test_kartu_bimbingan_dosen_menampilkan_info_lengkap_lomba(): void
    {
        $dosen = Dosen::first();
        $this->actingAsDosen($dosen);
        $this->createTimWithMembers($dosen);

        $response = $this->get(route('bimbingan.index'));

        $response->assertStatus(200);
        $response->assertSee('Tim UIUX Inovatif');
        $response->assertSee('Gemastik UX Design 2026');
        $response->assertSee('UI/UX Design');
        $response->assertSee('Nasional');
        $response->assertSee('Tenggat:');
        $response->assertSee('Fokus pengembangan mobile health app');
    }

    /**
     * Dosen dapat melihat susunan ketua dan anggota tim yang diterima beserta prodi
     */
    public function test_kartu_bimbingan_dosen_menampilkan_susunan_anggota_tim_dan_prodi(): void
    {
        $dosen = Dosen::first();
        $this->actingAsDosen($dosen);
        $pengajuan = $this->createTimWithMembers($dosen);

        $ketua = $pengajuan->tim->ketua;
        $anggota = $pengajuan->tim->anggotaDiterima->first();

        $response = $this->get(route('bimbingan.index'));

        $response->assertStatus(200);
        $response->assertSee($ketua->nama);
        $response->assertSee($ketua->nim);
        $response->assertSee($ketua->prodi);
        $response->assertSee('Ketua Tim');

        if ($anggota) {
            $response->assertSee($anggota->mahasiswa->nama);
            $response->assertSee($anggota->nim);
            $response->assertSee($anggota->mahasiswa->prodi);
            $response->assertSee('UI Designer');
        }
    }

    /**
     * Dosen pembimbing dapat mengunduh CV Ketua dan Anggota Tim
     */
    public function test_dosen_dapat_mengunduh_cv_ketua_dan_anggota_tim(): void
    {
        $dosen = Dosen::first();
        $this->actingAsDosen($dosen);
        $pengajuan = $this->createTimWithMembers($dosen);

        $tim = $pengajuan->tim;
        $anggota = $tim->anggotaDiterima->first();

        // Cek link muncul di halaman
        $response = $this->get(route('bimbingan.index'));
        $response->assertSee(route('tim.cv', ['type' => 'ketua', 'id' => $tim->id_tim]));
        if ($anggota) {
            $response->assertSee(route('tim.cv', ['type' => 'anggota', 'id' => $anggota->id_anggota]));
        }

        // Test download CV Ketua
        $resKetua = $this->get(route('tim.cv', ['type' => 'ketua', 'id' => $tim->id_tim]));
        $resKetua->assertStatus(200);

        // Test download CV Anggota
        if ($anggota) {
            $resAnggota = $this->get(route('tim.cv', ['type' => 'anggota', 'id' => $anggota->id_anggota]));
            $resAnggota->assertStatus(200);
        }
    }

    /**
     * Dosen dapat melihat progres babak terakhir tim bimbingan
     */
    public function test_kartu_bimbingan_dosen_menampilkan_progres_babak_terakhir(): void
    {
        $dosen = Dosen::first();
        $this->actingAsDosen($dosen);
        $this->createTimWithMembers($dosen);

        $response = $this->get(route('bimbingan.index'));

        $response->assertStatus(200);
        $response->assertSee('Babak Semifinal');
        $response->assertSee('Juara Harapan');
        $response->assertSee('Disetujui Dosen');
    }

    /**
     * Memastikan query database eager loaded dan bebas dari masalah N+1
     */
    public function test_halaman_bimbingan_dosen_bebas_dari_masalah_n_plus_one_query(): void
    {
        $dosen = Dosen::first();
        $this->actingAsDosen($dosen);

        // 1 Tim
        $this->createTimWithMembers($dosen);

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get(route('bimbingan.index'));
        $queryCount1 = count(DB::getQueryLog());
        DB::disableQueryLog();

        // Tambah 3 Tim lagi
        for ($i = 0; $i < 3; $i++) {
            $ketua = Mahasiswa::create([
                'nim' => '244107000'.$i,
                'nama' => 'Mhs Ketua '.$i,
                'email_kampus' => 'mhs'.$i.'@polinema.ac.id',
                'password' => Hash::make('password123'),
                'prodi' => 'D-IV Sistem Informasi Bisnis',
                'angkatan' => 2024,
            ]);

            $lomba = Lomba::create([
                'nama_lomba' => 'Kompetisi '.$i,
                'kategori' => 'Kategori '.$i,
                'tingkat' => 'Nasional',
                'penyelenggara' => 'Polinema',
                'tenggat' => now()->addDays(20),
                'tautan_daftar' => 'https://lomba.example.com/'.$i,
                'status_verifikasi' => 'disetujui',
            ]);

            $tim = Tim::create([
                'nama_tim' => 'Tim Tambahan '.$i,
                'id_lomba' => $lomba->id_lomba,
                'nim' => $ketua->nim,
                'kuota_anggota' => 3,
                'status' => 'disetujui',
            ]);

            PengajuanBimbingan::create([
                'id_tim' => $tim->id_tim,
                'nidn' => $dosen->nidn,
                'tgl_pengajuan' => now(),
                'status' => 'disetujui',
            ]);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get(route('bimbingan.index'));
        $queryCount2 = count(DB::getQueryLog());
        DB::disableQueryLog();

        // Jumlah query tidak boleh meledak secara linier dengan N (N+1 query)
        // Karena seluruh relasi tim, lomba, ketua, anggotaDiterima, mahasiswa, logbook, progresBabak
        // sudah di-eager-load secara batch.
        $this->assertSame($queryCount1, $queryCount2, "Query bertambah dari {$queryCount1} menjadi {$queryCount2}, terdeteksi potensi query N+1.");
    }
}
