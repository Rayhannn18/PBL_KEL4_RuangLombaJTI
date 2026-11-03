<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AnggotaTim;
use App\Models\Dosen;
use App\Models\Lomba;
use App\Models\Mahasiswa;
use App\Models\Tim;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BimbinganTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function actingAsMahasiswa(Mahasiswa $m): User
    {
        $user = User::firstOrCreate(
            ['email' => $m->email_kampus],
            [
                'name' => $m->nama,
                'nim_nip' => $m->nim,
                'role' => UserRole::Mahasiswa,
                'prodi' => $m->prodi,
                'angkatan' => $m->angkatan,
                'password' => Hash::make('password123'),
            ]
        );

        $this->actingAs($user);
        session([
            'auth_user' => [
                'id' => $m->nim,
                'nama' => $m->nama,
                'email' => $m->email_kampus,
                'role' => 'mahasiswa',
                'extra' => $m->prodi.' ('.$m->angkatan.')',
            ],
        ]);

        return $user;
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

        $this->actingAsMahasiswa($mahasiswaBaru);

        $response = $this->get('/bimbingan');

        $response->assertStatus(200);
        $response->assertSee('Anda Belum Terdaftar dalam Tim Lomba');
        $response->assertSee('Daftarkan Tim');
        $response->assertSee('Lomba Terbuka yang Siap Diikuti');
    }

    /**
     * Mahasiswa yang sudah ikut lomba melihat data tim dan bimbingan
     */
    public function test_mahasiswa_dengan_tim_melihat_halaman_bimbingan_aktif(): void
    {
        // Mahasiswa yang punya tim dari seeder (misal Sastra)
        $mahasiswa = Mahasiswa::where('nim', '244107060116')->first();
        $this->actingAsMahasiswa($mahasiswa);

        $response = $this->get('/bimbingan');

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
        $this->actingAsDosen($dosen);

        $response = $this->get('/bimbingan');

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
        $this->actingAsMahasiswa($mahasiswaBaru);

        $response = $this->post('/bimbingan/daftar-tim-cepat', [
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

    /**
     * Mahasiswa yang timnya belum memiliki pembimbing melihat rekomendasi AI Dosen
     */
    public function test_mahasiswa_melihat_rekomendasi_ai_dosen_pembimbing(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        [
                                            'nidn' => '0012058501',
                                            'skor' => 96,
                                            'alasan' => 'Sangat ahli di Software Engineering untuk lomba Web & Mobile.',
                                        ],
                                        [
                                            'nidn' => '0019038705',
                                            'skor' => 91,
                                            'alasan' => 'Keahlian UI/UX sangat membantu antarmuka aplikasi lomba.',
                                        ],
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $mahasiswa = Mahasiswa::create([
            'nim' => '244107067777',
            'nama' => 'Mahasiswa Cari Pembimbing',
            'email_kampus' => 'caripembimbing@student.polinema.ac.id',
            'password' => Hash::make('password123'),
            'prodi' => 'D-IV Sistem Informasi Bisnis',
            'angkatan' => 2024,
        ]);

        $lomba = Lomba::first();

        // Buat tim yang belum punya dosen pembimbing
        $tim = Tim::create([
            'nama_tim' => 'Tim AI Tester',
            'id_lomba' => $lomba->id_lomba,
            'nim' => $mahasiswa->nim,
            'kuota_anggota' => 3,
            'status' => 'disetujui',
        ]);

        $this->actingAsMahasiswa($mahasiswa);

        $response = $this->get('/bimbingan');

        $response->assertStatus(200);
        $response->assertSee('Rekomendasi AI Dosen Pembimbing');
        $response->assertSee('96% Cocok');
        $response->assertSee('Sangat ahli di Software Engineering');
        $response->assertSee('Pilih Dosen Ini');
    }

    /**
     * Anggota tim biasa tidak dapat mengajukan dosen pembimbing (hanya Ketua Tim)
     */
    public function test_anggota_biasa_tidak_bisa_mengajukan_dosen_pembimbing(): void
    {
        $ketua = Mahasiswa::create([
            'nim' => '244107061111',
            'nama' => 'Ketua Tim Resmi',
            'email_kampus' => 'ketua@student.polinema.ac.id',
            'password' => Hash::make('password123'),
            'prodi' => 'D-IV Sistem Informasi Bisnis',
            'angkatan' => 2024,
        ]);

        $anggota = Mahasiswa::create([
            'nim' => '244107062222',
            'nama' => 'Anggota Tim Biasa',
            'email_kampus' => 'anggota@student.polinema.ac.id',
            'password' => Hash::make('password123'),
            'prodi' => 'D-IV Sistem Informasi Bisnis',
            'angkatan' => 2024,
        ]);

        $lomba = Lomba::first();
        $dosen = Dosen::first();

        $tim = Tim::create([
            'nama_tim' => 'Tim Delegasi SIB',
            'id_lomba' => $lomba->id_lomba,
            'nim' => $ketua->nim,
            'kuota_anggota' => 3,
            'status' => 'disetujui',
        ]);

        AnggotaTim::create([
            'id_tim' => $tim->id_tim,
            'nim' => $anggota->nim,
            'status_gabung' => 'diterima',
            'peran' => 'Programmer',
        ]);

        // Login sebagai Anggota biasa
        $this->actingAsMahasiswa($anggota);

        // 1. Pada tampilan, anggota melihat pesan hak akses khusus ketua & tidak melihat tombol submit
        $viewResponse = $this->get('/bimbingan');
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Hak Akses Khusus Ketua Tim');
        $viewResponse->assertDontSee('Kirim Pengajuan Bimbingan');

        // 2. Pada submit POST, anggota ditolak dengan pesan error
        $postResponse = $this->post('/bimbingan/ajukan', [
            'id_tim' => $tim->id_tim,
            'nidn' => $dosen->nidn,
        ]);

        $postResponse->assertRedirect();
        $postResponse->assertSessionHas('error', 'Hanya Ketua Tim yang memiliki hak wewenang untuk memilih dan mengajukan Dosen Pembimbing.');
    }
}
