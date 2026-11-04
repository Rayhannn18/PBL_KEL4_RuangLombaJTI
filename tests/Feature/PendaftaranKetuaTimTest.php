<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\AnggotaTim;
use App\Models\Dosen;
use App\Models\Lomba;
use App\Models\Mahasiswa;
use App\Models\PengajuanBimbingan;
use App\Models\Tim;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PendaftaranKetuaTimTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    private User $adminUser;

    private Lomba $lomba;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->admin = Admin::first();
        $this->adminUser = User::create([
            'name' => $this->admin->nama,
            'email' => $this->admin->email,
            'nim_nip' => (string) $this->admin->id_admin,
            'role' => UserRole::Admin,
            'password' => Hash::make('password123'),
        ]);

        $this->lomba = Lomba::first();
    }

    private function createMahasiswaUser(string $nim, string $name, string $email): User
    {
        Mahasiswa::create([
            'nim' => $nim,
            'nama' => $name,
            'email_kampus' => $email,
            'password' => Hash::make('password123'),
            'prodi' => 'D-IV Teknik Informatika',
            'angkatan' => 2024,
        ]);

        return User::create([
            'name' => $name,
            'email' => $email,
            'nim_nip' => $nim,
            'role' => UserRole::Mahasiswa,
            'prodi' => 'D-IV Teknik Informatika',
            'angkatan' => 2024,
            'password' => Hash::make('password123'),
        ]);
    }

    private function actingAsMahasiswa(User $user): void
    {
        Auth::logout();
        session()->forget('auth_user');
        $this->actingAs($user);
        session([
            'auth_user' => [
                'id' => $user->nim_nip,
                'nama' => $user->name,
                'email' => $user->email,
                'role' => 'mahasiswa',
                'extra' => $user->prodi ? $user->prodi.' ('.$user->angkatan.')' : '',
            ],
        ]);
    }

    private function actingAsAdmin(): void
    {
        Auth::logout();
        session()->forget('auth_user');
        $this->actingAs($this->adminUser);
        session([
            'auth_user' => [
                'id' => $this->admin->id_admin,
                'nama' => $this->admin->nama,
                'email' => $this->admin->email,
                'role' => 'admin',
                'extra' => 'Kemahasiswaan JTI',
            ],
        ]);
    }

    /**
     * Skenario 1:
     * Pengajuan tim -> status menunggu -> Admin menyetujui -> mahasiswa otomatis menjadi Ketua Tim.
     */
    public function test_pengajuan_tim_status_menunggu_admin_menyetujui_mahasiswa_menjadi_ketua_tim(): void
    {
        Storage::fake('public');
        $pemohon = $this->createMahasiswaUser('244107060201', 'Calon Ketua Sukses', 'ketuasukses@polinema.ac.id');
        $pdfCv = UploadedFile::fake()->create('cv_calon_ketua.pdf', 200, 'application/pdf');

        $this->actingAsMahasiswa($pemohon);

        // 1. Mahasiswa mengirim formulir pembentukan tim
        $response = $this->post('/tim/buat', [
            'nama_tim' => 'Tim Garuda Juara',
            'id_lomba' => $this->lomba->id_lomba,
            'kuota_anggota' => 3,
            'catatan' => 'Mencari anggota programmer dan UI designer.',
            'cv' => $pdfCv,
        ]);

        // Harus diarahkan ke daftar tim dengan status berhasil diajukan (menunggu)
        $response->assertRedirect(route('tim.index'));
        $response->assertSessionHas('success');

        $tim = Tim::where('nama_tim', 'Tim Garuda Juara')->first();
        $this->assertNotNull($tim);
        $this->assertEquals('menunggu_persetujuan', $tim->status_tim);

        // Sebelum disetujui Admin: Mahasiswa belum berstatus Ketua Tim
        $this->assertFalse(Tim::ketuaDisetujui($pemohon->nim_nip));

        // AnggotaTim pemohon berstatus menunggu
        $this->assertDatabaseHas('anggota_tim', [
            'id_tim' => $tim->id_tim,
            'nim' => $pemohon->nim_nip,
            'status_gabung' => 'menunggu',
            'peran' => 'Ketua Tim',
        ]);

        // Mahasiswa dapat melihat status pengajuan "Menunggu Persetujuan Admin" pada halaman Tim
        $viewResponse = $this->get('/tim');
        $viewResponse->assertOk();
        $viewResponse->assertSee('Tim Garuda Juara');
        $viewResponse->assertSee('Menunggu Persetujuan Admin');

        // 2. Admin melihat daftar pengajuan tim di Admin Panel
        $this->actingAsAdmin();
        $adminPanelResponse = $this->get(route('admin.index', ['tab' => 'tim']));
        $adminPanelResponse->assertOk();
        $adminPanelResponse->assertSee('Tim Garuda Juara');

        // 3. Admin menyetujui tim
        $approveResponse = $this->post(route('admin.tim.verifikasi', ['id' => $tim->id_tim]), [
            'status' => 'disetujui',
        ]);
        $approveResponse->assertRedirect(route('admin.index', ['tab' => 'tim']));

        // Verifikasi database: Tim disetujui & Mahasiswa resmi menjadi Ketua Tim
        $tim->refresh();
        $this->assertEquals('disetujui', $tim->status_tim);
        $this->assertEquals($this->admin->id_admin, $tim->id_admin);

        $this->assertDatabaseHas('anggota_tim', [
            'id_tim' => $tim->id_tim,
            'nim' => $pemohon->nim_nip,
            'status_gabung' => 'diterima',
            'peran' => 'Ketua Tim',
        ]);

        // Sekarang mahasiswa resmi menjadi Ketua Tim
        $this->assertTrue(Tim::ketuaDisetujui($pemohon->nim_nip));

        // Tim resmi muncul di daftar tim publik dan lowongan
        $this->actingAsMahasiswa($pemohon);
        $lowonganResponse = $this->get('/tim/lowongan');
        $lowonganResponse->assertOk();
        $lowonganResponse->assertSee('Tim Garuda Juara');

        // Mahasiswa melihat status "Disetujui" pada halaman Tim
        $timPageResponse = $this->get('/tim');
        $timPageResponse->assertOk();
        $timPageResponse->assertSee('Tim Garuda Juara');
        $timPageResponse->assertSee('Disetujui');
    }

    /**
     * Skenario 2:
     * Pengajuan tim -> status menunggu -> Admin menolak -> mahasiswa tidak menjadi Ketua Tim.
     * Tampilkan status penolakan agar mahasiswa mengetahui hasil pengajuannya.
     */
    public function test_pengajuan_tim_status_menunggu_admin_menolak_mahasiswa_tidak_menjadi_ketua_tim(): void
    {
        Storage::fake('public');
        $pemohon = $this->createMahasiswaUser('244107060202', 'Calon Ketua Ditolak', 'ketuaditolak@polinema.ac.id');
        $otherStudent = $this->createMahasiswaUser('244107060299', 'Mahasiswa Pengamat', 'pengamat@polinema.ac.id');
        $pdfCv = UploadedFile::fake()->create('cv_calon_ketua2.pdf', 200, 'application/pdf');

        $this->actingAsMahasiswa($pemohon);

        // 1. Mahasiswa mengirim formulir pembentukan tim
        $this->post('/tim/buat', [
            'nama_tim' => 'Tim Ditolak Admin',
            'id_lomba' => $this->lomba->id_lomba,
            'kuota_anggota' => 2,
            'catatan' => 'Mencari anggota lomba.',
            'cv' => $pdfCv,
        ]);

        $tim = Tim::where('nama_tim', 'Tim Ditolak Admin')->first();
        $this->assertNotNull($tim);
        $this->assertEquals('menunggu_persetujuan', $tim->status_tim);

        // 2. Admin menolak tim
        $this->actingAsAdmin();
        $rejectResponse = $this->post(route('admin.tim.verifikasi', ['id' => $tim->id_tim]), [
            'status' => 'ditolak',
        ]);
        $rejectResponse->assertRedirect(route('admin.index', ['tab' => 'tim']));

        // Verifikasi database: Tim ditolak & AnggotaTim ditolak
        $tim->refresh();
        $this->assertEquals('ditolak', $tim->status_tim);
        $this->assertEquals($this->admin->id_admin, $tim->id_admin);

        $this->assertDatabaseHas('anggota_tim', [
            'id_tim' => $tim->id_tim,
            'nim' => $pemohon->nim_nip,
            'status_gabung' => 'ditolak',
        ]);

        // Mahasiswa TIDAK menjadi Ketua Tim
        $this->assertFalse(Tim::ketuaDisetujui($pemohon->nim_nip));

        // Mahasiswa melihat status penolakan di halaman Tim
        $this->actingAsMahasiswa($pemohon);
        $responseView = $this->get('/tim');
        $responseView->assertOk();
        $responseView->assertSee('Tim Ditolak Admin');
        $responseView->assertSee('Ditolak');
        $responseView->assertSee('Pengajuan pembentukan tim ditolak oleh Admin');

        // Tim yang ditolak TIDAK muncul di daftar lowongan terbuka bagi mahasiswa lain
        $this->actingAsMahasiswa($otherStudent);
        $responseLowongan = $this->get('/tim/lowongan');
        $responseLowongan->assertOk();
        $responseLowongan->assertDontSee('Tim Ditolak Admin');
    }

    /**
     * Skenario 3:
     * Mahasiswa yang pengajuannya masih menunggu tidak dapat menggunakan fitur Ketua Tim.
     */
    public function test_mahasiswa_yang_pengajuannya_masih_menunggu_tidak_dapat_menggunakan_fitur_ketua_tim(): void
    {
        Storage::fake('public');
        $pemohon = $this->createMahasiswaUser('244107060203', 'Calon Ketua Menunggu', 'menunggu@polinema.ac.id');
        $calonAnggota = $this->createMahasiswaUser('244107060204', 'Pelamar Anggota', 'pelamar@polinema.ac.id');
        $dosen = Dosen::first();

        // Buat tim dengan status menunggu_persetujuan
        $tim = Tim::create([
            'nama_tim' => 'Tim Menunggu Fitur',
            'id_lomba' => $this->lomba->id_lomba,
            'nim' => $pemohon->nim_nip,
            'kuota_anggota' => 3,
            'status_tim' => 'menunggu_persetujuan',
        ]);

        AnggotaTim::create([
            'id_tim' => $tim->id_tim,
            'nim' => $pemohon->nim_nip,
            'peran' => 'Ketua Tim',
            'status_gabung' => 'menunggu',
        ]);

        // Verifikasi mahasiswa belum diakui sebagai ketua disetujui
        $this->assertFalse(Tim::ketuaDisetujui($pemohon->nim_nip));

        // 1. Tim tidak muncul di lowongan publik bagi mahasiswa lain
        $this->actingAsMahasiswa($calonAnggota);
        $responseLowongan = $this->get('/tim/lowongan');
        $responseLowongan->assertOk();
        $responseLowongan->assertDontSee('Tim Menunggu Fitur');

        // Upaya mendaftar ke tim yang belum disetujui admin ditolak
        $gabungResponse = $this->post(route('tim.gabung', ['id' => $tim->id_tim]), [
            'peran' => 'Frontend Developer',
            'catatan' => 'Ingin bergabung.',
            'cv' => UploadedFile::fake()->create('cv_pelamar.pdf', 100, 'application/pdf'),
        ]);
        $gabungResponse->assertSessionHas('error');

        // Dan bagi pemohon, tim ini TIDAK masuk ke daftar $teams lowongan terbuka
        $this->actingAsMahasiswa($pemohon);
        $responsePemohon = $this->get('/tim/lowongan');
        $responsePemohon->assertOk();
        $teamsInView = $responsePemohon->viewData('teams');
        $this->assertFalse($teamsInView->contains('id_tim', $tim->id_tim));

        // Buat lamaran anggota lain ke tim ini untuk menguji hak akses respon anggota
        $pelamarAnggota = AnggotaTim::create([
            'id_tim' => $tim->id_tim,
            'nim' => $calonAnggota->nim_nip,
            'peran' => 'Frontend Developer',
            'status_gabung' => 'menunggu',
        ]);

        // 2. Mahasiswa tidak bisa merespon penerimaan anggota (fitur Ketua Tim)
        $responAnggota = $this->post(route('tim.anggota.respon', ['id' => $pelamarAnggota->id_anggota]), [
            'status' => 'diterima',
        ]);
        // Harus ditolak dengan pesan error
        $responAnggota->assertRedirect();
        $responAnggota->assertSessionHas('error');
        // Status pelamar tetap menunggu
        $pelamarAnggota->refresh();
        $this->assertEquals('menunggu', $pelamarAnggota->status_gabung);

        // 3. Mahasiswa tidak bisa mengajukan bimbingan dosen untuk tim yang belum disetujui
        $bimbinganAjukan = $this->post(route('bimbingan.ajukan'), [
            'id_tim' => $tim->id_tim,
            'nidn' => $dosen->nidn,
            'pesan_pengantar' => 'Mohon kesediaannya membimbing tim kami.',
        ]);
        $bimbinganAjukan->assertRedirect();
        $bimbinganAjukan->assertSessionHas('error');

        // 4. Mahasiswa tidak bisa mengisi logbook bimbingan jika tim belum disetujui admin
        $pengajuanBimbingan = PengajuanBimbingan::create([
            'id_tim' => $tim->id_tim,
            'nidn' => $dosen->nidn,
            'tgl_pengajuan' => now()->toDateString(),
            'status' => 'disetujui',
        ]);

        $logbookStore = $this->post(route('bimbingan.logbook.store'), [
            'id_pengajuan' => $pengajuanBimbingan->id_pengajuan,
            'tanggal' => now()->format('Y-m-d'),
            'materi' => 'Diskusi pembentukan arsitektur sistem tim.',
            'tindak_lanjut' => 'Arsitektur selesai',
        ]);
        $logbookStore->assertRedirect();
        $logbookStore->assertSessionHas('error');
    }
}
