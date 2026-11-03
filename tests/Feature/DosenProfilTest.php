<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Dosen;
use App\Models\Lomba;
use App\Models\Mahasiswa;
use App\Models\PengajuanBimbingan;
use App\Models\Tim;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DosenProfilTest extends TestCase
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

    /**
     * Dosen dapat mengakses halaman profil & kuota bimbingan
     */
    public function test_dosen_bisa_mengakses_halaman_profil(): void
    {
        $dosen = Dosen::first();
        $this->actingAsDosen($dosen);

        $response = $this->get(route('dosen.profil'));

        $response->assertOk();
        $response->assertSee('Profil Dosen Pembimbing');
        $response->assertSee($dosen->nama);
        $response->assertSee($dosen->nidn);
        $response->assertSee('Batas Maksimal Kuota Bimbingan');
    }

    /**
     * Tamu dan Mahasiswa dilarang mengakses halaman profil dosen
     */
    public function test_tamu_dan_mahasiswa_ditolak_mengakses_profil_dosen(): void
    {
        // 1. Tamu diarahkan ke login
        $guestResponse = $this->get(route('dosen.profil'));
        $guestResponse->assertRedirect(route('login'));

        // 2. Mahasiswa ditolak dengan pesan error
        $mahasiswa = Mahasiswa::first();
        $this->actingAsMahasiswa($mahasiswa);

        $mhsResponse = $this->get(route('dosen.profil'));
        $mhsResponse->assertRedirect(route('dashboard'));
        $mhsResponse->assertSessionHas('error', 'Akses ditolak! Tindakan ini khusus untuk Dosen Pembimbing.');
    }

    /**
     * Dosen berhasil memperbarui bidang keahlian dan kuota bimbingan
     */
    public function test_dosen_bisa_memperbarui_keahlian_dan_kuota_bimbingan(): void
    {
        $dosen = Dosen::first();
        $this->actingAsDosen($dosen);

        $keahlianBaru = 'Deep Learning, Natural Language Processing, Big Data Analytics';
        $kuotaBaru = 8;

        $response = $this->put(route('dosen.profil.update'), [
            'bidang_keahlian' => $keahlianBaru,
            'kuota_bimbingan' => $kuotaBaru,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $dosen->refresh();
        $this->assertSame($keahlianBaru, $dosen->bidang_keahlian);
        $this->assertSame($kuotaBaru, $dosen->kuota_bimbingan);
        $this->assertSame($keahlianBaru, session('auth_user')['extra']);
    }

    /**
     * Validasi gagal jika bidang keahlian kosong
     */
    public function test_update_profil_gagal_jika_keahlian_kosong(): void
    {
        $dosen = Dosen::first();
        $this->actingAsDosen($dosen);

        $response = $this->put(route('dosen.profil.update'), [
            'bidang_keahlian' => '',
            'kuota_bimbingan' => 5,
        ]);

        $response->assertSessionHasErrors('bidang_keahlian');
    }

    /**
     * Validasi gagal jika kuota baru lebih kecil dari jumlah bimbingan aktif (disetujui)
     */
    public function test_update_profil_gagal_jika_kuota_lebih_kecil_dari_bimbingan_aktif(): void
    {
        $dosen = Dosen::first();
        $mahasiswa = Mahasiswa::first();
        $lomba = Lomba::first();

        // Buat 2 tim bimbingan yang aktif (disetujui)
        for ($i = 1; $i <= 2; $i++) {
            $tim = Tim::create([
                'nama_tim' => 'Tim Bimbingan Aktif '.$i,
                'id_lomba' => $lomba->id_lomba,
                'nim' => $mahasiswa->nim,
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

        $this->actingAsDosen($dosen);

        // Coba turunkan kuota menjadi 1 padahal sedang membimbing 2 tim aktif
        $response = $this->put(route('dosen.profil.update'), [
            'bidang_keahlian' => $dosen->bidang_keahlian,
            'kuota_bimbingan' => 1,
        ]);

        $response->assertSessionHasErrors('kuota_bimbingan');

        $dosen->refresh();
        $this->assertNotEquals(1, $dosen->kuota_bimbingan);
    }

    /**
     * Pembaruan profil menghapus cache rekomendasi AI secara terarah
     */
    public function test_update_profil_membersihkan_cache_rekomendasi_ai_secara_terarah(): void
    {
        $dosen = Dosen::first();
        $lomba = Lomba::first();

        $cacheKey = "ai_dosen_rec_v1_{$lomba->id_lomba}";
        Cache::put($cacheKey, ['sample_cached_data'], now()->addHours(12));
        $this->assertTrue(Cache::has($cacheKey));

        $this->actingAsDosen($dosen);

        $this->put(route('dosen.profil.update'), [
            'bidang_keahlian' => 'Keahlian Baru Pembersih Cache',
            'kuota_bimbingan' => 10,
        ])->assertSessionHas('success');

        // Cache terarah harus terhapus
        $this->assertFalse(Cache::has($cacheKey));
    }
}
