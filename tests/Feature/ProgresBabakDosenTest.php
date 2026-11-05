<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Dosen;
use App\Models\Lomba;
use App\Models\Mahasiswa;
use App\Models\PengajuanBimbingan;
use App\Models\ProgresBabak;
use App\Models\Tim;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProgresBabakDosenTest extends TestCase
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

    private function createBimbinganWithProgres(Dosen $dosen, array $progresData = []): array
    {
        $ketua = Mahasiswa::first();
        $lomba = Lomba::first();

        $tim = Tim::create([
            'nama_tim' => 'Tim Progres Test '.uniqid(),
            'id_lomba' => $lomba->id_lomba,
            'nim' => $ketua->nim,
            'kuota_anggota' => 3,
            'status_tim' => 'disetujui',
        ]);

        $pengajuan = PengajuanBimbingan::create([
            'id_tim' => $tim->id_tim,
            'nidn' => $dosen->nidn,
            'tgl_pengajuan' => now(),
            'status' => 'disetujui',
        ]);

        $progres = ProgresBabak::create(array_merge([
            'id_pengajuan' => $pengajuan->id_pengajuan,
            'babak' => 'Babak Penyisihan',
            'tanggal_update' => now(),
            'hasil_akhir' => 'Lolos ke Semifinal',
            'status_setuju' => 'menunggu',
            'catatan_dosen' => null,
        ], $progresData));

        return [$pengajuan, $progres, $ketua];
    }

    /**
     * Mahasiswa tim bimbingan dapat melaporkan capaian progres babak kompetisi
     */
    public function test_mahasiswa_dapat_melaporkan_capaian_progres_babak(): void
    {
        $dosen = Dosen::first();
        [$pengajuan, , $ketua] = $this->createBimbinganWithProgres($dosen);

        $this->actingAsMahasiswa($ketua);

        $response = $this->post(route('bimbingan.progres.store'), [
            'id_pengajuan' => $pengajuan->id_pengajuan,
            'babak' => 'Babak Semifinal',
            'tanggal_update' => now()->format('Y-m-d'),
            'hasil_akhir' => 'Juara Harapan 1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('progres_babak', [
            'id_pengajuan' => $pengajuan->id_pengajuan,
            'babak' => 'Babak Semifinal',
            'hasil_akhir' => 'Juara Harapan 1',
            'status_setuju' => 'menunggu',
        ]);
    }

    /**
     * Mahasiswa luar tim tidak dapat melaporkan progres babak
     */
    public function test_mahasiswa_luar_tim_ditolak_melaporkan_progres_babak(): void
    {
        $dosen = Dosen::first();
        [$pengajuan, , $ketua] = $this->createBimbinganWithProgres($dosen);

        $mahasiswaLuar = Mahasiswa::where('nim', '!=', $ketua->nim)->first();
        $this->actingAsMahasiswa($mahasiswaLuar);

        $response = $this->post(route('bimbingan.progres.store'), [
            'id_pengajuan' => $pengajuan->id_pengajuan,
            'babak' => 'Babak Final',
            'tanggal_update' => now()->format('Y-m-d'),
            'hasil_akhir' => 'Juara 1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Anda bukan anggota resmi dari tim ini.');
    }

    /**
     * Anggota tim biasa (bukan Ketua Tim) ditolak saat melaporkan progres babak
     */
    public function test_anggota_biasa_ditolak_melaporkan_progres_babak(): void
    {
        $dosen = Dosen::first();
        [$pengajuan, , $ketua] = $this->createBimbinganWithProgres($dosen);

        $anggota = Mahasiswa::where('nim', '!=', $ketua->nim)->first();
        \App\Models\AnggotaTim::create([
            'id_tim' => $pengajuan->id_tim,
            'nim' => $anggota->nim,
            'status_gabung' => 'diterima',
            'peran' => 'Anggota Tim',
        ]);

        $this->actingAsMahasiswa($anggota);

        $response = $this->post(route('bimbingan.progres.store'), [
            'id_pengajuan' => $pengajuan->id_pengajuan,
            'babak' => 'Babak Final',
            'tanggal_update' => now()->format('Y-m-d'),
            'hasil_akhir' => 'Juara 1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Akses ditolak. Hanya Ketua Tim dengan status resmi yang berhak melaporkan progres babak kompetisi.');
    }

    /**
     * Dosen pembimbing dapat menyetujui progres babak tim bimbingannya
     */
    public function test_dosen_pembimbing_dapat_menyetujui_progres_babak(): void
    {
        $dosen = Dosen::first();
        $this->actingAsDosen($dosen);
        [, $progres] = $this->createBimbinganWithProgres($dosen);

        $response = $this->post(route('bimbingan.progres.respon', $progres->id_progres), [
            'status_setuju' => 'disetujui',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $progres->refresh();
        $this->assertSame('disetujui', $progres->status_setuju);
    }

    /**
     * Dosen pembimbing dapat menolak progres babak dengan menyertakan catatan evaluasi
     */
    public function test_dosen_pembimbing_dapat_menolak_progres_babak_dengan_catatan(): void
    {
        $dosen = Dosen::first();
        $this->actingAsDosen($dosen);
        [, $progres] = $this->createBimbinganWithProgres($dosen);

        $response = $this->post(route('bimbingan.progres.respon', $progres->id_progres), [
            'status_setuju' => 'ditolak',
            'catatan_dosen' => 'Dokumen lampiran sertifikat dan demo produk belum valid.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $progres->refresh();
        $this->assertSame('ditolak', $progres->status_setuju);
        $this->assertSame('Dokumen lampiran sertifikat dan demo produk belum valid.', $progres->catatan_dosen);
    }

    /**
     * Menolak progres babak tanpa catatan dosen gagal validasi
     */
    public function test_menolak_progres_babak_tanpa_catatan_dosen_gagal_validasi(): void
    {
        $dosen = Dosen::first();
        $this->actingAsDosen($dosen);
        [, $progres] = $this->createBimbinganWithProgres($dosen);

        $response = $this->post(route('bimbingan.progres.respon', $progres->id_progres), [
            'status_setuju' => 'ditolak',
            'catatan_dosen' => '',
        ]);

        $response->assertSessionHasErrors('catatan_dosen');

        $progres->refresh();
        $this->assertSame('menunggu', $progres->status_setuju);
    }

    /**
     * Dosen lain yang bukan pembimbing tim ditolak saat memvalidasi progres babak
     */
    public function test_dosen_bukan_pembimbing_ditolak_merespon_progres_babak(): void
    {
        $dosenPembimbing = Dosen::first();
        $dosenLain = Dosen::where('nidn', '!=', $dosenPembimbing->nidn)->first();

        $this->actingAsDosen($dosenLain);
        [, $progres] = $this->createBimbinganWithProgres($dosenPembimbing);

        $response = $this->post(route('bimbingan.progres.respon', $progres->id_progres), [
            'status_setuju' => 'disetujui',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Anda hanya dapat memvalidasi progres babak untuk tim bimbingan Anda.');

        $progres->refresh();
        $this->assertSame('menunggu', $progres->status_setuju);
    }

    /**
     * Mahasiswa dapat melihat status verifikasi dan catatan evaluasi dosen di view bimbingan
     */
    public function test_mahasiswa_melihat_status_dan_catatan_evaluasi_progres_babak(): void
    {
        $dosen = Dosen::first();
        [$pengajuan, $progres, $ketua] = $this->createBimbinganWithProgres($dosen, [
            'status_setuju' => 'ditolak',
            'catatan_dosen' => 'Tolong lampirkan bukti pengumuman resmi babak semifinal.',
        ]);

        $this->actingAsMahasiswa($ketua);

        $response = $this->get(route('bimbingan.index', ['tim_id' => $pengajuan->id_tim]));

        $response->assertStatus(200);
        $response->assertSee('Ditolak');
        $response->assertSee('Tolong lampirkan bukti pengumuman resmi babak semifinal.');
    }
}
