<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Dosen;
use App\Models\Lomba;
use App\Models\Mahasiswa;
use App\Models\Tim;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    protected function loginAdmin(): void
    {
        $this->post('/login', [
            'role' => 'admin',
            'identifier' => 'admin.jti@polinema.ac.id',
            'password' => 'password123',
        ]);
    }

    /**
     * Unauthenticated user cannot access admin panel
     */
    public function test_unauthenticated_user_cannot_access_admin(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
    }

    /**
     * Non-admin role (e.g. Mahasiswa) cannot access admin panel
     */
    public function test_mahasiswa_cannot_access_admin(): void
    {
        $mahasiswa = Mahasiswa::first();
        $this->post('/login', [
            'role' => 'mahasiswa',
            'identifier' => $mahasiswa->nim,
            'password' => 'password123',
        ]);

        $response = $this->get('/admin');
        $response->assertRedirect(route('dashboard.analitik'));
        $response->assertSessionHas('error');
    }

    /**
     * Non-admin role (Dosen) cannot access admin panel
     */
    public function test_dosen_cannot_access_admin(): void
    {
        $dosen = Dosen::first();
        $this->post('/login', [
            'role' => 'dosen',
            'identifier' => $dosen->email_kampus,
            'password' => 'password123',
        ]);

        $response = $this->get('/admin');
        $response->assertRedirect(route('dashboard.analitik'));
        $response->assertSessionHas('error');
    }

    /**
     * Admin can login using official credentials and is redirected to admin panel
     */
    public function test_admin_can_login_with_official_credentials(): void
    {
        $response = $this->post('/login', [
            'role' => 'admin',
            'identifier' => 'admin.jti@polinema.ac.id',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.index'));
        $this->assertTrue(session()->has('auth_user'));
        $this->assertEquals('admin', session('auth_user')['role']);
    }

    /**
     * Admin can access admin panel successfully
     */
    public function test_admin_can_access_admin_panel(): void
    {
        $this->loginAdmin();

        $response = $this->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('Panel Administrator RuangLombaJTI');
        $response->assertSee('Verifikasi & Kelola Lomba', false);
        $response->assertSee('Persetujuan Ketua', false);
        $response->assertSee('Kelola Data Dosen', false);
        $response->assertSee('Kelola Data Mahasiswa', false);
    }

    /**
     * Admin can verify a lomba (Lomba Kredibel)
     */
    public function test_admin_can_verify_lomba(): void
    {
        $this->loginAdmin();
        $admin = Admin::first();

        $lomba = Lomba::create([
            'nama_lomba' => 'Lomba Test Baru',
            'kategori' => 'Web Development',
            'tingkat' => 'Nasional',
            'penyelenggara' => 'Himanika',
            'tenggat' => now()->addDays(20),
            'biaya' => 0,
            'tautan_daftar' => 'https://example.com/lomba',
            'status_verifikasi' => 'menunggu',
        ]);

        $response = $this->post("/admin/lomba/{$lomba->id_lomba}/verifikasi", [
            'status' => 'terverifikasi',
        ]);

        $response->assertRedirect(route('admin.index', ['tab' => 'lomba']));
        $this->assertDatabaseHas('lomba', [
            'id_lomba' => $lomba->id_lomba,
            'status_verifikasi' => 'terverifikasi',
            'id_admin' => $admin->id_admin,
        ]);
    }

    public function test_admin_can_create_new_lomba(): void
    {
        Storage::fake('public');
        $this->loginAdmin();

        $payload = [
            'nama_lomba' => 'Hackathon Inovasi JTI 2026',
            'kategori' => 'Software Development',
            'tingkat' => 'Nasional',
            'tipe_peserta' => 'Kelompok',
            'mode_pelaksanaan' => 'Online',
            'penyelenggara' => 'Jurusan TI Polinema',
            'tenggat' => '2026-11-30',
            'biaya' => 50000,
            'tautan_daftar' => 'https://jti.polinema.ac.id/hackathon',
            'deskripsi' => 'Kompetisi software inovatif.',
            'persyaratan' => 'Mahasiswa aktif JTI Polinema.',
            'benefit' => 'Uang tunai dan sertifikat.',
            'poster' => UploadedFile::fake()->image('poster.jpg'),
            'buku_pedoman' => UploadedFile::fake()->create('pedoman.pdf', 100, 'application/pdf'),
        ];

        $response = $this->post('/admin/lomba', $payload);
        $response->assertRedirect(route('admin.index', ['tab' => 'lomba']));

        $this->assertDatabaseHas('lomba', [
            'nama_lomba' => 'Hackathon Inovasi JTI 2026',
            'tipe_peserta' => 'Kelompok',
            'mode_pelaksanaan' => 'Online',
            'status_verifikasi' => 'terverifikasi',
        ]);

        $lomba = Lomba::where('nama_lomba', 'Hackathon Inovasi JTI 2026')->first();
        $this->assertNotNull($lomba->poster_path);
        $this->assertNotNull($lomba->buku_pedoman_path);
        Storage::disk('public')->assertExists($lomba->poster_path);
        Storage::disk('public')->assertExists($lomba->buku_pedoman_path);
    }

    /**
     * Admin can update lomba and replace poster
     */
    public function test_admin_can_update_lomba_and_replace_poster(): void
    {
        Storage::fake('public');
        $this->loginAdmin();
        $lomba = Lomba::first();

        $oldPoster = UploadedFile::fake()->image('old_poster.png');
        $oldPath = $oldPoster->store('lomba/poster', 'public');
        $lomba->update(['poster_path' => $oldPath]);

        $newPoster = UploadedFile::fake()->image('new_poster.png');

        $response = $this->put('/admin/lomba/'.$lomba->id_lomba, [
            'nama_lomba' => 'Lomba Terupdate 2026',
            'kategori' => $lomba->kategori,
            'tingkat' => 'Internasional',
            'tipe_peserta' => 'Individu',
            'mode_pelaksanaan' => 'Hybrid',
            'penyelenggara' => $lomba->penyelenggara,
            'tenggat' => '2026-12-01',
            'biaya' => 0,
            'tautan_daftar' => 'https://lomba.example.com',
            'deskripsi' => 'Deskripsi lomba terupdate.',
            'persyaratan' => 'Persyaratan terupdate.',
            'benefit' => 'Benefit terupdate.',
            'status_verifikasi' => 'terverifikasi',
            'poster' => $newPoster,
        ]);

        $response->assertRedirect(route('admin.index', ['tab' => 'lomba']));
        $lomba->refresh();

        $this->assertEquals('Lomba Terupdate 2026', $lomba->nama_lomba);
        $this->assertEquals('Internasional', $lomba->tingkat);
        $this->assertEquals(0, $lomba->biaya);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($lomba->poster_path);
    }

    /**
     * Admin can delete lomba and associated files are removed
     */
    public function test_admin_can_delete_lomba_and_its_files(): void
    {
        Storage::fake('public');
        $this->loginAdmin();
        $lomba = Lomba::first();

        $posterPath = UploadedFile::fake()->image('del_poster.png')->store('lomba/poster', 'public');
        $pedomanPath = UploadedFile::fake()->create('del_pedoman.pdf', 50, 'application/pdf')->store('lomba/pedoman', 'public');

        $lomba->update([
            'poster_path' => $posterPath,
            'buku_pedoman_path' => $pedomanPath,
        ]);

        $response = $this->delete('/admin/lomba/'.$lomba->id_lomba);
        $response->assertRedirect(route('admin.index', ['tab' => 'lomba']));

        $this->assertDatabaseMissing('lomba', ['id_lomba' => $lomba->id_lomba]);
        Storage::disk('public')->assertMissing($posterPath);
        Storage::disk('public')->assertMissing($pedomanPath);
    }

    /**
     * User can download buku pedoman
     */
    public function test_can_download_buku_pedoman(): void
    {
        Storage::fake('public');
        $lomba = Lomba::first();
        $pedomanPath = UploadedFile::fake()->create('panduan_resmi.pdf', 50, 'application/pdf')->store('lomba/pedoman', 'public');
        $lomba->update(['buku_pedoman_path' => $pedomanPath]);

        $response = $this->get("/lomba/{$lomba->id_lomba}/pedoman");
        $response->assertStatus(200);
    }

    /**
     * Admin can approve or reject a Tim / Ketua Tim
     */
    public function test_admin_can_verify_tim(): void
    {
        $this->loginAdmin();
        $admin = Admin::first();
        $lomba = Lomba::first();
        $mahasiswa = Mahasiswa::first();

        $tim = Tim::create([
            'nama_tim' => 'Tim Garuda Alpha',
            'id_lomba' => $lomba->id_lomba,
            'nim' => $mahasiswa->nim,
            'kuota_anggota' => 3,
            'status_tim' => 'menunggu_persetujuan',
        ]);

        $response = $this->post("/admin/tim/{$tim->id_tim}/verifikasi", [
            'status' => 'disetujui',
        ]);

        $response->assertRedirect(route('admin.index', ['tab' => 'tim']));
        $this->assertDatabaseHas('tim', [
            'id_tim' => $tim->id_tim,
            'status_tim' => 'disetujui',
            'id_admin' => $admin->id_admin,
        ]);
    }

    /**
     * Admin can create and manage Dosen
     */
    public function test_admin_can_create_and_update_dosen(): void
    {
        $this->loginAdmin();

        // Create
        $response = $this->post('/admin/dosen', [
            'nidn' => '0099887766',
            'nama' => 'Dosen Baru S.Kom., M.T.',
            'email_kampus' => 'dosen.baru@polinema.ac.id',
            'password' => 'password123',
            'bidang_keahlian' => 'Cloud Computing',
            'kuota_bimbingan' => 6,
        ]);

        $response->assertRedirect(route('admin.index', ['tab' => 'dosen']));
        $this->assertDatabaseHas('dosen', [
            'nidn' => '0099887766',
            'nama' => 'Dosen Baru S.Kom., M.T.',
        ]);

        // Update
        $responseUpdate = $this->put('/admin/dosen/0099887766', [
            'nama' => 'Dosen Baru S.Kom., M.T., Ph.D.',
            'email_kampus' => 'dosen.baru@polinema.ac.id',
            'bidang_keahlian' => 'Cloud & DevOps Engineering',
            'kuota_bimbingan' => 8,
        ]);

        $responseUpdate->assertRedirect(route('admin.index', ['tab' => 'dosen']));
        $this->assertDatabaseHas('dosen', [
            'nidn' => '0099887766',
            'nama' => 'Dosen Baru S.Kom., M.T., Ph.D.',
            'kuota_bimbingan' => 8,
        ]);

        // Delete
        $responseDelete = $this->delete('/admin/dosen/0099887766');
        $responseDelete->assertRedirect(route('admin.index', ['tab' => 'dosen']));
        $this->assertDatabaseMissing('dosen', ['nidn' => '0099887766']);
    }

    /**
     * Admin can create, update, and delete Mahasiswa
     */
    public function test_admin_can_manage_mahasiswa(): void
    {
        $this->loginAdmin();

        // Create
        $response = $this->post('/admin/mahasiswa', [
            'nim' => '244107068888',
            'nama' => 'Mahasiswa Test Admin',
            'email_kampus' => 'mhs.admin@student.polinema.ac.id',
            'password' => 'password123',
            'prodi' => 'D-IV Teknik Informatika',
            'angkatan' => 2024,
        ]);

        $response->assertRedirect(route('admin.index', ['tab' => 'mahasiswa']));
        $this->assertDatabaseHas('mahasiswa', [
            'nim' => '244107068888',
            'nama' => 'Mahasiswa Test Admin',
        ]);

        // Update
        $responseUpdate = $this->put('/admin/mahasiswa/244107068888', [
            'nama' => 'Mahasiswa Test Admin Updated',
            'email_kampus' => 'mhs.admin@student.polinema.ac.id',
            'prodi' => 'D-IV Sistem Informasi Bisnis',
            'angkatan' => 2024,
        ]);

        $responseUpdate->assertRedirect(route('admin.index', ['tab' => 'mahasiswa']));
        $this->assertDatabaseHas('mahasiswa', [
            'nim' => '244107068888',
            'nama' => 'Mahasiswa Test Admin Updated',
            'prodi' => 'D-IV Sistem Informasi Bisnis',
        ]);

        // Delete
        $responseDelete = $this->delete('/admin/mahasiswa/244107068888');
        $responseDelete->assertRedirect(route('admin.index', ['tab' => 'mahasiswa']));
        $this->assertDatabaseMissing('mahasiswa', ['nim' => '244107068888']);
    }
}
