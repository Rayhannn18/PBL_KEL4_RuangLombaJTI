<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Dosen;
use App\Models\Logbook;
use App\Models\Lomba;
use App\Models\Mahasiswa;
use App\Models\PengajuanBimbingan;
use App\Models\Tim;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DosenRekapRiwayatTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
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

    private function createTeamWithStatus(Dosen $dosen, string $status, string $namaTim, ?Lomba $lomba = null): PengajuanBimbingan
    {
        $ketua = Mahasiswa::create([
            'nim' => '244108'.str_pad((string) mt_rand(1, 999999), 6, '0', STR_PAD_LEFT),
            'nama' => 'Mhs '.$namaTim,
            'email_kampus' => 'mhs'.uniqid().'@polinema.ac.id',
            'password' => Hash::make('password123'),
            'prodi' => 'D-IV Sistem Informasi Bisnis',
            'angkatan' => 2024,
        ]);

        if (! $lomba) {
            $lomba = Lomba::first();
        }

        $tim = Tim::create([
            'nama_tim' => $namaTim,
            'id_lomba' => $lomba->id_lomba,
            'nim' => $ketua->nim,
            'kuota_anggota' => 3,
            'status' => 'disetujui',
        ]);

        return PengajuanBimbingan::create([
            'id_tim' => $tim->id_tim,
            'nidn' => $dosen->nidn,
            'tgl_pengajuan' => now(),
            'status' => $status,
            'catatan' => $status === 'ditolak' ? 'Topik tidak sesuai' : null,
        ]);
    }

    /**
     * Halaman rekap dosen menampilkan ringkasan statistik lengkap
     */
    public function test_halaman_rekap_dosen_menampilkan_ringkasan_statistik_lengkap(): void
    {
        $dosen = Dosen::first();
        $this->actingAsDosen($dosen);

        $p1 = $this->createTeamWithStatus($dosen, 'disetujui', 'Tim Aktif 1');
        $this->createTeamWithStatus($dosen, 'menunggu', 'Tim Menunggu 1');
        $this->createTeamWithStatus($dosen, 'ditolak', 'Tim Ditolak 1');

        Logbook::create([
            'id_pengajuan' => $p1->id_pengajuan,
            'tanggal' => now(),
            'materi' => 'Bimbingan perdana',
            'status_validasi' => 'menunggu',
        ]);

        $response = $this->get(route('bimbingan.index'));

        $response->assertStatus(200);
        $response->assertSee('Total Kuota');
        $response->assertSee('Bimbingan Aktif');
        $response->assertSee('Pengajuan Masuk');
        $response->assertSee('Total Sesi Logbook');
        $response->assertSee('Logbook Menunggu');
        $response->assertSee('Riwayat Ditolak');
    }

    /**
     * Dosen dapat mencari tim bimbingan berdasarkan nama tim
     */
    public function test_dosen_dapat_mencari_tim_berdasarkan_nama_tim(): void
    {
        $dosen = Dosen::first();
        $this->actingAsDosen($dosen);

        $this->createTeamWithStatus($dosen, 'disetujui', 'Tim Cyber Hunter 99');
        $this->createTeamWithStatus($dosen, 'disetujui', 'Tim IoT Smart Garden');

        $response = $this->get(route('bimbingan.index', ['search' => 'Cyber Hunter']));

        $response->assertStatus(200);
        $response->assertSee('Tim Cyber Hunter 99');
        $response->assertDontSee('Tim IoT Smart Garden');
    }

    /**
     * Dosen dapat memfilter bimbingan berdasarkan status
     */
    public function test_dosen_dapat_memfilter_berdasarkan_status_pengajuan(): void
    {
        $dosen = Dosen::first();
        $this->actingAsDosen($dosen);

        $this->createTeamWithStatus($dosen, 'disetujui', 'Tim Khusus Disetujui');
        $this->createTeamWithStatus($dosen, 'ditolak', 'Tim Khusus Ditolak');

        // Filter status ditolak
        $resDitolak = $this->get(route('bimbingan.index', ['status' => 'ditolak']));
        $resDitolak->assertStatus(200);
        $resDitolak->assertSee('Tim Khusus Ditolak');
        $resDitolak->assertDontSee('Tim Khusus Disetujui');

        // Filter status disetujui
        $resSetuju = $this->get(route('bimbingan.index', ['status' => 'disetujui']));
        $resSetuju->assertStatus(200);
        $resSetuju->assertSee('Tim Khusus Disetujui');
        $resSetuju->assertDontSee('Tim Khusus Ditolak');
    }

    /**
     * Dosen dapat memfilter bimbingan berdasarkan lomba
     */
    public function test_dosen_dapat_memfilter_berdasarkan_kompetisi_lomba(): void
    {
        $dosen = Dosen::first();
        $this->actingAsDosen($dosen);

        $lombaA = Lomba::create([
            'nama_lomba' => 'Kompetisi Robotika Nasional 2026',
            'kategori' => 'Robotika',
            'tingkat' => 'Nasional',
            'penyelenggara' => 'Polinema',
            'tenggat' => now()->addDays(30),
            'tautan_daftar' => 'https://lomba.example.com/robotika',
            'status_verifikasi' => 'disetujui',
        ]);

        $lombaB = Lomba::create([
            'nama_lomba' => 'Kompetisi Data Science 2026',
            'kategori' => 'Data Science',
            'tingkat' => 'Nasional',
            'penyelenggara' => 'Polinema',
            'tenggat' => now()->addDays(30),
            'tautan_daftar' => 'https://lomba.example.com/datascience',
            'status_verifikasi' => 'disetujui',
        ]);

        $this->createTeamWithStatus($dosen, 'disetujui', 'Tim Robotika Hebat', $lombaA);
        $this->createTeamWithStatus($dosen, 'disetujui', 'Tim Data Hebat', $lombaB);

        $response = $this->get(route('bimbingan.index', ['lomba_id' => $lombaA->id_lomba]));

        $response->assertStatus(200);
        $response->assertSee('Tim Robotika Hebat');
        $response->assertDontSee('Tim Data Hebat');
    }

    /**
     * Halaman rekap dosen mendukung paginasi data jika jumlah pengajuan lebih dari 10
     */
    public function test_halaman_rekap_dosen_mendukung_paginasi(): void
    {
        $dosen = Dosen::first();
        $this->actingAsDosen($dosen);

        for ($i = 1; $i <= 12; $i++) {
            $this->createTeamWithStatus($dosen, 'disetujui', 'Tim Paginasi Ke '.$i);
        }

        // Halaman 1
        $res1 = $this->get(route('bimbingan.index'));
        $res1->assertStatus(200);

        // Halaman 2
        $res2 = $this->get(route('bimbingan.index', ['page' => 2]));
        $res2->assertStatus(200);
    }
}
