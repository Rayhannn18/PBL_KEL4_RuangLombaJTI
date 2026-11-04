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

class LogbookFeedbackTest extends TestCase
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

    private function createBimbinganWithLogbook(Dosen $dosen, array $logbookData = []): array
    {
        $ketua = Mahasiswa::create([
            'nim' => '24410706'.rand(1000, 9999),
            'nama' => 'Ketua Test '.uniqid(),
            'email_kampus' => 'ketua_'.uniqid().'@student.polinema.ac.id',
            'password' => Hash::make('password123'),
            'prodi' => 'D-IV Teknik Informatika',
            'angkatan' => 2024,
        ]);
        $lomba = Lomba::first();

        $tim = Tim::create([
            'nama_tim' => 'Tim Logbook Test '.uniqid(),
            'id_lomba' => $lomba->id_lomba,
            'nim' => $ketua->nim,
            'kuota_anggota' => 3,
            'status_tim' => 'disetujui',
        ]);

        AnggotaTim::create([
            'id_tim' => $tim->id_tim,
            'nim' => $ketua->nim,
            'status_gabung' => 'diterima',
            'peran' => 'Ketua Tim',
        ]);

        $pengajuan = PengajuanBimbingan::create([
            'id_tim' => $tim->id_tim,
            'nidn' => $dosen->nidn,
            'tgl_pengajuan' => now(),
            'status' => 'disetujui',
        ]);

        $logbook = Logbook::create(array_merge([
            'id_pengajuan' => $pengajuan->id_pengajuan,
            'tanggal' => now(),
            'materi' => 'Konsultasi Perancangan ERD dan Arsitektur Database',
            'tindak_lanjut' => 'Normalisasi tabel hingga 3NF',
            'status_validasi' => 'menunggu',
            'catatan_dosen' => null,
        ], $logbookData));

        return [$pengajuan, $logbook, $ketua];
    }

    /**
     * Dosen pembimbing dapat menyetujui logbook mahasiswa
     */
    public function test_dosen_pembimbing_dapat_menyetujui_logbook_mahasiswa(): void
    {
        $dosen = Dosen::first();
        $this->actingAsDosen($dosen);
        [, $logbook] = $this->createBimbinganWithLogbook($dosen);

        $response = $this->post(route('bimbingan.logbook.validasi', $logbook->id_logbook), [
            'status_validasi' => 'disetujui',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $logbook->refresh();
        $this->assertSame('disetujui', $logbook->status_validasi);
    }

    /**
     * Dosen pembimbing dapat meminta revisi logbook dengan menyertakan catatan
     */
    public function test_dosen_pembimbing_dapat_meminta_revisi_logbook_dengan_catatan(): void
    {
        $dosen = Dosen::first();
        $this->actingAsDosen($dosen);
        [, $logbook] = $this->createBimbinganWithLogbook($dosen);

        $response = $this->post(route('bimbingan.logbook.validasi', $logbook->id_logbook), [
            'status_validasi' => 'revisi',
            'catatan_dosen' => 'Harap sertakan penjelasan relasi Many to Many dan indeks performa.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $logbook->refresh();
        $this->assertSame('revisi', $logbook->status_validasi);
        $this->assertSame('Harap sertakan penjelasan relasi Many to Many dan indeks performa.', $logbook->catatan_dosen);
    }

    /**
     * Validasi gagal jika dosen memilih revisi tetapi mengosongkan catatan dosen
     */
    public function test_meminta_revisi_tanpa_catatan_dosen_gagal_validasi(): void
    {
        $dosen = Dosen::first();
        $this->actingAsDosen($dosen);
        [, $logbook] = $this->createBimbinganWithLogbook($dosen);

        $response = $this->post(route('bimbingan.logbook.validasi', $logbook->id_logbook), [
            'status_validasi' => 'revisi',
            'catatan_dosen' => '',
        ]);

        $response->assertSessionHasErrors('catatan_dosen');

        $logbook->refresh();
        $this->assertSame('menunggu', $logbook->status_validasi);
    }

    /**
     * Dosen lain yang bukan pembimbing tim ditolak saat memvalidasi logbook
     */
    public function test_dosen_bukan_pembimbing_tim_ditolak_saat_validasi_logbook(): void
    {
        $dosenPembimbing = Dosen::first();
        $dosenLain = Dosen::where('nidn', '!=', $dosenPembimbing->nidn)->first();

        $this->actingAsDosen($dosenLain);
        [, $logbook] = $this->createBimbinganWithLogbook($dosenPembimbing);

        $response = $this->post(route('bimbingan.logbook.validasi', $logbook->id_logbook), [
            'status_validasi' => 'disetujui',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Anda hanya dapat memvalidasi logbook untuk tim bimbingan Anda.');

        $logbook->refresh();
        $this->assertSame('menunggu', $logbook->status_validasi);
    }

    /**
     * Mahasiswa melihat catatan revisi dari dosen di view bimbingan
     */
    public function test_mahasiswa_dapat_melihat_catatan_revisi_dosen_di_halaman_bimbingan(): void
    {
        $dosen = Dosen::first();
        [, $logbook, $ketua] = $this->createBimbinganWithLogbook($dosen, [
            'status_validasi' => 'revisi',
            'catatan_dosen' => 'Diagram konteks belum sesuai spesifikasi.',
        ]);

        $this->actingAsMahasiswa($ketua);

        $response = $this->get(route('bimbingan.index', ['tim_id' => $logbook->pengajuan->id_tim]));

        $response->assertStatus(200);
        $response->assertSee('Perlu Revisi');
        $response->assertSee('Diagram konteks belum sesuai spesifikasi.');
        $response->assertSee('Perbaiki Logbook');
    }

    /**
     * Mahasiswa tim dapat memperbarui logbook berstatus revisi dan status kembali menunggu
     */
    public function test_mahasiswa_tim_dapat_memperbarui_logbook_revisi(): void
    {
        $dosen = Dosen::first();
        [, $logbook, $ketua] = $this->createBimbinganWithLogbook($dosen, [
            'status_validasi' => 'revisi',
            'catatan_dosen' => 'Perbaiki diagram konteks',
        ]);

        $this->actingAsMahasiswa($ketua);

        $response = $this->put(route('bimbingan.logbook.update', $logbook->id_logbook), [
            'tanggal' => now()->format('Y-m-d'),
            'materi' => 'Konsultasi Perancangan ERD (Telah diperbaiki sesuai catatan dosen)',
            'tindak_lanjut' => 'Menambahkan relasi pivot table dan foreign key',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $logbook->refresh();
        $this->assertSame('Konsultasi Perancangan ERD (Telah diperbaiki sesuai catatan dosen)', $logbook->materi);
        $this->assertSame('menunggu', $logbook->status_validasi);
    }

    /**
     * Mahasiswa yang bukan anggota tim ditolak saat mencoba memperbarui logbook
     */
    public function test_mahasiswa_luar_tim_tidak_bisa_memperbarui_logbook(): void
    {
        $dosen = Dosen::first();
        [, $logbook, $ketua] = $this->createBimbinganWithLogbook($dosen, [
            'status_validasi' => 'revisi',
        ]);

        $mahasiswaLuar = Mahasiswa::where('nim', '!=', $ketua->nim)->first();
        $this->actingAsMahasiswa($mahasiswaLuar);

        $response = $this->put(route('bimbingan.logbook.update', $logbook->id_logbook), [
            'tanggal' => now()->format('Y-m-d'),
            'materi' => 'Perubahan ilegal oleh user lain',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Anda bukan anggota resmi dari tim ini.');
    }

    /**
     * Logbook yang sudah disetujui tidak dapat diubah oleh mahasiswa
     */
    public function test_logbook_yang_sudah_disetujui_tidak_dapat_diubah_mahasiswa(): void
    {
        $dosen = Dosen::first();
        [, $logbook, $ketua] = $this->createBimbinganWithLogbook($dosen, [
            'status_validasi' => 'disetujui',
        ]);

        $this->actingAsMahasiswa($ketua);

        $response = $this->put(route('bimbingan.logbook.update', $logbook->id_logbook), [
            'tanggal' => now()->format('Y-m-d'),
            'materi' => 'Percobaan ubah materi setelah disetujui',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Logbook yang sudah disetujui oleh Dosen Pembimbing tidak dapat diubah.');
    }
}
