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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ModulTimTest extends TestCase
{
    use RefreshDatabase;

    private function createMahasiswaUser(string $nim, string $name, string $email): User
    {
        Mahasiswa::create([
            'nim' => $nim,
            'nama' => $name,
            'email_kampus' => $email,
            'password' => Hash::make('password123'),
            'prodi' => 'D-IV Sistem Informasi Bisnis',
            'angkatan' => 2024,
        ]);

        return User::create([
            'name' => $name,
            'email' => $email,
            'nim_nip' => $nim,
            'role' => UserRole::Mahasiswa,
            'prodi' => 'D-IV Sistem Informasi Bisnis',
            'angkatan' => 2024,
            'password' => Hash::make('password123'),
        ]);
    }

    private function createSampleLomba(string $nama = 'Gemastik 2026', string $kategori = 'UI/UX Design', string $tingkat = 'Nasional'): Lomba
    {
        return Lomba::create([
            'nama_lomba' => $nama,
            'kategori' => $kategori,
            'tingkat' => $tingkat,
            'penyelenggara' => 'Puspresnas Kemdikbudristek',
            'deskripsi' => 'Kompetisi tahunan bidang TIK untuk mahasiswa Indonesia.',
            'persyaratan' => 'Mahasiswa aktif D4/S1.',
            'tenggat' => now()->addMonths(2)->format('Y-m-d'),
            'biaya' => 0,
            'benefit' => 'Sertifikat dan hadiah pembinaan.',
            'tautan_daftar' => 'https://puspresnas.kemdikbud.go.id',
            'status_verifikasi' => 'terverifikasi',
        ]);
    }

    public function test_tamu_tidak_bisa_mengakses_modul_tim()
    {
        $response = $this->get('/tim');
        $response->assertRedirect('/login');
    }

    public function test_navigasi_mahasiswa_memiliki_empat_menu_utama()
    {
        $user = $this->createMahasiswaUser('244107060001', 'Budi Santoso', 'budi@polinema.ac.id');

        $response = $this->actingAs($user)->get('/tim');

        $response->assertStatus(200);
        $response->assertSee('Dashboard Analitik');
        $response->assertSee('Modul Lomba');
        $response->assertSee('Modul Tim');
        $response->assertSee('Modul Bimbingan');
    }

    public function test_halaman_modul_tim_menampilkan_bagian_a_dan_daftar_tim()
    {
        $user = $this->createMahasiswaUser('244107060002', 'Siti Rahma', 'siti@polinema.ac.id');
        $lomba = $this->createSampleLomba('Hackathon IoT 2026', 'IoT', 'Nasional');

        $tim = Tim::create([
            'nama_tim' => 'Tim Nusantara IoT',
            'id_lomba' => $lomba->id_lomba,
            'nim' => '244107060002',
            'kuota_anggota' => 3,
            'status_tim' => 'disetujui',
        ]);

        $response = $this->actingAs($user)->get('/tim');

        $response->assertStatus(200);
        $response->assertSee('Bagian A &bull; Inisiasi Tim Baru', false);
        $response->assertSee(route('tim.create'));
        $response->assertDontSee('Bagian B &bull; Eksplorasi Tim', false);
        $response->assertDontSee('Cari & Gabung Tim Kompetisi', false);
        $response->assertSee('Daftar Tim Kompetisi Mahasiswa');
        $response->assertSee('Tim Nusantara IoT');
    }

    public function test_modul_lomba_mengarah_ke_modul_tim_untuk_aksi_tim()
    {
        $user = $this->createMahasiswaUser('244107060003', 'Andi Pratama', 'andi@polinema.ac.id');
        $lomba = $this->createSampleLomba('KRI 2026', 'Robotika', 'Nasional');

        $tim = Tim::create([
            'nama_tim' => 'Tim Robotika JTI',
            'id_lomba' => $lomba->id_lomba,
            'nim' => '244107060003',
            'kuota_anggota' => 4,
            'status_tim' => 'disetujui',
        ]);

        $response = $this->actingAs($user)->get(route('lomba.show', $lomba->id_lomba));

        $response->assertStatus(200);
        $response->assertSee(route('tim.create', ['lomba_id' => $lomba->id_lomba]));
        $response->assertSee(route('tim.index', ['q' => $lomba->nama_lomba]));
    }

    public function test_pembentukan_tim_baru_sebagai_ketua_dengan_upload_cv_berhasil()
    {
        Storage::fake('public');
        $user = $this->createMahasiswaUser('244107060004', 'Rizky Ketua', 'rizky@polinema.ac.id');
        $lomba = $this->createSampleLomba();

        $pdfFile = UploadedFile::fake()->create('cv_ketua.pdf', 300, 'application/pdf');

        $response = $this->actingAs($user)->post('/tim/buat', [
            'nama_tim' => 'Tim Pengembang AI',
            'id_lomba' => $lomba->id_lomba,
            'kuota_anggota' => 3,
            'catatan' => 'Tim fokus pada pengembangan kecerdasan buatan.',
            'cv' => $pdfFile,
        ]);

        $tim = Tim::where('nama_tim', 'Tim Pengembang AI')->first();
        $this->assertNotNull($tim);
        $response->assertRedirect(route('bimbingan.index', ['tim_id' => $tim->id_tim]));
        $this->assertDatabaseHas('tim', [
            'nama_tim' => 'Tim Pengembang AI',
            'nim' => '244107060004',
            'id_lomba' => $lomba->id_lomba,
            'status_tim' => 'disetujui',
        ]);
        $this->assertNotNull($tim->file_cv);
        Storage::disk('public')->assertExists($tim->file_cv);
    }

    public function test_pengajuan_anggota_ke_tim_tersedia_dengan_upload_cv_berhasil()
    {
        Storage::fake('public');
        $ketua = $this->createMahasiswaUser('244107060005', 'Ketua Alpha', 'alpha@polinema.ac.id');
        $pelamar = $this->createMahasiswaUser('244107060006', 'Pelamar Beta', 'beta@polinema.ac.id');
        $lomba = $this->createSampleLomba();

        $tim = Tim::create([
            'nama_tim' => 'Tim Desain Juara',
            'id_lomba' => $lomba->id_lomba,
            'nim' => $ketua->nim_nip,
            'kuota_anggota' => 3,
            'status_tim' => 'disetujui',
        ]);

        // Form pengajuan mandiri di /tim/{id}/gabung
        $responseForm = $this->actingAs($pelamar)->get(route('tim.gabung.form', $tim->id_tim));
        $responseForm->assertStatus(200);
        $responseForm->assertSee('Tim Desain Juara');
        $responseForm->assertSee('Unggah Curiculum Vitae (CV)');

        // Submit pengajuan gabung
        $cvPelamar = UploadedFile::fake()->create('cv_pelamar.pdf', 400, 'application/pdf');
        $responseSubmit = $this->actingAs($pelamar)->post(route('tim.gabung', $tim->id_tim), [
            'peran' => 'UI/UX Researcher',
            'catatan' => 'Berpengalaman dalam user interview dan usability testing.',
            'cv' => $cvPelamar,
        ]);

        $responseSubmit->assertRedirect(route('tim.lowongan'));
        $this->assertDatabaseHas('anggota_tim', [
            'id_tim' => $tim->id_tim,
            'nim' => $pelamar->nim_nip,
            'status_gabung' => 'menunggu',
            'peran' => 'UI/UX Researcher',
        ]);

        $anggota = AnggotaTim::where('id_tim', $tim->id_tim)->where('nim', $pelamar->nim_nip)->first();
        $this->assertNotNull($anggota->file_cv);
        Storage::disk('public')->assertExists($anggota->file_cv);
    }

    public function test_halaman_modul_tim_filter_kategori_berfungsi_tanpa_error()
    {
        $user = $this->createMahasiswaUser('244107060007', 'Farhan Kategori', 'farhan@polinema.ac.id');
        $otherUser = $this->createMahasiswaUser('244107060008', 'Other Mahasiswa', 'other@polinema.ac.id');
        $lomba1 = $this->createSampleLomba('Lomba Mobile App', 'Mobile Development', 'Nasional');
        $lomba2 = $this->createSampleLomba('Lomba UI/UX Design', 'UI/UX Design', 'Nasional');

        Tim::create([
            'nama_tim' => 'Tim Flutter Mobile',
            'id_lomba' => $lomba1->id_lomba,
            'nim' => $otherUser->nim_nip,
            'kuota_anggota' => 3,
            'status_tim' => 'disetujui',
        ]);

        Tim::create([
            'nama_tim' => 'Tim Figma Kreatif',
            'id_lomba' => $lomba2->id_lomba,
            'nim' => $user->nim_nip,
            'kuota_anggota' => 3,
            'status_tim' => 'disetujui',
        ]);

        $response = $this->actingAs($user)->get('/tim?kategori=UI%2FUX+Design');

        $response->assertStatus(200);
        $response->assertSee('Tim Figma Kreatif');
        $response->assertDontSee('Tim Flutter Mobile');
    }

    public function test_halaman_modul_tim_menampilkan_empty_state_jika_belum_ada_pengajuan()
    {
        $user = $this->createMahasiswaUser('244107060010', 'Doni Mahasiswa', 'doni@polinema.ac.id');

        $response = $this->actingAs($user)->get('/tim');

        $response->assertStatus(200);
        $response->assertSee('Pengajuan Gabung Tim Saya');
        $response->assertSee('Kamu belum memiliki pengajuan gabung tim.');
    }

    public function test_halaman_modul_tim_menampilkan_pengajuan_dengan_status_menunggu_persetujuan()
    {
        $ketua = $this->createMahasiswaUser('244107060011', 'Ketua Garuda', 'garuda@polinema.ac.id');
        $pelamar = $this->createMahasiswaUser('244107060012', 'Pelamar Elang', 'elang@polinema.ac.id');
        $lomba = $this->createSampleLomba('Gemastik Cyber Security', 'Keamanan Siber', 'Nasional');

        $tim = Tim::create([
            'nama_tim' => 'Tim Garuda Cyber',
            'id_lomba' => $lomba->id_lomba,
            'nim' => $ketua->nim_nip,
            'kuota_anggota' => 3,
            'status_tim' => 'disetujui',
        ]);

        AnggotaTim::create([
            'id_tim' => $tim->id_tim,
            'nim' => $pelamar->nim_nip,
            'status_gabung' => 'menunggu',
            'peran' => 'Penetration Tester',
        ]);

        $response = $this->actingAs($pelamar)->get('/tim');

        $response->assertStatus(200);
        $response->assertSee('Pengajuan Gabung Tim Saya');
        $response->assertSee('Tim Garuda Cyber');
        $response->assertSee('Gemastik Cyber Security');
        $response->assertSee('Menunggu Persetujuan');
        $response->assertSee('Pengajuan kamu sedang menunggu keputusan ketua tim.');
        $response->assertSee('Penetration Tester');
        $response->assertDontSee('Kamu belum memiliki pengajuan gabung tim.');
    }

    public function test_halaman_modul_tim_menampilkan_pengajuan_dengan_status_diterima()
    {
        $ketua = $this->createMahasiswaUser('244107060013', 'Ketua Bintang', 'bintang@polinema.ac.id');
        $pelamar = $this->createMahasiswaUser('244107060014', 'Pelamar Surya', 'surya@polinema.ac.id');
        $lomba = $this->createSampleLomba('LIDM Divisi Poster', 'Inovasi Pembelajaran', 'Nasional');

        $tim = Tim::create([
            'nama_tim' => 'Tim Surya Digital',
            'id_lomba' => $lomba->id_lomba,
            'nim' => $ketua->nim_nip,
            'kuota_anggota' => 3,
            'status_tim' => 'disetujui',
        ]);

        $anggota = AnggotaTim::create([
            'id_tim' => $tim->id_tim,
            'nim' => $pelamar->nim_nip,
            'status_gabung' => 'menunggu',
            'peran' => 'Illustrator Digital',
        ]);

        // Ketua tim menyetujui pengajuan
        $this->actingAs($ketua)->post(route('tim.anggota.respon', $anggota->id_anggota), [
            'status' => 'diterima',
        ]);

        $response = $this->actingAs($pelamar)->get('/tim');

        $response->assertStatus(200);
        $response->assertSee('Pengajuan Gabung Tim Saya');
        $response->assertSee('Tim Surya Digital');
        $response->assertSee('LIDM Divisi Poster');
        $response->assertSee('Diterima');
        $response->assertSee('Pengajuan kamu diterima. Silakan cek status keanggotaan tim kamu.');
        $response->assertSee(route('bimbingan.index', ['tim_id' => $tim->id_tim]));
    }

    public function test_halaman_modul_tim_menampilkan_pengajuan_dengan_status_ditolak()
    {
        $ketua = $this->createMahasiswaUser('244107060015', 'Ketua Angkasa', 'angkasa@polinema.ac.id');
        $pelamar = $this->createMahasiswaUser('244107060016', 'Pelamar Awan', 'awan@polinema.ac.id');
        $lomba = $this->createSampleLomba('KRI Divisi SAR', 'Robotika', 'Nasional');

        $tim = Tim::create([
            'nama_tim' => 'Tim Robot Terbang',
            'id_lomba' => $lomba->id_lomba,
            'nim' => $ketua->nim_nip,
            'kuota_anggota' => 3,
            'status_tim' => 'disetujui',
        ]);

        $anggota = AnggotaTim::create([
            'id_tim' => $tim->id_tim,
            'nim' => $pelamar->nim_nip,
            'status_gabung' => 'menunggu',
            'peran' => 'Hardware Specialist',
        ]);

        // Ketua tim menolak pengajuan
        $this->actingAs($ketua)->post(route('tim.anggota.respon', $anggota->id_anggota), [
            'status' => 'ditolak',
        ]);

        $response = $this->actingAs($pelamar)->get('/tim');

        $response->assertStatus(200);
        $response->assertSee('Pengajuan Gabung Tim Saya');
        $response->assertSee('Tim Robot Terbang');
        $response->assertSee('KRI Divisi SAR');
        $response->assertSee('Ditolak');
        $response->assertSee('Pengajuan kamu ditolak oleh ketua tim.');
    }

    public function test_mahasiswa_hanya_melihat_pengajuan_miliknya_sendiri()
    {
        $ketua = $this->createMahasiswaUser('244107060017', 'Ketua Semesta', 'semesta@polinema.ac.id');
        $mhs1 = $this->createMahasiswaUser('244107060018', 'Mahasiswa Satu', 'mhs1@polinema.ac.id');
        $mhs2 = $this->createMahasiswaUser('244107060019', 'Mahasiswa Dua', 'mhs2@polinema.ac.id');
        $lomba = $this->createSampleLomba('Hackathon Big Data', 'Big Data', 'Nasional');

        $tim1 = Tim::create([
            'nama_tim' => 'Tim BigData One',
            'id_lomba' => $lomba->id_lomba,
            'nim' => $ketua->nim_nip,
            'kuota_anggota' => 4,
            'status_tim' => 'disetujui',
        ]);

        $tim2 = Tim::create([
            'nama_tim' => 'Tim BigData Two',
            'id_lomba' => $lomba->id_lomba,
            'nim' => $ketua->nim_nip,
            'kuota_anggota' => 4,
            'status_tim' => 'disetujui',
        ]);

        // Mhs 1 melamar ke Tim BigData One
        AnggotaTim::create([
            'id_tim' => $tim1->id_tim,
            'nim' => $mhs1->nim_nip,
            'status_gabung' => 'menunggu',
            'peran' => 'Data Analyst',
        ]);

        // Mhs 2 melamar ke Tim BigData Two
        AnggotaTim::create([
            'id_tim' => $tim2->id_tim,
            'nim' => $mhs2->nim_nip,
            'status_gabung' => 'menunggu',
            'peran' => 'Data Engineer',
        ]);

        // Mhs 1 mengecek halaman tim
        $responseMhs1 = $this->actingAs($mhs1)->get('/tim');
        $responseMhs1->assertStatus(200);
        $responseMhs1->assertSee('Tim BigData One');
        $responseMhs1->assertSee('Data Analyst');
        // Tidak boleh melihat pengajuan milik Mhs 2
        $responseMhs1->assertDontSee('Data Engineer');

        // Mhs 2 mengecek halaman tim
        $responseMhs2 = $this->actingAs($mhs2)->get('/tim');
        $responseMhs2->assertStatus(200);
        $responseMhs2->assertSee('Tim BigData Two');
        $responseMhs2->assertSee('Data Engineer');
        // Tidak boleh melihat pengajuan milik Mhs 1
        $responseMhs2->assertDontSee('Data Analyst');
    }

    public function test_mahasiswa_mengajukan_ke_beberapa_tim_melihat_setiap_pengajuan_terpisah()
    {
        $ketuaA = $this->createMahasiswaUser('244107060020', 'Ketua Tim A', 'ketuaa@polinema.ac.id');
        $ketuaB = $this->createMahasiswaUser('244107060021', 'Ketua Tim B', 'ketuab@polinema.ac.id');
        $pelamar = $this->createMahasiswaUser('244107060022', 'Pelamar Multi', 'multi@polinema.ac.id');

        $lomba1 = $this->createSampleLomba('Lomba AI Vision', 'AI', 'Nasional');
        $lomba2 = $this->createSampleLomba('Lomba Web App', 'Web', 'Nasional');

        $timA = Tim::create([
            'nama_tim' => 'Tim Visioner AI',
            'id_lomba' => $lomba1->id_lomba,
            'nim' => $ketuaA->nim_nip,
            'kuota_anggota' => 3,
            'status_tim' => 'disetujui',
        ]);

        $timB = Tim::create([
            'nama_tim' => 'Tim Web Inovatif',
            'id_lomba' => $lomba2->id_lomba,
            'nim' => $ketuaB->nim_nip,
            'kuota_anggota' => 3,
            'status_tim' => 'disetujui',
        ]);

        AnggotaTim::create([
            'id_tim' => $timA->id_tim,
            'nim' => $pelamar->nim_nip,
            'status_gabung' => 'menunggu',
            'peran' => 'ML Engineer',
        ]);

        AnggotaTim::create([
            'id_tim' => $timB->id_tim,
            'nim' => $pelamar->nim_nip,
            'status_gabung' => 'ditolak',
            'peran' => 'Fullstack Dev',
        ]);

        $response = $this->actingAs($pelamar)->get('/tim');

        $response->assertStatus(200);
        $response->assertSee('Pengajuan Gabung Tim Saya');
        $response->assertSee('2 Pengajuan');
        $response->assertSee('Tim Visioner AI');
        $response->assertSee('ML Engineer');
        $response->assertSee('Menunggu Persetujuan');
        $response->assertSee('Tim Web Inovatif');
        $response->assertSee('Fullstack Dev');
        $response->assertSee('Ditolak');
    }

    public function test_tim_yang_dibentuk_sendiri_tidak_masuk_pengajuan_gabung_tim_saya()
    {
        $ketua = $this->createMahasiswaUser('244107060023', 'Ketua Inisiator', 'inisiator@polinema.ac.id');
        $lomba = $this->createSampleLomba('KMHE 2026', 'Energi', 'Nasional');

        $tim = Tim::create([
            'nama_tim' => 'Tim Mobil Hemat Energi',
            'id_lomba' => $lomba->id_lomba,
            'nim' => $ketua->nim_nip,
            'kuota_anggota' => 3,
            'status_tim' => 'disetujui',
        ]);

        AnggotaTim::create([
            'id_tim' => $tim->id_tim,
            'nim' => $ketua->nim_nip,
            'status_gabung' => 'diterima',
            'peran' => 'Ketua Tim',
        ]);

        $response = $this->actingAs($ketua)->get('/tim');

        $response->assertStatus(200);
        // Tim yang dibentuk masuk ke Tim yang Anda Ikuti Saat Ini
        $response->assertSee('Tim yang Anda Ikuti Saat Ini');
        $response->assertSee('Ketua Tim');
        // Tapi pada Pengajuan Gabung Tim Saya, tampil empty state karena belum pernah mengajukan diri ke tim lain
        $response->assertSee('Kamu belum memiliki pengajuan gabung tim.');
    }

    public function test_formulir_pembentukan_tim_gagal_jika_field_wajib_kosong()
    {
        $user = $this->createMahasiswaUser('244107060030', 'User Validasi', 'val1@polinema.ac.id');

        $response = $this->actingAs($user)->from('/tim/buat')->post('/tim/buat', []);

        $response->assertRedirect('/tim/buat');
        $response->assertSessionHasErrors(['nama_tim', 'id_lomba', 'kuota_anggota', 'catatan', 'cv']);
    }

    public function test_formulir_pembentukan_tim_gagal_jika_deskripsi_kosong()
    {
        Storage::fake('public');
        $user = $this->createMahasiswaUser('244107060031', 'Ketua Tanpa Deskripsi', 'nodesc1@polinema.ac.id');
        $lomba = $this->createSampleLomba();
        $pdfFile = UploadedFile::fake()->create('cv_ketua.pdf', 300, 'application/pdf');

        $response = $this->actingAs($user)->from('/tim/buat')->post('/tim/buat', [
            'nama_tim' => 'Tim Tanpa Deskripsi',
            'id_lomba' => $lomba->id_lomba,
            'kuota_anggota' => 3,
            'catatan' => '',
            'cv' => $pdfFile,
        ]);

        $response->assertRedirect('/tim/buat');
        $response->assertSessionHasErrors(['catatan']);
        $this->assertDatabaseMissing('tim', ['nama_tim' => 'Tim Tanpa Deskripsi']);
    }

    public function test_formulir_pembentukan_tim_berhasil_jika_semua_field_wajib_valid()
    {
        Storage::fake('public');
        $user = $this->createMahasiswaUser('244107060032', 'Ketua Lengkap', 'lengkap1@polinema.ac.id');
        $lomba = $this->createSampleLomba();
        $pdfFile = UploadedFile::fake()->create('cv_ketua.pdf', 300, 'application/pdf');

        $response = $this->actingAs($user)->post('/tim/buat', [
            'nama_tim' => 'Tim Sukses Validasi',
            'id_lomba' => $lomba->id_lomba,
            'kuota_anggota' => 4,
            'catatan' => 'Mencari anggota pengembang fullstack dan UI/UX designer.',
            'cv' => $pdfFile,
        ]);

        $tim = Tim::where('nama_tim', 'Tim Sukses Validasi')->first();
        $this->assertNotNull($tim);
        $response->assertRedirect(route('bimbingan.index', ['tim_id' => $tim->id_tim]));
        $this->assertDatabaseHas('tim', [
            'nama_tim' => 'Tim Sukses Validasi',
            'nim' => $user->nim_nip,
        ]);
    }

    public function test_formulir_pengajuan_anggota_gagal_jika_field_wajib_kosong()
    {
        $ketua = $this->createMahasiswaUser('244107060033', 'Ketua Tim Sasaran', 'ketuasasaran@polinema.ac.id');
        $pelamar = $this->createMahasiswaUser('244107060034', 'Pelamar Kosong', 'kosong@polinema.ac.id');
        $lomba = $this->createSampleLomba();

        $tim = Tim::create([
            'nama_tim' => 'Tim Uji Form Kosong',
            'id_lomba' => $lomba->id_lomba,
            'nim' => $ketua->nim_nip,
            'kuota_anggota' => 3,
            'status_tim' => 'disetujui',
        ]);

        $response = $this->actingAs($pelamar)->from(route('tim.gabung.form', $tim->id_tim))->post(route('tim.gabung', $tim->id_tim), []);

        $response->assertRedirect(route('tim.gabung.form', $tim->id_tim));
        $response->assertSessionHasErrors(['peran', 'catatan', 'cv']);
    }

    public function test_formulir_pengajuan_anggota_gagal_jika_deskripsi_kosong()
    {
        Storage::fake('public');
        $ketua = $this->createMahasiswaUser('244107060035', 'Ketua Tim Desain', 'ketuadesain@polinema.ac.id');
        $pelamar = $this->createMahasiswaUser('244107060036', 'Pelamar Tanpa Catatan', 'nodesc2@polinema.ac.id');
        $lomba = $this->createSampleLomba();

        $tim = Tim::create([
            'nama_tim' => 'Tim Uji Deskripsi Anggota',
            'id_lomba' => $lomba->id_lomba,
            'nim' => $ketua->nim_nip,
            'kuota_anggota' => 3,
            'status_tim' => 'disetujui',
        ]);

        $cvFile = UploadedFile::fake()->create('cv_pelamar.pdf', 300, 'application/pdf');

        $response = $this->actingAs($pelamar)->from(route('tim.gabung.form', $tim->id_tim))->post(route('tim.gabung', $tim->id_tim), [
            'peran' => 'Frontend Developer',
            'catatan' => '',
            'cv' => $cvFile,
        ]);

        $response->assertRedirect(route('tim.gabung.form', $tim->id_tim));
        $response->assertSessionHasErrors(['catatan']);
        $this->assertDatabaseMissing('anggota_tim', [
            'id_tim' => $tim->id_tim,
            'nim' => $pelamar->nim_nip,
        ]);
    }

    public function test_formulir_pengajuan_anggota_berhasil_jika_semua_field_wajib_valid()
    {
        Storage::fake('public');
        $ketua = $this->createMahasiswaUser('244107060037', 'Ketua Tim Valid', 'ketuavalid@polinema.ac.id');
        $pelamar = $this->createMahasiswaUser('244107060038', 'Pelamar Lengkap', 'lengkap2@polinema.ac.id');
        $lomba = $this->createSampleLomba();

        $tim = Tim::create([
            'nama_tim' => 'Tim Siap Bertanding',
            'id_lomba' => $lomba->id_lomba,
            'nim' => $ketua->nim_nip,
            'kuota_anggota' => 3,
            'status_tim' => 'disetujui',
        ]);

        $cvFile = UploadedFile::fake()->create('cv_pelamar.pdf', 350, 'application/pdf');

        $response = $this->actingAs($pelamar)->post(route('tim.gabung', $tim->id_tim), [
            'peran' => 'Backend Developer',
            'catatan' => 'Memiliki pengalaman REST API Laravel dan basis data PostgreSQL.',
            'cv' => $cvFile,
        ]);

        $response->assertRedirect(route('tim.lowongan'));
        $this->assertDatabaseHas('anggota_tim', [
            'id_tim' => $tim->id_tim,
            'nim' => $pelamar->nim_nip,
            'status_gabung' => 'menunggu',
            'peran' => 'Backend Developer',
        ]);
    }
}
