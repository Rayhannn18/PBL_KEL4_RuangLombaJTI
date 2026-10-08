@extends('layouts.app')

@section('content')

@push('styles')
<style>
    /* Bimbingan Header */
    .bimbingan-hero {
        background: linear-gradient(135deg, #0F2744 0%, #1E40AF 100%);
        border-radius: var(--radius-lg);
        color: #fff;
        padding: 30px 35px;
        margin-bottom: 28px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 25px -5px rgba(15, 39, 68, 0.15);
    }
    .bimbingan-hero::after {
        content: '';
        position: absolute;
        top: -60px;
        right: -60px;
        width: 240px;
        height: 240px;
        background: radial-gradient(circle, rgba(59, 130, 246, 0.3) 0%, rgba(255,255,255,0) 70%);
        border-radius: 50%;
        pointer-events: none;
    }
    .hero-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(8px);
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 600;
        margin-bottom: 12px;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    /* Empty State (Belum Ikut Lomba) */
    .empty-lomba-card {
        background: var(--surface);
        border-radius: var(--radius-lg);
        border: 1px solid var(--border);
        box-shadow: var(--shadow-md);
        padding: 40px 30px;
        text-align: center;
        margin-bottom: 35px;
        position: relative;
        overflow: hidden;
    }
    .empty-lomba-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, #F59E0B 0%, #3B82F6 100%);
    }
    .empty-icon-wrap {
        width: 90px;
        height: 90px;
        margin: 0 auto 20px;
        border-radius: 50%;
        background: linear-gradient(135deg, #FEF3C7 0%, #DBEAFE 100%);
        color: #1E40AF;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.8rem;
        box-shadow: 0 10px 20px rgba(30, 64, 175, 0.1);
    }
    .step-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin: 32px 0 24px;
        text-align: left;
    }
    .step-card {
        background: #F8FAFC;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 20px;
        position: relative;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .step-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-sm);
        border-color: #BFDBFE;
    }
    .step-number {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: var(--primary);
        color: #fff;
        font-weight: 800;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 12px;
    }

    /* Grid Lomba Rekomendasi */
    .lomba-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 20px;
        margin-top: 15px;
    }
    .lomba-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-sm);
        padding: 20px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: all 0.2s;
    }
    .lomba-card:hover {
        border-color: #93C5FD;
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
    }

    /* Timeline Logbook */
    .timeline-wrap {
        position: relative;
        padding-left: 28px;
    }
    .timeline-wrap::before {
        content: '';
        position: absolute;
        left: 10px;
        top: 6px;
        bottom: 6px;
        width: 2px;
        background: #E2E8F0;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 22px;
    }
    .timeline-point {
        position: absolute;
        left: -28px;
        top: 4px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #fff;
        border: 4px solid var(--primary);
        box-shadow: 0 0 0 2px rgba(30, 64, 175, 0.15);
    }
    .timeline-content {
        background: #F8FAFC;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 16px 18px;
    }

    /* Modal Form */
    .modal-backdrop-custom {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        z-index: 999;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .modal-box {
        background: #fff;
        border-radius: var(--radius-lg);
        max-width: 550px;
        width: 100%;
        box-shadow: var(--shadow-lg);
        overflow: hidden;
        animation: modalSlide 0.25s ease-out;
    }
    @keyframes modalSlide {
        from { transform: translateY(20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    .modal-header-custom {
        padding: 18px 24px;
        background: #F8FAFC;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .modal-body-custom {
        padding: 24px;
    }
    .modal-footer-custom {
        padding: 16px 24px;
        background: #F8FAFC;
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
</style>
@endpush

    <!-- Hero Header Modul Bimbingan -->
    <div class="bimbingan-hero">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 20px;">
            <div>
                <div class="hero-tag">
                    <i class="bi bi-mortarboard-fill"></i> Modul Bimbingan Akademik & Kompetisi
                </div>
                <h1 style="font-size: 1.85rem; font-weight: 800; letter-spacing: -0.5px; margin-bottom: 8px;">
                    Portal Pendampingan Tim Lomba JTI Polinema
                </h1>
                <p style="font-size: 0.95rem; opacity: 0.9; max-width: 650px; line-height: 1.6; margin: 0;">
                    Fasilitas konsultasi intensif karya inovasi, rekam jejak logbook bimbingan, serta pemantauan progres kompetisi mahasiswa bersama Dosen Pembimbing.
                </p>
            </div>
            <div>
                <div style="background: rgba(255,255,255,0.12); padding: 10px 16px; border-radius: var(--radius-md); border: 1px solid rgba(255,255,255,0.2); font-size: 0.85rem;">
                    <div style="opacity: 0.8; font-size: 0.75rem;">Status Akun Masuk:</div>
                    <strong>{{ $authUser['nama'] }}</strong>
                    <div style="font-size: 0.78rem; opacity: 0.9; margin-top: 2px;">
                        Role: <span style="text-transform: capitalize; color: #93C5FD; font-weight: 700;">{{ $authUser['role'] }}</span>
                        @if(!empty($authUser['extra']))
                            &bull; {{ $authUser['extra'] }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- KONDISI 1: MAHASISWA BELUM IKUT LOMBA (Empty State Sesuai Permintaan User) --}}
    {{-- ========================================================================= --}}
    @if($role === 'mahasiswa' && !$hasLomba)
        <div class="empty-lomba-card">
            <div class="empty-icon-wrap">
                <i class="bi bi-trophy"></i>
            </div>
            <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--primary-dark); margin-bottom: 10px;">
                Anda Belum Terdaftar dalam Tim Lomba
            </h2>
            <p style="font-size: 0.95rem; color: var(--text-muted); max-width: 680px; margin: 0 auto; line-height: 1.7;">
                Modul bimbingan ini diperuntukkan bagi mahasiswa yang telah berpartisipasi dalam kompetisi.
                Untuk dapat <strong>mengajukan Dosen Pembimbing</strong> dan <strong>mengisi logbook konsultasi</strong>, Anda harus terlebih dahulu mendaftarkan tim pada lomba yang tersedia.
            </p>

            <!-- 3 Langkah Alur Bimbingan -->
            <div class="step-grid">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h4 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 6px; color: var(--primary-dark);">
                        Pilih Lomba & Bentuk Tim
                    </h4>
                    <p style="font-size: 0.82rem; color: var(--text-muted); line-height: 1.5; margin: 0;">
                        Tentukan lomba yang ingin diikuti dan bentuk tim bersama rekan mahasiswa JTI Polinema.
                    </p>
                </div>

                <div class="step-card">
                    <div class="step-number" style="background: #2563EB;">2</div>
                    <h4 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 6px; color: var(--primary-dark);">
                        Ajukan Dosen Pembimbing
                    </h4>
                    <p style="font-size: 0.82rem; color: var(--text-muted); line-height: 1.5; margin: 0;">
                        Pilih dosen pembimbing sesuai bidang keahlian (Software, AI, UI/UX, Jaringan/IoT) untuk memvalidasi proposal.
                    </p>
                </div>

                <div class="step-card">
                    <div class="step-number" style="background: #10B981;">3</div>
                    <h4 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 6px; color: var(--primary-dark);">
                        Konsultasi & Catat Logbook
                    </h4>
                    <p style="font-size: 0.82rem; color: var(--text-muted); line-height: 1.5; margin: 0;">
                        Lakukan bimbingan rutin, catat catatan evaluasi di logbook, dan pantau progres babak hingga meraih juara.
                    </p>
                </div>
            </div>

            <!-- Tombol Aksi Cepat -->
            <div style="display: flex; justify-content: center; gap: 12px; flex-wrap: wrap; margin-top: 20px;">
                <button type="button" class="btn btn-primary" onclick="openModalDaftarTim()">
                    <i class="bi bi-plus-circle-fill"></i> Daftarkan Tim Lomba Sekarang
                </button>
                <a href="{{ route('dashboard.analitik') }}" class="btn btn-outline-primary">
                    <i class="bi bi-graph-up-arrow"></i> Lihat Info Prestasi di Dashboard
                </a>
            </div>
        </div>

        <!-- Daftar Lomba Aktif Terbuka -->
        <div style="margin-bottom: 40px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <div>
                    <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--primary-dark); margin: 0;">
                        <i class="bi bi-award" style="color: var(--primary);"></i> Lomba Terbuka yang Siap Diikuti
                    </h3>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin: 3px 0 0 0;">
                        Pilih kompetisi di bawah ini untuk langsung membentuk tim dan mengaktifkan modul bimbingan Anda.
                    </p>
                </div>
            </div>

            <div class="lomba-grid">
                @forelse($lombaTersedia as $lomba)
                    <div class="lomba-card">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; margin-bottom: 10px;">
                                <span class="badge badge-primary">{{ $lomba->kategori }}</span>
                                <span class="badge badge-purple">{{ $lomba->tingkat }}</span>
                            </div>

                            <h4 style="font-size: 1.05rem; font-weight: 700; color: var(--primary-dark); margin-bottom: 6px; line-height: 1.35;">
                                {{ $lomba->nama_lomba }}
                            </h4>

                            <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 12px;">
                                <i class="bi bi-building"></i> {{ $lomba->penyelenggara }}
                            </div>

                            <p style="font-size: 0.83rem; color: var(--text-main); line-height: 1.5; margin-bottom: 15px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                {{ $lomba->deskripsi ?? 'Kompetisi resmi mahasiswa yang terverifikasi di lingkungan Jurusan Teknologi Informasi Polinema.' }}
                            </p>
                        </div>

                        <div style="border-top: 1px solid var(--border); padding-top: 14px; margin-top: 8px;">
                            <div style="display: flex; justify-content: space-between; font-size: 0.78rem; color: var(--text-muted); margin-bottom: 14px;">
                                <span><i class="bi bi-calendar3"></i> Tenggat: <strong>{{ $lomba->tenggat ? $lomba->tenggat->format('d M Y') : 'Segera' }}</strong></span>
                                <span><i class="bi bi-cash"></i> {{ $lomba->biaya > 0 ? 'Rp ' . number_format($lomba->biaya, 0, ',', '.') : 'Gratis' }}</span>
                            </div>

                            <button type="button"
                                    class="btn btn-outline-primary btn-sm"
                                    style="width: 100%; font-weight: 700;"
                                    onclick="openModalDaftarTim('{{ $lomba->id_lomba }}', '{{ addslashes($lomba->nama_lomba) }}')">
                                <i class="bi bi-people-fill"></i> Ikuti Lomba (Bentuk Tim)
                            </button>
                        </div>
                    </div>
                @empty
                    <div style="grid-column: 1 / -1; background: #fff; border: 1px solid var(--border); border-radius: var(--radius-md); padding: 30px; text-align: center; color: var(--text-muted);">
                        Belum ada kompetisi yang tersedia untuk pendaftaran saat ini.
                    </div>
                @endforelse
            </div>
        </div>

    {{-- ========================================================================= --}}
    {{-- KONDISI 2: MAHASISWA SUDAH IKUT LOMBA (Tampilan Penuh Modul Bimbingan)   --}}
    {{-- ========================================================================= --}}
    @elseif($role === 'mahasiswa' && $hasLomba)

        <!-- Pilihan Tim (Jika Ikut Lebih dari 1 Tim) -->
        @if($timList->count() > 1)
            <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 12px 18px; margin-bottom: 22px; display: flex; align-items: center; justify-content: space-between; flex-wrap: gap: 10px;">
                <div style="font-size: 0.88rem; font-weight: 700; color: var(--primary-dark);">
                    <i class="bi bi-people-fill" style="color: var(--primary);"></i> Tim yang Anda Ikuti:
                </div>
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    @foreach($timList as $itemTim)
                        <a href="{{ route('bimbingan.index', ['tim_id' => $itemTim->id_tim]) }}"
                           class="btn btn-sm {{ $selectedTim->id_tim == $itemTim->id_tim ? 'btn-primary' : 'btn-secondary' }}">
                            {{ $itemTim->nama_tim }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Layout 2 Kolom Modul Bimbingan -->
        <div style="display: grid; grid-template-columns: 1fr 1.35fr; gap: 24px; align-items: start;">

            <!-- KOLOM KIRI: Identitas Tim & Status Pembimbing -->
            <div style="display: flex; flex-direction: column; gap: 24px;">

                <!-- Kartu Identitas Tim & Lomba -->
                <div class="card">
                    <div class="card-header" style="background: linear-gradient(90deg, #F8FAFC 0%, #EFF6FF 100%);">
                        <div>
                            <div class="card-title">
                                <i class="bi bi-shield-shaded" style="color: var(--primary);"></i> Identitas Tim & Kompetisi
                            </div>
                            <div style="font-size: 0.78rem; color: var(--text-muted);">Informasi partisipasi lomba mahasiswa JTI</div>
                        </div>
                        <span class="badge badge-success">
                            <i class="bi bi-check-circle-fill"></i> Tim Aktif
                        </span>
                    </div>
                    <div class="card-body">
                        <div style="margin-bottom: 16px;">
                            <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--text-muted); letter-spacing: 0.5px;">Nama Tim</div>
                            <div style="font-size: 1.25rem; font-weight: 800; color: var(--primary-dark); margin-top: 2px;">
                                {{ $selectedTim->nama_tim }}
                            </div>
                        </div>

                        <div style="background: #F8FAFC; border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 14px; margin-bottom: 16px;">
                            <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">KOMPETISI YANG DIIKUTI</div>
                            <div style="font-weight: 700; color: var(--text-main); font-size: 0.95rem; margin-bottom: 4px;">
                                {{ $selectedTim->lomba->nama_lomba ?? 'Kompetisi JTI' }}
                            </div>
                            <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                                <span class="badge badge-primary" style="font-size: 0.72rem;">{{ $selectedTim->lomba->kategori ?? 'Umum' }}</span>
                                <span class="badge badge-purple" style="font-size: 0.72rem;">{{ $selectedTim->lomba->tingkat ?? 'Nasional' }}</span>
                            </div>
                        </div>

                        <div>
                            <div style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;">SUSUNAN ANGGOTA TIM:</div>
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.85rem; padding: 6px 10px; background: #fff; border: 1px solid var(--border); border-radius: 6px;">
                                    <div>
                                        <strong>{{ $selectedTim->ketua->nama ?? '-' }}</strong>
                                        <div style="font-size: 0.72rem; color: var(--text-muted);">NIM: {{ $selectedTim->nim }} ({{ $selectedTim->ketua->prodi ?? '' }})</div>
                                    </div>
                                    <span class="badge badge-primary" style="font-size: 0.7rem;">Ketua Tim</span>
                                </div>

                                @foreach($selectedTim->anggotaDiterima as $ang)
                                    @if($ang->nim !== $selectedTim->nim)
                                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.85rem; padding: 6px 10px; background: #fff; border: 1px solid var(--border); border-radius: 6px;">
                                            <div>
                                                <strong>{{ $ang->mahasiswa->nama ?? '-' }}</strong>
                                                <div style="font-size: 0.72rem; color: var(--text-muted);">NIM: {{ $ang->nim }}</div>
                                            </div>
                                            <span class="badge badge-gray" style="font-size: 0.7rem;">{{ $ang->peran ?? 'Anggota' }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kartu Status Dosen Pembimbing -->
                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title">
                                <i class="bi bi-person-workspace" style="color: var(--primary);"></i> Dosen Pembimbing
                            </div>
                            <div style="font-size: 0.78rem; color: var(--text-muted);">Pendamping resmi karya kompetisi</div>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(! $pengajuan)
                            <!-- Sub-kondisi A: Belum mengajukan Dosen Pembimbing -->
                            <div style="background: #FEF3C7; border: 1px solid #FDE68A; border-radius: var(--radius-sm); padding: 14px; margin-bottom: 18px; color: #92400E; font-size: 0.85rem;">
                                <div style="font-weight: 700; margin-bottom: 2px;">
                                    <i class="bi bi-exclamation-circle-fill"></i> Belum Memiliki Dosen Pembimbing
                                </div>
                                Silakan pilih salah satu dosen di Jurusan Teknologi Informasi untuk diajukan sebagai pembimbing karya tim Anda.
                            </div>

                            <form action="{{ route('bimbingan.ajukan') }}" method="POST">
                                @csrf
                                <input type="hidden" name="id_tim" value="{{ $selectedTim->id_tim }}">

                                <div class="form-group" style="margin-bottom: 14px;">
                                    <label class="form-label">Pilih Dosen Pembimbing *</label>
                                    <select name="nidn" class="form-select" required>
                                        <option value="" disabled selected>-- Pilih Dosen JTI --</option>
                                        @foreach($dosenList as $dsn)
                                            <option value="{{ $dsn->nidn }}" {{ $dsn->sisa_kuota <= 0 ? 'disabled' : '' }}>
                                                {{ $dsn->nama }} (Keahlian: {{ $dsn->bidang_keahlian }}) — Sisa Kuota: {{ $dsn->sisa_kuota }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group" style="margin-bottom: 16px;">
                                    <label class="form-label">Catatan / Rencana Ide Karya untuk Dosen (Opsional)</label>
                                    <textarea name="catatan" rows="3" class="form-control" placeholder="Jelaskan ringkas ide atau judul proyek yang ingin dikembangkan bersama dosen..."></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary" style="width: 100%;">
                                    <i class="bi bi-send-fill"></i> Kirim Pengajuan Bimbingan
                                </button>
                            </form>

                        @elseif($pengajuan->status === 'menunggu')
                            <!-- Sub-kondisi B: Pengajuan Sedang Menunggu Persetujuan Dosen -->
                            <div style="text-align: center; padding: 20px 10px;">
                                <div style="width: 54px; height: 54px; border-radius: 50%; background: #FEF3C7; color: #D97706; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; margin: 0 auto 12px;">
                                    <i class="bi bi-hourglass-split"></i>
                                </div>
                                <div class="badge badge-warning" style="font-size: 0.8rem; padding: 6px 12px; margin-bottom: 8px;">
                                    Menunggu Persetujuan Dosen
                                </div>
                                <h4 style="font-size: 1.05rem; font-weight: 700; color: var(--primary-dark); margin-bottom: 4px;">
                                    {{ $pengajuan->dosen->nama ?? '-' }}
                                </h4>
                                <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 12px;">
                                    NIDN: {{ $pengajuan->nidn }} &bull; Diajukan pada: {{ $pengajuan->tgl_pengajuan ? $pengajuan->tgl_pengajuan->format('d M Y') : '-' }}
                                </div>
                                <p style="font-size: 0.83rem; background: #F8FAFC; border: 1px solid var(--border); border-radius: 8px; padding: 10px; color: var(--text-main); margin: 0;">
                                    "{{ $pengajuan->catatan ?? 'Menunggu konfirmasi penerimaan bimbingan oleh dosen.' }}"
                                </p>
                            </div>

                        @elseif($pengajuan->status === 'ditolak')
                            <!-- Sub-kondisi C: Ditolak, Beri Kesempatan Ajukan Ulang -->
                            <div style="background: #FEE2E2; border: 1px solid #FECACA; border-radius: var(--radius-sm); padding: 14px; margin-bottom: 16px; color: #991B1B; font-size: 0.85rem;">
                                <div style="font-weight: 700; margin-bottom: 2px;">
                                    <i class="bi bi-x-circle-fill"></i> Pengajuan Bimbingan Sebelumnya Ditolak
                                </div>
                                Catatan Dosen: {{ $pengajuan->catatan ?? 'Silakan memilih dosen pembimbing lain.' }}
                            </div>

                            <form action="{{ route('bimbingan.ajukan') }}" method="POST">
                                @csrf
                                <input type="hidden" name="id_tim" value="{{ $selectedTim->id_tim }}">

                                <div class="form-group" style="margin-bottom: 14px;">
                                    <label class="form-label">Pilih Dosen Pengganti *</label>
                                    <select name="nidn" class="form-select" required>
                                        <option value="" disabled selected>-- Pilih Dosen JTI --</option>
                                        @foreach($dosenList as $dsn)
                                            <option value="{{ $dsn->nidn }}" {{ $dsn->sisa_kuota <= 0 ? 'disabled' : '' }}>
                                                {{ $dsn->nama }} (Keahlian: {{ $dsn->bidang_keahlian }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary" style="width: 100%;">
                                    <i class="bi bi-send-fill"></i> Ajukan Dosen Lain
                                </button>
                            </form>

                        @else
                            <!-- Sub-kondisi D: Bimbingan Resmi Disetujui -->
                            <div style="display: flex; align-items: flex-start; gap: 14px; margin-bottom: 16px;">
                                <div style="width: 52px; height: 52px; border-radius: var(--radius-md); background: linear-gradient(135deg, #1E40AF 0%, #3B82F6 100%); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;">
                                    <i class="bi bi-person-check-fill"></i>
                                </div>
                                <div>
                                    <span class="badge badge-success" style="font-size: 0.72rem; margin-bottom: 4px;">
                                        <i class="bi bi-shield-check"></i> Pembimbing Resmi
                                    </span>
                                    <div style="font-weight: 800; font-size: 1.05rem; color: var(--primary-dark);">
                                        {{ $pengajuan->dosen->nama }}
                                    </div>
                                    <div style="font-size: 0.78rem; color: var(--text-muted);">
                                        NIDN: {{ $pengajuan->dosen->nidn }} &bull; {{ $pengajuan->dosen->email_kampus }}
                                    </div>
                                </div>
                            </div>

                            <div style="background: #F8FAFC; border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 12px; font-size: 0.83rem; margin-bottom: 12px;">
                                <strong>Bidang Keahlian:</strong><br>
                                <span style="color: var(--primary); font-weight: 600;">{{ $pengajuan->dosen->bidang_keahlian }}</span>
                            </div>

                            @if($pengajuan->catatan)
                                <div style="font-size: 0.8rem; background: #EFF6FF; border-left: 3px solid var(--primary); padding: 8px 12px; border-radius: 4px; color: #1E3A8A;">
                                    <strong>Arahan Pembimbing:</strong> {{ $pengajuan->catatan }}
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

            </div>

            <!-- KOLOM KANAN: Aktivitas Logbook & Capaian Progres Babak -->
            <div style="display: flex; flex-direction: column; gap: 24px;">

                <!-- Kartu Logbook Konsultasi -->
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div class="card-title">
                                <i class="bi bi-journal-check" style="color: var(--primary);"></i> Logbook Aktivitas Bimbingan
                            </div>
                            <div style="font-size: 0.78rem; color: var(--text-muted);">Rekam jejak evaluasi dan konsultasi bersama dosen</div>
                        </div>

                        @if($pengajuan && $pengajuan->status === 'disetujui')
                            <button type="button" class="btn btn-primary btn-sm" onclick="openModalLogbook()">
                                <i class="bi bi-plus-lg"></i> Isi Logbook Baru
                            </button>
                        @endif
                    </div>
                    <div class="card-body">
                        @if(! $pengajuan || $pengajuan->status !== 'disetujui')
                            <div style="text-align: center; padding: 35px 20px; color: var(--text-muted);">
                                <i class="bi bi-lock-fill" style="font-size: 2rem; color: #94A3B8; display: block; margin-bottom: 8px;"></i>
                                <h4 style="font-size: 0.98rem; font-weight: 700; color: var(--text-main); margin-bottom: 4px;">Fitur Logbook Terkunci</h4>
                                <p style="font-size: 0.83rem; max-width: 400px; margin: 0 auto; line-height: 1.5;">
                                    Pengisian logbook bimbingan akan aktif secara otomatis setelah pengajuan Dosen Pembimbing telah <strong>disetujui</strong>.
                                </p>
                            </div>
                        @else
                            @php
                                $logbooks = $pengajuan->logbook ?? collect();
                            @endphp

                            <div style="display: flex; justify-content: space-between; align-items: center; background: #F8FAFC; border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 10px 14px; margin-bottom: 18px; font-size: 0.82rem;">
                                <span>Total Sesi Konsultasi: <strong>{{ $logbooks->count() }} Kali</strong></span>
                                <span class="badge badge-success">{{ $logbooks->where('status_validasi', 'disetujui')->count() }} Divalidasi</span>
                            </div>

                            @if($logbooks->isEmpty())
                                <div style="text-align: center; padding: 30px; color: var(--text-muted); font-size: 0.88rem;">
                                    <i class="bi bi-journal-x" style="font-size: 1.8rem; opacity: 0.5; display: block; margin-bottom: 6px;"></i>
                                    Belum ada catatan logbook bimbingan. Klik tombol <strong>"Isi Logbook Baru"</strong> di atas setelah Anda berkonsultasi dengan dosen pembimbing.
                                </div>
                            @else
                                <div class="timeline-wrap">
                                    @foreach($logbooks as $log)
                                        <div class="timeline-item">
                                            <div class="timeline-point"></div>
                                            <div class="timeline-content">
                                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; flex-wrap: wrap; gap: 6px;">
                                                    <span style="font-size: 0.78rem; font-weight: 700; color: var(--primary);">
                                                        <i class="bi bi-calendar-event"></i> {{ $log->tanggal ? $log->tanggal->format('d F Y') : '-' }}
                                                    </span>

                                                    @if($log->status_validasi === 'disetujui')
                                                        <span class="badge badge-success" style="font-size: 0.7rem;">
                                                            <i class="bi bi-check-circle-fill"></i> Divalidasi Dosen
                                                        </span>
                                                    @elseif($log->status_validasi === 'revisi')
                                                        <span class="badge badge-danger" style="font-size: 0.7rem;">
                                                            <i class="bi bi-exclamation-triangle-fill"></i> Perlu Revisi
                                                        </span>
                                                    @else
                                                        <span class="badge badge-warning" style="font-size: 0.7rem;">
                                                            <i class="bi bi-clock-history"></i> Menunggu Validasi
                                                        </span>
                                                    @endif
                                                </div>

                                                <div style="font-size: 0.86rem; font-weight: 700; color: var(--primary-dark); margin-bottom: 4px;">
                                                    Materi & Bahasan Konsultasi:
                                                </div>
                                                <p style="font-size: 0.84rem; color: var(--text-main); line-height: 1.5; margin-bottom: 8px;">
                                                    {{ $log->materi }}
                                                </p>

                                                @if($log->tindak_lanjut && $log->tindak_lanjut !== '-')
                                                    <div style="font-size: 0.78rem; background: #fff; border: 1px solid var(--border); border-radius: 6px; padding: 8px 10px; color: #475569;">
                                                        <strong>Rencana Tindak Lanjut:</strong> {{ $log->tindak_lanjut }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- Kartu Monitoring Progres Babak Tim -->
                @if($pengajuan && $pengajuan->progresBabak->isNotEmpty())
                    <div class="card">
                        <div class="card-header">
                            <div>
                                <div class="card-title">
                                    <i class="bi bi-flag-fill" style="color: var(--primary);"></i> Monitoring Progres Babak
                                </div>
                                <div style="font-size: 0.78rem; color: var(--text-muted);">Tahapan kompetisi yang ditempuh tim</div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div style="display: flex; flex-direction: column; gap: 10px;">
                                @foreach($pengajuan->progresBabak as $prog)
                                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 14px; background: #F8FAFC; border: 1px solid var(--border); border-radius: 8px;">
                                        <div>
                                            <div style="font-weight: 700; font-size: 0.9rem; color: var(--primary-dark);">
                                                Babak: {{ $prog->babak }}
                                            </div>
                                            <div style="font-size: 0.75rem; color: var(--text-muted);">
                                                Tanggal Update: {{ $prog->tanggal_update ? $prog->tanggal_update->format('d M Y') : '-' }}
                                            </div>
                                        </div>
                                        <div>
                                            <span class="badge {{ str_contains(strtolower($prog->hasil_akhir ?? ''), 'juara') ? 'badge-warning' : 'badge-primary' }}">
                                                {{ $prog->hasil_akhir ?? 'Sedang Berlangsung' }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

            </div>

        </div>

    {{-- ========================================================================= --}}
    {{-- KONDISI 3: ROLE DOSEN (Portal Pembimbingan Dosen)                          --}}
    {{-- ========================================================================= --}}
    @elseif($role === 'dosen')

        <!-- Statistik Bimbingan Dosen -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
            <div class="card" style="padding: 18px;">
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Total Kuota Bimbingan</div>
                <div style="font-size: 1.8rem; font-weight: 800; color: var(--primary-dark); margin: 4px 0;">{{ $dosen->kuota_bimbingan ?? 5 }} Tim</div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">Kapasitas maksimal bimbingan semester ini</div>
            </div>

            <div class="card" style="padding: 18px;">
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Tim Bimbingan Aktif</div>
                <div style="font-size: 1.8rem; font-weight: 800; color: #10B981; margin: 4px 0;">{{ $pengajuanList->where('status', 'disetujui')->count() }} Tim</div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">Tim yang resmi Anda setujui</div>
            </div>

            <div class="card" style="padding: 18px;">
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Pengajuan Menunggu</div>
                <div style="font-size: 1.8rem; font-weight: 800; color: #F59E0B; margin: 4px 0;">{{ $pengajuanList->where('status', 'menunggu')->count() }} Tim</div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">Menunggu konfirmasi persetujuan</div>
            </div>
        </div>

        <!-- Daftar Pengajuan & Tim Bimbingan -->
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title"><i class="bi bi-people-fill" style="color: var(--primary);"></i> Daftar Tim Mahasiswa Bimbingan</div>
                    <div style="font-size: 0.78rem; color: var(--text-muted);">Kelola persetujuan bimbingan dan validasi logbook mahasiswa</div>
                </div>
            </div>
            <div class="card-body">
                @if($pengajuanList->isEmpty())
                    <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                        Belum ada tim mahasiswa yang mengajukan bimbingan kepada Anda.
                    </div>
                @else
                    <div style="display: flex; flex-direction: column; gap: 18px;">
                        @foreach($pengajuanList as $item)
                            <div style="background: #F8FAFC; border: 1px solid var(--border); border-radius: var(--radius-md); padding: 18px;">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 10px; margin-bottom: 12px;">
                                    <div>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <h4 style="font-size: 1.1rem; font-weight: 800; color: var(--primary-dark); margin: 0;">
                                                {{ $item->tim->nama_tim ?? '-' }}
                                            </h4>
                                            @if($item->status === 'disetujui')
                                                <span class="badge badge-success">Disetujui</span>
                                            @elseif($item->status === 'ditolak')
                                                <span class="badge badge-danger">Ditolak</span>
                                            @else
                                                <span class="badge badge-warning">Menunggu Konfirmasi</span>
                                            @endif
                                        </div>
                                        <div style="font-size: 0.82rem; color: var(--text-muted); margin-top: 3px;">
                                            Kompetisi: <strong>{{ $item->tim->lomba->nama_lomba ?? '-' }}</strong> &bull; Ketua: {{ $item->tim->ketua->nama ?? '-' }} (NIM: {{ $item->tim->nim }})
                                        </div>
                                    </div>

                                    @if($item->status === 'menunggu')
                                        <div style="display: flex; gap: 8px;">
                                            <form action="{{ route('bimbingan.pengajuan.respon', $item->id_pengajuan) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="status" value="disetujui">
                                                <button type="submit" class="btn btn-success btn-sm">
                                                    <i class="bi bi-check-lg"></i> Terima Bimbingan
                                                </button>
                                            </form>

                                            <form action="{{ route('bimbingan.pengajuan.respon', $item->id_pengajuan) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="status" value="ditolak">
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menolak pengajuan bimbingan ini?')">
                                                    <i class="bi bi-x-lg"></i> Tolak
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>

                                <!-- Bagian Logbook Mahasiswa Bimbingan -->
                                @if($item->status === 'disetujui')
                                    <div style="border-top: 1px solid var(--border); padding-top: 12px; margin-top: 10px;">
                                        <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;">
                                            LOGBOOK MAHASISWA ({{ $item->logbook->count() }} Sesi Tercatat):
                                        </div>

                                        @if($item->logbook->isEmpty())
                                            <div style="font-size: 0.8rem; color: var(--text-muted); font-style: italic;">
                                                Mahasiswa belum mengisi catatan logbook bimbingan.
                                            </div>
                                        @else
                                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                                @foreach($item->logbook as $lb)
                                                    <div style="background: #fff; border: 1px solid var(--border); border-radius: 6px; padding: 10px 12px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                                                        <div style="max-width: 600px;">
                                                            <div style="font-size: 0.75rem; color: var(--text-muted);">
                                                                Tanggal: {{ $lb->tanggal ? $lb->tanggal->format('d/m/Y') : '-' }} &bull;
                                                                Status: <strong>{{ ucfirst($lb->status_validasi) }}</strong>
                                                            </div>
                                                            <div style="font-size: 0.84rem; font-weight: 600; color: var(--text-main);">
                                                                {{ $lb->materi }}
                                                            </div>
                                                        </div>

                                                        @if($lb->status_validasi !== 'disetujui')
                                                            <form action="{{ route('bimbingan.logbook.validasi', $lb->id_logbook) }}" method="POST">
                                                                @csrf
                                                                <input type="hidden" name="status_validasi" value="disetujui">
                                                                <button type="submit" class="btn btn-outline-primary btn-sm">
                                                                    <i class="bi bi-check2-circle"></i> Setujui Logbook
                                                                </button>
                                                            </form>
                                                        @else
                                                            <span class="badge badge-success"><i class="bi bi-check-all"></i> Terverifikasi</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

    {{-- ========================================================================= --}}
    {{-- KONDISI 4: ROLE ADMIN (Rekapitulasi Seluruh Bimbingan JTI)                 --}}
    {{-- ========================================================================= --}}
    @elseif($role === 'admin')
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title"><i class="bi bi-database-check" style="color: var(--primary);"></i> Rekapitulasi Bimbingan Seluruh JTI</div>
                    <div style="font-size: 0.78rem; color: var(--text-muted);">Monitoring pembimbingan tim kompetisi mahasiswa oleh dosen</div>
                </div>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tim & Kompetisi</th>
                                <th>Ketua Tim</th>
                                <th>Dosen Pembimbing</th>
                                <th>Status Bimbingan</th>
                                <th>Logbook</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pengajuanList as $row)
                                <tr>
                                    <td>
                                        <strong>{{ $row->tim->nama_tim ?? '-' }}</strong>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">
                                            {{ $row->tim->lomba->nama_lomba ?? '-' }}
                                        </div>
                                    </td>
                                    <td>
                                        {{ $row->tim->ketua->nama ?? '-' }}
                                        <div style="font-size: 0.72rem; color: var(--text-muted);">NIM: {{ $row->tim->nim }}</div>
                                    </td>
                                    <td>
                                        <strong>{{ $row->dosen->nama ?? '-' }}</strong>
                                        <div style="font-size: 0.72rem; color: var(--text-muted);">{{ $row->dosen->bidang_keahlian ?? '' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $row->status === 'disetujui' ? 'badge-success' : ($row->status === 'ditolak' ? 'badge-danger' : 'badge-warning') }}">
                                            {{ ucfirst($row->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-purple">{{ $row->logbook->count() }} Sesi</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                        Belum ada data bimbingan tercatat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- MODAL 1: Pendaftaran Tim Cepat untuk Mahasiswa yang Belum Ikut Lomba      --}}
    {{-- ========================================================================= --}}
    <div id="modalDaftarTim" class="modal-backdrop-custom">
        <div class="modal-box">
            <div class="modal-header-custom">
                <div style="font-weight: 800; font-size: 1.05rem; color: var(--primary-dark);">
                    <i class="bi bi-trophy-fill" style="color: var(--primary);"></i> Bentuk Tim & Ikuti Lomba
                </div>
                <button type="button" onclick="closeModalDaftarTim()" style="background: none; border: none; font-size: 1.4rem; cursor: pointer; color: var(--text-muted);">&times;</button>
            </div>
            <form action="{{ route('bimbingan.daftar_tim') }}" method="POST">
                @csrf
                <div class="modal-body-custom">
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 16px;">
                        Setelah membentuk tim, modul bimbingan Anda akan otomatis aktif sehingga Anda dapat langsung memilih Dosen Pembimbing.
                    </p>

                    <div class="form-group" style="margin-bottom: 14px;">
                        <label class="form-label">Pilih Kompetisi / Lomba *</label>
                        <select name="id_lomba" id="modalSelectLomba" class="form-select" required>
                            <option value="" disabled selected>-- Pilih Lomba Terverifikasi --</option>
                            @if(isset($lombaTersedia))
                                @foreach($lombaTersedia as $itemLomba)
                                    <option value="{{ $itemLomba->id_lomba }}">
                                        {{ $itemLomba->nama_lomba }} ({{ $itemLomba->tingkat }})
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 14px;">
                        <label class="form-label">Nama Tim Anda *</label>
                        <input type="text" name="nama_tim" class="form-control" placeholder="Contoh: Garuda Cyber Team" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Kuota Anggota Tim</label>
                        <select name="kuota_anggota" class="form-select">
                            <option value="3" selected>3 Orang Mahasiswa</option>
                            <option value="2">2 Orang Mahasiswa</option>
                            <option value="4">4 Orang Mahasiswa</option>
                            <option value="5">5 Orang Mahasiswa</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer-custom">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="closeModalDaftarTim()">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-check-circle-fill"></i> Daftarkan Tim & Buka Bimbingan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL 2: Form Tambah Logbook Aktivitas Bimbingan                          --}}
    {{-- ========================================================================= --}}
    @if(isset($pengajuan) && $pengajuan && $pengajuan->status === 'disetujui')
        <div id="modalLogbook" class="modal-backdrop-custom">
            <div class="modal-box">
                <div class="modal-header-custom">
                    <div style="font-weight: 800; font-size: 1.05rem; color: var(--primary-dark);">
                        <i class="bi bi-journal-plus" style="color: var(--primary);"></i> Tambah Catatan Logbook Bimbingan
                    </div>
                    <button type="button" onclick="closeModalLogbook()" style="background: none; border: none; font-size: 1.4rem; cursor: pointer; color: var(--text-muted);">&times;</button>
                </div>
                <form action="{{ route('bimbingan.logbook.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id_pengajuan" value="{{ $pengajuan->id_pengajuan }}">

                    <div class="modal-body-custom">
                        <div class="form-group" style="margin-bottom: 14px;">
                            <label class="form-label">Tanggal Pelaksanaan Bimbingan *</label>
                            <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="form-group" style="margin-bottom: 14px;">
                            <label class="form-label">Materi / Bahasan Konsultasi *</label>
                            <textarea name="materi" rows="3" class="form-control" placeholder="Contoh: Konsultasi arsitektur sistem, pemilihan database, atau review prototype UI/UX..." required></textarea>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Arahan & Tindak Lanjut dari Dosen</label>
                            <textarea name="tindak_lanjut" rows="2" class="form-control" placeholder="Contoh: Menyempurnakan diagram sequence dan menyiapkan demo video MVP..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer-custom">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="closeModalLogbook()">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-save-fill"></i> Simpan Catatan Logbook
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

@endsection

@push('scripts')
<script>
    function openModalDaftarTim(idLomba = null, namaLomba = '') {
        const modal = document.getElementById('modalDaftarTim');
        if (modal) {
            modal.style.display = 'flex';
            if (idLomba) {
                const select = document.getElementById('modalSelectLomba');
                if (select) select.value = idLomba;
            }
        }
    }

    function closeModalDaftarTim() {
        const modal = document.getElementById('modalDaftarTim');
        if (modal) modal.style.display = 'none';
    }

    function openModalLogbook() {
        const modal = document.getElementById('modalLogbook');
        if (modal) modal.style.display = 'flex';
    }

    function closeModalLogbook() {
        const modal = document.getElementById('modalLogbook');
        if (modal) modal.style.display = 'none';
    }

    // Tutup modal jika klik di luar box
    window.addEventListener('click', function(e) {
        const modalDaftar = document.getElementById('modalDaftarTim');
        const modalLogbook = document.getElementById('modalLogbook');
        if (e.target === modalDaftar) closeModalDaftarTim();
        if (e.target === modalLogbook) closeModalLogbook();
    });
</script>
@endpush
