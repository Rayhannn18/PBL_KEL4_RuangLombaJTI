<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\AnggotaTim;
use App\Models\Dosen;
use App\Models\Lomba;
use App\Models\Mahasiswa;
use App\Models\PengajuanBimbingan;
use App\Models\PrestasiMahasiswa;
use App\Models\ProgresBabak;
use App\Models\Tim;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PrestasiAnalitikTest extends TestCase
{
    use RefreshDatabase;

    private function createMahasiswa(string $nim, string $nama, string $prodi, int $angkatan): Mahasiswa
    {
        return Mahasiswa::create([
            'nim' => $nim,
            'nama' => $nama,
            'email_kampus' => $nim.'@student.polinema.ac.id',
            'password' => Hash::make('password123'),
            'prodi' => $prodi,
            'angkatan' => $angkatan,
        ]);
    }

    private function actingAsMahasiswa(Mahasiswa $m): User
    {
        $user = User::firstOrCreate(
            ['email' => $m->email_kampus],
            [
                'name' => $m->nama,
                'nim_nip' => $m->nim,
                'role' => UserRole::Mahasiswa,
                'prodi' => $m->prodi,
                'angkatan' => $m->angkatan,
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

    private function createDosen(string $nidn, string $nama): Dosen
    {
        return Dosen::create([
            'nidn' => $nidn,
            'nama' => $nama,
            'email_kampus' => $nidn.'@polinema.ac.id',
            'password' => Hash::make('password123'),
            'bidang_keahlian' => 'Software Engineering',
            'kuota_bimbingan' => 5,
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

    private function actingAsAdmin(): User
    {
        $admin = Admin::firstOrCreate(
            ['email' => 'admin.test@polinema.ac.id'],
            [
                'nama' => 'Admin Test',
                'password' => Hash::make('password123'),
            ]
        );

        $user = User::firstOrCreate(
            ['email' => $admin->email],
            [
                'name' => $admin->nama,
                'role' => UserRole::Admin,
                'password' => Hash::make('password123'),
            ]
        );

        $this->actingAs($user);
        session([
            'auth_user' => [
                'id' => $admin->id_admin,
                'nama' => $admin->nama,
                'email' => $admin->email,
                'role' => 'admin',
                'extra' => 'Kemahasiswaan JTI',
            ],
        ]);

        return $user;
    }

    private function createLomba(string $nama = 'GEMASTIK', string $tingkat = 'Nasional'): Lomba
    {
        return Lomba::create([
            'nama_lomba' => $nama,
            'kategori' => 'Software Development',
            'tingkat' => $tingkat,
            'penyelenggara' => 'BPTI',
            'deskripsi' => 'Kompetisi TIK.',
            'persyaratan' => 'Mahasiswa aktif.',
            'tenggat' => now()->addMonth(),
            'biaya' => 0,
            'benefit' => 'Sertifikat',
            'tautan_daftar' => 'https://example.com',
            'status_verifikasi' => 'terverifikasi',
        ]);
    }

    /**
     * @param  array<int, Mahasiswa>  $anggotaList
     */
    private function setupTim3Anggota(Mahasiswa $ketua, array $anggotaList, Dosen $dosen, Lomba $lomba, string $statusPengajuan = 'disetujui'): array
    {
        $tim = Tim::create([
            'nama_tim' => 'Tim '.$ketua->nim,
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

        foreach ($anggotaList as $ang) {
            AnggotaTim::create([
                'id_tim' => $tim->id_tim,
                'nim' => $ang->nim,
                'status_gabung' => 'diterima',
                'peran' => 'Anggota',
            ]);
        }

        $pengajuan = PengajuanBimbingan::create([
            'id_tim' => $tim->id_tim,
            'nidn' => $dosen->nidn,
            'tgl_pengajuan' => now(),
            'status' => $statusPengajuan,
        ]);

        return [$tim, $pengajuan];
    }

    public function test_setujui_juara_1_tim_3_anggota_menghasilkan_3_baris_prestasi(): void
    {
        Storage::fake('local');
        $ketua = $this->createMahasiswa('244107060001', 'Ketua', 'D-IV Sistem Informasi Bisnis', 2024);
        $ang1 = $this->createMahasiswa('244107060002', 'Anggota 1', 'D-IV Sistem Informasi Bisnis', 2024);
        $ang2 = $this->createMahasiswa('2341720003', 'Anggota 2', 'D-IV Teknik Informatika', 2023);
        $dosen = $this->createDosen('198501012010121001', 'Dosen Satu');
        $lomba = $this->createLomba('GEMASTIK XVIII', 'Nasional');

        [$tim, $pengajuan] = $this->setupTim3Anggota($ketua, [$ang1, $ang2], $dosen, $lomba);

        $this->actingAsMahasiswa($ketua);
        $this->post(route('bimbingan.progres.store'), [
            'id_pengajuan' => $pengajuan->id_pengajuan,
            'babak' => 'Final',
            'tanggal_update' => now()->format('Y-m-d'),
            'hasil_akhir' => 'Juara 1',
            'keterangan_hasil' => 'Divisi RPL',
            'file_bukti' => UploadedFile::fake()->create('juara1.pdf', 100, 'application/pdf'),
        ])->assertSessionHasNoErrors();

        // Sebelum disetujui dosen: 0 prestasi di tabel dan analitik
        $this->assertSame(0, PrestasiMahasiswa::count());
        $this->get(route('dashboard.analitik'))->assertViewHas('totalPrestasi', 0);

        $progres = ProgresBabak::firstOrFail();

        $this->actingAsDosen($dosen);
        $this->post(route('bimbingan.progres.respon', $progres->id_progres), [
            'status_setuju' => 'disetujui',
        ])->assertSessionHas('success');

        $this->assertSame(3, PrestasiMahasiswa::count());
        $this->assertDatabaseHas('prestasi_mahasiswa', [
            'nim' => '244107060001',
            'id_tim' => $tim->id_tim,
            'id_lomba' => $lomba->id_lomba,
            'id_progres' => $progres->id_progres,
            'jenis_prestasi' => 'peringkat',
            'hasil' => 'Juara 1',
            'tingkat_lomba' => 'Nasional',
            'angkatan' => 2024,
            'prodi' => 'D-IV Sistem Informasi Bisnis',
            'keterangan_hasil' => 'Divisi RPL',
        ]);
        $this->assertDatabaseHas('prestasi_mahasiswa', [
            'nim' => '2341720003',
            'angkatan' => 2023,
            'prodi' => 'D-IV Teknik Informatika',
        ]);

        // Setujui / sync dua kali tidak menggandakan
        PrestasiMahasiswa::syncFromProgres($progres->fresh());
        $this->assertSame(3, PrestasiMahasiswa::count());

        // Angka analitik & admin sama (3)
        $dashResponse = $this->get(route('dashboard.analitik'));
        $dashResponse->assertViewHas('totalPrestasi', 3);
        $dashResponse->assertSee('3 anggota');
        $dashResponse->assertSee('Juara 1 Nasional');
        $dashResponse->assertSee('Divisi RPL');

        $this->actingAsAdmin();
        $adminResponse = $this->get(route('admin.index'));
        $adminResponse->assertViewHas('stats', fn ($stats) => ($stats['total_prestasi'] ?? -1) === 3);
    }

    public function test_semifinal_lolos_dan_final_finalis_disetujui_tidak_membuat_baris_prestasi(): void
    {
        $ketua = $this->createMahasiswa('244107060010', 'Ketua', 'D-IV Sistem Informasi Bisnis', 2024);
        $dosen = $this->createDosen('198501012010121002', 'Dosen Dua');
        $lomba = $this->createLomba();
        [, $pengajuan] = $this->setupTim3Anggota($ketua, [], $dosen, $lomba);

        $this->actingAsMahasiswa($ketua);
        $this->post(route('bimbingan.progres.store'), [
            'id_pengajuan' => $pengajuan->id_pengajuan,
            'babak' => 'Semifinal',
            'tanggal_update' => now()->format('Y-m-d'),
            'hasil_akhir' => 'Lolos ke babak berikutnya',
        ])->assertSessionHasNoErrors();

        $this->post(route('bimbingan.progres.store'), [
            'id_pengajuan' => $pengajuan->id_pengajuan,
            'babak' => 'Final',
            'tanggal_update' => now()->format('Y-m-d'),
            'hasil_akhir' => 'Finalis (tanpa penghargaan)',
        ])->assertSessionHasNoErrors();

        $this->actingAsDosen($dosen);
        foreach (ProgresBabak::all() as $prog) {
            $this->post(route('bimbingan.progres.respon', $prog->id_progres), [
                'status_setuju' => 'disetujui',
            ])->assertSessionHas('success');
        }

        $this->assertSame(0, PrestasiMahasiswa::count());

        $response = $this->get(route('dashboard.analitik'));
        $response->assertViewHas('totalPrestasi', 0);
        $response->assertViewHas('daftarJuara', fn ($list) => $list->isEmpty());
        $response->assertViewHas('babakMonitoring', fn ($list) => $list->count() === 2);
    }

    public function test_anti_ganda_peringkat_tertinggi_dan_penghargaan_khusus_terpisah(): void
    {
        Storage::fake('local');
        $mhs = $this->createMahasiswa('244107060020', 'Mhs Multi Tim', 'D-IV Sistem Informasi Bisnis', 2024);
        $ketua2 = $this->createMahasiswa('244107060021', 'Ketua Dua', 'D-IV Teknik Informatika', 2024);
        $dosen = $this->createDosen('198501012010121003', 'Dosen Tiga');
        $lomba = $this->createLomba('Hackathon Nasional', 'Nasional');

        [, $pengajuan1] = $this->setupTim3Anggota($mhs, [], $dosen, $lomba);
        [, $pengajuan2] = $this->setupTim3Anggota($ketua2, [$mhs], $dosen, $lomba);

        $progJuara3 = ProgresBabak::create([
            'id_pengajuan' => $pengajuan1->id_pengajuan,
            'babak' => 'Final',
            'tanggal_update' => now(),
            'hasil_akhir' => 'Juara 3',
            'status_setuju' => 'disetujui',
        ]);

        // Mahasiswa yang sama di tim lain mendapat Juara 1 pada lomba yang sama: baris peringkat diperbarui ke Juara 1
        $progJuara1 = ProgresBabak::create([
            'id_pengajuan' => $pengajuan2->id_pengajuan,
            'babak' => 'Final',
            'tanggal_update' => now(),
            'hasil_akhir' => 'Juara 1',
            'status_setuju' => 'disetujui',
        ]);

        // Tim 1 juga mendapat Penghargaan Khusus: dihitung terpisah dari peringkat
        $progKhusus = ProgresBabak::create([
            'id_pengajuan' => $pengajuan1->id_pengajuan,
            'babak' => 'Final',
            'tanggal_update' => now(),
            'hasil_akhir' => 'Penghargaan Khusus',
            'keterangan_hasil' => 'Best Prototype & Implementation',
            'status_setuju' => 'disetujui',
        ]);

        $this->assertDatabaseHas('prestasi_mahasiswa', [
            'nim' => $mhs->nim,
            'id_lomba' => $lomba->id_lomba,
            'jenis_prestasi' => 'peringkat',
            'hasil' => 'Juara 1',
            'id_progres' => $progJuara1->id_progres,
        ]);

        $this->assertDatabaseHas('prestasi_mahasiswa', [
            'nim' => $mhs->nim,
            'id_lomba' => $lomba->id_lomba,
            'jenis_prestasi' => 'penghargaan_khusus',
            'hasil' => 'Penghargaan Khusus',
            'keterangan_hasil' => 'Best Prototype & Implementation',
            'id_progres' => $progKhusus->id_progres,
        ]);

        // Menghapus laporan Juara 1 menghapus baris prestasinya
        $progJuara1->delete();
        $this->assertDatabaseMissing('prestasi_mahasiswa', ['id_progres' => $progJuara1->id_progres]);
        $this->assertSame(1, PrestasiMahasiswa::where('nim', $mhs->nim)->count());
    }

    public function test_otorisasi_403_untuk_kirim_respon_dan_lihat_bukti(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('progres_bukti/test.pdf', 'pdf');

        $ketua = $this->createMahasiswa('244107060030', 'Ketua', 'D-IV Sistem Informasi Bisnis', 2024);
        $anggota = $this->createMahasiswa('244107060031', 'Anggota', 'D-IV Sistem Informasi Bisnis', 2024);
        $luar = $this->createMahasiswa('244107060032', 'Luar', 'D-IV Teknik Informatika', 2024);
        $dosenBenar = $this->createDosen('198501012010121004', 'Dosen Benar');
        $dosenSalah = $this->createDosen('198501012010121005', 'Dosen Salah');
        $lomba = $this->createLomba();

        [, $pengajuan] = $this->setupTim3Anggota($ketua, [$anggota], $dosenBenar, $lomba);
        [, $pengajuanMenunggu] = $this->setupTim3Anggota($luar, [], $dosenBenar, $lomba, 'menunggu');

        $progres = ProgresBabak::create([
            'id_pengajuan' => $pengajuan->id_pengajuan,
            'babak' => 'Final',
            'tanggal_update' => now(),
            'hasil_akhir' => 'Juara 2',
            'file_bukti' => 'progres_bukti/test.pdf',
            'status_setuju' => 'menunggu',
        ]);

        $progresPengajuanMenunggu = ProgresBabak::create([
            'id_pengajuan' => $pengajuanMenunggu->id_pengajuan,
            'babak' => 'Final',
            'tanggal_update' => now(),
            'hasil_akhir' => 'Juara 1',
            'file_bukti' => 'progres_bukti/test.pdf',
            'status_setuju' => 'menunggu',
        ]);

        // Kirim laporan: anggota -> 403, admin -> 403
        $this->actingAsMahasiswa($anggota);
        $this->post(route('bimbingan.progres.store'), [
            'id_pengajuan' => $pengajuan->id_pengajuan,
            'babak' => 'Penyisihan',
            'tanggal_update' => now()->format('Y-m-d'),
            'hasil_akhir' => 'Lolos ke babak berikutnya',
        ])->assertForbidden();

        $this->actingAsAdmin();
        $this->post(route('bimbingan.progres.store'), [
            'id_pengajuan' => $pengajuan->id_pengajuan,
            'babak' => 'Penyisihan',
            'tanggal_update' => now()->format('Y-m-d'),
            'hasil_akhir' => 'Lolos ke babak berikutnya',
        ])->assertForbidden();

        // Setujui/tolak: mahasiswa -> 403, admin -> 403, dosen salah -> 403, dosen pada pengajuan menunggu -> 403
        $this->actingAsMahasiswa($ketua);
        $this->post(route('bimbingan.progres.respon', $progres->id_progres), ['status_setuju' => 'disetujui'])->assertForbidden();

        $this->actingAsAdmin();
        $this->post(route('bimbingan.progres.respon', $progres->id_progres), ['status_setuju' => 'disetujui'])->assertForbidden();
        $this->get(route('bimbingan.progres.bukti', $progres->id_progres))->assertOk();

        $this->actingAsDosen($dosenSalah);
        $this->post(route('bimbingan.progres.respon', $progres->id_progres), ['status_setuju' => 'disetujui'])->assertForbidden();
        $this->get(route('bimbingan.progres.bukti', $progres->id_progres))->assertForbidden();

        $this->actingAsDosen($dosenBenar);
        $this->post(route('bimbingan.progres.respon', $progresPengajuanMenunggu->id_progres), ['status_setuju' => 'disetujui'])->assertForbidden();
        $this->get(route('bimbingan.progres.bukti', $progresPengajuanMenunggu->id_progres))->assertForbidden();
        $this->get(route('bimbingan.progres.bukti', $progres->id_progres))->assertOk();

        // Lihat bukti: anggota tim diterima -> 200, mahasiswa luar -> 403
        $this->actingAsMahasiswa($anggota);
        $this->get(route('bimbingan.progres.bukti', $progres->id_progres))->assertOk();

        $this->actingAsMahasiswa($luar);
        $this->get(route('bimbingan.progres.bukti', $progres->id_progres))->assertForbidden();
    }

    public function test_backfill_command_dry_run_dan_eksekusi_idempoten(): void
    {
        $ketua = $this->createMahasiswa('244107060040', 'Ketua', 'D-IV Sistem Informasi Bisnis', 2024);
        $ang = $this->createMahasiswa('244107060041', 'Anggota', 'D-IV Teknik Informatika', 2024);
        $dosen = $this->createDosen('198501012010121006', 'Dosen Enam');
        $lomba = $this->createLomba('CTF', 'Nasional');
        [, $pengajuan] = $this->setupTim3Anggota($ketua, [$ang], $dosen, $lomba);

        $progLama = ProgresBabak::create([
            'id_pengajuan' => $pengajuan->id_pengajuan,
            'babak' => 'Final',
            'tanggal_update' => now(),
            'hasil_akhir' => 'Juara 1 Nasional CTF',
            'status_setuju' => 'disetujui',
        ]);

        // Dry run tidak mengubah DB
        $this->artisan('prestasi:backfill --dry-run')->assertExitCode(0);
        $this->assertSame('Juara 1 Nasional CTF', $progLama->fresh()->hasil_akhir);
        $this->assertSame(0, PrestasiMahasiswa::count());

        // Eksekusi backfill
        $this->artisan('prestasi:backfill')->assertExitCode(0);
        $progLama->refresh();
        $this->assertSame('Juara 1', $progLama->hasil_akhir);
        $this->assertSame('CTF', $progLama->keterangan_hasil);
        $this->assertSame('disetujui', $progLama->status_setuju);
        $this->assertSame(2, PrestasiMahasiswa::count());

        // Jalankan ulang idempoten
        $this->artisan('prestasi:backfill')->assertExitCode(0);
        $this->assertSame(2, PrestasiMahasiswa::count());
    }

    public function test_kompetisi_selesai_mengunci_lapor_progres_logbook_dan_rab_baru(): void
    {
        Storage::fake('local');
        $ketua = $this->createMahasiswa('244107060050', 'Ketua Kunci', 'D-IV Sistem Informasi Bisnis', 2024);
        $dosen = $this->createDosen('198501012010121007', 'Dosen Tujuh');
        $lomba = $this->createLomba('GEMASTIK Kunci', 'Nasional');
        [$tim, $pengajuan] = $this->setupTim3Anggota($ketua, [], $dosen, $lomba);

        ProgresBabak::create([
            'id_pengajuan' => $pengajuan->id_pengajuan,
            'babak' => 'Final',
            'tanggal_update' => now(),
            'hasil_akhir' => 'Juara 1',
            'status_setuju' => 'disetujui',
        ]);

        $this->actingAsMahasiswa($ketua);

        // View bimbingan mengunci tombol dan menampilkan badge Kompetisi Selesai
        $view = $this->get(route('bimbingan.index', ['tim_id' => $tim->id_tim]));
        $view->assertStatus(200);
        $view->assertSee('Kompetisi Selesai');
        $view->assertDontSee('onclick="openModalTambahProgres()"', false);
        $view->assertDontSee('onclick="openModalLogbook(', false);

        // Server menolak laporan progres baru (misal Penyisihan)
        $this->post(route('bimbingan.progres.store'), [
            'id_pengajuan' => $pengajuan->id_pengajuan,
            'babak' => 'Penyisihan',
            'tanggal_update' => now()->format('Y-m-d'),
            'hasil_akhir' => 'Lolos ke babak berikutnya',
        ])->assertSessionHas('error');

        $this->assertSame(1, ProgresBabak::where('id_pengajuan', $pengajuan->id_pengajuan)->count());

        // Server menolak logbook baru
        $this->post(route('bimbingan.logbook.store'), [
            'id_pengajuan' => $pengajuan->id_pengajuan,
            'tanggal' => now()->format('Y-m-d'),
            'materi' => 'Logbook setelah selesai',
        ])->assertSessionHas('error');

        $this->assertDatabaseMissing('logbook', ['id_pengajuan' => $pengajuan->id_pengajuan]);
    }
}
