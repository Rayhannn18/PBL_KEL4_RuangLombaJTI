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

class DosenRoleBaselineTest extends TestCase
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
     * Verifikasi alur login Dosen seeder (NIDN 10 digit):
     * auth_user id selalu sama dengan dosen.nidn
     */
    public function test_dosen_seeder_nidn_login_sets_correct_auth_id(): void
    {
        $response = $this->post('/login', [
            'role' => 'dosen',
            'login' => '0012058501',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertTrue(session()->has('auth_user'));
        $this->assertSame('0012058501', session('auth_user')['id']);
        $this->assertSame('dosen', session('auth_user')['role']);
    }

    /**
     * Verifikasi alur registrasi & login Dosen NIP 18 digit:
     * auth_user id selalu sama dengan dosen.nidn
     */
    public function test_dosen_nip_18_digit_registration_and_login_sets_correct_auth_id(): void
    {
        $nip = '198010102005012001';

        $password = 'Password123!';

        $regResponse = $this->post('/register', [
            'role' => 'dosen',
            'name' => 'Dr. Rosa Andrie, S.T., M.T.',
            'nim_nip' => $nip,
            'email' => 'rosa.nip@polinema.ac.id',
            'password' => $password,
            'password_confirmation' => $password,
            'terms' => 'on',
        ]);

        $regResponse->assertRedirect(route('dosen.profil'));
        $this->assertAuthenticated();
        $this->assertSame($nip, session('auth_user')['id']);
        $this->assertDatabaseHas('dosen', ['nidn' => $nip]);
        $this->assertDatabaseHas('users', ['nim_nip' => $nip]);

        // Uji coba login ulang dengan NIP 18 digit
        $this->post('/logout');
        $this->assertGuest();

        $loginResponse = $this->post('/login', [
            'role' => 'dosen',
            'login' => $nip,
            'password' => $password,
        ]);

        $loginResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertSame($nip, session('auth_user')['id']);
    }

    /**
     * Tamu / Guest ditolak saat mengakses rute aksi dosen
     */
    public function test_guest_is_redirected_to_login_on_dosen_routes(): void
    {
        $responResponse = $this->post('/bimbingan/pengajuan/1/respon', [
            'status' => 'disetujui',
        ]);
        $responResponse->assertRedirect(route('login'));

        $validasiResponse = $this->post('/bimbingan/logbook/1/validasi', [
            'status_validasi' => 'disetujui',
        ]);
        $validasiResponse->assertRedirect(route('login'));
    }

    /**
     * Mahasiswa ditolak saat mengakses rute aksi dosen
     */
    public function test_mahasiswa_is_rejected_on_dosen_routes(): void
    {
        $mahasiswa = Mahasiswa::first();
        $this->actingAsMahasiswa($mahasiswa);

        $responResponse = $this->post('/bimbingan/pengajuan/1/respon', [
            'status' => 'disetujui',
        ]);
        $responResponse->assertRedirect(route('dashboard'));
        $responResponse->assertSessionHas('error', 'Akses ditolak! Tindakan ini khusus untuk Dosen Pembimbing.');

        $validasiResponse = $this->post('/bimbingan/logbook/1/validasi', [
            'status_validasi' => 'disetujui',
        ]);
        $validasiResponse->assertRedirect(route('dashboard'));
        $validasiResponse->assertSessionHas('error', 'Akses ditolak! Tindakan ini khusus untuk Dosen Pembimbing.');
    }

    /**
     * Dosen pembimbing yang berhak dapat mengakses rute aksi dosen
     */
    public function test_dosen_can_access_dosen_routes(): void
    {
        $dosen = Dosen::first();
        $mahasiswa = Mahasiswa::first();
        $lomba = Lomba::first();

        $tim = Tim::create([
            'nama_tim' => 'Tim Uji Baseline',
            'id_lomba' => $lomba->id_lomba,
            'nim' => $mahasiswa->nim,
            'kuota_anggota' => 3,
            'status' => 'disetujui',
        ]);

        $pengajuan = PengajuanBimbingan::create([
            'id_tim' => $tim->id_tim,
            'nidn' => $dosen->nidn,
            'tgl_pengajuan' => now(),
            'status' => 'menunggu',
        ]);

        $logbook = Logbook::create([
            'id_pengajuan' => $pengajuan->id_pengajuan,
            'tanggal' => now()->format('Y-m-d'),
            'materi' => 'Konsultasi Perdana',
            'tindak_lanjut' => 'Lanjut babak 1',
            'status_validasi' => 'menunggu',
        ]);

        $this->actingAsDosen($dosen);

        $responResponse = $this->post("/bimbingan/pengajuan/{$pengajuan->id_pengajuan}/respon", [
            'status' => 'disetujui',
        ]);
        $responResponse->assertRedirect();
        $responResponse->assertSessionHas('success');

        $validasiResponse = $this->post("/bimbingan/logbook/{$logbook->id_logbook}/validasi", [
            'status_validasi' => 'disetujui',
        ]);
        $validasiResponse->assertRedirect();
        $validasiResponse->assertSessionHas('success');
    }
}
