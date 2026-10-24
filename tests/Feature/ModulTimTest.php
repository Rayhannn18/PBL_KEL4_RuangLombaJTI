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

    public function test_halaman_modul_tim_menampilkan_bagian_a_dan_bagian_b()
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
        $response->assertSee('Bagian B &bull; Eksplorasi Tim', false);
        $response->assertSee('Cari & Gabung Tim Kompetisi', false);
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
        $lomba1 = $this->createSampleLomba('Lomba Mobile App', 'Mobile Development', 'Nasional');
        $lomba2 = $this->createSampleLomba('Lomba UI/UX Design', 'UI/UX Design', 'Nasional');

        Tim::create([
            'nama_tim' => 'Tim Flutter Mobile',
            'id_lomba' => $lomba1->id_lomba,
            'nim' => $user->nim_nip,
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
        $response->assertSee('value="UI/UX Design"', false);
        $response->assertSee('selected', false);
    }
}
