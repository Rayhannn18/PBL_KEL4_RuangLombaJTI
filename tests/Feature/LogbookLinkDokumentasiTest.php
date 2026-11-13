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

class LogbookLinkDokumentasiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
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

    /**
     * @return array{0: PengajuanBimbingan, 1: Mahasiswa, 2: Dosen}
     */
    private function createActiveBimbingan(): array
    {
        $dosen = Dosen::first();
        $ketua = Mahasiswa::create([
            'nim' => '24410706'.rand(1000, 9999),
            'nama' => 'Ketua Logbook GDrive '.uniqid(),
            'email_kampus' => 'ketua_gdrive_'.uniqid().'@student.polinema.ac.id',
            'password' => Hash::make('password123'),
            'prodi' => 'D-IV Teknik Informatika',
            'angkatan' => 2024,
        ]);
        $lomba = Lomba::first();

        $tim = Tim::create([
            'nama_tim' => 'Tim GDrive '.uniqid(),
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

        return [$pengajuan, $ketua, $dosen];
    }

    public function test_ketua_tim_dapat_menyimpan_logbook_dengan_link_google_drive(): void
    {
        [$pengajuan, $ketua, $dosen] = $this->createActiveBimbingan();
        $this->actingAsMahasiswa($ketua);

        $gdriveUrl = 'https://drive.google.com/drive/folders/1AbCdEfGhIjKlMnOpQrStUvWxYz';

        $response = $this->post(route('bimbingan.logbook.store'), [
            'id_pengajuan' => $pengajuan->id_pengajuan,
            'tanggal' => now()->toDateString(),
            'materi' => 'Review dokumentasi progres dan foto bimbingan',
            'tindak_lanjut' => 'Menyempurnakan laporan akhir',
            'link_dokumentasi' => $gdriveUrl,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('logbook', [
            'id_pengajuan' => $pengajuan->id_pengajuan,
            'materi' => 'Review dokumentasi progres dan foto bimbingan',
            'link_dokumentasi' => $gdriveUrl,
        ]);

        // Pastikan tombol Lihat Dokumentasi (Google Drive) muncul di halaman bimbingan mahasiswa & dosen
        $mhsPage = $this->get(route('bimbingan.index', ['tim_id' => $pengajuan->id_tim]));
        $mhsPage->assertOk();
        $mhsPage->assertSee($gdriveUrl);
        $mhsPage->assertSee('Lihat Dokumentasi (Google Drive)');

        $this->actingAsDosen($dosen);
        $dosenPage = $this->get(route('bimbingan.index'));
        $dosenPage->assertOk();
        $dosenPage->assertSee($gdriveUrl);
    }

    public function test_ketua_tim_dapat_menyimpan_logbook_tanpa_link_dokumentasi(): void
    {
        [$pengajuan, $ketua] = $this->createActiveBimbingan();
        $this->actingAsMahasiswa($ketua);

        $response = $this->post(route('bimbingan.logbook.store'), [
            'id_pengajuan' => $pengajuan->id_pengajuan,
            'tanggal' => now()->toDateString(),
            'materi' => 'Konsultasi tanpa lampiran link GDrive',
            'tindak_lanjut' => 'Lanjut pengerjaan modul',
            'link_dokumentasi' => '',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('logbook', [
            'id_pengajuan' => $pengajuan->id_pengajuan,
            'materi' => 'Konsultasi tanpa lampiran link GDrive',
            'link_dokumentasi' => null,
        ]);
    }

    public function test_validasi_menolak_link_dokumentasi_yang_bukan_url_https_valid(): void
    {
        [$pengajuan, $ketua] = $this->createActiveBimbingan();
        $this->actingAsMahasiswa($ketua);

        $response = $this->post(route('bimbingan.logbook.store'), [
            'id_pengajuan' => $pengajuan->id_pengajuan,
            'tanggal' => now()->toDateString(),
            'materi' => 'Konsultasi dengan link tidak valid',
            'link_dokumentasi' => 'bukan-url-valid',
        ]);

        $response->assertSessionHasErrors('link_dokumentasi');
    }

    public function test_ketua_tim_dapat_memperbarui_link_google_drive_pada_logbook_revisi(): void
    {
        [$pengajuan, $ketua] = $this->createActiveBimbingan();
        $this->actingAsMahasiswa($ketua);

        $logbook = Logbook::create([
            'id_pengajuan' => $pengajuan->id_pengajuan,
            'tanggal' => now()->toDateString(),
            'materi' => 'Materi awal sebelum revisi',
            'tindak_lanjut' => 'Tindak lanjut awal',
            'link_dokumentasi' => null,
            'status_validasi' => 'revisi',
            'catatan_dosen' => 'Mohon lampirkan bukti foto di Google Drive.',
        ]);

        $updatedGdriveUrl = 'https://drive.google.com/file/d/1XyZ987654321/view?usp=sharing';

        $response = $this->put(route('bimbingan.logbook.update', $logbook->id_logbook), [
            'tanggal' => now()->toDateString(),
            'materi' => 'Materi setelah dilengkapi dokumentasi GDrive',
            'tindak_lanjut' => 'Tindak lanjut diperbarui',
            'link_dokumentasi' => $updatedGdriveUrl,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('logbook', [
            'id_logbook' => $logbook->id_logbook,
            'link_dokumentasi' => $updatedGdriveUrl,
            'status_validasi' => 'menunggu',
        ]);
    }
}
