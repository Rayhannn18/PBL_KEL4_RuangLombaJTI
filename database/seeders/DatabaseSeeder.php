<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\AnggotaTim;
use App\Models\Dosen;
use App\Models\Logbook;
use App\Models\Lomba;
use App\Models\Mahasiswa;
use App\Models\PengajuanBimbingan;
use App\Models\ProgresBabak;
use App\Models\Tim;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Admin
        $admin = Admin::create([
            'nama' => 'Admin Kemahasiswaan JTI',
            'email' => 'admin.jti@polinema.ac.id',
            'password' => Hash::make('password123'),
        ]);

        // 2. Dosen Pembimbing
        $dosenList = [
            [
                'nidn' => '0012058501',
                'nama' => 'Ade Ismail, S.Kom., M.T.I.',
                'email_kampus' => 'ade.ismail@polinema.ac.id',
                'password' => Hash::make('password123'),
                'bidang_keahlian' => 'Software Engineering & Web Development',
                'kuota_bimbingan' => 6,
            ],
            [
                'nidn' => '0015088202',
                'nama' => 'Rudy Ariyanto, S.T., M.Cs.',
                'email_kampus' => 'rudy.ariyanto@polinema.ac.id',
                'password' => Hash::make('password123'),
                'bidang_keahlian' => 'Information Systems & Project Management',
                'kuota_bimbingan' => 5,
            ],
            [
                'nidn' => '0020048603',
                'nama' => 'Usman Nurhasan, S.Kom., M.T.',
                'email_kampus' => 'usman.nurhasan@polinema.ac.id',
                'password' => Hash::make('password123'),
                'bidang_keahlian' => 'Software Quality Assurance & Cyber Security',
                'kuota_bimbingan' => 5,
            ],
            [
                'nidn' => '0024098904',
                'nama' => 'Dr. Eng. Rosa Andrie Asmara, S.T., M.T.',
                'email_kampus' => 'rosa.andrie@polinema.ac.id',
                'password' => Hash::make('password123'),
                'bidang_keahlian' => 'Artificial Intelligence & Computer Vision',
                'kuota_bimbingan' => 4,
            ],
            [
                'nidn' => '0019038705',
                'nama' => 'Mimi Nur Hajizah, S.Kom., M.Sc.',
                'email_kampus' => 'mimi.nur@polinema.ac.id',
                'password' => Hash::make('password123'),
                'bidang_keahlian' => 'UI/UX Design & Human-Computer Interaction',
                'kuota_bimbingan' => 5,
            ],
        ];

        foreach ($dosenList as $d) {
            Dosen::create($d);
        }

        // 3. Mahasiswa
        $mahasiswaList = [
            [
                'nim' => '244107060116',
                'nama' => 'Sastra Maheva Zaky',
                'email_kampus' => 'sastra.maheva@student.polinema.ac.id',
                'password' => Hash::make('password123'),
                'prodi' => 'D-IV Sistem Informasi Bisnis',
                'angkatan' => 2024,
            ],
            [
                'nim' => '244107060147',
                'nama' => 'Nadya Syantika Naraya',
                'email_kampus' => 'nadya.syantika@student.polinema.ac.id',
                'password' => Hash::make('password123'),
                'prodi' => 'D-IV Sistem Informasi Bisnis',
                'angkatan' => 2024,
            ],
            [
                'nim' => '244107060083',
                'nama' => 'Gempita Fitri Nurdini',
                'email_kampus' => 'gempita.fitri@student.polinema.ac.id',
                'password' => Hash::make('password123'),
                'prodi' => 'D-IV Sistem Informasi Bisnis',
                'angkatan' => 2024,
            ],
            [
                'nim' => '244107060122',
                'nama' => 'Rayhan Giri Putra',
                'email_kampus' => 'rayhan.giri@student.polinema.ac.id',
                'password' => Hash::make('password123'),
                'prodi' => 'D-IV Sistem Informasi Bisnis',
                'angkatan' => 2024,
            ],
            [
                'nim' => '2341720055',
                'nama' => 'Ahmad Fajar Pratama',
                'email_kampus' => 'ahmad.fajar@student.polinema.ac.id',
                'password' => Hash::make('password123'),
                'prodi' => 'D-IV Teknik Informatika',
                'angkatan' => 2023,
            ],
            [
                'nim' => '2341720089',
                'nama' => 'Citra Dewi Kusuma',
                'email_kampus' => 'citra.dewi@student.polinema.ac.id',
                'password' => Hash::make('password123'),
                'prodi' => 'D-IV Teknik Informatika',
                'angkatan' => 2023,
            ],
            [
                'nim' => '254107060012',
                'nama' => 'Bima Arya Nugraha',
                'email_kampus' => 'bima.arya@student.polinema.ac.id',
                'password' => Hash::make('password123'),
                'prodi' => 'D-IV Sistem Informasi Bisnis',
                'angkatan' => 2025,
            ],
            [
                'nim' => '2541720034',
                'nama' => 'Rania Putri Safitri',
                'email_kampus' => 'rania.putri@student.polinema.ac.id',
                'password' => Hash::make('password123'),
                'prodi' => 'D-IV Teknik Informatika',
                'angkatan' => 2025,
            ],
            [
                'nim' => '264107060015',
                'nama' => 'Daffa Raihan Alfarizi',
                'email_kampus' => 'daffa.raihan@student.polinema.ac.id',
                'password' => Hash::make('password123'),
                'prodi' => 'D-IV Sistem Informasi Bisnis',
                'angkatan' => 2026,
            ],
            [
                'nim' => '2641720021',
                'nama' => 'Nabila Syahrani Putri',
                'email_kampus' => 'nabila.syahrani@student.polinema.ac.id',
                'password' => Hash::make('password123'),
                'prodi' => 'D-IV Teknik Informatika',
                'angkatan' => 2026,
            ],
        ];

        foreach ($mahasiswaList as $m) {
            Mahasiswa::create($m);
        }

        // 4. Lomba
        $lomba1 = Lomba::create([
            'nama_lomba' => 'GEMASTIK XVIII (Pagelaran Mahasiswa Nasional Bidang TIK)',
            'kategori' => 'Software Development',
            'tingkat' => 'Nasional',
            'penyelenggara' => 'Balai Pengembangan Talenta Indonesia (BPTI) Kemendikbudristek',
            'deskripsi' => 'Kompetisi TIK paling bergengsi tingkat perguruan tinggi se-Indonesia divisi Pengembangan Perangkat Lunak, Data Mining, UI/UX, dan Smart City.',
            'persyaratan' => '1. Mahasiswa aktif D3/D4/S1 terdaftar di PDDikti.\n2. Tim terdiri atas 3 orang mahasiswa dengan 1 dosen pembimbing.\n3. Mengunggah proposal dan prototype produk sesuai format panduan resmi.',
            'tenggat' => Carbon::now()->addDays(28),
            'biaya' => 0,
            'benefit' => 'Medali Emas/Perak/Perunggu, Uang Pembinaan s.d Rp 25.000.000, Pengakuan Konversi SKS, Sertifikat Puspresnas.',
            'tautan_daftar' => 'https://gemastik.kemdikbud.go.id',
            'status_verifikasi' => 'terverifikasi',
            'poster' => 'gemastik.jpg',
            'nim' => '244107060116',
            'id_admin' => $admin->id_admin,
        ]);

        $lomba2 = Lomba::create([
            'nama_lomba' => 'KMIPN VII Polinema (Kompetisi Mahasiswa Informatika Politeknik Nasional)',
            'kategori' => 'Internet of Things & Embedded System',
            'tingkat' => 'Nasional',
            'penyelenggara' => 'Badan Koordinasi Kemahasiswaan Politeknik Se-Indonesia (BAKLLMA)',
            'deskripsi' => 'Ajang kompetisi berskala nasional khusus mahasiswa politeknik seluruh Indonesia untuk menguji keahlian terapan di bidang TIK.',
            'persyaratan' => '1. Mahasiswa politeknik aktif se-Indonesia.\n2. Maksimal 3 anggota per tim didampingi 1 dosen pembimbing.\n3. Menyertakan lembar orisinalitas karya.',
            'tenggat' => Carbon::now()->addDays(42),
            'biaya' => 50000,
            'benefit' => 'Piala Bergilir Kemendikbudristek, Sertifikat Juara Nasional, Uang Pembinaan Rp 15.000.000.',
            'tautan_daftar' => 'https://kmipn.polinema.ac.id',
            'status_verifikasi' => 'terverifikasi',
            'poster' => 'kmipn.jpg',
            'nim' => '244107060147',
            'id_admin' => $admin->id_admin,
        ]);

        $lomba3 = Lomba::create([
            'nama_lomba' => 'COMPFEST 17 UI - Software Engineering Academy & Competition',
            'kategori' => 'Software Development',
            'tingkat' => 'Nasional',
            'penyelenggara' => 'Fakultas Ilmu Komputer Universitas Indonesia',
            'deskripsi' => 'Kompetisi Software Engineering berskala nasional yang berkolaborasi dengan unicorn dan tech company terkemuka di Asia Tenggara.',
            'persyaratan' => '1. Terbuka untuk seluruh mahasiswa aktif.\n2. Tim beranggotakan 2-3 orang.\n3. Menyerahkan submission tahap penyisihan berupa ide solusi dan arsitektur sistem.',
            'tenggat' => Carbon::now()->addDays(15),
            'biaya' => 75000,
            'benefit' => 'Hadiah total puluhan juta rupiah, mentoring eksklusif dari CTO/Tech Lead industri, fast-track hiring.',
            'tautan_daftar' => 'https://compfest.id',
            'status_verifikasi' => 'terverifikasi',
            'poster' => 'compfest.jpg',
            'nim' => '2341720055',
            'id_admin' => $admin->id_admin,
        ]);

        $lomba4 = Lomba::create([
            'nama_lomba' => 'Data Science Hackathon 2026 - DataFest Asia',
            'kategori' => 'Data Science & AI',
            'tingkat' => 'Internasional',
            'penyelenggara' => 'Data Science Association International & Tech Asia',
            'deskripsi' => 'Tantangan pemecahan masalah data skala besar menggunakan algoritma Machine Learning, Deep Learning, dan Business Intelligence.',
            'persyaratan' => '1. Mahasiswa sarjana/diploma aktif.\n2. Menguasai Python/R dan tools analitik data.\n3. Submisi notebook dan presentasi video.',
            'tenggat' => Carbon::now()->addDays(60),
            'biaya' => 0,
            'benefit' => 'Hadiah USD 3,000, Sertifikat Internasional, Kesempatan Paper Conference.',
            'tautan_daftar' => 'https://datafest-asia.org',
            'status_verifikasi' => 'terverifikasi',
            'poster' => 'datafest.jpg',
            'nim' => '244107060083',
            'id_admin' => $admin->id_admin,
        ]);

        $lomba5 = Lomba::create([
            'nama_lomba' => 'UI/UX Design Challenge National Tech Summit 2026',
            'kategori' => 'UI/UX Design',
            'tingkat' => 'Nasional',
            'penyelenggara' => 'Himpunan Mahasiswa Teknologi Informasi ITB',
            'deskripsi' => 'Kompetisi perancangan pengalaman pengguna digital yang berfokus pada solusi inklusif dan human-centered design.',
            'persyaratan' => '1. Tim terdiri dari 2 orang.\n2. Submisi berupa prototype Figma dan studi kasus UX.',
            'tenggat' => Carbon::now()->addDays(5),
            'biaya' => 40000,
            'benefit' => 'Uang Pembinaan Rp 8.000.000, Design Kit Pro license 1 tahun, Sertifikat Nasional.',
            'tautan_daftar' => 'https://techsummit-itb.id/uiux',
            'status_verifikasi' => 'terverifikasi',
            'poster' => 'uiux.jpg',
            'nim' => '244107060122',
            'id_admin' => $admin->id_admin,
        ]);

        $lomba6 = Lomba::create([
            'nama_lomba' => 'Capture The Flag (CTF) Cyber Security Defense 2026',
            'kategori' => 'Cyber Security',
            'tingkat' => 'Nasional',
            'penyelenggara' => 'Badan Siber dan Sandi Negara (BSSN) RI',
            'deskripsi' => 'Kompetisi keamanan siber divisi Jeopardy & Attack-Defense meliputi Cryptography, Reverse Engineering, Web Exploitation, dan Forensic.',
            'persyaratan' => '1. Mahasiswa aktif perguruan tinggi.\n2. Tim terdiri atas 3 orang.\n3. Menyetujui pakta integritas ethical hacking.',
            'tenggat' => Carbon::now()->subDays(5), // Telah berakhir
            'biaya' => 0,
            'benefit' => 'Trofi BSSN, sertifikasi lisensi Cyber Analyst, Uang Pembinaan.',
            'tautan_daftar' => 'https://ctf.bssn.go.id',
            'status_verifikasi' => 'terverifikasi',
            'poster' => 'ctf.jpg',
            'nim' => '2341720089',
            'id_admin' => $admin->id_admin,
        ]);

        $lombaPending = Lomba::create([
            'nama_lomba' => 'Hackathon Inovasi Maritim Digital 2026',
            'kategori' => 'Software Development',
            'tingkat' => 'Regional',
            'penyelenggara' => 'Dinas Kelautan & Kominfo Jawa Timur',
            'deskripsi' => 'Ajang hackathon 48 jam untuk menciptakan aplikasi pemberdayaan nelayan dan logistik pelabuhan berbasis IoT dan AI.',
            'persyaratan' => '1. Mahasiswa di wilayah Jawa Timur.\n2. Tim 3-4 orang.',
            'tenggat' => Carbon::now()->addDays(35),
            'biaya' => 0,
            'benefit' => 'Uang tunai Rp 12.000.000 dan inkubasi Pemprov Jatim.',
            'tautan_daftar' => 'https://hackathon-maritim.jatimprov.go.id',
            'status_verifikasi' => 'menunggu', // Belum diverifikasi admin
            'poster' => 'maritim.jpg',
            'nim' => '244107060116',
            'id_admin' => null,
        ]);

        // 5. Tim Lomba
        // Tim 1: RuangLomba Squad (SIB) -> GEMASTIK
        $tim1 = Tim::create([
            'nama_tim' => 'Garuda Tech SIB',
            'id_lomba' => $lomba1->id_lomba,
            'nim' => '244107060116', // Sastra (Ketua)
            'kuota_anggota' => 3,
            'status_tim' => 'disetujui',
            'id_admin' => $admin->id_admin,
        ]);

        AnggotaTim::create(['id_tim' => $tim1->id_tim, 'nim' => '244107060116', 'status_gabung' => 'diterima', 'peran' => 'Ketua & Backend Developer']);
        AnggotaTim::create(['id_tim' => $tim1->id_tim, 'nim' => '244107060147', 'status_gabung' => 'diterima', 'peran' => 'Database Engineer']);
        AnggotaTim::create(['id_tim' => $tim1->id_tim, 'nim' => '244107060122', 'status_gabung' => 'diterima', 'peran' => 'UI/UX Designer']);

        // Tim 2: Informatics Winner (TI) -> COMPFEST
        $tim2 = Tim::create([
            'nama_tim' => 'Algorise TI',
            'id_lomba' => $lomba3->id_lomba,
            'nim' => '2341720055', // Ahmad Fajar (Ketua)
            'kuota_anggota' => 3,
            'status_tim' => 'disetujui',
            'id_admin' => $admin->id_admin,
        ]);

        AnggotaTim::create(['id_tim' => $tim2->id_tim, 'nim' => '2341720055', 'status_gabung' => 'diterima', 'peran' => 'Ketua & Fullstack Developer']);
        AnggotaTim::create(['id_tim' => $tim2->id_tim, 'nim' => '2341720089', 'status_gabung' => 'diterima', 'peran' => 'Cloud & DevOps']);

        // Tim 3: IoT Innovation -> KMIPN
        $tim3 = Tim::create([
            'nama_tim' => 'JTI Smart Sensor',
            'id_lomba' => $lomba2->id_lomba,
            'nim' => '244107060083', // Gempita (Ketua)
            'kuota_anggota' => 3,
            'status_tim' => 'disetujui',
            'id_admin' => $admin->id_admin,
        ]);

        AnggotaTim::create(['id_tim' => $tim3->id_tim, 'nim' => '244107060083', 'status_gabung' => 'diterima', 'peran' => 'Ketua & Hardware Lead']);
        AnggotaTim::create(['id_tim' => $tim3->id_tim, 'nim' => '254107060012', 'status_gabung' => 'diterima', 'peran' => 'Embedded Software']);

        // Tim 4: Cyber Defenders -> CTF (Sudah Selesai & Menang)
        $tim4 = Tim::create([
            'nama_tim' => 'Polinema Cyber Sentinel',
            'id_lomba' => $lomba6->id_lomba,
            'nim' => '2341720089', // Citra Dewi (Ketua)
            'kuota_anggota' => 3,
            'status_tim' => 'disetujui',
            'id_admin' => $admin->id_admin,
        ]);

        AnggotaTim::create(['id_tim' => $tim4->id_tim, 'nim' => '2341720089', 'status_gabung' => 'diterima', 'peran' => 'Ketua & Cryptographer']);
        AnggotaTim::create(['id_tim' => $tim4->id_tim, 'nim' => '2341720055', 'status_gabung' => 'diterima', 'peran' => 'Reverse Engineering']);

        // Tim 5: Maba Innovator (Angkatan 2026) -> Lomba 1
        $tim5 = Tim::create([
            'nama_tim' => 'JTI Muda Berkarya',
            'id_lomba' => $lomba1->id_lomba,
            'nim' => '264107060015', // Daffa Raihan (Ketua - 2026)
            'kuota_anggota' => 3,
            'status_tim' => 'disetujui',
            'id_admin' => $admin->id_admin,
        ]);
        AnggotaTim::create(['id_tim' => $tim5->id_tim, 'nim' => '264107060015', 'status_gabung' => 'diterima', 'peran' => 'Ketua & Pitcher']);
        AnggotaTim::create(['id_tim' => $tim5->id_tim, 'nim' => '2641720021', 'status_gabung' => 'diterima', 'peran' => 'Frontend Dev']);

        // Tim 6: UI/UX Master (Angkatan 2024) -> Lomba 5
        $tim6 = Tim::create([
            'nama_tim' => 'Apex Design Studio',
            'id_lomba' => $lomba5->id_lomba,
            'nim' => '244107060147', // Nadya Syantika (Ketua - 2024)
            'kuota_anggota' => 3,
            'status_tim' => 'disetujui',
            'id_admin' => $admin->id_admin,
        ]);
        AnggotaTim::create(['id_tim' => $tim6->id_tim, 'nim' => '244107060147', 'status_gabung' => 'diterima', 'peran' => 'Ketua & UI/UX Designer']);
        AnggotaTim::create(['id_tim' => $tim6->id_tim, 'nim' => '244107060122', 'status_gabung' => 'diterima', 'peran' => 'UX Researcher']);

        // Tim 7: Data Science Hub (Angkatan 2025) -> Lomba 4
        $tim7 = Tim::create([
            'nama_tim' => 'Polinema Data Vanguard',
            'id_lomba' => $lomba4->id_lomba,
            'nim' => '2541720034', // Rania Putri (Ketua - 2025)
            'kuota_anggota' => 3,
            'status_tim' => 'disetujui',
            'id_admin' => $admin->id_admin,
        ]);
        AnggotaTim::create(['id_tim' => $tim7->id_tim, 'nim' => '2541720034', 'status_gabung' => 'diterima', 'peran' => 'Ketua & Data Analyst']);
        AnggotaTim::create(['id_tim' => $tim7->id_tim, 'nim' => '254107060012', 'status_gabung' => 'diterima', 'peran' => 'ML Engineer']);

        // 6. Pengajuan Bimbingan
        // Bimbingan Tim 1 -> Ade Ismail
        $pengajuan1 = PengajuanBimbingan::create([
            'id_tim' => $tim1->id_tim,
            'nidn' => '0012058501',
            'tgl_pengajuan' => Carbon::now()->subDays(20),
            'status' => 'disetujui',
            'catatan' => 'Ide arsitektur sistem sangat baik. Silakan lanjutkan ke tahap implementasi MVP dan lakukan bimbingan rutin tiap pekan.',
        ]);

        // Bimbingan Tim 2 -> Rudy Ariyanto
        $pengajuan2 = PengajuanBimbingan::create([
            'id_tim' => $tim2->id_tim,
            'nidn' => '0015088202',
            'tgl_pengajuan' => Carbon::now()->subDays(15),
            'status' => 'disetujui',
            'catatan' => 'Fokus pada business model dan user adoption saat pitch deck.',
        ]);

        // Bimbingan Tim 3 -> Rosa Andrie Asmara
        $pengajuan3 = PengajuanBimbingan::create([
            'id_tim' => $tim3->id_tim,
            'nidn' => '0024098904',
            'tgl_pengajuan' => Carbon::now()->subDays(10),
            'status' => 'disetujui',
            'catatan' => 'Perhatikan latency pengiriman data sensor ke cloud broker.',
        ]);

        // Bimbingan Tim 4 -> Usman Nurhasan
        $pengajuan4 = PengajuanBimbingan::create([
            'id_tim' => $tim4->id_tim,
            'nidn' => '0020048603',
            'tgl_pengajuan' => Carbon::now()->subMonths(2),
            'status' => 'disetujui',
            'catatan' => 'Latihan penetrasi payload web dan kernel exploit diperdalam.',
        ]);

        // Bimbingan Tim 5 -> Mimi Nur Hajizah
        $pengajuan5 = PengajuanBimbingan::create([
            'id_tim' => $tim5->id_tim,
            'nidn' => '0019038705',
            'tgl_pengajuan' => Carbon::now()->subDays(8),
            'status' => 'disetujui',
            'catatan' => 'Ide kreatif untuk kompetisi tingkat pemula.',
        ]);

        // Bimbingan Tim 6 -> Mimi Nur Hajizah
        $pengajuan6 = PengajuanBimbingan::create([
            'id_tim' => $tim6->id_tim,
            'nidn' => '0019038705',
            'tgl_pengajuan' => Carbon::now()->subDays(12),
            'status' => 'disetujui',
            'catatan' => 'Design thinking dan user flow sangat matang.',
        ]);

        // Bimbingan Tim 7 -> Rosa Andrie Asmara
        $pengajuan7 = PengajuanBimbingan::create([
            'id_tim' => $tim7->id_tim,
            'nidn' => '0024098904',
            'tgl_pengajuan' => Carbon::now()->subDays(14),
            'status' => 'disetujui',
            'catatan' => 'Metode prediksi dan akurasi model data sudah teruji.',
        ]);

        // 7. Logbook Bimbingan
        Logbook::create([
            'id_pengajuan' => $pengajuan1->id_pengajuan,
            'tanggal' => Carbon::now()->subDays(18),
            'materi' => 'Konsultasi arsitektur database relasional dan use case diagram sistem.',
            'tindak_lanjut' => 'Menyempurnakan foreign key constraint dan relasi multi-role.',
            'status_validasi' => 'disetujui',
        ]);

        Logbook::create([
            'id_pengajuan' => $pengajuan1->id_pengajuan,
            'tanggal' => Carbon::now()->subDays(11),
            'materi' => 'Review mockup UI/UX dan alur autentikasi email kampus.',
            'tindak_lanjut' => 'Menambahkan indikator status babak dan dashboard analitik visual.',
            'status_validasi' => 'disetujui',
        ]);

        Logbook::create([
            'id_pengajuan' => $pengajuan4->id_pengajuan,
            'tanggal' => Carbon::now()->subMonths(1)->subDays(10),
            'materi' => 'Evaluasi writeup CTF babak penyisihan dan simulasi attack-defense.',
            'tindak_lanjut' => 'Otomasi script deteksi flag menggunakan Python.',
            'status_validasi' => 'disetujui',
        ]);

        // 8. Progres Babak & Kemenangan / Prestasi
        // Tim 1 (Angkatan 2024 - Sastra, Garuda Tech): Babak Final -> Juara 1 Nasional & Best Prototype!
        ProgresBabak::create([
            'id_pengajuan' => $pengajuan1->id_pengajuan,
            'babak' => 'Penyisihan',
            'tanggal_update' => Carbon::now()->subDays(14),
            'hasil_akhir' => 'Lolos ke Semifinal',
            'status_setuju' => 'disetujui',
        ]);

        ProgresBabak::create([
            'id_pengajuan' => $pengajuan1->id_pengajuan,
            'babak' => 'Final',
            'tanggal_update' => Carbon::now()->subDays(1),
            'hasil_akhir' => 'Juara 1 Nasional',
            'status_setuju' => 'disetujui',
        ]);

        ProgresBabak::create([
            'id_pengajuan' => $pengajuan1->id_pengajuan,
            'babak' => 'Final Exhibition',
            'tanggal_update' => Carbon::now()->subDays(1),
            'hasil_akhir' => 'Best Prototype & Implementation',
            'status_setuju' => 'disetujui',
        ]);

        // Tim 6 (Angkatan 2024 - Nadya, Apex Design): Babak Final -> Juara 2 UI/UX
        ProgresBabak::create([
            'id_pengajuan' => $pengajuan6->id_pengajuan,
            'babak' => 'Final',
            'tanggal_update' => Carbon::now()->subDays(2),
            'hasil_akhir' => 'Juara 2 Nasional UI/UX',
            'status_setuju' => 'disetujui',
        ]);

        // Tim 4 (Angkatan 2023 - Citra Dewi, Polinema Cyber Sentinel): Babak Final -> Juara 1 Nasional!
        ProgresBabak::create([
            'id_pengajuan' => $pengajuan4->id_pengajuan,
            'babak' => 'Final',
            'tanggal_update' => Carbon::now()->subDays(10),
            'hasil_akhir' => 'Juara 1 Nasional CTF',
            'status_setuju' => 'disetujui',
        ]);

        // Tim 2 (Angkatan 2023 - Ahmad Fajar, Algorise): Babak Final -> Juara 2 Software Engineering
        ProgresBabak::create([
            'id_pengajuan' => $pengajuan2->id_pengajuan,
            'babak' => 'Final',
            'tanggal_update' => Carbon::now()->subDays(5),
            'hasil_akhir' => 'Juara 2 Software Engineering',
            'status_setuju' => 'disetujui',
        ]);

        // Tim 3 (Angkatan 2024/2025 - Gempita, Smart Sensor): Babak Final -> Juara Harapan 1
        ProgresBabak::create([
            'id_pengajuan' => $pengajuan3->id_pengajuan,
            'babak' => 'Final',
            'tanggal_update' => Carbon::now()->subDays(3),
            'hasil_akhir' => 'Juara Harapan 1 Nasional IoT',
            'status_setuju' => 'disetujui',
        ]);

        // Tim 7 (Angkatan 2025 - Rania Putri, Data Vanguard): Babak Final -> Juara 2 Data Science
        ProgresBabak::create([
            'id_pengajuan' => $pengajuan7->id_pengajuan,
            'babak' => 'Final',
            'tanggal_update' => Carbon::now()->subDays(7),
            'hasil_akhir' => 'Juara 2 Data Analytics',
            'status_setuju' => 'disetujui',
        ]);

        // Tim 5 (Angkatan 2026 - Daffa Raihan, JTI Muda): Babak Final -> Juara 3 Kompetisi Ide
        ProgresBabak::create([
            'id_pengajuan' => $pengajuan5->id_pengajuan,
            'babak' => 'Final',
            'tanggal_update' => Carbon::now()->subDays(4),
            'hasil_akhir' => 'Juara 3 Business Pitching Maba',
            'status_setuju' => 'disetujui',
        ]);
    }
}
