<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AnggotaTim;
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

class LogbookHakAksesTest extends TestCase
{
    use RefreshDatabase;

    private Dosen $dosenPembimbing;

    private Dosen $dosenLain;

    private Mahasiswa $ketua;

    private Mahasiswa $anggota;

    private Mahasiswa $stranger;

    private Tim $tim;

    private PengajuanBimbingan $pengajuan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        // 1. Dosen Pembimbing & Dosen Lain
        $this->dosenPembimbing = Dosen::first();
        $this->dosenLain = Dosen::where('nidn', '!=', $this->dosenPembimbing->nidn)->first();

        // 2. Mahasiswa: Ketua, Anggota Resmi, dan Mahasiswa Asing
        $this->ketua = Mahasiswa::create([
            'nim' => '244107060101',
            'nama' => 'Ketua Tim Juara',
            'email_kampus' => 'ketua_juara@student.polinema.ac.id',
            'password' => Hash::make('password123'),
            'prodi' => 'D-IV Teknik Informatika',
            'angkatan' => 2024,
        ]);

        $this->anggota = Mahasiswa::create([
            'nim' => '244107060102',
            'nama' => 'Anggota Tim Setia',
            'email_kampus' => 'anggota_setia@student.polinema.ac.id',
            'password' => Hash::make('password123'),
            'prodi' => 'D-IV Teknik Informatika',
            'angkatan' => 2024,
        ]);

        $this->stranger = Mahasiswa::create([
            'nim' => '244107060103',
            'nama' => 'Mahasiswa Luar Tim',
            'email_kampus' => 'orang_asing@student.polinema.ac.id',
            'password' => Hash::make('password123'),
            'prodi' => 'D-IV Sistem Informasi Bisnis',
            'angkatan' => 2024,
        ]);

        // 3. Buat Tim & Daftarkan Ketua serta Anggota
        $lomba = Lomba::first();
        $this->tim = Tim::create([
            'nama_tim' => 'Tim Hak Akses Logbook',
            'id_lomba' => $lomba->id_lomba,
            'nim' => $this->ketua->nim,
            'kuota_anggota' => 3,
            'status_tim' => 'disetujui',
        ]);

        AnggotaTim::create([
            'id_tim' => $this->tim->id_tim,
            'nim' => $this->ketua->nim,
            'status_gabung' => 'diterima',
            'peran' => 'Ketua Tim',
        ]);

        AnggotaTim::create([
            'id_tim' => $this->tim->id_tim,
            'nim' => $this->anggota->nim,
            'status_gabung' => 'diterima',
            'peran' => 'Programmer Frontend',
        ]);

        // 4. Bimbingan Resmi Disetujui
        $this->pengajuan = PengajuanBimbingan::create([
            'id_tim' => $this->tim->id_tim,
            'nidn' => $this->dosenPembimbing->nidn,
            'tgl_pengajuan' => now()->toDateString(),
            'status' => 'disetujui',
        ]);
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
                'extra' => $m->prodi,
            ],
        ]);

        return $user;
    }

    // =========================================================================
    // 1. KETUA TIM (MAHASISWA)
    // =========================================================================

    /**
     * Ketua Tim dapat melihat seluruh logbook bimbingan timnya dan tombol 'Isi Logbook Baru'.
     */
    public function test_ketua_tim_dapat_melihat_logbook_dan_tombol_isi_logbook(): void
    {
        $this->actingAsMahasiswa($this->ketua);

        $response = $this->get(route('bimbingan.index', ['tim_id' => $this->tim->id_tim]));

        $response->assertStatus(200);
        $response->assertSee('Logbook Aktivitas Bimbingan');
        $response->assertSee('Isi Logbook Baru');
    }

    /**
     * Ketua Tim dapat menambahkan logbook bimbingan baru.
     */
    public function test_ketua_tim_dapat_menambahkan_logbook_bimbingan(): void
    {
        $this->actingAsMahasiswa($this->ketua);

        $response = $this->post(route('bimbingan.logbook.store'), [
            'id_pengajuan' => $this->pengajuan->id_pengajuan,
            'tanggal' => now()->format('Y-m-d'),
            'materi' => 'Diskusi Arsitektur Microservices dengan Dosen',
            'tindak_lanjut' => 'Menyusun spesifikasi API Gateway',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Catatan logbook bimbingan berhasil dicatat!');

        $this->assertDatabaseHas('logbook', [
            'id_pengajuan' => $this->pengajuan->id_pengajuan,
            'materi' => 'Diskusi Arsitektur Microservices dengan Dosen',
            'status_validasi' => 'menunggu',
        ]);
    }

    /**
     * Ketua Tim dapat mengedit logbook yang memerlukan revisi.
     */
    public function test_ketua_tim_dapat_mengedit_logbook_yang_memerlukan_revisi(): void
    {
        $logbook = Logbook::create([
            'id_pengajuan' => $this->pengajuan->id_pengajuan,
            'tanggal' => now()->format('Y-m-d'),
            'materi' => 'Draft Awal Bab 1',
            'tindak_lanjut' => '-',
            'status_validasi' => 'revisi',
            'catatan_dosen' => 'Latar belakang belum tajam.',
        ]);

        $this->actingAsMahasiswa($this->ketua);

        $response = $this->put(route('bimbingan.logbook.update', $logbook->id_logbook), [
            'tanggal' => now()->format('Y-m-d'),
            'materi' => 'Revisi Bab 1: Latar Belakang Diperkuat',
            'tindak_lanjut' => 'Konsultasi metodologi pada pertemuan berikutnya',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $logbook->refresh();
        $this->assertSame('Revisi Bab 1: Latar Belakang Diperkuat', $logbook->materi);
        $this->assertSame('menunggu', $logbook->status_validasi);
    }

    // =========================================================================
    // 2. DOSEN PEMBIMBING
    // =========================================================================

    /**
     * Dosen Pembimbing dapat melihat seluruh logbook dari tim yang dibimbingnya.
     */
    public function test_dosen_pembimbing_dapat_melihat_logbook_tim_yang_dibimbingnya(): void
    {
        Logbook::create([
            'id_pengajuan' => $this->pengajuan->id_pengajuan,
            'tanggal' => now()->format('Y-m-d'),
            'materi' => 'Sesi Konsultasi Desain Sistem',
            'tindak_lanjut' => 'Selesai',
            'status_validasi' => 'disetujui',
        ]);

        $this->actingAsDosen($this->dosenPembimbing);

        $response = $this->get(route('bimbingan.index'));

        $response->assertStatus(200);
        $response->assertSee('LOGBOOK BIMBINGAN');
        $response->assertSee('Isi Logbook Bimbingan');
        $response->assertSee('Sesi Konsultasi Desain Sistem');
    }

    /**
     * Dosen Pembimbing dapat mengisi atau menambahkan logbook bimbingan.
     */
    public function test_dosen_pembimbing_dapat_menambahkan_logbook_bimbingan(): void
    {
        $this->actingAsDosen($this->dosenPembimbing);

        $response = $this->post(route('bimbingan.logbook.store'), [
            'id_pengajuan' => $this->pengajuan->id_pengajuan,
            'tanggal' => now()->format('Y-m-d'),
            'materi' => 'Evaluasi Prototype dan Pengujian User Usability',
            'tindak_lanjut' => 'Perbaiki alur checkout',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Catatan logbook bimbingan berhasil dicatat!');

        $this->assertDatabaseHas('logbook', [
            'id_pengajuan' => $this->pengajuan->id_pengajuan,
            'materi' => 'Evaluasi Prototype dan Pengujian User Usability',
            'status_validasi' => 'disetujui',
        ]);
    }

    /**
     * Dosen Pembimbing dapat memberikan validasi dan catatan evaluasi logbook.
     */
    public function test_dosen_pembimbing_dapat_memvalidasi_dan_memberi_catatan_logbook(): void
    {
        $logbook = Logbook::create([
            'id_pengajuan' => $this->pengajuan->id_pengajuan,
            'tanggal' => now()->format('Y-m-d'),
            'materi' => 'Implementasi Backend API',
            'tindak_lanjut' => '-',
            'status_validasi' => 'menunggu',
        ]);

        $this->actingAsDosen($this->dosenPembimbing);

        $response = $this->post(route('bimbingan.logbook.validasi', $logbook->id_logbook), [
            'status_validasi' => 'revisi',
            'catatan_dosen' => 'Harap tambahkan rate limiting dan validasi input.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $logbook->refresh();
        $this->assertSame('revisi', $logbook->status_validasi);
        $this->assertSame('Harap tambahkan rate limiting dan validasi input.', $logbook->catatan_dosen);
    }

    /**
     * Dosen lain yang bukan pembimbing tim ditolak saat mencoba menambahkan logbook tim ini.
     */
    public function test_dosen_lain_bukan_pembimbing_ditolak_saat_menambahkan_logbook(): void
    {
        $this->actingAsDosen($this->dosenLain);

        $response = $this->post(route('bimbingan.logbook.store'), [
            'id_pengajuan' => $this->pengajuan->id_pengajuan,
            'tanggal' => now()->format('Y-m-d'),
            'materi' => 'Percobaan intervensi dosen lain',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Anda hanya dapat menambahkan logbook untuk tim yang Anda bimbing.');

        $this->assertDatabaseMissing('logbook', [
            'materi' => 'Percobaan intervensi dosen lain',
        ]);
    }

    // =========================================================================
    // 3. ANGGOTA TIM (MAHASISWA SELAIN KETUA TIM) - READ ONLY
    // =========================================================================

    /**
     * Anggota Tim dapat melihat seluruh logbook bimbingan timnya,
     * TETAPI tidak menampilkan tombol atau form tambah/edit logbook (read-only di UI).
     */
    public function test_anggota_tim_dapat_melihat_logbook_tetapi_tanpa_tombol_dan_form_tambah(): void
    {
        Logbook::create([
            'id_pengajuan' => $this->pengajuan->id_pengajuan,
            'tanggal' => now()->format('Y-m-d'),
            'materi' => 'Materi Konsultasi Tim',
            'tindak_lanjut' => 'Lanjutkan coding',
            'status_validasi' => 'revisi',
            'catatan_dosen' => 'Perbaiki modul autentikasi',
        ]);

        $this->actingAsMahasiswa($this->anggota);

        $response = $this->get(route('bimbingan.index', ['tim_id' => $this->tim->id_tim]));

        $response->assertStatus(200);
        // Dapat melihat logbook timnya
        $response->assertSee('Logbook Aktivitas Bimbingan');
        $response->assertSee('Materi Konsultasi Tim');
        $response->assertSee('Perbaiki modul autentikasi');

        // TIDAK menampilkan tombol aksi tambah atau edit logbook
        $response->assertDontSee('Isi Logbook Baru');
        $response->assertDontSee('Perbaiki Logbook');

        // Form modal tambah logbook tidak boleh dirender untuk anggota tim
        $response->assertDontSee('id="modalLogbook"', false);
    }

    /**
     * Anggota Tim DITOLAK di backend saat mencoba mengakses endpoint tambah logbook (POST).
     */
    public function test_anggota_tim_ditolak_di_backend_saat_mencoba_menambahkan_logbook(): void
    {
        $this->actingAsMahasiswa($this->anggota);

        $response = $this->post(route('bimbingan.logbook.store'), [
            'id_pengajuan' => $this->pengajuan->id_pengajuan,
            'tanggal' => now()->format('Y-m-d'),
            'materi' => 'Percobaan pengisian logbook oleh anggota biasa',
            'tindak_lanjut' => 'Percobaan ilegal',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Akses ditolak. Anggota tim hanya memiliki hak akses baca (read-only) pada logbook bimbingan.');

        $this->assertDatabaseMissing('logbook', [
            'materi' => 'Percobaan pengisian logbook oleh anggota biasa',
        ]);
    }

    /**
     * Anggota Tim DITOLAK di backend saat mencoba mengakses endpoint edit logbook (PUT).
     */
    public function test_anggota_tim_ditolak_di_backend_saat_mencoba_mengedit_logbook(): void
    {
        $logbook = Logbook::create([
            'id_pengajuan' => $this->pengajuan->id_pengajuan,
            'tanggal' => now()->format('Y-m-d'),
            'materi' => 'Catatan Resmi Ketua',
            'tindak_lanjut' => '-',
            'status_validasi' => 'revisi',
        ]);

        $this->actingAsMahasiswa($this->anggota);

        $response = $this->put(route('bimbingan.logbook.update', $logbook->id_logbook), [
            'tanggal' => now()->format('Y-m-d'),
            'materi' => 'Catatan ini diubah secara tidak sah oleh anggota',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Akses ditolak. Anggota tim hanya memiliki hak akses baca (read-only) dan tidak dapat mengubah logbook.');

        $logbook->refresh();
        $this->assertSame('Catatan Resmi Ketua', $logbook->materi);
    }

    /**
     * Anggota Tim DITOLAK di backend saat mencoba mengakses endpoint hapus logbook (DELETE).
     */
    public function test_anggota_tim_ditolak_di_backend_saat_mencoba_menghapus_logbook(): void
    {
        $logbook = Logbook::create([
            'id_pengajuan' => $this->pengajuan->id_pengajuan,
            'tanggal' => now()->format('Y-m-d'),
            'materi' => 'Catatan Sebelum Dihapus',
            'tindak_lanjut' => '-',
            'status_validasi' => 'menunggu',
        ]);

        $this->actingAsMahasiswa($this->anggota);

        $response = $this->delete(route('bimbingan.logbook.destroy', $logbook->id_logbook));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Akses ditolak. Anggota tim hanya memiliki hak akses baca (read-only) dan tidak dapat menghapus logbook.');

        $this->assertDatabaseHas('logbook', [
            'id_logbook' => $logbook->id_logbook,
        ]);
    }

    /**
     * Ketua Tim dapat menghapus logbook yang masih menunggu validasi.
     */
    public function test_ketua_tim_dapat_menghapus_logbook_yang_belum_disetujui(): void
    {
        $logbook = Logbook::create([
            'id_pengajuan' => $this->pengajuan->id_pengajuan,
            'tanggal' => now()->format('Y-m-d'),
            'materi' => 'Catatan Salah Tulis',
            'tindak_lanjut' => '-',
            'status_validasi' => 'menunggu',
        ]);

        $this->actingAsMahasiswa($this->ketua);

        $response = $this->delete(route('bimbingan.logbook.destroy', $logbook->id_logbook));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Catatan logbook berhasil dihapus.');

        $this->assertDatabaseMissing('logbook', [
            'id_logbook' => $logbook->id_logbook,
        ]);
    }

    // =========================================================================
    // 4. ROLE ADMIN - REKAPITULASI LOGBOOK BIMBINGAN (READ-ONLY)
    // =========================================================================

    private function actingAsAdmin(): User
    {
        $user = User::firstOrCreate(
            ['email' => 'admin.jti@polinema.ac.id'],
            [
                'name' => 'Admin Kemahasiswaan JTI',
                'nim_nip' => 'ADMIN01',
                'role' => UserRole::Admin,
                'password' => Hash::make('password123'),
            ]
        );

        $this->actingAs($user);
        session([
            'auth_user' => [
                'id' => 1,
                'nama' => 'Admin Kemahasiswaan JTI',
                'email' => 'admin.jti@polinema.ac.id',
                'role' => 'admin',
                'extra' => 'Kemahasiswaan JTI',
            ],
        ]);

        return $user;
    }

    /**
     * Admin dapat melihat seluruh rekap logbook bimbingan secara read-only beserta filter,
     * tanpa tombol/modal tambah, edit, hapus, atau validasi logbook.
     */
    public function test_admin_melihat_rekap_logbook_bimbingan_secara_readonly_dan_bisa_filter(): void
    {
        Logbook::create([
            'id_pengajuan' => $this->pengajuan->id_pengajuan,
            'tanggal' => now()->format('Y-m-d'),
            'materi' => 'Sesi Konsultasi Monitoring Admin',
            'tindak_lanjut' => 'Penyempurnaan Laporan Akhir',
            'status_validasi' => 'disetujui',
        ]);

        $this->actingAsAdmin();

        $response = $this->get(route('bimbingan.index'));

        $response->assertStatus(200);
        $response->assertSee('Rekapitulasi Bimbingan Seluruh JTI');
        $response->assertSee('Mode Admin: Hanya Lihat');
        $response->assertSee('Total Tim Bimbingan');
        $response->assertSee('Total Sesi Logbook');
        $response->assertSee('Tim Belum Ada Logbook');
        $response->assertSee('Dosen Aktif Membimbing');
        $response->assertSee('Tim Hak Akses Logbook');
        $response->assertSee('Sesi Konsultasi Monitoring Admin');
        $response->assertSee('Penyempurnaan Laporan Akhir');

        // Tidak ada modal atau tombol tulis/validasi logbook untuk Admin
        $response->assertDontSee('id="modalLogbook"', false);
        $response->assertDontSee('id="modalEditLogbook"', false);
        $response->assertDontSee('id="modalRevisiLogbook"', false);

        // Uji filter pencarian & status logbook
        $filterResponse = $this->get(route('bimbingan.index', [
            'search' => 'Tim Hak Akses Logbook',
            'status_logbook' => 'sudah',
        ]));
        $filterResponse->assertStatus(200);
        $filterResponse->assertSee('Tim Hak Akses Logbook');
    }

    /**
     * Admin ditolak di sisi server saat mencoba menambah, mengedit, menghapus, atau memvalidasi logbook.
     */
    public function test_admin_ditolak_di_server_untuk_semua_aksi_tulis_dan_validasi_logbook(): void
    {
        $logbook = Logbook::create([
            'id_pengajuan' => $this->pengajuan->id_pengajuan,
            'tanggal' => now()->format('Y-m-d'),
            'materi' => 'Catatan Awal Tim',
            'tindak_lanjut' => '-',
            'status_validasi' => 'menunggu',
        ]);

        $this->actingAsAdmin();

        // 1. Store Logbook ditolak
        $storeResponse = $this->post(route('bimbingan.logbook.store'), [
            'id_pengajuan' => $this->pengajuan->id_pengajuan,
            'tanggal' => now()->format('Y-m-d'),
            'materi' => 'Logbook oleh Admin',
        ]);
        $storeResponse->assertRedirect();
        $storeResponse->assertSessionHas('error');
        $this->assertDatabaseMissing('logbook', ['materi' => 'Logbook oleh Admin']);

        // 2. Update Logbook ditolak
        $updateResponse = $this->put(route('bimbingan.logbook.update', $logbook->id_logbook), [
            'tanggal' => now()->format('Y-m-d'),
            'materi' => 'Diubah oleh Admin',
        ]);
        $updateResponse->assertRedirect();
        $updateResponse->assertSessionHas('error');
        $this->assertDatabaseMissing('logbook', ['materi' => 'Diubah oleh Admin']);

        // 3. Validasi Logbook ditolak
        $validasiResponse = $this->post(route('bimbingan.logbook.validasi', $logbook->id_logbook), [
            'status_validasi' => 'disetujui',
        ]);
        $validasiResponse->assertRedirect();
        $validasiResponse->assertSessionHas('error', 'Hanya Dosen Pembimbing yang berhak memvalidasi logbook.');
        $this->assertSame('menunggu', $logbook->fresh()->status_validasi);

        // 4. Destroy Logbook ditolak
        $destroyResponse = $this->delete(route('bimbingan.logbook.destroy', $logbook->id_logbook));
        $destroyResponse->assertRedirect();
        $destroyResponse->assertSessionHas('error');
        $this->assertDatabaseHas('logbook', ['id_logbook' => $logbook->id_logbook]);
    }
}
