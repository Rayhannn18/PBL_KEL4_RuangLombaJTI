-- =====================================================================
-- SKEMA DATABASE RUANG LOMBA JTI (SUPABASE POSTGRESQL)
-- Fokus: Modul Bimbingan & Dashboard Analitik Prestasi
-- =====================================================================

-- 1. Tabel Mahasiswa
CREATE TABLE IF NOT EXISTS mahasiswa (
    nim VARCHAR(20) PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    email_kampus VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    prodi VARCHAR(100) NOT NULL,
    angkatan INT NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 2. Tabel Dosen (Pembimbing)
CREATE TABLE IF NOT EXISTS dosen (
    nidn VARCHAR(20) PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    email_kampus VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    bidang_keahlian VARCHAR(255) NOT NULL,
    kuota_bimbingan INT DEFAULT 5,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 3. Tabel Admin
CREATE TABLE IF NOT EXISTS admin (
    id_admin BIGSERIAL PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 4. Tabel Lomba (Konteks Kompetisi untuk Dashboard & Bimbingan)
CREATE TABLE IF NOT EXISTS lomba (
    id_lomba BIGSERIAL PRIMARY KEY,
    nama_lomba VARCHAR(255) NOT NULL,
    kategori VARCHAR(100) NOT NULL,
    tingkat VARCHAR(50) NOT NULL, -- Nasional, Internasional, Regional, Provinsi
    penyelenggara VARCHAR(150) NOT NULL,
    deskripsi TEXT,
    persyaratan TEXT,
    tenggat DATE NOT NULL,
    biaya NUMERIC(12, 2) DEFAULT 0,
    benefit TEXT,
    tautan_daftar VARCHAR(255) NOT NULL,
    status_verifikasi VARCHAR(30) DEFAULT 'menunggu', -- terverifikasi, menunggu, ditolak
    poster VARCHAR(255),
    nim VARCHAR(20) REFERENCES mahasiswa(nim) ON DELETE SET NULL,
    id_admin BIGINT REFERENCES admin(id_admin) ON DELETE SET NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 5. Tabel Tim
CREATE TABLE IF NOT EXISTS tim (
    id_tim BIGSERIAL PRIMARY KEY,
    nama_tim VARCHAR(150) NOT NULL,
    id_lomba BIGINT NOT NULL REFERENCES lomba(id_lomba) ON DELETE CASCADE,
    nim VARCHAR(20) NOT NULL REFERENCES mahasiswa(nim) ON DELETE CASCADE, -- Ketua Tim
    kuota_anggota INT DEFAULT 3,
    status_tim VARCHAR(30) DEFAULT 'menunggu_persetujuan', -- disetujui, menunggu_persetujuan, ditolak
    id_admin BIGINT REFERENCES admin(id_admin) ON DELETE SET NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 6. Tabel Anggota Tim
CREATE TABLE IF NOT EXISTS anggota_tim (
    id_anggota BIGSERIAL PRIMARY KEY,
    id_tim BIGINT NOT NULL REFERENCES tim(id_tim) ON DELETE CASCADE,
    nim VARCHAR(20) NOT NULL REFERENCES mahasiswa(nim) ON DELETE CASCADE,
    status_gabung VARCHAR(30) DEFAULT 'menunggu', -- diterima, menunggu, ditolak
    peran VARCHAR(100) DEFAULT 'Anggota',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 7. TABEL MODUL BIMBINGAN: Pengajuan Bimbingan
CREATE TABLE IF NOT EXISTS pengajuan_bimbingan (
    id_pengajuan BIGSERIAL PRIMARY KEY,
    id_tim BIGINT NOT NULL REFERENCES tim(id_tim) ON DELETE CASCADE,
    nidn VARCHAR(20) NOT NULL REFERENCES dosen(nidn) ON DELETE CASCADE,
    tgl_pengajuan DATE NOT NULL,
    status VARCHAR(30) DEFAULT 'menunggu', -- disetujui, menunggu, ditolak
    catatan TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 8. TABEL MODUL BIMBINGAN: Logbook Aktivitas Bimbingan
CREATE TABLE IF NOT EXISTS logbook (
    id_logbook BIGSERIAL PRIMARY KEY,
    id_pengajuan BIGINT NOT NULL REFERENCES pengajuan_bimbingan(id_pengajuan) ON DELETE CASCADE,
    tanggal DATE NOT NULL,
    materi TEXT NOT NULL,
    tindak_lanjut TEXT,
    status_validasi VARCHAR(30) DEFAULT 'menunggu', -- disetujui, menunggu, revisi
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 9. TABEL MODUL BIMBINGAN & DASHBOARD: Progres Babak & Capaian Prestasi
CREATE TABLE IF NOT EXISTS progres_babak (
    id_progres BIGSERIAL PRIMARY KEY,
    id_pengajuan BIGINT NOT NULL REFERENCES pengajuan_bimbingan(id_pengajuan) ON DELETE CASCADE,
    babak VARCHAR(50) NOT NULL, -- Persiapan, Penyisihan, Semifinal, Final
    tanggal_update DATE NOT NULL,
    hasil_akhir VARCHAR(100), -- Juara 1, Juara 2, Juara 3, Juara Harapan, Lolos, Finalis
    status_setuju VARCHAR(30) DEFAULT 'menunggu', -- disetujui, menunggu, ditolak
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================================
-- DATA SEED AWAL (DUMMY REALISTIS POLINEMA UNTUK TESTING DASHBOARD & BIMBINGAN)
-- Password default: password123 (bcrypt hash: $2y$12$e6yD2v7c4Oq7/k2h0v3T1e2y.kF4bQy1rKq7XmN2z1g8i5q1p4y2a)
-- =====================================================================

-- Admin
INSERT INTO admin (id_admin, nama, email, password) VALUES
(1, 'Admin Kemahasiswaan JTI', 'admin.jti@polinema.ac.id', '$2y$12$tB6e4Uv6pI2K8xQyWb9sA.Z8uRzL1u7aJt6sQx5lQyFmJbQ3xP7qO')
ON CONFLICT (id_admin) DO NOTHING;

-- Dosen Pembimbing
INSERT INTO dosen (nidn, nama, email_kampus, password, bidang_keahlian, kuota_bimbingan) VALUES
('0012058501', 'Ade Ismail, S.Kom., M.T.I.', 'ade.ismail@polinema.ac.id', '$2y$12$tB6e4Uv6pI2K8xQyWb9sA.Z8uRzL1u7aJt6sQx5lQyFmJbQ3xP7qO', 'Software Engineering & Web Development', 6),
('0015088202', 'Rudy Ariyanto, S.T., M.Cs.', 'rudy.ariyanto@polinema.ac.id', '$2y$12$tB6e4Uv6pI2K8xQyWb9sA.Z8uRzL1u7aJt6sQx5lQyFmJbQ3xP7qO', 'Information Systems & Project Management', 5),
('0020048603', 'Usman Nurhasan, S.Kom., M.T.', 'usman.nurhasan@polinema.ac.id', '$2y$12$tB6e4Uv6pI2K8xQyWb9sA.Z8uRzL1u7aJt6sQx5lQyFmJbQ3xP7qO', 'Cyber Security & Software Quality Assurance', 5),
('0024098904', 'Dr. Eng. Rosa Andrie Asmara, S.T., M.T.', 'rosa.andrie@polinema.ac.id', '$2y$12$tB6e4Uv6pI2K8xQyWb9sA.Z8uRzL1u7aJt6sQx5lQyFmJbQ3xP7qO', 'Artificial Intelligence & Computer Vision', 4),
('0019038705', 'Mimi Nur Hajizah, S.Kom., M.Sc.', 'mimi.nur@polinema.ac.id', '$2y$12$tB6e4Uv6pI2K8xQyWb9sA.Z8uRzL1u7aJt6sQx5lQyFmJbQ3xP7qO', 'UI/UX Design & Human-Computer Interaction', 5)
ON CONFLICT (nidn) DO NOTHING;

-- Mahasiswa
INSERT INTO mahasiswa (nim, nama, email_kampus, password, prodi, angkatan) VALUES
('244107060116', 'Sastra Maheva Zaky', 'sastra.maheva@student.polinema.ac.id', '$2y$12$tB6e4Uv6pI2K8xQyWb9sA.Z8uRzL1u7aJt6sQx5lQyFmJbQ3xP7qO', 'D-IV Sistem Informasi Bisnis', 2024),
('244107060147', 'Nadya Syantika Naraya', 'nadya.syantika@student.polinema.ac.id', '$2y$12$tB6e4Uv6pI2K8xQyWb9sA.Z8uRzL1u7aJt6sQx5lQyFmJbQ3xP7qO', 'D-IV Sistem Informasi Bisnis', 2024),
('244107060083', 'Gempita Fitri Nurdini', 'gempita.fitri@student.polinema.ac.id', '$2y$12$tB6e4Uv6pI2K8xQyWb9sA.Z8uRzL1u7aJt6sQx5lQyFmJbQ3xP7qO', 'D-IV Sistem Informasi Bisnis', 2024),
('244107060122', 'Rayhan Giri Putra', 'rayhan.giri@student.polinema.ac.id', '$2y$12$tB6e4Uv6pI2K8xQyWb9sA.Z8uRzL1u7aJt6sQx5lQyFmJbQ3xP7qO', 'D-IV Sistem Informasi Bisnis', 2024),
('2341720055', 'Ahmad Fajar Pratama', 'ahmad.fajar@student.polinema.ac.id', '$2y$12$tB6e4Uv6pI2K8xQyWb9sA.Z8uRzL1u7aJt6sQx5lQyFmJbQ3xP7qO', 'D-IV Teknik Informatika', 2023),
('2341720089', 'Citra Dewi Kusuma', 'citra.dewi@student.polinema.ac.id', '$2y$12$tB6e4Uv6pI2K8xQyWb9sA.Z8uRzL1u7aJt6sQx5lQyFmJbQ3xP7qO', 'D-IV Teknik Informatika', 2023),
('254107060012', 'Bima Arya Nugraha', 'bima.arya@student.polinema.ac.id', '$2y$12$tB6e4Uv6pI2K8xQyWb9sA.Z8uRzL1u7aJt6sQx5lQyFmJbQ3xP7qO', 'D-IV Sistem Informasi Bisnis', 2025),
('2541720034', 'Rania Putri Safitri', 'rania.putri@student.polinema.ac.id', '$2y$12$tB6e4Uv6pI2K8xQyWb9sA.Z8uRzL1u7aJt6sQx5lQyFmJbQ3xP7qO', 'D-IV Teknik Informatika', 2025),
('264107060015', 'Daffa Raihan Alfarizi', 'daffa.raihan@student.polinema.ac.id', '$2y$12$tB6e4Uv6pI2K8xQyWb9sA.Z8uRzL1u7aJt6sQx5lQyFmJbQ3xP7qO', 'D-IV Sistem Informasi Bisnis', 2026),
('2641720021', 'Nabila Syahrani Putri', 'nabila.syahrani@student.polinema.ac.id', '$2y$12$tB6e4Uv6pI2K8xQyWb9sA.Z8uRzL1u7aJt6sQx5lQyFmJbQ3xP7qO', 'D-IV Teknik Informatika', 2026)
ON CONFLICT (nim) DO NOTHING;

-- Lomba
INSERT INTO lomba (id_lomba, nama_lomba, kategori, tingkat, penyelenggara, deskripsi, persyaratan, tenggat, biaya, benefit, tautan_daftar, status_verifikasi, nim, id_admin) VALUES
(1, 'GEMASTIK XVIII (Pagelaran Mahasiswa Nasional TIK)', 'Software Development', 'Nasional', 'BPTI Kemendikbudristek', 'Kompetisi TIK paling bergengsi perguruan tinggi se-Indonesia.', 'Mahasiswa aktif D3/D4/S1, tim 3 orang + 1 dosen pembimbing.', CURRENT_DATE + INTERVAL '30 day', 0, 'Medali Puspresnas & Uang Pembinaan', 'https://gemastik.kemdikbud.go.id', 'terverifikasi', '244107060116', 1),
(2, 'KMIPN VII (Kompetisi Mahasiswa Informatika Politeknik Nasional)', 'Internet of Things & Embedded System', 'Nasional', 'BAKLLMA Politeknik Se-Indonesia', 'Ajang kompetisi nasional mahasiswa politeknik seluruh Indonesia.', 'Mahasiswa politeknik aktif se-Indonesia, tim 3 orang.', CURRENT_DATE + INTERVAL '45 day', 50000, 'Piala Bergilir & Uang Pembinaan Rp 15jt', 'https://kmipn.polinema.ac.id', 'terverifikasi', '244107060147', 1),
(3, 'COMPFEST 17 UI - Software Engineering', 'Software Development', 'Nasional', 'Fasilkom Universitas Indonesia', 'Kompetisi rekayasa perangkat lunak kolaborasi tech unicorn.', 'Tim 2-3 orang, submission ide & arsitektur.', CURRENT_DATE + INTERVAL '15 day', 75000, 'Mentoring eksklusif CTO & Fast-track hiring', 'https://compfest.id', 'terverifikasi', '2341720055', 1),
(4, 'Data Science Hackathon 2026 - DataFest Asia', 'Data Science & AI', 'Internasional', 'Data Science Association International', 'Tantangan analisis big data dan machine learning.', 'Notebook Python dan presentasi video.', CURRENT_DATE + INTERVAL '60 day', 0, 'Hadiah USD 3,000 & Paper Conference', 'https://datafest-asia.org', 'terverifikasi', '244107060083', 1),
(5, 'UI/UX Design Challenge National Tech Summit 2026', 'UI/UX Design', 'Nasional', 'HMIF Institut Teknologi Bandung', 'Kompetisi perancangan pengalaman pengguna digital.', 'Tim 2 orang, prototype Figma & UX case study.', CURRENT_DATE + INTERVAL '5 day', 40000, 'Uang Pembinaan Rp 8.000.000 & License kit', 'https://techsummit-itb.id/uiux', 'terverifikasi', '244107060122', 1),
(6, 'Capture The Flag (CTF) Cyber Security Defense 2026', 'Cyber Security', 'Nasional', 'BSSN RI', 'Kompetisi cyber security Jeopardy & Attack-Defense.', 'Tim 3 orang mahasiswa.', CURRENT_DATE - INTERVAL '10 day', 0, 'Trofi BSSN & Sertifikasi Cyber Analyst', 'https://ctf.bssn.go.id', 'terverifikasi', '2341720089', 1)
ON CONFLICT (id_lomba) DO NOTHING;

-- Tim
INSERT INTO tim (id_tim, nama_tim, id_lomba, nim, kuota_anggota, status_tim, id_admin) VALUES
(1, 'Garuda Tech SIB', 1, '244107060116', 3, 'disetujui', 1),
(2, 'Algorise TI', 3, '2341720055', 3, 'disetujui', 1),
(3, 'JTI Smart Sensor', 2, '244107060083', 3, 'disetujui', 1),
(4, 'Polinema Cyber Sentinel', 6, '2341720089', 3, 'disetujui', 1),
(5, 'JTI Muda Berkarya', 1, '264107060015', 3, 'disetujui', 1),
(6, 'Apex Design Studio', 5, '244107060147', 3, 'disetujui', 1),
(7, 'Polinema Data Vanguard', 4, '2541720034', 3, 'disetujui', 1)
ON CONFLICT (id_tim) DO NOTHING;

-- Anggota Tim
INSERT INTO anggota_tim (id_anggota, id_tim, nim, status_gabung, peran) VALUES
(1, 1, '244107060116', 'diterima', 'Ketua & Backend Developer'),
(2, 1, '244107060147', 'diterima', 'Database Engineer'),
(3, 1, '244107060122', 'diterima', 'UI/UX Designer'),
(4, 2, '2341720055', 'diterima', 'Ketua & Fullstack Developer'),
(5, 2, '2341720089', 'diterima', 'Cloud & DevOps'),
(6, 3, '244107060083', 'diterima', 'Ketua & Hardware Lead'),
(7, 3, '254107060012', 'diterima', 'Embedded Software'),
(8, 4, '2341720089', 'diterima', 'Ketua & Cryptographer'),
(9, 4, '2341720055', 'diterima', 'Reverse Engineering'),
(10, 5, '264107060015', 'diterima', 'Ketua & Pitcher'),
(11, 5, '2641720021', 'diterima', 'Frontend Developer'),
(12, 6, '244107060147', 'diterima', 'Ketua & UI/UX Designer'),
(13, 6, '244107060122', 'diterima', 'UX Researcher'),
(14, 7, '2541720034', 'diterima', 'Ketua & Data Analyst'),
(15, 7, '254107060012', 'diterima', 'ML Engineer')
ON CONFLICT (id_anggota) DO NOTHING;

-- Pengajuan Bimbingan
INSERT INTO pengajuan_bimbingan (id_pengajuan, id_tim, nidn, tgl_pengajuan, status, catatan) VALUES
(1, 1, '0012058501', CURRENT_DATE - INTERVAL '20 day', 'disetujui', 'Ide arsitektur sistem sangat baik. Silakan bimbingan rutin tiap pekan.'),
(2, 2, '0015088202', CURRENT_DATE - INTERVAL '15 day', 'disetujui', 'Fokus pada business model dan user adoption saat pitch deck.'),
(3, 3, '0024098904', CURRENT_DATE - INTERVAL '10 day', 'disetujui', 'Perhatikan latency pengiriman data sensor ke cloud broker.'),
(4, 4, '0020048603', CURRENT_DATE - INTERVAL '60 day', 'disetujui', 'Latihan penetrasi payload web dan kernel exploit diperdalam.'),
(5, 5, '0019038705', CURRENT_DATE - INTERVAL '8 day', 'disetujui', 'Ide kreatif untuk kompetisi tingkat pemula.'),
(6, 6, '0019038705', CURRENT_DATE - INTERVAL '12 day', 'disetujui', 'Design thinking dan user flow sangat matang.'),
(7, 7, '0024098904', CURRENT_DATE - INTERVAL '14 day', 'disetujui', 'Metode prediksi dan akurasi model data sudah teruji.')
ON CONFLICT (id_pengajuan) DO NOTHING;

-- Logbook Bimbingan
INSERT INTO logbook (id_logbook, id_pengajuan, tanggal, materi, tindak_lanjut, status_validasi) VALUES
(1, 1, CURRENT_DATE - INTERVAL '18 day', 'Konsultasi arsitektur database relasional dan use case diagram sistem.', 'Menyempurnakan foreign key constraint dan relasi multi-role.', 'disetujui'),
(2, 1, CURRENT_DATE - INTERVAL '11 day', 'Review mockup UI/UX dan alur autentikasi email kampus.', 'Menambahkan indikator status babak dan dashboard analitik visual.', 'disetujui'),
(3, 4, CURRENT_DATE - INTERVAL '40 day', 'Evaluasi writeup CTF babak penyisihan dan simulasi attack-defense.', 'Otomasi script deteksi flag menggunakan Python.', 'disetujui')
ON CONFLICT (id_logbook) DO NOTHING;

-- Progres Babak & Juara (Feed Dashboard Analitik)
INSERT INTO progres_babak (id_progres, id_pengajuan, babak, tanggal_update, hasil_akhir, status_setuju) VALUES
(1, 1, 'Penyisihan', CURRENT_DATE - INTERVAL '14 day', 'Lolos ke Semifinal', 'disetujui'),
(2, 1, 'Final', CURRENT_DATE - INTERVAL '1 day', 'Juara 1 Nasional', 'disetujui'),
(3, 1, 'Final Exhibition', CURRENT_DATE - INTERVAL '1 day', 'Best Prototype & Implementation', 'disetujui'),
(4, 6, 'Final', CURRENT_DATE - INTERVAL '2 day', 'Juara 2 Nasional UI/UX', 'disetujui'),
(5, 4, 'Final', CURRENT_DATE - INTERVAL '10 day', 'Juara 1 Nasional CTF', 'disetujui'),
(6, 2, 'Final', CURRENT_DATE - INTERVAL '5 day', 'Juara 2 Software Engineering', 'disetujui'),
(7, 3, 'Final', CURRENT_DATE - INTERVAL '3 day', 'Juara Harapan 1 Nasional IoT', 'disetujui'),
(8, 7, 'Final', CURRENT_DATE - INTERVAL '7 day', 'Juara 2 Data Analytics', 'disetujui'),
(9, 5, 'Final', CURRENT_DATE - INTERVAL '4 day', 'Juara 3 Business Pitching Maba', 'disetujui')
ON CONFLICT (id_progres) DO NOTHING;

-- Reset Sequence PostgreSQL agar auto-increment ID berlanjut dengan benar
SELECT setval('admin_id_admin_seq', (SELECT COALESCE(MAX(id_admin), 1) FROM admin));
SELECT setval('lomba_id_lomba_seq', (SELECT COALESCE(MAX(id_lomba), 1) FROM lomba));
SELECT setval('tim_id_tim_seq', (SELECT COALESCE(MAX(id_tim), 1) FROM tim));
SELECT setval('anggota_tim_id_anggota_seq', (SELECT COALESCE(MAX(id_anggota), 1) FROM anggota_tim));
SELECT setval('pengajuan_bimbingan_id_pengajuan_seq', (SELECT COALESCE(MAX(id_pengajuan), 1) FROM pengajuan_bimbingan));
SELECT setval('logbook_id_logbook_seq', (SELECT COALESCE(MAX(id_logbook), 1) FROM logbook));
SELECT setval('progres_babak_id_progres_seq', (SELECT COALESCE(MAX(id_progres), 1) FROM progres_babak));
