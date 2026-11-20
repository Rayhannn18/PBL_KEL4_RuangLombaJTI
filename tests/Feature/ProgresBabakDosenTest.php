<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AnggotaTim;
use App\Models\Dosen;
use App\Models\Lomba;
use App\Models\Mahasiswa;
use App\Models\PengajuanBimbingan;
use App\Models\ProgresBabak;
use App\Models\Tim;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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
                'nim_nip' => $d->nip,
                'role' => UserRole::Dosen,
                'password' => Hash::make('password123'),
            ]
        );

        $this->actingAs($user);
        session([
            'auth_user' => [
                'id' => $d->nip,
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
            'nip' => $dosen->nip,
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
            'babak' => 'Semifinal',
            'tanggal_update' => now()->format('Y-m-d'),
            'hasil_akhir' => 'Lolos ke babak berikutnya',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('progres_babak', [
            'id_pengajuan' => $pengajuan->id_pengajuan,
            'babak' => 'Semifinal',
            'hasil_akhir' => 'Lolos ke babak berikutnya',
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
            'babak' => 'Penyisihan',
            'tanggal_update' => now()->format('Y-m-d'),
            'hasil_akhir' => 'Tidak lolos',
        ]);

        $response->assertForbidden();
    }

    /**
     * Anggota tim biasa (bukan Ketua Tim) ditolak saat melaporkan progres babak
     */
    public function test_anggota_biasa_ditolak_melaporkan_progres_babak(): void
    {
        $dosen = Dosen::first();
        [$pengajuan, , $ketua] = $this->createBimbinganWithProgres($dosen);

        $anggota = Mahasiswa::where('nim', '!=', $ketua->nim)->first();
        AnggotaTim::create([
            'id_tim' => $pengajuan->id_tim,
            'nim' => $anggota->nim,
            'status_gabung' => 'diterima',
            'peran' => 'Anggota Tim',
        ]);

        $this->actingAsMahasiswa($anggota);

        $response = $this->post(route('bimbingan.progres.store'), [
            'id_pengajuan' => $pengajuan->id_pengajuan,
            'babak' => 'Penyisihan',
            'tanggal_update' => now()->format('Y-m-d'),
            'hasil_akhir' => 'Tidak lolos',
        ]);

        $response->assertForbidden();
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
            'alasan_penolakan' => 'Dokumen lampiran sertifikat dan demo produk belum valid.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $progres->refresh();
        $this->assertSame('ditolak', $progres->status_setuju);
        $this->assertSame('Dokumen lampiran sertifikat dan demo produk belum valid.', $progres->alasan_penolakan);
        $this->assertNotNull($progres->diverifikasi_pada);
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
            'alasan_penolakan' => '',
        ]);

        $response->assertSessionHasErrors('alasan_penolakan');

        $progres->refresh();
        $this->assertSame('menunggu', $progres->status_setuju);
    }

    /**
     * Dosen lain yang bukan pembimbing tim ditolak saat memvalidasi progres babak
     */
    public function test_dosen_bukan_pembimbing_ditolak_merespon_progres_babak(): void
    {
        $dosenPembimbing = Dosen::first();
        $dosenLain = Dosen::where('nip', '!=', $dosenPembimbing->nip)->first();

        $this->actingAsDosen($dosenLain);
        [, $progres] = $this->createBimbinganWithProgres($dosenPembimbing);

        $response = $this->post(route('bimbingan.progres.respon', $progres->id_progres), [
            'status_setuju' => 'disetujui',
        ]);

        $response->assertForbidden();

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

    private function payloadFinal(int $idPengajuan, string $hasil, array $extra = []): array
    {
        return array_merge([
            'id_pengajuan' => $idPengajuan,
            'babak' => 'Final',
            'tanggal_update' => now()->format('Y-m-d'),
            'hasil_akhir' => $hasil,
        ], $extra);
    }

    public function test_juara_1_pada_babak_semifinal_ditolak_validasi(): void
    {
        $dosen = Dosen::first();
        [$pengajuan, , $ketua] = $this->createBimbinganWithProgres($dosen);
        $this->actingAsMahasiswa($ketua);

        $response = $this->post(route('bimbingan.progres.store'), [
            'id_pengajuan' => $pengajuan->id_pengajuan,
            'babak' => 'Semifinal',
            'tanggal_update' => now()->format('Y-m-d'),
            'hasil_akhir' => 'Juara 1',
        ]);

        $response->assertSessionHasErrors('hasil_akhir');
        $this->assertDatabaseMissing('progres_babak', ['babak' => 'Semifinal', 'hasil_akhir' => 'Juara 1']);
    }

    public function test_final_dengan_hasil_juara_tanpa_bukti_gagal_validasi(): void
    {
        $dosen = Dosen::first();
        [$pengajuan, , $ketua] = $this->createBimbinganWithProgres($dosen);
        $this->actingAsMahasiswa($ketua);

        $response = $this->post(route('bimbingan.progres.store'), $this->payloadFinal($pengajuan->id_pengajuan, 'Juara 1'));

        $response->assertSessionHasErrors('file_bukti');
    }

    public function test_final_finalis_tanpa_bukti_diizinkan(): void
    {
        $dosen = Dosen::first();
        [$pengajuan, , $ketua] = $this->createBimbinganWithProgres($dosen);
        $this->actingAsMahasiswa($ketua);

        $response = $this->post(route('bimbingan.progres.store'), $this->payloadFinal($pengajuan->id_pengajuan, 'Finalis (tanpa penghargaan)'));

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('progres_babak', ['hasil_akhir' => 'Finalis (tanpa penghargaan)', 'status_setuju' => 'menunggu']);
    }

    public function test_bukti_tidak_boleh_diunggah_untuk_babak_non_final(): void
    {
        Storage::fake('local');
        $dosen = Dosen::first();
        [$pengajuan, , $ketua] = $this->createBimbinganWithProgres($dosen);
        $this->actingAsMahasiswa($ketua);

        $response = $this->post(route('bimbingan.progres.store'), [
            'id_pengajuan' => $pengajuan->id_pengajuan,
            'babak' => 'Penyisihan',
            'tanggal_update' => now()->format('Y-m-d'),
            'hasil_akhir' => 'Lolos ke babak berikutnya',
            'file_bukti' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors('file_bukti');
    }

    public function test_final_juara_dengan_bukti_valid_tersimpan(): void
    {
        Storage::fake('local');
        $dosen = Dosen::first();
        [$pengajuan, , $ketua] = $this->createBimbinganWithProgres($dosen);
        $this->actingAsMahasiswa($ketua);

        $response = $this->post(route('bimbingan.progres.store'), $this->payloadFinal($pengajuan->id_pengajuan, 'Juara 1', [
            'keterangan_hasil' => 'Juara 1 Kategori Software',
            'file_bukti' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'),
        ]));

        $response->assertSessionHasNoErrors();
        $progres = ProgresBabak::where('id_pengajuan', $pengajuan->id_pengajuan)
            ->where('hasil_akhir', 'Juara 1')
            ->firstOrFail();
        $this->assertSame('menunggu', $progres->status_setuju);
        $this->assertSame('Juara 1 Kategori Software', $progres->keterangan_hasil);
        Storage::disk('local')->assertExists($progres->file_bukti);
    }

    public function test_bukti_lebih_dari_2_mb_ditolak(): void
    {
        Storage::fake('local');
        $dosen = Dosen::first();
        [$pengajuan, , $ketua] = $this->createBimbinganWithProgres($dosen);
        $this->actingAsMahasiswa($ketua);

        $response = $this->post(route('bimbingan.progres.store'), $this->payloadFinal($pengajuan->id_pengajuan, 'Juara 2', [
            'file_bukti' => UploadedFile::fake()->create('bukti.pdf', 3000, 'application/pdf'),
        ]));

        $response->assertSessionHasErrors('file_bukti');
    }

    public function test_laporan_peringkat_final_kedua_ditolak_tetapi_penghargaan_khusus_diizinkan(): void
    {
        Storage::fake('local');
        $dosen = Dosen::first();
        [$pengajuan, , $ketua] = $this->createBimbinganWithProgres($dosen);
        $this->actingAsMahasiswa($ketua);

        $bukti = fn () => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf');

        $this->post(route('bimbingan.progres.store'), $this->payloadFinal($pengajuan->id_pengajuan, 'Juara 1', ['file_bukti' => $bukti()]))
            ->assertSessionHasNoErrors();

        $kedua = $this->post(route('bimbingan.progres.store'), $this->payloadFinal($pengajuan->id_pengajuan, 'Juara 2', ['file_bukti' => $bukti()]));
        $kedua->assertSessionHasErrors('hasil_akhir');
        $this->assertSame(1, ProgresBabak::where('babak', 'Final')->where('id_pengajuan', $pengajuan->id_pengajuan)->count());

        $khusus = $this->post(route('bimbingan.progres.store'), $this->payloadFinal($pengajuan->id_pengajuan, 'Penghargaan Khusus', [
            'keterangan_hasil' => 'Best Prototype & Implementation',
            'file_bukti' => $bukti(),
        ]));
        $khusus->assertSessionHasNoErrors();
        $this->assertSame(2, ProgresBabak::where('babak', 'Final')->where('id_pengajuan', $pengajuan->id_pengajuan)->count());

        $khususKedua = $this->post(route('bimbingan.progres.store'), $this->payloadFinal($pengajuan->id_pengajuan, 'Penghargaan Khusus', ['file_bukti' => $bukti()]));
        $khususKedua->assertSessionHasErrors('hasil_akhir');
    }

    public function test_laporan_final_yang_ditolak_tidak_menghalangi_laporan_baru(): void
    {
        $dosen = Dosen::first();
        [$pengajuan, $progres, $ketua] = $this->createBimbinganWithProgres($dosen, [
            'babak' => 'Final',
            'hasil_akhir' => 'Finalis (tanpa penghargaan)',
            'status_setuju' => 'ditolak',
        ]);
        $this->actingAsMahasiswa($ketua);

        $response = $this->post(route('bimbingan.progres.store'), $this->payloadFinal($pengajuan->id_pengajuan, 'Finalis (tanpa penghargaan)'));

        $response->assertSessionHasNoErrors();
        $this->assertSame(2, ProgresBabak::where('id_pengajuan', $pengajuan->id_pengajuan)->count());
    }

    public function test_ketua_dapat_memperbaiki_dan_kirim_ulang_laporan_ditolak(): void
    {
        $dosen = Dosen::first();
        [, $progres, $ketua] = $this->createBimbinganWithProgres($dosen, [
            'status_setuju' => 'ditolak',
            'alasan_penolakan' => 'Hasil tidak sesuai pengumuman.',
            'catatan_dosen' => 'Hasil tidak sesuai pengumuman.',
        ]);
        $this->actingAsMahasiswa($ketua);

        $response = $this->put(route('bimbingan.progres.update', $progres->id_progres), [
            'id_pengajuan' => $progres->id_pengajuan,
            'babak' => 'Penyisihan',
            'tanggal_update' => now()->format('Y-m-d'),
            'hasil_akhir' => 'Tidak lolos',
        ]);

        $response->assertSessionHasNoErrors();
        $progres->refresh();
        $this->assertSame('menunggu', $progres->status_setuju);
        $this->assertSame('Tidak lolos', $progres->hasil_akhir);
        $this->assertNull($progres->alasan_penolakan);
    }

    public function test_laporan_yang_tidak_ditolak_tidak_dapat_diperbaiki(): void
    {
        $dosen = Dosen::first();
        [, $progres, $ketua] = $this->createBimbinganWithProgres($dosen);
        $this->actingAsMahasiswa($ketua);

        $response = $this->put(route('bimbingan.progres.update', $progres->id_progres), [
            'id_pengajuan' => $progres->id_pengajuan,
            'babak' => 'Penyisihan',
            'tanggal_update' => now()->format('Y-m-d'),
            'hasil_akhir' => 'Tidak lolos',
        ]);

        $response->assertSessionHas('error');
        $this->assertSame('Lolos ke Semifinal', $progres->fresh()->hasil_akhir);
    }

    public function test_dosen_tidak_dapat_merespon_laporan_yang_sudah_diputuskan(): void
    {
        $dosen = Dosen::first();
        $this->actingAsDosen($dosen);
        [, $progres] = $this->createBimbinganWithProgres($dosen, ['status_setuju' => 'disetujui']);

        $response = $this->post(route('bimbingan.progres.respon', $progres->id_progres), [
            'status_setuju' => 'ditolak',
            'alasan_penolakan' => 'Alasan penolakan yang cukup panjang.',
        ]);

        $response->assertSessionHas('error');
        $this->assertSame('disetujui', $progres->fresh()->status_setuju);
    }

    public function test_alasan_penolakan_kurang_dari_10_karakter_gagal(): void
    {
        $dosen = Dosen::first();
        $this->actingAsDosen($dosen);
        [, $progres] = $this->createBimbinganWithProgres($dosen);

        $response = $this->post(route('bimbingan.progres.respon', $progres->id_progres), [
            'status_setuju' => 'ditolak',
            'alasan_penolakan' => 'pendek',
        ]);

        $response->assertSessionHasErrors('alasan_penolakan');
    }

    public function test_lihat_bukti_hanya_untuk_pihak_terkait(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('progres_bukti/bukti.pdf', 'isi');

        $dosen = Dosen::first();
        [, $progres, $ketua] = $this->createBimbinganWithProgres($dosen, [
            'babak' => 'Final',
            'hasil_akhir' => 'Juara 1',
            'file_bukti' => 'progres_bukti/bukti.pdf',
        ]);

        $this->actingAsMahasiswa($ketua);
        $this->get(route('bimbingan.progres.bukti', $progres->id_progres))->assertOk();

        $luar = Mahasiswa::where('nim', '!=', $ketua->nim)->first();
        $this->actingAsMahasiswa($luar);
        $this->get(route('bimbingan.progres.bukti', $progres->id_progres))->assertForbidden();

        $dosenLain = Dosen::where('nip', '!=', $dosen->nip)->first();
        $this->actingAsDosen($dosenLain);
        $this->get(route('bimbingan.progres.bukti', $progres->id_progres))->assertForbidden();

        $this->actingAsDosen($dosen);
        $this->get(route('bimbingan.progres.bukti', $progres->id_progres))->assertOk();
    }
}
