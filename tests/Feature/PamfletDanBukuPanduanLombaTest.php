<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Lomba;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PamfletDanBukuPanduanLombaTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->admin = Admin::first();
    }

    private function loginAdmin(): void
    {
        $this->post('/login', [
            'role' => 'admin',
            'identifier' => $this->admin->email,
            'password' => 'password123',
        ]);
    }

    /**
     * 1. Admin dapat mengunggah Pamflet (JPG/PNG) dan Buku Panduan (PDF) saat menambah lomba.
     */
    public function test_admin_dapat_menambah_lomba_dengan_pamflet_dan_buku_panduan(): void
    {
        Storage::fake('public');
        $this->loginAdmin();

        $pamflet = UploadedFile::fake()->image('pamflet_gemastik.jpg', 800, 1200);
        $guidebook = UploadedFile::fake()->create('guidebook_gemastik.pdf', 300, 'application/pdf');

        $response = $this->post(route('admin.lomba.store'), [
            'nama_lomba' => 'Kompetisi Pemrograman Nasional 2026',
            'kategori' => 'Software Development',
            'tingkat' => 'Nasional',
            'tipe_peserta' => 'Kelompok',
            'mode_pelaksanaan' => 'Online',
            'penyelenggara' => 'Politeknik Negeri Malang',
            'tenggat' => now()->addDays(30)->toDateString(),
            'biaya' => 50000,
            'tautan_daftar' => 'https://lomba.polinema.ac.id/daftar',
            'deskripsi' => 'Kompetisi coding nasional mahasiswa.',
            'persyaratan' => 'Mahasiswa aktif.',
            'benefit' => 'Sertifikat dan hadiah pembinaan.',
            'poster' => $pamflet,
            'buku_pedoman' => $guidebook,
        ]);

        $response->assertRedirect(route('admin.index', ['tab' => 'lomba']));
        $response->assertSessionHas('success');

        $lomba = Lomba::where('nama_lomba', 'Kompetisi Pemrograman Nasional 2026')->first();
        $this->assertNotNull($lomba);
        $this->assertNotNull($lomba->poster_path);
        $this->assertNotNull($lomba->buku_pedoman_path);

        // File tersimpan fisik di disk public
        Storage::disk('public')->assertExists($lomba->poster_path);
        Storage::disk('public')->assertExists($lomba->buku_pedoman_path);
    }

    /**
     * 2. Validasi format file:
     * - Pamflet harus JPG, JPEG, atau PNG.
     * - Buku Panduan harus PDF.
     */
    public function test_validasi_format_file_pamflet_dan_buku_panduan(): void
    {
        Storage::fake('public');
        $this->loginAdmin();

        // Coba upload file teks sebagai pamflet dan file teks sebagai buku panduan
        $invalidPamflet = UploadedFile::fake()->create('pamflet_palsu.txt', 50, 'text/plain');
        $invalidGuidebook = UploadedFile::fake()->create('panduan_word.docx', 50, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $response = $this->post(route('admin.lomba.store'), [
            'nama_lomba' => 'Lomba Uji Validasi Format',
            'kategori' => 'Software Development',
            'tingkat' => 'Nasional',
            'tipe_peserta' => 'Kelompok',
            'mode_pelaksanaan' => 'Online',
            'penyelenggara' => 'JTI Polinema',
            'tenggat' => now()->addDays(20)->toDateString(),
            'biaya' => 0,
            'tautan_daftar' => 'https://example.com/daftar',
            'deskripsi' => 'Deskripsi lomba.',
            'persyaratan' => 'Persyaratan lomba.',
            'benefit' => 'Benefit lomba.',
            'poster' => $invalidPamflet,
            'buku_pedoman' => $invalidGuidebook,
        ]);

        $response->assertSessionHasErrors(['poster', 'buku_pedoman']);
        $this->assertDatabaseMissing('lomba', ['nama_lomba' => 'Lomba Uji Validasi Format']);
    }

    /**
     * 3. Admin dapat menambah lomba tanpa mengunggah file (opsional).
     */
    public function test_admin_dapat_menambah_lomba_tanpa_pamflet_dan_panduan(): void
    {
        Storage::fake('public');
        $this->loginAdmin();

        $response = $this->post(route('admin.lomba.store'), [
            'nama_lomba' => 'Lomba Polos Tanpa File',
            'kategori' => 'UI/UX Design',
            'tingkat' => 'Nasional',
            'tipe_peserta' => 'Individu',
            'mode_pelaksanaan' => 'Online',
            'penyelenggara' => 'Himpunan JTI',
            'tenggat' => now()->addDays(14)->toDateString(),
            'biaya' => 0,
            'tautan_daftar' => 'https://example.com/polos',
            'deskripsi' => 'Deskripsi lomba tanpa berkas.',
            'persyaratan' => 'Persyaratan lomba tanpa berkas.',
            'benefit' => 'Benefit lomba tanpa berkas.',
        ]);

        $response->assertRedirect(route('admin.index', ['tab' => 'lomba']));

        $lomba = Lomba::where('nama_lomba', 'Lomba Polos Tanpa File')->first();
        $this->assertNotNull($lomba);
        $this->assertNull($lomba->poster_path);
        $this->assertNull($lomba->buku_pedoman_path);
    }

    /**
     * 4. Halaman Detail Lomba menampilkan Pamflet dan Buku Panduan jika tersedia,
     * serta tombol melihat dan mengunduh PDF.
     */
    public function test_detail_lomba_menampilkan_pamflet_dan_buku_panduan_tersedia(): void
    {
        Storage::fake('public');

        $pamfletFile = UploadedFile::fake()->image('pamflet_resmi.png', 600, 900);
        $pamfletPath = $pamfletFile->store('lomba/poster', 'public');

        $pdfFile = UploadedFile::fake()->create('guidebook_resmi.pdf', 200, 'application/pdf');
        $pdfPath = $pdfFile->store('lomba/pedoman', 'public');

        $lomba = Lomba::create([
            'nama_lomba' => 'Lomba Desain Aplikasi Mobile 2026',
            'kategori' => 'Mobile Application',
            'tingkat' => 'Nasional',
            'tipe_peserta' => 'Kelompok',
            'mode_pelaksanaan' => 'Online',
            'penyelenggara' => 'Google Developer Student Clubs',
            'tenggat' => now()->addDays(25)->toDateString(),
            'biaya' => 0,
            'tautan_daftar' => 'https://gdsc.example.com',
            'deskripsi' => 'Kompetisi perancangan aplikasi flutter.',
            'persyaratan' => 'Mahasiswa aktif.',
            'benefit' => 'Merchandise dan pendanaan.',
            'status_verifikasi' => 'terverifikasi',
            'poster_path' => $pamfletPath,
            'buku_pedoman_path' => $pdfPath,
        ]);

        $response = $this->get(route('lomba.show', $lomba->id_lomba));
        $response->assertOk();

        // Menampilkan Pamflet Resmi
        $response->assertSee('Pamflet Resmi Lomba');
        $response->assertSee(Storage::disk('public')->url($pamfletPath));

        // Menampilkan Bagian Buku Panduan Lomba
        $response->assertSee('Buku Panduan Lomba');
        $response->assertSee('Lihat PDF');
        $response->assertSee('Unduh PDF');
        $response->assertSee(route('lomba.pedoman.view', $lomba->id_lomba));
        $response->assertSee(route('lomba.pedoman.download', $lomba->id_lomba));

        // Uji Akses Route Pratinjau Inline PDF
        $viewPdfResponse = $this->get(route('lomba.pedoman.view', $lomba->id_lomba));
        $viewPdfResponse->assertOk();
        $this->assertEquals('application/pdf', $viewPdfResponse->headers->get('content-type'));

        // Uji Akses Route Unduh PDF
        $downloadPdfResponse = $this->get(route('lomba.pedoman.download', $lomba->id_lomba));
        $downloadPdfResponse->assertOk();
        $this->assertStringContainsString('attachment', (string) $downloadPdfResponse->headers->get('content-disposition'));
    }

    /**
     * 5. Halaman Detail Lomba saat belum ada pamflet & belum ada buku panduan:
     * - Jangan tampilkan gambar kosong / placeholder pamflet yang mengganggu.
     * - Tampilkan informasi bahwa buku panduan belum tersedia.
     */
    public function test_detail_lomba_tanpa_pamflet_dan_panduan(): void
    {
        $lomba = Lomba::create([
            'nama_lomba' => 'Lomba Tanpa Berkas Terverifikasi',
            'kategori' => 'Cyber Security',
            'tingkat' => 'Nasional',
            'tipe_peserta' => 'Kelompok',
            'mode_pelaksanaan' => 'Online',
            'penyelenggara' => 'BSSN',
            'tenggat' => now()->addDays(10)->toDateString(),
            'biaya' => 0,
            'tautan_daftar' => 'https://bssn.example.com',
            'deskripsi' => 'Deskripsi.',
            'persyaratan' => 'Persyaratan.',
            'benefit' => 'Benefit.',
            'status_verifikasi' => 'terverifikasi',
            'poster_path' => null,
            'buku_pedoman_path' => null,
        ]);

        $response = $this->get(route('lomba.show', $lomba->id_lomba));
        $response->assertOk();

        // Tidak menampilkan header / card pamflet
        $response->assertDontSee('Pamflet Resmi Lomba');

        // Menampilkan informasi bahwa buku panduan belum tersedia
        $response->assertSee('Buku Panduan Lomba');
        $response->assertSee('Buku panduan lomba (guidebook) belum tersedia untuk kompetisi ini');
        $response->assertSee('Belum tersedia');
    }

    /**
     * 6. Admin dapat mengganti file pamflet dan buku panduan saat edit:
     * - File lama dihapus dari disk.
     * - File baru tersimpan di disk dan terhubung ke lomba.
     */
    public function test_admin_dapat_mengganti_file_pamflet_dan_panduan(): void
    {
        Storage::fake('public');
        $this->loginAdmin();

        $oldPamflet = UploadedFile::fake()->image('old_pamflet.jpg');
        $oldPamfletPath = $oldPamflet->store('lomba/poster', 'public');

        $oldGuide = UploadedFile::fake()->create('old_guide.pdf', 100, 'application/pdf');
        $oldGuidePath = $oldGuide->store('lomba/pedoman', 'public');

        $lomba = Lomba::create([
            'nama_lomba' => 'Lomba Edit Ganti File',
            'kategori' => 'Software Development',
            'tingkat' => 'Nasional',
            'tipe_peserta' => 'Kelompok',
            'mode_pelaksanaan' => 'Online',
            'penyelenggara' => 'Kemdikbud',
            'tenggat' => now()->addDays(20)->toDateString(),
            'biaya' => 0,
            'tautan_daftar' => 'https://example.com/lomba',
            'deskripsi' => 'Deskripsi awal.',
            'persyaratan' => 'Persyaratan awal.',
            'benefit' => 'Benefit awal.',
            'status_verifikasi' => 'terverifikasi',
            'poster_path' => $oldPamfletPath,
            'buku_pedoman_path' => $oldGuidePath,
        ]);

        $newPamflet = UploadedFile::fake()->image('new_pamflet.png');
        $newGuide = UploadedFile::fake()->create('new_guide.pdf', 150, 'application/pdf');

        $response = $this->put(route('admin.lomba.update', $lomba->id_lomba), [
            'nama_lomba' => 'Lomba Edit Ganti File Terupdate',
            'kategori' => $lomba->kategori,
            'tingkat' => $lomba->tingkat,
            'tipe_peserta' => $lomba->tipe_peserta,
            'mode_pelaksanaan' => $lomba->mode_pelaksanaan,
            'penyelenggara' => $lomba->penyelenggara,
            'tenggat' => $lomba->tenggat->toDateString(),
            'biaya' => 0,
            'tautan_daftar' => $lomba->tautan_daftar,
            'deskripsi' => $lomba->deskripsi,
            'persyaratan' => $lomba->persyaratan,
            'benefit' => $lomba->benefit,
            'status_verifikasi' => 'terverifikasi',
            'poster' => $newPamflet,
            'buku_pedoman' => $newGuide,
        ]);

        $response->assertRedirect(route('admin.index', ['tab' => 'lomba']));
        $lomba->refresh();

        // File lama terhapus dari storage
        Storage::disk('public')->assertMissing($oldPamfletPath);
        Storage::disk('public')->assertMissing($oldGuidePath);

        // File baru ada di storage dan database
        Storage::disk('public')->assertExists($lomba->poster_path);
        Storage::disk('public')->assertExists($lomba->buku_pedoman_path);
    }

    /**
     * 7. Admin dapat mempertahankan file lama saat mengedit field lain tanpa upload file baru.
     */
    public function test_file_lama_dipertahankan_jika_tidak_unggah_file_baru(): void
    {
        Storage::fake('public');
        $this->loginAdmin();

        $pamflet = UploadedFile::fake()->image('keep_pamflet.jpg');
        $pamfletPath = $pamflet->store('lomba/poster', 'public');

        $guide = UploadedFile::fake()->create('keep_guide.pdf', 100, 'application/pdf');
        $guidePath = $guide->store('lomba/pedoman', 'public');

        $lomba = Lomba::create([
            'nama_lomba' => 'Lomba Pertahankan File',
            'kategori' => 'Data Science & AI',
            'tingkat' => 'Nasional',
            'tipe_peserta' => 'Individu',
            'mode_pelaksanaan' => 'Online',
            'penyelenggara' => 'Tech Asia',
            'tenggat' => now()->addDays(30)->toDateString(),
            'biaya' => 0,
            'tautan_daftar' => 'https://example.com/keep',
            'deskripsi' => 'Deskripsi asli.',
            'persyaratan' => 'Persyaratan asli.',
            'benefit' => 'Benefit asli.',
            'status_verifikasi' => 'terverifikasi',
            'poster_path' => $pamfletPath,
            'buku_pedoman_path' => $guidePath,
        ]);

        $response = $this->put(route('admin.lomba.update', $lomba->id_lomba), [
            'nama_lomba' => 'Lomba Pertahankan File Updated',
            'kategori' => $lomba->kategori,
            'tingkat' => 'Internasional',
            'tipe_peserta' => $lomba->tipe_peserta,
            'mode_pelaksanaan' => $lomba->mode_pelaksanaan,
            'penyelenggara' => $lomba->penyelenggara,
            'tenggat' => $lomba->tenggat->toDateString(),
            'biaya' => 0,
            'tautan_daftar' => $lomba->tautan_daftar,
            'deskripsi' => 'Deskripsi diedit.',
            'persyaratan' => $lomba->persyaratan,
            'benefit' => $lomba->benefit,
            'status_verifikasi' => 'terverifikasi',
        ]);

        $response->assertRedirect(route('admin.index', ['tab' => 'lomba']));
        $lomba->refresh();

        $this->assertEquals('Lomba Pertahankan File Updated', $lomba->nama_lomba);
        $this->assertEquals($pamfletPath, $lomba->poster_path);
        $this->assertEquals($guidePath, $lomba->buku_pedoman_path);
        Storage::disk('public')->assertExists($pamfletPath);
        Storage::disk('public')->assertExists($guidePath);
    }

    /**
     * 8. Admin dapat menghapus file pamflet dan buku panduan (checkbox hapus atau direct delete).
     */
    public function test_admin_dapat_menghapus_file_yang_sudah_diunggah(): void
    {
        Storage::fake('public');
        $this->loginAdmin();

        $pamflet = UploadedFile::fake()->image('delete_pamflet.jpg');
        $pamfletPath = $pamflet->store('lomba/poster', 'public');

        $guide = UploadedFile::fake()->create('delete_guide.pdf', 100, 'application/pdf');
        $guidePath = $guide->store('lomba/pedoman', 'public');

        $lomba = Lomba::create([
            'nama_lomba' => 'Lomba Hapus Berkas',
            'kategori' => 'Competitive Programming',
            'tingkat' => 'Nasional',
            'tipe_peserta' => 'Kelompok',
            'mode_pelaksanaan' => 'Online',
            'penyelenggara' => 'Himanika',
            'tenggat' => now()->addDays(20)->toDateString(),
            'biaya' => 0,
            'tautan_daftar' => 'https://example.com/del',
            'deskripsi' => 'Deskripsi.',
            'persyaratan' => 'Persyaratan.',
            'benefit' => 'Benefit.',
            'status_verifikasi' => 'terverifikasi',
            'poster_path' => $pamfletPath,
            'buku_pedoman_path' => $guidePath,
        ]);

        // Hapus pamflet & panduan via form update dengan parameter hapus_poster & hapus_buku_pedoman
        $response = $this->put(route('admin.lomba.update', $lomba->id_lomba), [
            'nama_lomba' => $lomba->nama_lomba,
            'kategori' => $lomba->kategori,
            'tingkat' => $lomba->tingkat,
            'tipe_peserta' => $lomba->tipe_peserta,
            'mode_pelaksanaan' => $lomba->mode_pelaksanaan,
            'penyelenggara' => $lomba->penyelenggara,
            'tenggat' => $lomba->tenggat->toDateString(),
            'biaya' => 0,
            'tautan_daftar' => $lomba->tautan_daftar,
            'deskripsi' => $lomba->deskripsi,
            'persyaratan' => $lomba->persyaratan,
            'benefit' => $lomba->benefit,
            'status_verifikasi' => 'terverifikasi',
            'hapus_poster' => 1,
            'hapus_buku_pedoman' => 1,
        ]);

        $response->assertRedirect(route('admin.index', ['tab' => 'lomba']));
        $lomba->refresh();

        $this->assertNull($lomba->poster_path);
        $this->assertNull($lomba->buku_pedoman_path);
        Storage::disk('public')->assertMissing($pamfletPath);
        Storage::disk('public')->assertMissing($guidePath);

        // Uji juga direct delete routes
        $pamflet2 = UploadedFile::fake()->image('direct_pamflet.png');
        $pamfletPath2 = $pamflet2->store('lomba/poster', 'public');
        $guide2 = UploadedFile::fake()->create('direct_guide.pdf', 80, 'application/pdf');
        $guidePath2 = $guide2->store('lomba/pedoman', 'public');

        $lomba->update([
            'poster_path' => $pamfletPath2,
            'buku_pedoman_path' => $guidePath2,
        ]);

        $delPosterRes = $this->delete(route('admin.lomba.destroy_poster', $lomba->id_lomba));
        $delPosterRes->assertRedirect(route('admin.index', ['tab' => 'lomba']));
        $lomba->refresh();
        $this->assertNull($lomba->poster_path);
        Storage::disk('public')->assertMissing($pamfletPath2);

        $delPedomanRes = $this->delete(route('admin.lomba.destroy_pedoman', $lomba->id_lomba));
        $delPedomanRes->assertRedirect(route('admin.index', ['tab' => 'lomba']));
        $lomba->refresh();
        $this->assertNull($lomba->buku_pedoman_path);
        Storage::disk('public')->assertMissing($guidePath2);
    }

    /**
     * 9. Isolasi data: Pamflet dan Buku Panduan hanya tampil pada Detail Lomba yang sesuai.
     */
    public function test_berkas_hanya_muncul_pada_detail_lomba_yang_sesuai(): void
    {
        Storage::fake('public');

        $pamfletA = UploadedFile::fake()->image('pamflet_a.png');
        $pamfletPathA = $pamfletA->store('lomba/poster', 'public');

        $lombaA = Lomba::create([
            'nama_lomba' => 'Kompetisi Alpha JTI 2026',
            'kategori' => 'Software Development',
            'tingkat' => 'Nasional',
            'tipe_peserta' => 'Kelompok',
            'mode_pelaksanaan' => 'Online',
            'penyelenggara' => 'Penyelenggara Alpha',
            'tenggat' => now()->addDays(20)->toDateString(),
            'biaya' => 0,
            'tautan_daftar' => 'https://alpha.example.com',
            'deskripsi' => 'Deskripsi Alpha.',
            'persyaratan' => 'Persyaratan Alpha.',
            'benefit' => 'Benefit Alpha.',
            'status_verifikasi' => 'terverifikasi',
            'poster_path' => $pamfletPathA,
        ]);

        $lombaB = Lomba::create([
            'nama_lomba' => 'Kompetisi Beta JTI 2026',
            'kategori' => 'UI/UX Design',
            'tingkat' => 'Nasional',
            'tipe_peserta' => 'Kelompok',
            'mode_pelaksanaan' => 'Online',
            'penyelenggara' => 'Penyelenggara Beta',
            'tenggat' => now()->addDays(20)->toDateString(),
            'biaya' => 0,
            'tautan_daftar' => 'https://beta.example.com',
            'deskripsi' => 'Deskripsi Beta.',
            'persyaratan' => 'Persyaratan Beta.',
            'benefit' => 'Benefit Beta.',
            'status_verifikasi' => 'terverifikasi',
            'poster_path' => null,
            'buku_pedoman_path' => null,
        ]);

        // Halaman Lomba A memiliki pamflet A
        $resA = $this->get(route('lomba.show', $lombaA->id_lomba));
        $resA->assertOk();
        $resA->assertSee($lombaA->poster_url);

        // Halaman Lomba B tidak menampilkan pamflet A
        $resB = $this->get(route('lomba.show', $lombaB->id_lomba));
        $resB->assertOk();
        $resB->assertDontSee($lombaA->poster_url);
        $resB->assertDontSee('Pamflet Resmi Lomba');
    }
}
