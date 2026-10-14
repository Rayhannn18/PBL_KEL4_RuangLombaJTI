<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\Lomba;
use App\Models\Mahasiswa;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BimbinganTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Guest diarahkan ke login jika belum masuk
     */
    public function test_guest_redirected_to_login(): void
    {
        $response = $this->get('/bimbingan');
        $response->assertRedirect('/login');
    }

    /**
     * Mahasiswa yang belum ikut lomba melihat halaman peringatan/prasyarat
     */
    public function test_mahasiswa_tanpa_tim_melihat_prasyarat_belum_ikut_lomba(): void
    {
        // Mahasiswa baru tanpa tim
        $mahasiswaBaru = Mahasiswa::create([
            'nim' => '244107069999',
            'nama' => 'Mahasiswa Baru',
            'email_kampus' => 'baru@student.polinema.ac.id',
            'password' => Hash::make('password123'),
            'prodi' => 'D-IV Sistem Informasi Bisnis',
            'angkatan' => 2024,
        ]);

        $response = $this->withSession([
            'auth_user' => [
                'id' => $mahasiswaBaru->nim,
                'nama' => $mahasiswaBaru->nama,
                'email' => $mahasiswaBaru->email_kampus,
                'role' => 'mahasiswa',
                'extra' => 'D-IV Sistem Informasi Bisnis (2024)',
            ],
        ])->get('/bimbingan');

        $response->assertStatus(200);
        $response->assertSee('Anda Belum Terdaftar dalam Tim Lomba');
        $response->assertSee('Daftarkan Tim Lomba Sekarang');
        $response->assertSee('Lomba Terbuka yang Siap Diikuti');
    }

    /**
     * Mahasiswa yang sudah ikut lomba melihat data tim dan bimbingan
     */
    public function test_mahasiswa_dengan_tim_melihat_halaman_bimbingan_aktif(): void
    {
        // Mahasiswa yang punya tim dari seeder (misal Sastra)
        $mahasiswa = Mahasiswa::where('nim', '244107060116')->first();

        $response = $this->withSession([
            'auth_user' => [
                'id' => $mahasiswa->nim,
                'nama' => $mahasiswa->nama,
                'email' => $mahasiswa->email_kampus,
                'role' => 'mahasiswa',
                'extra' => 'D-IV Sistem Informasi Bisnis (2024)',
            ],
        ])->get('/bimbingan');

        $response->assertStatus(200);
        $response->assertSee('Identitas Tim');
        $response->assertSee('Dosen Pembimbing');
        $response->assertSee('Logbook Aktivitas Bimbingan');
    }

    /**
     * Dosen melihat portal pembimbingan
     */
    public function test_dosen_melihat_portal_bimbingan(): void
    {
        $dosen = Dosen::first();

        $response = $this->withSession([
            'auth_user' => [
                'id' => $dosen->nidn,
                'nama' => $dosen->nama,
                'email' => $dosen->email_kampus,
                'role' => 'dosen',
                'extra' => $dosen->bidang_keahlian,
            ],
        ])->get('/bimbingan');

        $response->assertStatus(200);
        $response->assertSee('Daftar Tim Mahasiswa Bimbingan');
        $response->assertSee('Total Kuota Bimbingan');
    }

    /**
     * Mahasiswa bisa mendaftarkan tim cepat lalu halaman bimbingan aktif
     */
    public function test_mahasiswa_bisa_daftarkan_tim_cepat(): void
    {
        $mahasiswaBaru = Mahasiswa::create([
            'nim' => '244107068888',
            'nama' => 'Mahasiswa Test Cepat',
            'email_kampus' => 'testcepat@student.polinema.ac.id',
            'password' => Hash::make('password123'),
            'prodi' => 'D-IV Teknik Informatika',
            'angkatan' => 2024,
        ]);

        $lomba = Lomba::first();

        $response = $this->withSession([
            'auth_user' => [
                'id' => $mahasiswaBaru->nim,
                'nama' => $mahasiswaBaru->nama,
                'email' => $mahasiswaBaru->email_kampus,
                'role' => 'mahasiswa',
                'extra' => 'D-IV Teknik Informatika (2024)',
            ],
        ])->post('/bimbingan/daftar-tim-cepat', [
            'id_lomba' => $lomba->id_lomba,
            'nama_tim' => 'Tim Cepat Berjaya',
            'kuota_anggota' => 3,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tim', [
            'nama_tim' => 'Tim Cepat Berjaya',
            'nim' => $mahasiswaBaru->nim,
        ]);
    }
}
