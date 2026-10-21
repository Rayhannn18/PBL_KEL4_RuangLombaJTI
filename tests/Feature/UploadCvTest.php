<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AnggotaTim;
use App\Models\Lomba;
use App\Models\Mahasiswa;
use App\Models\Tim;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadCvTest extends TestCase
{
    use RefreshDatabase;

    private function createMahasiswaUser(string $nim, string $name, string $email): User
    {
        Mahasiswa::create([
            'nim' => $nim,
            'nama' => $name,
            'email_kampus' => $email,
            'password' => Hash::make('password123'),
            'prodi' => 'D-IV Teknik Informatika',
            'angkatan' => 2023,
        ]);

        return User::create([
            'name' => $name,
            'email' => $email,
            'nim_nip' => $nim,
            'role' => UserRole::Mahasiswa,
            'prodi' => 'D-IV Teknik Informatika',
            'angkatan' => 2023,
            'password' => Hash::make('password123'),
        ]);
    }

    private function createLomba(): Lomba
    {
        return Lomba::create([
            'nama_lomba' => 'Gemastik Software Development 2026',
            'kategori' => 'Software Development',
            'tingkat' => 'Nasional',
            'penyelenggara' => 'Puspresnas Kemdikbud',
            'deskripsi' => 'Kompetisi pengembangan perangkat lunak nasional.',
            'persyaratan' => 'Mahasiswa aktif.',
            'tenggat' => now()->addMonths(2)->toDateString(),
            'biaya' => 0,
            'tautan_daftar' => 'https://gemastik.kemdikbud.go.id',
            'status_verifikasi' => 'terverifikasi',
        ]);
    }

    /**
     * 1. Ketua dapat mengajukan tim dengan CV PDF yang valid.
     */
    public function test_ketua_dapat_mengajukan_tim_dengan_cv_pdf_yang_valid(): void
    {
        Storage::fake('public');

        $user = $this->createMahasiswaUser('2241720001', 'Ketua Hebat', 'ketua@student.polinema.ac.id');
        $lomba = $this->createLomba();

        $cvFile = UploadedFile::fake()->create('cv_ketua.pdf', 350, 'application/pdf');

        $response = $this->actingAs($user)->post('/tim/buat', [
            'nama_tim' => 'Tim Garuda TI',
            'id_lomba' => $lomba->id_lomba,
            'kuota_anggota' => 3,
            'catatan' => 'Mencari anggota pengembang frontend dan backend.',
            'cv' => $cvFile,
        ]);

        $tim = Tim::where('nama_tim', 'Tim Garuda TI')->first();
        $this->assertNotNull($tim, 'Tim gagal disimpan di database.');
        $response->assertRedirect(route('bimbingan.index', ['tim_id' => $tim->id_tim]));
        $response->assertSessionHas('success');

        // Path tersimpan di tim dan anggota_tim
        $this->assertNotEmpty($tim->file_cv);
        $this->assertDatabaseHas('anggota_tim', [
            'id_tim' => $tim->id_tim,
            'nim' => $user->nim_nip,
            'peran' => 'Ketua Tim',
            'status_gabung' => 'diterima',
            'file_cv' => $tim->file_cv,
        ]);

        // File fisik tersimpan di disk public
        Storage::disk('public')->assertExists($tim->file_cv);
    }

    /**
     * 2. Ketua tidak dapat mengajukan tim tanpa CV.
     */
    public function test_ketua_tidak_dapat_mengajukan_tim_tanpa_cv(): void
    {
        Storage::fake('public');

        $user = $this->createMahasiswaUser('2241720002', 'Ketua Tanpa CV', 'tanpa_cv@student.polinema.ac.id');
        $lomba = $this->createLomba();

        $response = $this->actingAs($user)->from('/tim/buat')->post('/tim/buat', [
            'nama_tim' => 'Tim Tanpa CV',
            'id_lomba' => $lomba->id_lomba,
            'kuota_anggota' => 3,
            'catatan' => 'Catatan tim.',
        ]);

        $response->assertRedirect('/tim/buat');
        $response->assertSessionHasErrors(['cv']);
        $this->assertDatabaseMissing('tim', ['nama_tim' => 'Tim Tanpa CV']);
    }

    /**
     * 3. File selain PDF ditolak untuk Ketua Tim.
     */
    public function test_ketua_file_selain_pdf_ditolak(): void
    {
        Storage::fake('public');

        $user = $this->createMahasiswaUser('2241720003', 'Ketua Word CV', 'word_cv@student.polinema.ac.id');
        $lomba = $this->createLomba();

        $docFile = UploadedFile::fake()->create('cv.docx', 500, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $response = $this->actingAs($user)->from('/tim/buat')->post('/tim/buat', [
            'nama_tim' => 'Tim Format Salah',
            'id_lomba' => $lomba->id_lomba,
            'kuota_anggota' => 3,
            'cv' => $docFile,
        ]);

        $response->assertRedirect('/tim/buat');
        $response->assertSessionHasErrors(['cv']);
        $this->assertDatabaseMissing('tim', ['nama_tim' => 'Tim Format Salah']);
    }

    /**
     * 4. File lebih dari 2 MB ditolak untuk Ketua Tim.
     */
    public function test_ketua_file_lebih_dari_2mb_ditolak(): void
    {
        Storage::fake('public');

        $user = $this->createMahasiswaUser('2241720004', 'Ketua File Besar', 'besar@student.polinema.ac.id');
        $lomba = $this->createLomba();

        // 2500 KB > 2048 KB
        $largeFile = UploadedFile::fake()->create('cv_large.pdf', 2500, 'application/pdf');

        $response = $this->actingAs($user)->from('/tim/buat')->post('/tim/buat', [
            'nama_tim' => 'Tim File Besar',
            'id_lomba' => $lomba->id_lomba,
            'kuota_anggota' => 3,
            'cv' => $largeFile,
        ]);

        $response->assertRedirect('/tim/buat');
        $response->assertSessionHasErrors(['cv']);
        $this->assertDatabaseMissing('tim', ['nama_tim' => 'Tim File Besar']);
    }

    /**
     * 5. Anggota dapat mengirim pengajuan bergabung dengan CV PDF yang valid.
     */
    public function test_anggota_dapat_mengirim_pengajuan_bergabung_dengan_cv_pdf_valid(): void
    {
        Storage::fake('public');

        $ketua = $this->createMahasiswaUser('2241720005', 'Ketua Tim A', 'ketuaA@student.polinema.ac.id');
        $anggota = $this->createMahasiswaUser('2241720006', 'Calon Anggota B', 'anggotaB@student.polinema.ac.id');
        $lomba = $this->createLomba();

        $tim = Tim::create([
            'nama_tim' => 'Tim Inovasi Polinema',
            'id_lomba' => $lomba->id_lomba,
            'nim' => $ketua->nim_nip,
            'kuota_anggota' => 3,
            'status_tim' => 'disetujui',
            'file_cv' => 'cv/ketua_dummy.pdf',
        ]);

        AnggotaTim::create([
            'id_tim' => $tim->id_tim,
            'nim' => $ketua->nim_nip,
            'status_gabung' => 'diterima',
            'peran' => 'Ketua Tim',
            'file_cv' => 'cv/ketua_dummy.pdf',
        ]);

        $cvAnggota = UploadedFile::fake()->create('cv_anggota.pdf', 280, 'application/pdf');

        $response = $this->actingAs($anggota)->from('/tim/lowongan')->post("/tim/{$tim->id_tim}/gabung", [
            'peran' => 'Frontend Developer',
            'catatan' => 'Berpengalaman menggunakan Vue dan Tailwind.',
            'cv' => $cvAnggota,
        ]);

        $response->assertRedirect('/tim/lowongan');
        $response->assertSessionHas('success');

        $pengajuan = AnggotaTim::where('id_tim', $tim->id_tim)
            ->where('nim', $anggota->nim_nip)
            ->first();

        $this->assertNotNull($pengajuan);
        $this->assertSame('menunggu', $pengajuan->status_gabung);
        $this->assertSame('Frontend Developer', $pengajuan->peran);
        $this->assertNotEmpty($pengajuan->file_cv);

        Storage::disk('public')->assertExists($pengajuan->file_cv);
    }

    /**
     * 6. Pengajuan anggota tanpa CV ditolak.
     */
    public function test_pengajuan_anggota_tanpa_cv_ditolak(): void
    {
        Storage::fake('public');

        $ketua = $this->createMahasiswaUser('2241720007', 'Ketua Tim C', 'ketuaC@student.polinema.ac.id');
        $anggota = $this->createMahasiswaUser('2241720008', 'Calon Anggota C', 'anggotaC@student.polinema.ac.id');
        $lomba = $this->createLomba();

        $tim = Tim::create([
            'nama_tim' => 'Tim Tanpa CV Anggota',
            'id_lomba' => $lomba->id_lomba,
            'nim' => $ketua->nim_nip,
            'kuota_anggota' => 3,
            'status_tim' => 'disetujui',
        ]);

        $response = $this->actingAs($anggota)->from('/tim/lowongan')->post("/tim/{$tim->id_tim}/gabung", [
            'peran' => 'Backend Developer',
        ]);

        $response->assertRedirect('/tim/lowongan');
        $response->assertSessionHasErrors(['cv']);
        $this->assertDatabaseMissing('anggota_tim', [
            'id_tim' => $tim->id_tim,
            'nim' => $anggota->nim_nip,
        ]);
    }

    /**
     * 7. Anggota file selain PDF atau lebih dari 2 MB ditolak.
     */
    public function test_pengajuan_anggota_file_tidak_valid_ditolak(): void
    {
        Storage::fake('public');

        $ketua = $this->createMahasiswaUser('2241720009', 'Ketua D', 'ketuaD@student.polinema.ac.id');
        $anggota = $this->createMahasiswaUser('2241720010', 'Anggota D', 'anggotaD@student.polinema.ac.id');
        $lomba = $this->createLomba();

        $tim = Tim::create([
            'nama_tim' => 'Tim Uji Format Anggota',
            'id_lomba' => $lomba->id_lomba,
            'nim' => $ketua->nim_nip,
            'kuota_anggota' => 3,
            'status_tim' => 'disetujui',
        ]);

        // Uji non-PDF
        $imageFile = UploadedFile::fake()->create('foto.jpg', 400, 'image/jpeg');
        $response = $this->actingAs($anggota)->from('/tim/lowongan')->post("/tim/{$tim->id_tim}/gabung", [
            'peran' => 'UI/UX Designer',
            'cv' => $imageFile,
        ]);
        $response->assertSessionHasErrors(['cv']);

        // Uji file > 2MB
        $largeFile = UploadedFile::fake()->create('cv_besar.pdf', 3000, 'application/pdf');
        $response2 = $this->actingAs($anggota)->from('/tim/lowongan')->post("/tim/{$tim->id_tim}/gabung", [
            'peran' => 'UI/UX Designer',
            'cv' => $largeFile,
        ]);
        $response2->assertSessionHasErrors(['cv']);
    }

    /**
     * 8. Path CV tersimpan pada data yang benar dan hak akses CV terlindungi.
     */
    public function test_path_cv_tersimpan_dan_hak_akses_cv_terlindungi(): void
    {
        Storage::fake('public');

        $ketua = $this->createMahasiswaUser('2241720011', 'Ketua Akses', 'ketua_akses@student.polinema.ac.id');
        $anggota = $this->createMahasiswaUser('2241720012', 'Anggota Akses', 'anggota_akses@student.polinema.ac.id');
        $stranger = $this->createMahasiswaUser('2241720013', 'Mahasiswa Lain', 'lain@student.polinema.ac.id');
        $lomba = $this->createLomba();

        // 1. Ketua buat tim
        $cvKetua = UploadedFile::fake()->create('cv_ketua_resmi.pdf', 200, 'application/pdf');
        $this->actingAs($ketua)->post('/tim/buat', [
            'nama_tim' => 'Tim Hak Akses',
            'id_lomba' => $lomba->id_lomba,
            'kuota_anggota' => 3,
            'cv' => $cvKetua,
        ]);

        $tim = Tim::where('nama_tim', 'Tim Hak Akses')->firstOrFail();

        // 2. Anggota daftar ke tim
        $cvAnggota = UploadedFile::fake()->create('cv_anggota_resmi.pdf', 250, 'application/pdf');
        $this->actingAs($anggota)->post("/tim/{$tim->id_tim}/gabung", [
            'peran' => 'UI/UX Designer',
            'cv' => $cvAnggota,
        ]);

        $pengajuanAnggota = AnggotaTim::where('id_tim', $tim->id_tim)
            ->where('nim', $anggota->nim_nip)
            ->firstOrFail();

        // 3. Hak Akses: Ketua dapat melihat CV sendiri dan CV pelamar
        $resKetuaSeeOwn = $this->actingAs($ketua)->get(route('tim.cv', ['type' => 'ketua', 'id' => $tim->id_tim]));
        $resKetuaSeeOwn->assertOk();

        $resKetuaSeeAnggota = $this->actingAs($ketua)->get(route('tim.cv', ['type' => 'anggota', 'id' => $pengajuanAnggota->id_anggota]));
        $resKetuaSeeAnggota->assertOk();

        // 4. Hak Akses: Anggota dapat melihat CV miliknya
        $resAnggotaSeeOwn = $this->actingAs($anggota)->get(route('tim.cv', ['type' => 'anggota', 'id' => $pengajuanAnggota->id_anggota]));
        $resAnggotaSeeOwn->assertOk();

        // 5. Hak Akses: Mahasiswa asing di luar tim TIDAK BOLEH melihat CV pelamar atau CV ketua
        $resStrangerKetua = $this->actingAs($stranger)->get(route('tim.cv', ['type' => 'ketua', 'id' => $tim->id_tim]));
        $resStrangerKetua->assertForbidden();

        $resStrangerAnggota = $this->actingAs($stranger)->get(route('tim.cv', ['type' => 'anggota', 'id' => $pengajuanAnggota->id_anggota]));
        $resStrangerAnggota->assertForbidden();
    }

    /**
     * 9. Kegagalan penyimpanan DB tidak meninggalkan file yang tidak terpakai (Rollback Cleanup).
     */
    public function test_kegagalan_penyimpanan_db_tidak_meninggalkan_file_cv(): void
    {
        Storage::fake('public');

        $user = $this->createMahasiswaUser('2241720014', 'Ketua Rollback', 'rollback@student.polinema.ac.id');
        $lomba = $this->createLomba();

        // Hook ke creating event pada model Tim agar melempar exception saat simpan
        Tim::creating(function () {
            throw new \RuntimeException('Simulasi kegagalan koneksi database');
        });

        $cvFile = UploadedFile::fake()->create('cv_gagal.pdf', 300, 'application/pdf');

        $response = $this->actingAs($user)->from('/tim/buat')->post('/tim/buat', [
            'nama_tim' => 'Tim Rollback Fail',
            'id_lomba' => $lomba->id_lomba,
            'kuota_anggota' => 3,
            'cv' => $cvFile,
        ]);

        $response->assertRedirect('/tim/buat');
        $response->assertSessionHas('error');

        // Pastikan tidak ada file yang tersisa di storage public/cv
        $filesInStorage = Storage::disk('public')->allFiles('cv');
        $this->assertEmpty($filesInStorage, 'File CV gagal di-cleanup saat DB transaction error!');
    }

    /**
     * 10. Fitur lama: Ketua merespon pengajuan anggota dan halaman lowongan tetap berjalan.
     */
    public function test_fitur_lama_respon_anggota_dan_lowongan_tetap_berjalan(): void
    {
        $ketua = $this->createMahasiswaUser('2241720015', 'Ketua Respon', 'respon_ketua@student.polinema.ac.id');
        $anggota = $this->createMahasiswaUser('2241720016', 'Anggota Respon', 'respon_anggota@student.polinema.ac.id');
        $lomba = $this->createLomba();

        $tim = Tim::create([
            'nama_tim' => 'Tim Respon Lama',
            'id_lomba' => $lomba->id_lomba,
            'nim' => $ketua->nim_nip,
            'kuota_anggota' => 3,
            'status_tim' => 'disetujui',
            'file_cv' => 'cv/dummy.pdf',
        ]);

        $pengajuan = AnggotaTim::create([
            'id_tim' => $tim->id_tim,
            'nim' => $anggota->nim_nip,
            'status_gabung' => 'menunggu',
            'peran' => 'Backend Developer',
            'file_cv' => 'cv/dummy_anggota.pdf',
        ]);

        // Cek halaman lowongan tim dapat dimuat
        $resLowongan = $this->actingAs($anggota)->get('/tim/lowongan');
        $resLowongan->assertOk();
        $resLowongan->assertSee('Tim Respon Lama');

        // Ketua menyetujui anggota
        $resRespon = $this->actingAs($ketua)->from('/bimbingan')->post(route('tim.anggota.respon', $pengajuan->id_anggota), [
            'status' => 'diterima',
        ]);

        $resRespon->assertRedirect('/bimbingan');
        $resRespon->assertSessionHas('success');

        $pengajuan->refresh();
        $this->assertSame('diterima', $pengajuan->status_gabung);
    }
}
