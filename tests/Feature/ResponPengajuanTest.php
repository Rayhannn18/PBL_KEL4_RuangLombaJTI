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
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ResponPengajuanTest extends TestCase
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

    private function createPengajuan(Dosen $dosen, string $status = 'menunggu'): PengajuanBimbingan
    {
        $mahasiswa = Mahasiswa::first();
        $lomba = Lomba::first();

        $tim = Tim::create([
            'nama_tim' => 'Tim Pengujian Respon '.uniqid(),
            'id_lomba' => $lomba->id_lomba,
            'nim' => $mahasiswa->nim,
            'kuota_anggota' => 3,
            'status' => 'disetujui',
        ]);

        return PengajuanBimbingan::create([
            'id_tim' => $tim->id_tim,
            'nidn' => $dosen->nidn,
            'tgl_pengajuan' => now(),
            'status' => $status,
            'catatan' => null,
        ]);
    }

    /**
     * Dosen yang dituju dapat menyetujui pengajuan bimbingan jika kuota tersedia
     */
    public function test_dosen_bisa_menyetujui_pengajuan_bimbingan_jika_kuota_tersedia(): void
    {
        $dosen = Dosen::first();
        $pengajuan = $this->createPengajuan($dosen, 'menunggu');

        $this->actingAsDosen($dosen);

        $response = $this->post(route('bimbingan.pengajuan.respon', $pengajuan->id_pengajuan), [
            'status' => 'disetujui',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $pengajuan->refresh();
        $this->assertSame('disetujui', $pengajuan->status);
    }

    /**
     * Dosen dapat menolak pengajuan bimbingan dengan menyertakan alasan
     */
    public function test_dosen_bisa_menolak_pengajuan_bimbingan_dengan_alasan(): void
    {
        $dosen = Dosen::first();
        $pengajuan = $this->createPengajuan($dosen, 'menunggu');

        $this->actingAsDosen($dosen);

        $alasan = 'Maaf, topik lomba ini di luar fokus riset saya, silakan ajukan ke pembimbing bidang AI.';

        $response = $this->post(route('bimbingan.pengajuan.respon', $pengajuan->id_pengajuan), [
            'status' => 'ditolak',
            'catatan' => $alasan,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $pengajuan->refresh();
        $this->assertSame('ditolak', $pengajuan->status);
        $this->assertSame($alasan, $pengajuan->catatan);
    }

    /**
     * Penolakan pengajuan bimbingan gagal bila alasan penolakan kosong
     */
    public function test_penolakan_pengajuan_bimbingan_gagal_jika_alasan_kosong(): void
    {
        $dosen = Dosen::first();
        $pengajuan = $this->createPengajuan($dosen, 'menunggu');

        $this->actingAsDosen($dosen);

        $response = $this->post(route('bimbingan.pengajuan.respon', $pengajuan->id_pengajuan), [
            'status' => 'ditolak',
            'catatan' => '',
        ]);

        $response->assertSessionHasErrors('catatan');

        $pengajuan->refresh();
        $this->assertSame('menunggu', $pengajuan->status);
    }

    /**
     * Penolakan pengajuan bimbingan gagal bila alasan melebihi 500 karakter
     */
    public function test_penolakan_pengajuan_bimbingan_gagal_jika_alasan_melebihi_500_karakter(): void
    {
        $dosen = Dosen::first();
        $pengajuan = $this->createPengajuan($dosen, 'menunggu');

        $this->actingAsDosen($dosen);

        $response = $this->post(route('bimbingan.pengajuan.respon', $pengajuan->id_pengajuan), [
            'status' => 'ditolak',
            'catatan' => str_repeat('a', 501),
        ]);

        $response->assertSessionHasErrors('catatan');

        $pengajuan->refresh();
        $this->assertSame('menunggu', $pengajuan->status);
    }

    /**
     * Persetujuan pengajuan gagal bila kuota bimbingan dosen sudah habis
     */
    public function test_persetujuan_pengajuan_gagal_jika_kuota_dosen_sudah_penuh(): void
    {
        // Buat dosen khusus dengan kuota 1
        $dosen = Dosen::create([
            'nidn' => '0099887766',
            'nama' => 'Dosen Kuota Terbatas, M.T.',
            'email_kampus' => 'terbatas@polinema.ac.id',
            'password' => Hash::make('password123'),
            'bidang_keahlian' => 'Data Science',
            'kuota_bimbingan' => 1,
        ]);

        // Berikan 1 bimbingan yang sudah disetujui (kuota habis)
        $this->createPengajuan($dosen, 'disetujui');

        // Pengajuan baru berstatus menunggu
        $pengajuanBaru = $this->createPengajuan($dosen, 'menunggu');

        $this->actingAsDosen($dosen);

        $response = $this->post(route('bimbingan.pengajuan.respon', $pengajuanBaru->id_pengajuan), [
            'status' => 'disetujui',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $pengajuanBaru->refresh();
        $this->assertSame('menunggu', $pengajuanBaru->status);
    }

    /**
     * Mencegah respon ganda bila pengajuan sudah disetujui atau ditolak sebelumnya
     */
    public function test_cegah_respon_ganda_pada_pengajuan_yang_sudah_direspons(): void
    {
        $dosen = Dosen::first();
        $pengajuan = $this->createPengajuan($dosen, 'disetujui');

        $this->actingAsDosen($dosen);

        $response = $this->post(route('bimbingan.pengajuan.respon', $pengajuan->id_pengajuan), [
            'status' => 'ditolak',
            'catatan' => 'Mencoba tolak padahal sudah setuju',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $pengajuan->refresh();
        $this->assertSame('disetujui', $pengajuan->status);
    }

    /**
     * Dosen lain yang tidak dituju tidak berhak merespons pengajuan
     */
    public function test_dosen_lain_tidak_berhak_merespons_pengajuan(): void
    {
        $dosenA = Dosen::first();
        $dosenB = Dosen::skip(1)->first();

        // Pengajuan ditujukan untuk Dosen A
        $pengajuan = $this->createPengajuan($dosenA, 'menunggu');

        // Dosen B mencoba merespons
        $this->actingAsDosen($dosenB);

        $response = $this->post(route('bimbingan.pengajuan.respon', $pengajuan->id_pengajuan), [
            'status' => 'disetujui',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $pengajuan->refresh();
        $this->assertSame('menunggu', $pengajuan->status);
    }
}
