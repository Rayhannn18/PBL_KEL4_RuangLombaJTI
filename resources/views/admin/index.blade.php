@extends('layouts.app')

@section('title', 'Panel Administrator Kemahasiswaan JTI')

@push('styles')
<style>
    /* Styling Khusus Panel Admin */
    .admin-header {
        background: linear-gradient(135deg, #0F2744 0%, #1E3A8A 60%, #1D4ED8 100%);
        border-radius: var(--radius-lg);
        padding: 32px 36px;
        color: #fff;
        margin-bottom: 28px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 25px -5px rgba(15, 39, 68, 0.25);
    }
    .admin-header::after {
        content: '';
        position: absolute;
        top: -40px;
        right: -40px;
        width: 220px;
        height: 220px;
        background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, rgba(255,255,255,0) 70%);
        border-radius: 50%;
        pointer-events: none;
    }
    .admin-badge-role {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(239, 68, 68, 0.25);
        border: 1px solid rgba(239, 68, 68, 0.45);
        color: #FECACA;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        margin-bottom: 12px;
    }
    .admin-title {
        font-size: 1.75rem;
        font-weight: 800;
        letter-spacing: -0.5px;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .admin-subtitle {
        font-size: 0.92rem;
        color: rgba(255, 255, 255, 0.82);
        max-width: 680px;
        line-height: 1.5;
    }

    /* KPI Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 16px;
        margin-bottom: 28px;
    }
    .stat-card {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 18px 20px;
        box-shadow: var(--shadow-sm);
        transition: transform 0.2s, box-shadow 0.2s;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }
    .stat-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        margin-bottom: 12px;
    }
    .stat-val {
        font-size: 1.65rem;
        font-weight: 800;
        color: var(--primary-dark);
        line-height: 1.1;
    }
    .stat-label {
        font-size: 0.8rem;
        color: var(--text-muted);
        font-weight: 600;
        margin-top: 4px;
    }
    .stat-sub {
        font-size: 0.72rem;
        margin-top: 8px;
        padding-top: 6px;
        border-top: 1px dashed #E2E8F0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* Tab Navigasi Admin */
    .admin-tabs {
        display: flex;
        gap: 6px;
        border-bottom: 2px solid #E2E8F0;
        margin-bottom: 24px;
        overflow-x: auto;
    }
    .admin-tab-btn {
        padding: 12px 20px;
        background: transparent;
        border: none;
        font-size: 0.92rem;
        font-weight: 700;
        color: var(--text-muted);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border-bottom: 3px solid transparent;
        margin-bottom: -2px;
        transition: all 0.2s ease;
        text-decoration: none;
        white-space: nowrap;
    }
    .admin-tab-btn:hover {
        color: var(--primary);
    }
    .admin-tab-btn.active {
        color: var(--primary);
        border-bottom-color: var(--primary);
        background: rgba(30, 64, 175, 0.04);
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
    }

    /* Filter Bar */
    .filter-bar {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 14px 18px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }
    .filter-pills {
        display: flex;
        gap: 6px;
        align-items: center;
        flex-wrap: wrap;
    }
    .filter-pill {
        padding: 6px 14px;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 600;
        background: #F1F5F9;
        color: var(--text-muted);
        text-decoration: none;
        transition: all 0.2s;
        border: 1px solid transparent;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .filter-pill:hover {
        background: #E2E8F0;
        color: var(--text-main);
    }
    .filter-pill.active {
        background: var(--primary);
        color: #fff;
    }

    /* Modern Table */
    .table-container {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        overflow: hidden;
        box-shadow: var(--shadow-sm);
    }
    .custom-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.88rem;
        text-align: left;
    }
    .custom-table th {
        background: #F8FAFC;
        padding: 14px 18px;
        font-weight: 700;
        color: #475569;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
    }
    .custom-table td {
        padding: 16px 18px;
        border-bottom: 1px solid #F1F5F9;
        color: var(--text-main);
        vertical-align: middle;
    }
    .custom-table tr:hover td {
        background-color: #F8FAFC;
    }
    .custom-table tr:last-child td {
        border-bottom: none;
    }

    /* Modal Styling */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        padding: 20px;
    }
    .modal-box {
        background: #fff;
        width: 100%;
        max-width: 680px;
        max-height: 90vh;
        overflow-y: auto;
        border-radius: var(--radius-lg);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        border: 1px solid var(--border);
        animation: modalFadeIn 0.25s ease-out;
    }
    @keyframes modalFadeIn {
        from { opacity: 0; transform: scale(0.96) translateY(10px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }
    .modal-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #F8FAFC;
        border-top-left-radius: var(--radius-lg);
        border-top-right-radius: var(--radius-lg);
    }
    .modal-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--primary-dark);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .modal-close {
        background: none;
        border: none;
        font-size: 1.4rem;
        color: #94A3B8;
        cursor: pointer;
        transition: color 0.2s;
    }
    .modal-close:hover {
        color: var(--danger);
    }
    .modal-body {
        padding: 24px;
    }
    .modal-footer {
        padding: 16px 24px;
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        background: #F8FAFC;
        border-bottom-left-radius: var(--radius-lg);
        border-bottom-right-radius: var(--radius-lg);
    }

    @media (max-width: 1100px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 640px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        .filter-bar {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>
@endpush

@section('content')
<div class="container">

    <!-- Header Banner Panel Administrator -->
    <div class="admin-header">
        <div style="position: relative; z-index: 1;">
            <div class="admin-badge-role">
                <i class="bi bi-shield-lock-fill"></i> Hak Akses: Administrator Kemahasiswaan
            </div>
            <h1 class="admin-title">
                <i class="bi bi-gear-wide-connected"></i> Panel Administrator RuangLombaJTI
            </h1>
            <p class="admin-subtitle">
                Pusat kendali operasional terpadu Jurusan Teknologi Informasi: verifikasi kurasi lomba eksternal/mitra, persetujuan ketua, kelola data master dosen dan mahasiswa, serta pemantauan capaian prestasi mahasiswa.
            </p>
        </div>
    </div>

    <!-- Alert Notifikasi Session -->
    @if(session('success'))
        <div class="alert alert-success">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="bi bi-check-circle-fill" style="font-size: 1.2rem;"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="bi bi-exclamation-triangle-fill" style="font-size: 1.2rem;"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <div>
                <strong><i class="bi bi-exclamation-circle-fill"></i> Terjadi kesalahan validasi:</strong>
                <ul style="margin-left: 20px; margin-top: 6px;">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
            <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    @endif

    <!-- 5 Kartu Metrik KPI Operasional -->
    <div class="stats-grid">
        <!-- 1. Lomba -->
        <div class="stat-card">
            <div>
                <div class="stat-icon" style="background: #EFF6FF; color: #2563EB;">
                    <i class="bi bi-trophy-fill"></i>
                </div>
                <div class="stat-val">{{ $stats['total_lomba'] }}</div>
                <div class="stat-label">Total Data Lomba</div>
            </div>
            <div class="stat-sub">
                <span>Perlu Verifikasi:</span>
                <span class="badge {{ $stats['lomba_menunggu'] > 0 ? 'badge-warning' : 'badge-gray' }}">
                    {{ $stats['lomba_menunggu'] }} Pengajuan
                </span>
            </div>
        </div>

        <!-- 2. Tim & Ketua -->
        <div class="stat-card">
            <div>
                <div class="stat-icon" style="background: #FEF3C7; color: #D97706;">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="stat-val">{{ $stats['total_tim'] }}</div>
                <div class="stat-label">Tim Terdaftar</div>
            </div>
            <div class="stat-sub">
                <span>Menunggu Approval:</span>
                <span class="badge {{ $stats['tim_menunggu'] > 0 ? 'badge-danger' : 'badge-gray' }}">
                    {{ $stats['tim_menunggu'] }} Tim
                </span>
            </div>
        </div>

        <!-- 3. Dosen -->
        <div class="stat-card">
            <div>
                <div class="stat-icon" style="background: #F0FDF4; color: #16A34A;">
                    <i class="bi bi-person-video3"></i>
                </div>
                <div class="stat-val">{{ $stats['total_dosen'] }}</div>
                <div class="stat-label">Dosen Pembimbing</div>
            </div>
            <div class="stat-sub">
                <span>Kapasitas Kuota:</span>
                <span style="font-weight: 700; color: #16A34A;">{{ $stats['total_kuota_dosen'] }} Kuota</span>
            </div>
        </div>

        <!-- 4. Mahasiswa -->
        <div class="stat-card">
            <div>
                <div class="stat-icon" style="background: #FAF5FF; color: #9333EA;">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <div class="stat-val">{{ $stats['total_mahasiswa'] }}</div>
                <div class="stat-label">Mahasiswa Aktif</div>
            </div>
            <div class="stat-sub">
                <span>Program Studi:</span>
                <span style="font-weight: 600; color: #64748B;">3 Prodi JTI</span>
            </div>
        </div>

        <!-- 5. Prestasi -->
        <div class="stat-card">
            <div>
                <div class="stat-icon" style="background: #ECFDF5; color: #059669;">
                    <i class="bi bi-award-fill"></i>
                </div>
                <div class="stat-val">{{ $stats['total_prestasi'] }}</div>
                <div class="stat-label">Prestasi Mahasiswa</div>
            </div>
            <div class="stat-sub">
                <a href="{{ route('dashboard.analitik') }}" style="color: var(--primary); font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                    Lihat Analitik <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Tab Navigasi Sesuai Activity Diagram -->
    <div class="admin-tabs">
        <a href="{{ route('admin.index', ['tab' => 'overview']) }}" class="admin-tab-btn {{ $currentTab === 'overview' ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Ringkasan & Monitoring
        </a>
        <a href="{{ route('admin.index', ['tab' => 'lomba']) }}" class="admin-tab-btn {{ $currentTab === 'lomba' ? 'active' : '' }}">
            <i class="bi bi-check2-circle"></i> Verifikasi & Kelola Lomba
            @if($stats['lomba_menunggu'] > 0)
                <span class="badge badge-warning" style="font-size: 0.7rem; padding: 2px 7px;">{{ $stats['lomba_menunggu'] }}</span>
            @endif
        </a>
        <a href="{{ route('admin.index', ['tab' => 'tim']) }}" class="admin-tab-btn {{ $currentTab === 'tim' ? 'active' : '' }}">
            <i class="bi bi-person-check"></i> Persetujuan Ketua
            @if($stats['tim_menunggu'] > 0)
                <span class="badge badge-danger" style="font-size: 0.7rem; padding: 2px 7px;">{{ $stats['tim_menunggu'] }}</span>
            @endif
        </a>
        <a href="{{ route('admin.index', ['tab' => 'dosen']) }}" class="admin-tab-btn {{ $currentTab === 'dosen' ? 'active' : '' }}">
            <i class="bi bi-person-badge"></i> Kelola Data Dosen
        </a>
        <a href="{{ route('admin.index', ['tab' => 'mahasiswa']) }}" class="admin-tab-btn {{ $currentTab === 'mahasiswa' ? 'active' : '' }}">
            <i class="bi bi-mortarboard"></i> Kelola Data Mahasiswa
        </a>
    </div>

    {{-- ========================================================= --}}
    {{-- TAB 1: OVERVIEW & RINGKASAN MONITORING PRESTASI           --}}
    {{-- ========================================================= --}}
    @if($currentTab === 'overview')
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 28px;">
            <!-- Aksi Cepat & Status Verifikasi -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="bi bi-lightning-charge-fill" style="color: var(--accent);"></i> Tindakan Cepat Administrator
                    </div>
                    <span style="font-size: 0.8rem; color: var(--text-muted);">Aksi Utama Sistem</span>
                </div>
                <div class="card-body">
                    <p style="font-size: 0.88rem; color: var(--text-muted); margin-bottom: 18px;">
                        Silakan pilih tindakan operasional di bawah ini sesuai aktivitas yang sedang berlangsung:
                    </p>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 24px;">
                        <button type="button" class="btn btn-outline-primary" onclick="openModal('modalTambahLomba')" style="padding: 12px; font-size: 0.85rem;">
                            <i class="bi bi-plus-circle-fill"></i> Tambah Lomba Resmi
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="openModal('modalTambahDosen')" style="padding: 12px; font-size: 0.85rem;">
                            <i class="bi bi-person-plus-fill"></i> Tambah Dosen
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="openModal('modalTambahMahasiswa')" style="padding: 12px; font-size: 0.85rem;">
                            <i class="bi bi-mortarboard-fill"></i> Tambah Mahasiswa
                        </button>
                    </div>

                    <!-- Progress Lomba & Tim -->
                    <div style="background: #F8FAFC; border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 16px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 0.84rem; font-weight: 700; color: var(--primary-dark);">Status Validasi Lomba</span>
                            <span style="font-size: 0.78rem; color: var(--text-muted);">
                                {{ $stats['lomba_terverifikasi'] }} Terverifikasi / {{ $stats['total_lomba'] }} Total
                            </span>
                        </div>
                        <div style="background: #E2E8F0; height: 10px; border-radius: 999px; overflow: hidden; display: flex;">
                            @php
                                $pctLombaVerif = $stats['total_lomba'] > 0 ? ($stats['lomba_terverifikasi'] / $stats['total_lomba']) * 100 : 0;
                                $pctLombaMenunggu = $stats['total_lomba'] > 0 ? ($stats['lomba_menunggu'] / $stats['total_lomba']) * 100 : 0;
                            @endphp
                            <div style="width: {{ $pctLombaVerif }}%; background: var(--success);" title="Terverifikasi: {{ $stats['lomba_terverifikasi'] }}"></div>
                            <div style="width: {{ $pctLombaMenunggu }}%; background: var(--accent);" title="Menunggu: {{ $stats['lomba_menunggu'] }}"></div>
                        </div>
                        <div style="display: flex; gap: 16px; margin-top: 8px; font-size: 0.75rem; color: var(--text-muted);">
                            <span><i class="bi bi-circle-fill" style="color: var(--success); font-size: 0.6rem;"></i> Terverifikasi ({{ $stats['lomba_terverifikasi'] }})</span>
                            <span><i class="bi bi-circle-fill" style="color: var(--accent); font-size: 0.6rem;"></i> Menunggu ({{ $stats['lomba_menunggu'] }})</span>
                            <span><i class="bi bi-circle-fill" style="color: var(--danger); font-size: 0.6rem;"></i> Ditolak ({{ $stats['lomba_ditolak'] }})</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tautan Analitik Prestasi & Info Sistem -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="bi bi-bar-chart-fill" style="color: var(--primary-light);"></i> Integrasi Prestasi
                    </div>
                </div>
                <div class="card-body">
                    <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5; margin-bottom: 16px;">
                        Dashboard Analitik Prestasi memuat grafik distribusi juara per Program Studi (SIB vs TI), tren angkatan (2023-2026), serta riwayat babak monitoring.
                    </p>
                    <a href="{{ route('dashboard.analitik') }}" class="btn btn-primary" style="width: 100%; margin-bottom: 12px;">
                        <i class="bi bi-graph-up-arrow"></i> Buka Dashboard Analitik
                    </a>
                    <a href="{{ route('bimbingan.index') }}" class="btn btn-secondary" style="width: 100%;">
                        <i class="bi bi-journal-text"></i> Rekap Logbook Bimbingan
                    </a>
                </div>
            </div>
        </div>

        <!-- Tabel Perhatian Utama 1: Lomba Menunggu Verifikasi Segera -->
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-header">
                <div class="card-title">
                    <i class="bi bi-hourglass-split" style="color: var(--accent);"></i> Lomba Menunggu Verifikasi Kredibilitas
                </div>
                <a href="{{ route('admin.index', ['tab' => 'lomba', 'lomba_status' => 'menunggu']) }}" class="btn btn-secondary btn-sm">
                    Lihat Semua ({{ $stats['lomba_menunggu'] }})
                </a>
            </div>
            <div class="table-container">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Nama Lomba</th>
                            <th>Kategori & Tingkat</th>
                            <th>Penyelenggara</th>
                            <th>Tenggat</th>
                            <th>Pengaju</th>
                            <th style="text-align: right;">Aksi Kurasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lombaList->where('status_verifikasi', 'menunggu')->take(5) as $item)
                            <tr>
                                <td>
                                    <div style="font-weight: 700; color: var(--primary-dark);">{{ $item->nama_lomba }}</div>
                                    <span class="badge badge-warning" style="font-size: 0.7rem; margin-top: 4px;">Menunggu Verifikasi</span>
                                </td>
                                <td>
                                    <div>{{ $item->kategori }}</div>
                                    <span style="font-size: 0.75rem; color: var(--text-muted);">Tingkat {{ $item->tingkat }}</span>
                                </td>
                                <td>{{ $item->penyelenggara }}</td>
                                <td>
                                    <div>{{ $item->tenggat ? $item->tenggat->format('d M Y') : '-' }}</div>
                                    <span style="font-size: 0.72rem; color: {{ $item->is_buka ? 'var(--success)' : 'var(--danger)' }}; font-weight: 600;">
                                        {{ $item->is_buka ? $item->sisa_hari . ' hari lagi' : 'Telah berakhir' }}
                                    </span>
                                </td>
                                <td>
                                    @if($item->pengaju)
                                        <div style="font-weight: 600;">{{ $item->pengaju->nama }}</div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">NIM: {{ $item->pengaju->nim }}</div>
                                    @else
                                        <span class="badge badge-gray">Eksternal / Mitra</span>
                                    @endif
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 6px;">
                                        <form action="{{ route('admin.lomba.verifikasi', $item->id_lomba) }}" method="POST" style="margin: 0;">
                                            @csrf
                                            <input type="hidden" name="status" value="terverifikasi">
                                            <button type="submit" class="btn btn-success btn-sm" title="Nyatakan Kredibel & Publikasikan">
                                                <i class="bi bi-check-lg"></i> Kredibel
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.lomba.verifikasi', $item->id_lomba) }}" method="POST" style="margin: 0;">
                                            @csrf
                                            <input type="hidden" name="status" value="ditolak">
                                            <button type="submit" class="btn btn-danger btn-sm" title="Tolak Pengajuan Lomba">
                                                <i class="bi bi-x-lg"></i> Tolak
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                    <i class="bi bi-check-all" style="font-size: 2rem; color: var(--success); display: block; margin-bottom: 8px;"></i>
                                    Tidak ada pengajuan lomba baru yang menunggu verifikasi. Seluruh lomba telah dikurasi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tabel Perhatian Utama 2: Tim Menunggu Persetujuan Ketua -->
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-header">
                <div class="card-title">
                    <i class="bi bi-person-exclamation" style="color: var(--danger);"></i> Pengajuan Tim & Ketua yang Menunggu Persetujuan
                </div>
                <a href="{{ route('admin.index', ['tab' => 'tim', 'tim_status' => 'menunggu_persetujuan']) }}" class="btn btn-secondary btn-sm">
                    Lihat Semua ({{ $stats['tim_menunggu'] }})
                </a>
            </div>
            <div class="table-container">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Nama Tim</th>
                            <th>Lomba yang Diikuti</th>
                            <th>Ketua Tim</th>
                            <th>Anggota Terdaftar</th>
                            <th>Tanggal Pengajuan</th>
                            <th style="text-align: right;">Aksi Validasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($timList->where('status_tim', 'menunggu_persetujuan')->take(5) as $tim)
                            <tr>
                                <td>
                                    <div style="font-weight: 700; color: var(--primary-dark);">{{ $tim->nama_tim }}</div>
                                    <span class="badge badge-warning" style="font-size: 0.7rem; margin-top: 4px;">Menunggu Persetujuan</span>
                                </td>
                                <td>
                                    <div style="font-weight: 600;">{{ $tim->lomba ? $tim->lomba->nama_lomba : '-' }}</div>
                                    <span style="font-size: 0.75rem; color: var(--text-muted);">{{ $tim->lomba ? $tim->lomba->kategori : '' }}</span>
                                </td>
                                <td>
                                    @if($tim->ketua)
                                        <div style="font-weight: 600;">{{ $tim->ketua->nama }}</div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">NIM: {{ $tim->ketua->nim }} • {{ $tim->ketua->prodi }}</div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-primary">{{ $tim->anggota->count() }} / {{ $tim->kuota_anggota }} Orang</span>
                                </td>
                                <td>
                                    <span style="font-size: 0.8rem; color: var(--text-muted);">{{ $tim->created_at ? $tim->created_at->format('d M Y') : '-' }}</span>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 6px;">
                                        <form action="{{ route('admin.tim.verifikasi', $tim->id_tim) }}" method="POST" style="margin: 0;">
                                            @csrf
                                            <input type="hidden" name="status" value="disetujui">
                                            <button type="submit" class="btn btn-success btn-sm" title="Setujui Tim & Ketua">
                                                <i class="bi bi-check-circle"></i> Setujui
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.tim.verifikasi', $tim->id_tim) }}" method="POST" style="margin: 0;">
                                            @csrf
                                            <input type="hidden" name="status" value="ditolak">
                                            <button type="submit" class="btn btn-danger btn-sm" title="Tolak Pengajuan Tim">
                                                <i class="bi bi-x-circle"></i> Tolak
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                    <i class="bi bi-check-all" style="font-size: 2rem; color: var(--success); display: block; margin-bottom: 8px;"></i>
                                    Tidak ada pengajuan ketua tim baru yang tertunda. Semua tim telah diproses.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tabel Perhatian Utama 3: Riwayat Prestasi Mahasiswa Terbaru -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="bi bi-award-fill" style="color: var(--success);"></i> Riwayat Prestasi & Monitoring Terkini
                </div>
                <a href="{{ route('dashboard.analitik') }}" class="btn btn-secondary btn-sm">
                    Buka Halaman Analitik Lengkap
                </a>
            </div>
            <div class="table-container">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Tim & Mahasiswa</th>
                            <th>Kompetisi / Lomba</th>
                            <th>Dosen Pembimbing</th>
                            <th>Babak Terakhir</th>
                            <th>Pencapaian / Juara</th>
                            <th>Tanggal Update</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($prestasiList->take(6) as $p)
                            <tr>
                                <td>
                                    <div style="font-weight: 700; color: var(--primary-dark);">
                                        {{ $p->pengajuan?->tim?->nama_tim ?? '-' }}
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">
                                        Ketua: {{ $p->pengajuan?->tim?->ketua?->nama ?? '-' }}
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 600;">
                                        {{ $p->pengajuan?->tim?->lomba?->nama_lomba ?? '-' }}
                                    </div>
                                    <span class="badge badge-purple" style="font-size: 0.68rem;">
                                        {{ $p->pengajuan?->tim?->lomba?->tingkat ?? '-' }}
                                    </span>
                                </td>
                                <td>
                                    <div style="font-size: 0.82rem; font-weight: 600;">
                                        {{ $p->pengajuan?->dosen?->nama ?? '-' }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-primary">{{ $p->babak }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ str_contains($p->hasil_akhir, 'Juara') ? 'badge-success' : 'badge-warning' }}" style="font-size: 0.8rem; font-weight: 700;">
                                        <i class="bi bi-trophy-fill"></i> {{ $p->hasil_akhir }}
                                    </span>
                                </td>
                                <td style="font-size: 0.8rem; color: var(--text-muted);">
                                    {{ $p->tanggal_update ? $p->tanggal_update->format('d M Y') : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                    Belum ada catatan riwayat prestasi yang tervalidasi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ========================================================= --}}
    {{-- TAB 2: VERIFIKASI & KELOLA DATA LOMBA                     --}}
    {{-- ========================================================= --}}
    @if($currentTab === 'lomba')
        <!-- Filter Bar & Search Lomba -->
        <div class="filter-bar">
            <div class="filter-pills">
                <span style="font-weight: 700; font-size: 0.85rem; color: var(--primary-dark); margin-right: 6px;">Status:</span>
                <a href="{{ route('admin.index', ['tab' => 'lomba', 'lomba_status' => 'semua', 'lomba_search' => $lombaSearch]) }}" class="filter-pill {{ $lombaStatusFilter === 'semua' ? 'active' : '' }}">
                    Semua ({{ $stats['total_lomba'] }})
                </a>
                <a href="{{ route('admin.index', ['tab' => 'lomba', 'lomba_status' => 'menunggu', 'lomba_search' => $lombaSearch]) }}" class="filter-pill {{ $lombaStatusFilter === 'menunggu' ? 'active' : '' }}">
                    <i class="bi bi-clock-history"></i> Menunggu ({{ $stats['lomba_menunggu'] }})
                </a>
                <a href="{{ route('admin.index', ['tab' => 'lomba', 'lomba_status' => 'terverifikasi', 'lomba_search' => $lombaSearch]) }}" class="filter-pill {{ $lombaStatusFilter === 'terverifikasi' ? 'active' : '' }}">
                    <i class="bi bi-check-circle"></i> Terverifikasi ({{ $stats['lomba_terverifikasi'] }})
                </a>
                <a href="{{ route('admin.index', ['tab' => 'lomba', 'lomba_status' => 'ditolak', 'lomba_search' => $lombaSearch]) }}" class="filter-pill {{ $lombaStatusFilter === 'ditolak' ? 'active' : '' }}">
                    <i class="bi bi-x-circle"></i> Ditolak ({{ $stats['lomba_ditolak'] }})
                </a>
            </div>

            <div style="display: flex; gap: 10px; align-items: center;">
                <form action="{{ route('admin.index') }}" method="GET" style="display: flex; gap: 8px; margin: 0;">
                    <input type="hidden" name="tab" value="lomba">
                    <input type="hidden" name="lomba_status" value="{{ $lombaStatusFilter }}">
                    <input type="text" name="lomba_search" class="form-control" style="width: 220px; padding: 7px 12px; font-size: 0.85rem;" placeholder="Cari nama/penyelenggara..." value="{{ $lombaSearch }}">
                    <button type="submit" class="btn btn-secondary btn-sm"><i class="bi bi-search"></i></button>
                    @if(!empty($lombaSearch))
                        <a href="{{ route('admin.index', ['tab' => 'lomba', 'lomba_status' => $lombaStatusFilter]) }}" class="btn btn-secondary btn-sm" title="Reset pencarian"><i class="bi bi-x"></i></a>
                    @endif
                </form>

                <button type="button" class="btn btn-primary btn-sm" onclick="openModal('modalTambahLomba')">
                    <i class="bi bi-plus-lg"></i> Tambah Lomba
                </button>
            </div>
        </div>

        <!-- Tabel Lomba -->
        <div class="table-container">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th>Nama Lomba</th>
                        <th>Kategori & Tingkat</th>
                        <th>Penyelenggara</th>
                        <th>Tenggat & Biaya</th>
                        <th>Status Kurasi</th>
                        <th>Pengaju / Kurator</th>
                        <th style="text-align: right; width: 180px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lombaList as $item)
                        <tr>
                            <td style="font-weight: 700; color: #94A3B8;">#{{ $item->id_lomba }}</td>
                            <td>
                                <div style="font-weight: 700; color: var(--primary-dark);">{{ $item->nama_lomba }}</div>
                                @if(!empty($item->tautan_daftar))
                                    <a href="{{ $item->tautan_daftar }}" target="_blank" style="font-size: 0.72rem; color: #2563EB; display: inline-flex; align-items: center; gap: 3px; margin-top: 3px;">
                                        <i class="bi bi-box-arrow-up-right"></i> Tautan Pendaftaran
                                    </a>
                                @endif
                            </td>
                            <td>
                                <div style="font-weight: 600;">{{ $item->kategori }}</div>
                                <div style="display: flex; gap: 4px; flex-wrap: wrap; margin-top: 4px;">
                                    <span class="badge badge-purple" style="font-size: 0.7rem;">{{ $item->tingkat }}</span>
                                    @if(!empty($item->tipe_peserta))
                                        <span class="badge" style="font-size: 0.68rem; background: #F1F5F9; color: #475569; border: 1px solid #CBD5E1;">{{ $item->tipe_peserta }}</span>
                                    @endif
                                    @if(!empty($item->mode_pelaksanaan))
                                        <span class="badge" style="font-size: 0.68rem; background: #E0F2FE; color: #0369A1;">{{ $item->mode_pelaksanaan }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>{{ $item->penyelenggara }}</td>
                            <td>
                                <div>{{ $item->tenggat ? $item->tenggat->format('d M Y') : '-' }}</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">
                                    {{ $item->biaya > 0 ? 'Rp ' . number_format($item->biaya, 0, ',', '.') : 'Gratis' }}
                                </div>
                            </td>
                            <td>
                                @if($item->status_verifikasi === 'terverifikasi')
                                    <span class="badge badge-success"><i class="bi bi-shield-check"></i> Terverifikasi</span>
                                @elseif($item->status_verifikasi === 'menunggu')
                                    <span class="badge badge-warning"><i class="bi bi-hourglass-split"></i> Menunggu</span>
                                @else
                                    <span class="badge badge-danger"><i class="bi bi-x-circle"></i> Ditolak</span>
                                @endif
                            </td>
                            <td>
                                @if($item->pengaju)
                                    <div style="font-size: 0.8rem; font-weight: 600;">{{ $item->pengaju->nama }}</div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">Mahasiswa ({{ $item->pengaju->nim }})</div>
                                @elseif($item->verifikator)
                                    <div style="font-size: 0.8rem; font-weight: 600; color: var(--primary);">{{ $item->verifikator->nama }}</div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">Admin Resmi Kemahasiswaan</div>
                                @else
                                    <span style="font-size: 0.78rem; color: var(--text-muted);">-</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 4px;">
                                    @if($item->status_verifikasi === 'menunggu')
                                        <!-- Tombol Aksi Kurasi Lomba Kredibel / Tolak -->
                                        <form action="{{ route('admin.lomba.verifikasi', $item->id_lomba) }}" method="POST" style="margin: 0;">
                                            @csrf
                                            <input type="hidden" name="status" value="terverifikasi">
                                            <button type="submit" class="btn btn-success btn-sm" title="Nyatakan Lomba Kredibel">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.lomba.verifikasi', $item->id_lomba) }}" method="POST" style="margin: 0;">
                                            @csrf
                                            <input type="hidden" name="status" value="ditolak">
                                            <button type="submit" class="btn btn-danger btn-sm" title="Tolak Lomba">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Tombol Detail / Pratinjau -->
                                    <button type="button" class="btn btn-secondary btn-sm" title="Lihat Detail Lomba" onclick="previewLomba({{ json_encode($item) }})">
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    <!-- Tombol Edit Lomba -->
                                    <button type="button" class="btn btn-secondary btn-sm" title="Ubah Data Lomba" onclick="editLomba({{ json_encode($item) }})">
                                        <i class="bi bi-pencil"></i>
                                    </button>

                                    <!-- Tombol Hapus Lomba -->
                                    <form action="{{ route('admin.lomba.destroy', $item->id_lomba) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data lomba ini secara permanen?');" style="margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-secondary btn-sm" style="color: var(--danger);" title="Hapus Lomba">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                <i class="bi bi-inbox" style="font-size: 2.2rem; display: block; margin-bottom: 8px;"></i>
                                Tidak ditemukan data lomba yang sesuai dengan kriteria filter atau pencarian saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    {{-- ========================================================= --}}
    {{-- TAB 3: PERSETUJUAN KETUA                                  --}}
    {{-- ========================================================= --}}
    @if($currentTab === 'tim')
        <!-- Filter Bar & Search Tim -->
        <div class="filter-bar">
            <div class="filter-pills">
                <span style="font-weight: 700; font-size: 0.85rem; color: var(--primary-dark); margin-right: 6px;">Status Tim:</span>
                <a href="{{ route('admin.index', ['tab' => 'tim', 'tim_status' => 'semua', 'tim_search' => $timSearch]) }}" class="filter-pill {{ $timStatusFilter === 'semua' ? 'active' : '' }}">
                    Semua ({{ $stats['total_tim'] }})
                </a>
                <a href="{{ route('admin.index', ['tab' => 'tim', 'tim_status' => 'menunggu_persetujuan', 'tim_search' => $timSearch]) }}" class="filter-pill {{ $timStatusFilter === 'menunggu_persetujuan' ? 'active' : '' }}">
                    <i class="bi bi-clock-history"></i> Menunggu Persetujuan ({{ $stats['tim_menunggu'] }})
                </a>
                <a href="{{ route('admin.index', ['tab' => 'tim', 'tim_status' => 'disetujui', 'tim_search' => $timSearch]) }}" class="filter-pill {{ $timStatusFilter === 'disetujui' ? 'active' : '' }}">
                    <i class="bi bi-check-circle"></i> Disetujui ({{ $stats['tim_disetujui'] }})
                </a>
                <a href="{{ route('admin.index', ['tab' => 'tim', 'tim_status' => 'ditolak', 'tim_search' => $timSearch]) }}" class="filter-pill {{ $timStatusFilter === 'ditolak' ? 'active' : '' }}">
                    <i class="bi bi-x-circle"></i> Ditolak ({{ $stats['tim_ditolak'] }})
                </a>
            </div>

            <div style="display: flex; gap: 10px; align-items: center;">
                <form action="{{ route('admin.index') }}" method="GET" style="display: flex; gap: 8px; margin: 0;">
                    <input type="hidden" name="tab" value="tim">
                    <input type="hidden" name="tim_status" value="{{ $timStatusFilter }}">
                    <input type="text" name="tim_search" class="form-control" style="width: 240px; padding: 7px 12px; font-size: 0.85rem;" placeholder="Cari tim, lomba, ketua..." value="{{ $timSearch }}">
                    <button type="submit" class="btn btn-secondary btn-sm"><i class="bi bi-search"></i></button>
                    @if(!empty($timSearch))
                        <a href="{{ route('admin.index', ['tab' => 'tim', 'tim_status' => $timStatusFilter]) }}" class="btn btn-secondary btn-sm" title="Reset pencarian"><i class="bi bi-x"></i></a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Tabel Tim -->
        <div class="table-container">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th>Nama Tim</th>
                        <th>Lomba yang Diikuti</th>
                        <th>Ketua Tim (NIM & Prodi)</th>
                        <th>Anggota Terdaftar</th>
                        <th>Dosen Pembimbing</th>
                        <th>Status Tim</th>
                        <th style="text-align: right; width: 170px;">Aksi Persetujuan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($timList as $tim)
                        <tr>
                            <td style="font-weight: 700; color: #94A3B8;">#{{ $tim->id_tim }}</td>
                            <td>
                                <div style="font-weight: 700; color: var(--primary-dark);">{{ $tim->nama_tim }}</div>
                                <span style="font-size: 0.72rem; color: var(--text-muted);">
                                    Dibuat: {{ $tim->created_at ? $tim->created_at->format('d M Y') : '-' }}
                                </span>
                            </td>
                            <td>
                                <div style="font-weight: 600;">{{ $tim->lomba ? $tim->lomba->nama_lomba : '-' }}</div>
                                <span class="badge badge-purple" style="font-size: 0.7rem; margin-top: 3px;">{{ $tim->lomba ? $tim->lomba->kategori : '' }}</span>
                            </td>
                            <td>
                                @if($tim->ketua)
                                    <div style="font-weight: 700;">{{ $tim->ketua->nama }}</div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">
                                        NIM: {{ $tim->ketua->nim }} • {{ $tim->ketua->prodi }}
                                    </div>
                                @else
                                    <span style="color: var(--text-muted);">-</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <span class="badge {{ $tim->anggota->count() >= $tim->kuota_anggota ? 'badge-success' : 'badge-primary' }}">
                                        {{ $tim->anggota->count() }} / {{ $tim->kuota_anggota }} Orang
                                    </span>
                                </div>
                                <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 4px;">
                                    Sisa Kuota: {{ $tim->sisa_kuota_anggota }} slot
                                </div>
                            </td>
                            <td>
                                @php
                                    $bimbingan = $tim->pengajuanBimbingan->first();
                                @endphp
                                @if($bimbingan && $bimbingan->dosen)
                                    <div style="font-size: 0.82rem; font-weight: 600;">{{ $bimbingan->dosen->nama }}</div>
                                    <span class="badge {{ $bimbingan->status === 'disetujui' ? 'badge-success' : 'badge-warning' }}" style="font-size: 0.68rem;">
                                        {{ ucfirst($bimbingan->status) }}
                                    </span>
                                @else
                                    <span style="font-size: 0.78rem; color: var(--text-muted);">Belum ada pembimbing</span>
                                @endif
                            </td>
                            <td>
                                @if($tim->status_tim === 'disetujui')
                                    <span class="badge badge-success"><i class="bi bi-check-circle-fill"></i> Disetujui</span>
                                @elseif($tim->status_tim === 'menunggu_persetujuan')
                                    <span class="badge badge-warning"><i class="bi bi-clock-history"></i> Menunggu Approval</span>
                                @else
                                    <span class="badge badge-danger"><i class="bi bi-x-circle-fill"></i> Ditolak</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 4px;">
                                    @if($tim->status_tim === 'menunggu_persetujuan')
                                        <!-- Aksi Setujui / Tolak Ketua Tim -->
                                        <form action="{{ route('admin.tim.verifikasi', $tim->id_tim) }}" method="POST" style="margin: 0;">
                                            @csrf
                                            <input type="hidden" name="status" value="disetujui">
                                            <button type="submit" class="btn btn-success btn-sm" title="Setujui Tim & Ketua">
                                                <i class="bi bi-check-lg"></i> Setujui
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.tim.verifikasi', $tim->id_tim) }}" method="POST" style="margin: 0;">
                                            @csrf
                                            <input type="hidden" name="status" value="ditolak">
                                            <button type="submit" class="btn btn-danger btn-sm" title="Tolak Pengajuan Tim">
                                                <i class="bi bi-x-lg"></i> Tolak
                                            </button>
                                        </form>
                                    @else
                                        <!-- Tombol Toggle Ulang Jika Perlu Re-validasi -->
                                        @if($tim->status_tim === 'ditolak')
                                            <form action="{{ route('admin.tim.verifikasi', $tim->id_tim) }}" method="POST" style="margin: 0;">
                                                @csrf
                                                <input type="hidden" name="status" value="disetujui">
                                                <button type="submit" class="btn btn-outline-primary btn-sm" title="Ubah status jadi Disetujui">
                                                    Setujui
                                                </button>
                                            </form>
                                        @elseif($tim->status_tim === 'disetujui')
                                            <form action="{{ route('admin.tim.verifikasi', $tim->id_tim) }}" method="POST" style="margin: 0;">
                                                @csrf
                                                <input type="hidden" name="status" value="ditolak">
                                                <button type="submit" class="btn btn-secondary btn-sm" style="color: var(--danger);" title="Tolak Tim">
                                                    Tolak
                                                </button>
                                            </form>
                                        @endif
                                    @endif

                                    <!-- Hapus Tim -->
                                    <form action="{{ route('admin.tim.destroy', $tim->id_tim) }}" method="POST" onsubmit="return confirm('Hapus data tim ini?');" style="margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-secondary btn-sm" style="color: var(--danger);" title="Hapus Tim">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                <i class="bi bi-people" style="font-size: 2.2rem; display: block; margin-bottom: 8px;"></i>
                                Tidak ditemukan data tim lomba yang sesuai dengan filter saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    {{-- ========================================================= --}}
    {{-- TAB 4: KELOLA DATA DOSEN PEMBIMBING                       --}}
    {{-- ========================================================= --}}
    @if($currentTab === 'dosen')
        <!-- Filter & Tambah Dosen -->
        <div class="filter-bar">
            <div>
                <span style="font-weight: 700; color: var(--primary-dark); font-size: 0.95rem;">
                    <i class="bi bi-person-video3"></i> Data Master Dosen Pembimbing Lomba JTI
                </span>
                <span style="font-size: 0.8rem; color: var(--text-muted); margin-left: 8px;">
                    ({{ $stats['total_dosen'] }} Dosen Terdaftar)
                </span>
            </div>

            <div style="display: flex; gap: 10px; align-items: center;">
                <form action="{{ route('admin.index') }}" method="GET" style="display: flex; gap: 8px; margin: 0;">
                    <input type="hidden" name="tab" value="dosen">
                    <input type="text" name="dosen_search" class="form-control" style="width: 240px; padding: 7px 12px; font-size: 0.85rem;" placeholder="Cari NIDN / Nama / Keahlian..." value="{{ $dosenSearch }}">
                    <button type="submit" class="btn btn-secondary btn-sm"><i class="bi bi-search"></i></button>
                    @if(!empty($dosenSearch))
                        <a href="{{ route('admin.index', ['tab' => 'dosen']) }}" class="btn btn-secondary btn-sm" title="Reset pencarian"><i class="bi bi-x"></i></a>
                    @endif
                </form>

                <button type="button" class="btn btn-primary btn-sm" onclick="openModal('modalTambahDosen')">
                    <i class="bi bi-person-plus-fill"></i> Tambah Dosen Baru
                </button>
            </div>
        </div>

        <!-- Tabel Dosen -->
        <div class="table-container">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="width: 140px;">NIDN</th>
                        <th>Nama Dosen & Gelar</th>
                        <th>Email Kampus</th>
                        <th>Bidang Keahlian</th>
                        <th>Kuota Bimbingan</th>
                        <th>Bimbingan Aktif</th>
                        <th style="text-align: right; width: 130px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dosenList as $d)
                        <tr>
                            <td style="font-weight: 700; color: var(--primary);">{{ $d->nidn }}</td>
                            <td>
                                <div style="font-weight: 700; color: var(--primary-dark);">{{ $d->nama }}</div>
                            </td>
                            <td>
                                <a href="mailto:{{ $d->email_kampus }}" style="color: #2563EB;">{{ $d->email_kampus }}</a>
                            </td>
                            <td>
                                <span class="badge badge-purple" style="font-size: 0.78rem;">{{ $d->bidang_keahlian }}</span>
                            </td>
                            <td>
                                <span style="font-weight: 700; color: var(--primary-dark);">{{ $d->kuota_bimbingan }}</span>
                                <span style="font-size: 0.75rem; color: var(--text-muted);">tim</span>
                            </td>
                            <td>
                                <span class="badge {{ $d->bimbingan_aktif_count >= $d->kuota_bimbingan ? 'badge-danger' : 'badge-success' }}">
                                    {{ $d->bimbingan_aktif_count ?? 0 }} Tim Aktif
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 4px;">
                                    <button type="button" class="btn btn-secondary btn-sm" title="Ubah Data Dosen" onclick="editDosen({{ json_encode($d) }})">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('admin.dosen.destroy', $d->nidn) }}" method="POST" onsubmit="return confirm('Hapus data dosen ini?');" style="margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-secondary btn-sm" style="color: var(--danger);" title="Hapus Dosen">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                <i class="bi bi-person-x" style="font-size: 2.2rem; display: block; margin-bottom: 8px;"></i>
                                Tidak ada data dosen yang sesuai dengan pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    {{-- ========================================================= --}}
    {{-- TAB 5: KELOLA DATA MAHASISWA                              --}}
    {{-- ========================================================= --}}
    @if($currentTab === 'mahasiswa')
        <!-- Filter & Tambah Mahasiswa -->
        <div class="filter-bar">
            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <span style="font-weight: 700; color: var(--primary-dark); font-size: 0.95rem;">
                    <i class="bi bi-mortarboard-fill"></i> Data Mahasiswa
                </span>

                <form action="{{ route('admin.index') }}" method="GET" style="display: flex; gap: 8px; margin: 0; align-items: center; flex-wrap: wrap;">
                    <input type="hidden" name="tab" value="mahasiswa">
                    
                    <select name="mhs_prodi" class="form-select" style="width: auto; padding: 6px 10px; font-size: 0.85rem;" onchange="this.form.submit()">
                        <option value="semua">Semua Program Studi</option>
                        @foreach($prodiOptions as $p)
                            <option value="{{ $p }}" {{ $mhsProdi === $p ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>

                    <select name="mhs_angkatan" class="form-select" style="width: auto; padding: 6px 10px; font-size: 0.85rem;" onchange="this.form.submit()">
                        <option value="semua">Semua Angkatan</option>
                        @foreach([2023, 2024, 2025, 2026] as $ang)
                            <option value="{{ $ang }}" {{ $mhsAngkatan == $ang ? 'selected' : '' }}>Angkatan {{ $ang }}</option>
                        @endforeach
                    </select>

                    <input type="text" name="mhs_search" class="form-control" style="width: 200px; padding: 6px 10px; font-size: 0.85rem;" placeholder="Cari NIM / Nama..." value="{{ $mhsSearch }}">
                    <button type="submit" class="btn btn-secondary btn-sm"><i class="bi bi-search"></i></button>
                    @if(!empty($mhsSearch) || $mhsProdi !== 'semua' || $mhsAngkatan !== 'semua')
                        <a href="{{ route('admin.index', ['tab' => 'mahasiswa']) }}" class="btn btn-secondary btn-sm" title="Reset"><i class="bi bi-x"></i></a>
                    @endif
                </form>
            </div>

            <div>
                <button type="button" class="btn btn-primary btn-sm" onclick="openModal('modalTambahMahasiswa')">
                    <i class="bi bi-person-plus-fill"></i> Tambah Mahasiswa Baru
                </button>
            </div>
        </div>

        <!-- Tabel Mahasiswa -->
        <div class="table-container">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="width: 150px;">NIM</th>
                        <th>Nama Mahasiswa</th>
                        <th>Email Kampus</th>
                        <th>Program Studi</th>
                        <th>Angkatan</th>
                        <th>Keaktifan Lomba</th>
                        <th style="text-align: right; width: 130px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mahasiswaList as $mhs)
                        <tr>
                            <td style="font-weight: 700; color: var(--primary);">{{ $mhs->nim }}</td>
                            <td>
                                <div style="font-weight: 700; color: var(--primary-dark);">{{ $mhs->nama }}</div>
                            </td>
                            <td>
                                <a href="mailto:{{ $mhs->email_kampus }}" style="color: #2563EB;">{{ $mhs->email_kampus }}</a>
                            </td>
                            <td>{{ $mhs->prodi }}</td>
                            <td>
                                <span class="badge badge-purple">{{ $mhs->angkatan }}</span>
                            </td>
                            <td>
                                <div style="font-size: 0.8rem;">
                                    <span>Ketua: <strong>{{ $mhs->tim_ketua_count ?? 0 }}</strong> tim</span> •
                                    <span>Anggota: <strong>{{ $mhs->keanggotaan_count ?? 0 }}</strong> tim</span>
                                </div>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 4px;">
                                    <button type="button" class="btn btn-secondary btn-sm" title="Ubah Data Mahasiswa" onclick="editMahasiswa({{ json_encode($mhs) }})">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('admin.mahasiswa.destroy', $mhs->nim) }}" method="POST" onsubmit="return confirm('Hapus data mahasiswa ini?');" style="margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-secondary btn-sm" style="color: var(--danger);" title="Hapus Mahasiswa">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                <i class="bi bi-person-x" style="font-size: 2.2rem; display: block; margin-bottom: 8px;"></i>
                                Tidak ditemukan data mahasiswa yang sesuai dengan kriteria filter saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

</div>

{{-- ========================================================= --}}
{{-- MODALS SECTION                                            --}}
{{-- ========================================================= --}}

<!-- 1. Modal Tambah Lomba -->
<div class="modal-overlay" id="modalTambahLomba">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title">
                <i class="bi bi-trophy-fill" style="color: var(--primary);"></i> Tambah Lomba Baru Resmi
            </div>
            <button type="button" class="modal-close" onclick="closeModal('modalTambahLomba')">&times;</button>
        </div>
        <form action="{{ route('admin.lomba.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nama Lomba <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="nama_lomba" class="form-control" required placeholder="Contoh: Gemastik XVIII 2026">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label">Kategori Minat <span style="color: var(--danger);">*</span></label>
                        <select name="kategori" class="form-select" required>
                            @foreach($kategoriLombaOptions as $kat)
                                <option value="{{ $kat }}">{{ $kat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tingkat Lomba <span style="color: var(--danger);">*</span></label>
                        <select name="tingkat" class="form-select" required>
                            @foreach($tingkatOptions as $ting)
                                <option value="{{ $ting }}">{{ $ting }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label">Tipe Peserta <span style="color: var(--danger);">*</span></label>
                        <select name="tipe_peserta" class="form-select" required>
                            <option value="Individu">Individu</option>
                            <option value="Kelompok" selected>Kelompok</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mode Pelaksanaan <span style="color: var(--danger);">*</span></label>
                        <select name="mode_pelaksanaan" class="form-select" required>
                            <option value="Online">Online (Daring)</option>
                            <option value="Offline">Offline (Luring)</option>
                            <option value="Hybrid">Hybrid</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label">Penyelenggara <span style="color: var(--danger);">*</span></label>
                        <input type="text" name="penyelenggara" class="form-control" required placeholder="Contoh: Puspresnas Kemdikbud">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tenggat Pendaftaran <span style="color: var(--danger);">*</span></label>
                        <input type="date" name="tenggat" class="form-control" required value="{{ now()->addDays(30)->format('Y-m-d') }}">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label">Biaya Pendaftaran (Rp) <span style="color: var(--danger);">*</span> <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: normal;">(0 jika gratis)</span></label>
                        <div style="position: relative; display: flex; align-items: center;">
                            <span style="position: absolute; left: 12px; font-weight: 700; color: var(--text-muted); font-size: 0.88rem; pointer-events: none;">Rp</span>
                            <input type="text" id="tambah_lomba_biaya_display" class="form-control" style="padding-left: 38px;" placeholder="0" value="0" required oninput="formatRupiahInput(this, 'tambah_lomba_biaya')">
                            <input type="hidden" name="biaya" id="tambah_lomba_biaya" value="0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tautan Pendaftaran Resmi <span style="color: var(--danger);">*</span></label>
                        <input type="url" name="tautan_daftar" class="form-control" required placeholder="https://puspresnas.kemdikbud.go.id">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Deskripsi Kompetisi <span style="color: var(--danger);">*</span></label>
                    <textarea name="deskripsi" class="form-control" rows="3" required placeholder="Uraian singkat mengenai lomba..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Persyaratan & Ketentuan Delegasi <span style="color: var(--danger);">*</span></label>
                    <textarea name="persyaratan" class="form-control" rows="2" required placeholder="Mahasiswa aktif JTI, kartu identitas, dll..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Benefit & Penghargaan <span style="color: var(--danger);">*</span></label>
                    <textarea name="benefit" class="form-control" rows="2" required placeholder="Uang pembinaan, sertifikat nasional, konversi sks..."></textarea>
                </div>

                <!-- Input Berkas Poster & Buku Pedoman -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-top: 14px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Poster Lomba <span style="color: var(--danger);">*</span> <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: normal;">(PNG, JPG, JPEG maks 2MB)</span></label>
                        <input type="file" name="poster" id="tambah_lomba_poster" class="form-control" accept=".png,.jpg,.jpeg,image/png,image/jpeg" required onchange="previewPosterFile(this, 'tambah_poster_preview', 'tambah_poster_preview_img')">
                        <div id="tambah_poster_preview" style="display: none; margin-top: 8px; border: 1px dashed var(--border); border-radius: var(--radius-sm); padding: 6px; text-align: center; background: #F8FAFC;">
                            <img id="tambah_poster_preview_img" src="" alt="Pratinjau Poster" style="max-height: 120px; max-width: 100%; border-radius: 4px; object-fit: contain;">
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Buku Pedoman <span style="color: var(--danger);">*</span> <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: normal;">(PDF, DOC, DOCX maks 10MB)</span></label>
                        <input type="file" name="buku_pedoman" id="tambah_lomba_pedoman" class="form-control" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required onchange="previewPedomanFile(this, 'tambah_pedoman_info', 'tambah_pedoman_filename')">
                        <div id="tambah_pedoman_info" style="display: none; margin-top: 8px; font-size: 0.8rem; color: #0369A1; background: #E0F2FE; border-radius: var(--radius-sm); padding: 6px 10px;">
                            <i class="bi bi-file-earmark-check-fill"></i> <span id="tambah_pedoman_filename"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('modalTambahLomba')">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-cloud-arrow-up-fill"></i> Simpan & Publikasikan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 2. Modal Edit Lomba -->
<div class="modal-overlay" id="modalEditLomba">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title">
                <i class="bi bi-pencil-square" style="color: var(--primary);"></i> Ubah Data Lomba
            </div>
            <button type="button" class="modal-close" onclick="closeModal('modalEditLomba')">&times;</button>
        </div>
        <form id="formEditLomba" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nama Lomba <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="nama_lomba" id="edit_lomba_nama" class="form-control" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label">Kategori Minat <span style="color: var(--danger);">*</span></label>
                        <select name="kategori" id="edit_lomba_kategori" class="form-select" required>
                            @foreach($kategoriLombaOptions as $kat)
                                <option value="{{ $kat }}">{{ $kat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tingkat Lomba <span style="color: var(--danger);">*</span></label>
                        <select name="tingkat" id="edit_lomba_tingkat" class="form-select" required>
                            @foreach($tingkatOptions as $ting)
                                <option value="{{ $ting }}">{{ $ting }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label">Tipe Peserta <span style="color: var(--danger);">*</span></label>
                        <select name="tipe_peserta" id="edit_lomba_tipe_peserta" class="form-select" required>
                            <option value="Individu">Individu</option>
                            <option value="Kelompok">Kelompok</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mode Pelaksanaan <span style="color: var(--danger);">*</span></label>
                        <select name="mode_pelaksanaan" id="edit_lomba_mode_pelaksanaan" class="form-select" required>
                            <option value="Online">Online (Daring)</option>
                            <option value="Offline">Offline (Luring)</option>
                            <option value="Hybrid">Hybrid</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label">Penyelenggara <span style="color: var(--danger);">*</span></label>
                        <input type="text" name="penyelenggara" id="edit_lomba_penyelenggara" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tenggat Pendaftaran <span style="color: var(--danger);">*</span></label>
                        <input type="date" name="tenggat" id="edit_lomba_tenggat" class="form-control" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label">Biaya Pendaftaran (Rp) <span style="color: var(--danger);">*</span> <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: normal;">(0 jika gratis)</span></label>
                        <div style="position: relative; display: flex; align-items: center;">
                            <span style="position: absolute; left: 12px; font-weight: 700; color: var(--text-muted); font-size: 0.88rem; pointer-events: none;">Rp</span>
                            <input type="text" id="edit_lomba_biaya_display" class="form-control" style="padding-left: 38px;" placeholder="0" value="0" required oninput="formatRupiahInput(this, 'edit_lomba_biaya')">
                            <input type="hidden" name="biaya" id="edit_lomba_biaya" value="0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status Verifikasi <span style="color: var(--danger);">*</span></label>
                        <select name="status_verifikasi" id="edit_lomba_status" class="form-select" required>
                            <option value="terverifikasi">Terverifikasi (Publik)</option>
                            <option value="menunggu">Menunggu</option>
                            <option value="ditolak">Ditolak</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Tautan Pendaftaran Resmi <span style="color: var(--danger);">*</span></label>
                    <input type="url" name="tautan_daftar" id="edit_lomba_tautan" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Deskripsi Kompetisi <span style="color: var(--danger);">*</span></label>
                    <textarea name="deskripsi" id="edit_lomba_deskripsi" class="form-control" rows="2" required></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Persyaratan & Ketentuan Delegasi <span style="color: var(--danger);">*</span></label>
                    <textarea name="persyaratan" id="edit_lomba_persyaratan" class="form-control" rows="2" required></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Benefit & Penghargaan <span style="color: var(--danger);">*</span></label>
                    <textarea name="benefit" id="edit_lomba_benefit" class="form-control" rows="2" required></textarea>
                </div>

                <!-- Input Berkas Poster & Buku Pedoman Edit -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-top: 14px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Poster Lomba <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: normal;">(PNG/JPG/JPEG maks 2MB, kosongkan jika tak diubah)</span></label>
                        <input type="file" name="poster" id="edit_lomba_poster" class="form-control" accept=".png,.jpg,.jpeg,image/png,image/jpeg" onchange="previewPosterFile(this, 'edit_poster_preview', 'edit_poster_preview_img')">
                        <div id="edit_poster_preview" style="display: none; margin-top: 8px; border: 1px dashed var(--border); border-radius: var(--radius-sm); padding: 6px; text-align: center; background: #F8FAFC;">
                            <img id="edit_poster_preview_img" src="" alt="Pratinjau Poster" style="max-height: 120px; max-width: 100%; border-radius: 4px; object-fit: contain;">
                        </div>
                        <div id="edit_poster_current" style="display: none; margin-top: 6px; font-size: 0.75rem; color: var(--text-muted);"></div>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Buku Pedoman <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: normal;">(PDF/DOC/DOCX maks 10MB, kosongkan jika tak diubah)</span></label>
                        <input type="file" name="buku_pedoman" id="edit_lomba_pedoman" class="form-control" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" onchange="previewPedomanFile(this, 'edit_pedoman_info', 'edit_pedoman_filename')">
                        <div id="edit_pedoman_info" style="display: none; margin-top: 8px; font-size: 0.8rem; color: #0369A1; background: #E0F2FE; border-radius: var(--radius-sm); padding: 6px 10px;">
                            <i class="bi bi-file-earmark-check-fill"></i> <span id="edit_pedoman_filename"></span>
                        </div>
                        <div id="edit_pedoman_current" style="display: none; margin-top: 6px; font-size: 0.75rem; color: var(--text-muted);"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('modalEditLomba')">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- 3. Modal Pratinjau Detail Lomba -->
<div class="modal-overlay" id="modalPreviewLomba">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title">
                <i class="bi bi-info-circle-fill" style="color: var(--primary);"></i> Detail Informasi Lomba
            </div>
            <button type="button" class="modal-close" onclick="closeModal('modalPreviewLomba')">&times;</button>
        </div>
        <div class="modal-body" id="previewLombaContent">
            <!-- Diisi lewat JS -->
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('modalPreviewLomba')">Tutup</button>
        </div>
    </div>
</div>

<!-- 4. Modal Tambah Dosen -->
<div class="modal-overlay" id="modalTambahDosen">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title">
                <i class="bi bi-person-plus-fill" style="color: var(--primary);"></i> Tambah Dosen Pembimbing Baru
            </div>
            <button type="button" class="modal-close" onclick="closeModal('modalTambahDosen')">&times;</button>
        </div>
        <form action="{{ route('admin.dosen.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label">NIDN <span style="color: var(--danger);">*</span></label>
                        <input type="text" name="nidn" class="form-control" required placeholder="Contoh: 0012058509">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kuota Bimbingan <span style="color: var(--danger);">*</span></label>
                        <input type="number" name="kuota_bimbingan" class="form-control" required value="5" min="1" max="20">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Nama Lengkap & Gelar <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="nama" class="form-control" required placeholder="Contoh: Dr. Budi Santoso, S.Kom., M.T.">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label">Email Kampus <span style="color: var(--danger);">*</span></label>
                        <input type="email" name="email_kampus" class="form-control" required placeholder="budi.santoso@polinema.ac.id">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kata Sandi Awal <span style="color: var(--danger);">*</span></label>
                        <input type="password" name="password" class="form-control" required placeholder="Minimal 6 karakter" value="password123">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Bidang Keahlian <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="bidang_keahlian" class="form-control" required placeholder="Contoh: Software Engineering & Web Development">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('modalTambahDosen')">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Simpan Data Dosen</button>
            </div>
        </form>
    </div>
</div>

<!-- 5. Modal Edit Dosen -->
<div class="modal-overlay" id="modalEditDosen">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title">
                <i class="bi bi-pencil-square" style="color: var(--primary);"></i> Ubah Data Dosen Pembimbing
            </div>
            <button type="button" class="modal-close" onclick="closeModal('modalEditDosen')">&times;</button>
        </div>
        <form id="formEditDosen" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label">NIDN (Kunci Primer)</label>
                        <input type="text" id="edit_dosen_nidn_view" class="form-control" disabled>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kuota Bimbingan <span style="color: var(--danger);">*</span></label>
                        <input type="number" name="kuota_bimbingan" id="edit_dosen_kuota" class="form-control" required min="1" max="20">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Nama Lengkap & Gelar <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="nama" id="edit_dosen_nama" class="form-control" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label">Email Kampus <span style="color: var(--danger);">*</span></label>
                        <input type="email" name="email_kampus" id="edit_dosen_email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kata Sandi Baru (Opsional)</label>
                        <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tak diubah">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Bidang Keahlian <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="bidang_keahlian" id="edit_dosen_keahlian" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('modalEditDosen')">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Perbarui Data Dosen</button>
            </div>
        </form>
    </div>
</div>

<!-- 6. Modal Tambah Mahasiswa -->
<div class="modal-overlay" id="modalTambahMahasiswa">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title">
                <i class="bi bi-mortarboard-fill" style="color: var(--primary);"></i> Tambah Mahasiswa Baru
            </div>
            <button type="button" class="modal-close" onclick="closeModal('modalTambahMahasiswa')">&times;</button>
        </div>
        <form action="{{ route('admin.mahasiswa.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label">NIM <span style="color: var(--danger);">*</span></label>
                        <input type="text" name="nim" class="form-control" required placeholder="Contoh: 244107060199">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Angkatan <span style="color: var(--danger);">*</span></label>
                        <select name="angkatan" class="form-select" required>
                            @foreach([2023, 2024, 2025, 2026] as $ang)
                                <option value="{{ $ang }}" {{ $ang === 2024 ? 'selected' : '' }}>{{ $ang }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Nama Lengkap Mahasiswa <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="nama" class="form-control" required placeholder="Contoh: Ahmad Fauzan">
                </div>

                <div class="form-group">
                    <label class="form-label">Program Studi <span style="color: var(--danger);">*</span></label>
                    <select name="prodi" class="form-select" required>
                        @foreach($prodiOptions as $p)
                            <option value="{{ $p }}">{{ $p }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 0;">
                    <div class="form-group">
                        <label class="form-label">Email Kampus <span style="color: var(--danger);">*</span></label>
                        <input type="email" name="email_kampus" class="form-control" required placeholder="ahmad@student.polinema.ac.id">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kata Sandi Awal <span style="color: var(--danger);">*</span></label>
                        <input type="password" name="password" class="form-control" required value="password123">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('modalTambahMahasiswa')">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Simpan Data Mahasiswa</button>
            </div>
        </form>
    </div>
</div>

<!-- 7. Modal Edit Mahasiswa -->
<div class="modal-overlay" id="modalEditMahasiswa">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title">
                <i class="bi bi-pencil-square" style="color: var(--primary);"></i> Ubah Data Mahasiswa
            </div>
            <button type="button" class="modal-close" onclick="closeModal('modalEditMahasiswa')">&times;</button>
        </div>
        <form id="formEditMahasiswa" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label">NIM</label>
                        <input type="text" id="edit_mhs_nim_view" class="form-control" disabled>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Angkatan <span style="color: var(--danger);">*</span></label>
                        <select name="angkatan" id="edit_mhs_angkatan" class="form-select" required>
                            @foreach([2023, 2024, 2025, 2026] as $ang)
                                <option value="{{ $ang }}">{{ $ang }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Nama Lengkap <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="nama" id="edit_mhs_nama" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Program Studi <span style="color: var(--danger);">*</span></label>
                    <select name="prodi" id="edit_mhs_prodi" class="form-select" required>
                        @foreach($prodiOptions as $p)
                            <option value="{{ $p }}">{{ $p }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 0;">
                    <div class="form-group">
                        <label class="form-label">Email Kampus <span style="color: var(--danger);">*</span></label>
                        <input type="email" name="email_kampus" id="edit_mhs_email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kata Sandi Baru (Opsional)</label>
                        <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tak diubah">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('modalEditMahasiswa')">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Perbarui Data Mahasiswa</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Helper Modal Open & Close
    function openModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.style.display = 'flex';
        }
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.style.display = 'none';
        }
    }

    // Close modal on click outside
    window.onclick = function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.style.display = 'none';
        }
    }

    // Format Angka Rupiah Real-time
    function formatRupiahInput(input, hiddenId) {
        let raw = input.value.replace(/[^0-9]/g, '');
        if (!raw) {
            input.value = '';
            if (hiddenId) {
                document.getElementById(hiddenId).value = 0;
            }
            return;
        }
        let number = parseInt(raw, 10);
        input.value = number.toLocaleString('id-ID');
        if (hiddenId) {
            document.getElementById(hiddenId).value = number;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const tDisplay = document.getElementById('tambah_lomba_biaya_display');
        if (tDisplay) {
            tDisplay.addEventListener('blur', function() {
                if (this.value.trim() === '') {
                    this.value = '0';
                    document.getElementById('tambah_lomba_biaya').value = 0;
                }
            });
        }
        const eDisplay = document.getElementById('edit_lomba_biaya_display');
        if (eDisplay) {
            eDisplay.addEventListener('blur', function() {
                if (this.value.trim() === '') {
                    this.value = '0';
                    document.getElementById('edit_lomba_biaya').value = 0;
                }
            });
        }
    });

    // File Preview Helpers
    function previewPosterFile(input, containerId, imgId) {
        const container = document.getElementById(containerId);
        const img = document.getElementById(imgId);
        if (input.files && input.files[0]) {
            const file = input.files[0];
            if (file.size > 2 * 1024 * 1024) {
                alert('Ukuran file poster maksimal 2 MB.');
                input.value = '';
                if (container) container.style.display = 'none';
                return;
            }
            const reader = new FileReader();
            reader.onload = function(e) {
                if (img) img.src = e.target.result;
                if (container) container.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            if (container) container.style.display = 'none';
            if (img) img.src = '';
        }
    }

    function previewPedomanFile(input, containerId, filenameId) {
        const container = document.getElementById(containerId);
        const nameEl = document.getElementById(filenameId);
        if (input.files && input.files[0]) {
            const file = input.files[0];
            if (file.size > 10 * 1024 * 1024) {
                alert('Ukuran berkas buku pedoman maksimal 10 MB.');
                input.value = '';
                if (container) container.style.display = 'none';
                return;
            }
            const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
            if (nameEl) nameEl.textContent = file.name + ' (' + sizeMb + ' MB)';
            if (container) container.style.display = 'block';
        } else {
            if (container) container.style.display = 'none';
            if (nameEl) nameEl.textContent = '';
        }
    }

    // Pratinjau Lomba
    function previewLomba(item) {
        const biayaNum = parseInt(item.biaya, 10) || 0;
        const formattedBiaya = biayaNum > 0 ? 'Rp ' + biayaNum.toLocaleString('id-ID') : 'Gratis';

        const posterHtml = item.poster_url ? `
            <div style="text-align: center; margin-bottom: 16px; background: #F8FAFC; border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 10px;">
                <img src="${item.poster_url}" alt="Poster ${item.nama_lomba}" style="max-height: 220px; max-width: 100%; border-radius: var(--radius-sm); object-fit: contain;">
            </div>
        ` : '';

        const pedomanBtn = (item.buku_pedoman_path || item.buku_pedoman_url) ? `
            <a href="/lomba/${item.id_lomba}/pedoman" target="_blank" class="btn btn-outline-secondary btn-sm" style="margin-left: 8px;">
                <i class="bi bi-file-earmark-arrow-down-fill"></i> Unduh Buku Pedoman
            </a>
        ` : '';

        const content = `
            ${posterHtml}
            <div style="margin-bottom: 16px;">
                <span class="badge ${item.status_verifikasi === 'terverifikasi' ? 'badge-success' : (item.status_verifikasi === 'menunggu' ? 'badge-warning' : 'badge-danger')}">
                    ${item.status_verifikasi.toUpperCase()}
                </span>
                <span class="badge badge-purple" style="margin-left: 6px;">Tingkat ${item.tingkat}</span>
                <span class="badge badge-primary" style="margin-left: 6px;">${item.kategori}</span>
                <span class="badge" style="margin-left: 6px; background: #F1F5F9; color: #475569; border: 1px solid #CBD5E1;">${item.tipe_peserta || 'Kelompok'}</span>
                <span class="badge" style="margin-left: 6px; background: #E0F2FE; color: #0369A1;">${item.mode_pelaksanaan || 'Online'}</span>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--primary-dark); margin-bottom: 6px;">
                ${item.nama_lomba}
            </h3>
            <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 16px;">
                Penyelenggara: <strong>${item.penyelenggara}</strong> • Tenggat: <strong>${item.tenggat || '-'}</strong> • Biaya: <strong>${formattedBiaya}</strong>
            </div>

            <div style="background: #F8FAFC; border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 14px; margin-bottom: 16px;">
                <div style="font-size: 0.82rem; font-weight: 700; color: var(--primary-dark); margin-bottom: 4px;">Deskripsi Kompetisi:</div>
                <div style="font-size: 0.88rem; color: #334155;">${item.deskripsi || 'Tidak ada deskripsi rinci.'}</div>
            </div>

            <div style="margin-bottom: 14px;">
                <div style="font-size: 0.82rem; font-weight: 700; color: var(--primary-dark); margin-bottom: 4px;">Persyaratan & Ketentuan Delegasi:</div>
                <div style="font-size: 0.85rem; color: #475569;">${item.persyaratan || 'Persyaratan standar mahasiswa aktif.'}</div>
            </div>

            <div style="margin-bottom: 16px;">
                <div style="font-size: 0.82rem; font-weight: 700; color: var(--primary-dark); margin-bottom: 4px;">Benefit & Penghargaan:</div>
                <div style="font-size: 0.85rem; color: #475569;">${item.benefit || 'Sertifikat apresiasi & penghargaan.'}</div>
            </div>

            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                ${item.tautan_daftar ? `
                    <a href="${item.tautan_daftar}" target="_blank" class="btn btn-primary btn-sm">
                        <i class="bi bi-box-arrow-up-right"></i> Registrasi Lomba
                    </a>
                ` : ''}
                ${pedomanBtn}
            </div>
        `;
        document.getElementById('previewLombaContent').innerHTML = content;
        openModal('modalPreviewLomba');
    }

    // Isi Data Modal Edit Lomba
    function editLomba(item) {
        document.getElementById('formEditLomba').action = '/admin/lomba/' + item.id_lomba;
        document.getElementById('edit_lomba_nama').value = item.nama_lomba || '';
        document.getElementById('edit_lomba_kategori').value = item.kategori || '';
        document.getElementById('edit_lomba_tingkat').value = item.tingkat || '';
        document.getElementById('edit_lomba_tipe_peserta').value = item.tipe_peserta || 'Kelompok';
        document.getElementById('edit_lomba_mode_pelaksanaan').value = item.mode_pelaksanaan || 'Online';
        document.getElementById('edit_lomba_penyelenggara').value = item.penyelenggara || '';
        document.getElementById('edit_lomba_tenggat').value = item.tenggat ? item.tenggat.substring(0, 10) : '';

        const biayaVal = parseInt(item.biaya, 10) || 0;
        document.getElementById('edit_lomba_biaya').value = biayaVal;
        document.getElementById('edit_lomba_biaya_display').value = biayaVal.toLocaleString('id-ID');

        document.getElementById('edit_lomba_status').value = item.status_verifikasi || 'terverifikasi';
        document.getElementById('edit_lomba_tautan').value = item.tautan_daftar || '';
        document.getElementById('edit_lomba_deskripsi').value = item.deskripsi || '';
        document.getElementById('edit_lomba_persyaratan').value = item.persyaratan || '';
        document.getElementById('edit_lomba_benefit').value = item.benefit || '';

        // Reset file inputs & previews
        document.getElementById('edit_lomba_poster').value = '';
        document.getElementById('edit_lomba_pedoman').value = '';
        document.getElementById('edit_poster_preview').style.display = 'none';
        document.getElementById('edit_poster_preview_img').src = '';
        document.getElementById('edit_pedoman_info').style.display = 'none';
        document.getElementById('edit_pedoman_filename').textContent = '';

        const currentPosterEl = document.getElementById('edit_poster_current');
        if (item.poster_url) {
            currentPosterEl.innerHTML = '<span style="color: #059669;"><i class="bi bi-check-circle-fill"></i> Poster terunggah</span> (<a href="' + item.poster_url + '" target="_blank" style="text-decoration: underline; color: var(--primary);">Lihat poster</a>)';
            currentPosterEl.style.display = 'block';
        } else {
            currentPosterEl.style.display = 'none';
        }

        const currentPedomanEl = document.getElementById('edit_pedoman_current');
        if (item.buku_pedoman_path || item.buku_pedoman_url) {
            currentPedomanEl.innerHTML = '<span style="color: #059669;"><i class="bi bi-file-earmark-check-fill"></i> Pedoman terunggah</span> (<a href="/lomba/' + item.id_lomba + '/pedoman" target="_blank" style="text-decoration: underline; color: var(--primary);">Unduh berkas</a>)';
            currentPedomanEl.style.display = 'block';
        } else {
            currentPedomanEl.style.display = 'none';
        }

        openModal('modalEditLomba');
    }

    // Isi Data Modal Edit Dosen
    function editDosen(item) {
        document.getElementById('formEditDosen').action = '/admin/dosen/' + item.nidn;
        document.getElementById('edit_dosen_nidn_view').value = item.nidn || '';
        document.getElementById('edit_dosen_nama').value = item.nama || '';
        document.getElementById('edit_dosen_email').value = item.email_kampus || '';
        document.getElementById('edit_dosen_keahlian').value = item.bidang_keahlian || '';
        document.getElementById('edit_dosen_kuota').value = item.kuota_bimbingan || 5;

        openModal('modalEditDosen');
    }

    // Isi Data Modal Edit Mahasiswa
    function editMahasiswa(item) {
        document.getElementById('formEditMahasiswa').action = '/admin/mahasiswa/' + item.nim;
        document.getElementById('edit_mhs_nim_view').value = item.nim || '';
        document.getElementById('edit_mhs_nama').value = item.nama || '';
        document.getElementById('edit_mhs_email').value = item.email_kampus || '';
        document.getElementById('edit_mhs_prodi').value = item.prodi || '';
        document.getElementById('edit_mhs_angkatan').value = item.angkatan || 2024;

        openModal('modalEditMahasiswa');
    }
</script>
@endsection
