@extends('layouts.app')

@section('content')

@push('styles')
<style>

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
    .guide-cards-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 22px;
        margin: 32px 0 20px;
        text-align: left;
    }
    @media (max-width: 900px) {
        .guide-cards-grid {
            grid-template-columns: 1fr;
        }
    }
    .guide-card {
        background: #F8FAFC;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 24px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: all 0.25s ease;
    }
    .guide-card:hover {
        background: #FFFFFF;
        border-color: #93C5FD;
        transform: translateY(-4px);
        box-shadow: var(--shadow-md);
    }
    .guide-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        margin-bottom: 16px;
    }
    .icon-box-blue {
        background: #EFF6FF;
        color: #2563EB;
        border: 1px solid #DBEAFE;
    }
    .icon-box-amber {
        background: #FEF3C7;
        color: #D97706;
        border: 1px solid #FDE68A;
    }
    .icon-box-green {
        background: #ECFDF5;
        color: #059669;
        border: 1px solid #A7F3D0;
    }
    .guide-card-title {
        font-size: 1.05rem;
        font-weight: 800;
        color: var(--primary-dark);
        margin-bottom: 8px;
        line-height: 1.3;
    }
    .guide-card-desc {
        font-size: 0.85rem;
        color: var(--text-muted);
        line-height: 1.6;
        margin-bottom: 22px;
        flex: 1;
    }
    .guide-card-btn {
        width: 100%;
        font-weight: 700;
        font-size: 0.85rem;
        padding: 9px 16px;
        border-radius: var(--radius-sm);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
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
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .timeline-content.status-disetujui {
        background: #F0FDF4;
        border: 1px solid #BBF7D0;
        border-left: 4px solid #10B981;
    }
    .timeline-content.status-menunggu {
        background: #FFFBEB;
        border: 1px solid #FDE68A;
        border-left: 4px solid #F59E0B;
    }
    .timeline-content.status-revisi,
    .timeline-content.status-ditolak {
        background: #FEF2F2;
        border: 1px solid #FECACA;
        border-left: 4px solid #EF4444;
    }

    /* Progres Babak Item Styles */
    .progres-item {
        padding: 12px 14px;
        border-radius: 8px;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .progres-item.status-disetujui {
        background: #F0FDF4;
        border: 1px solid #BBF7D0;
        border-left: 4px solid #10B981;
    }
    .progres-item.status-menunggu {
        background: #FFFBEB;
        border: 1px solid #FDE68A;
        border-left: 4px solid #F59E0B;
    }
    .progres-item.status-ditolak,
    .progres-item.status-revisi {
        background: #FEF2F2;
        border: 1px solid #FECACA;
        border-left: 4px solid #EF4444;
    }

    /* RAB Card Item Styles */
    .rab-card-item {
        padding: 14px 16px;
        border-radius: 10px;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .rab-card-item.status-disetujui {
        background: #F0FDF4;
        border: 1px solid #BBF7D0;
        border-left: 4px solid #10B981;
    }
    .rab-card-item.status-menunggu {
        background: #FFFBEB;
        border: 1px solid #FDE68A;
        border-left: 4px solid #F59E0B;
    }
    .rab-card-item.status-revisi,
    .rab-card-item.status-ditolak {
        background: #FEF2F2;
        border: 1px solid #FECACA;
        border-left: 4px solid #EF4444;
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

    /* AI Dosen Recommendation Box */
    .ai-rec-container {
        background: linear-gradient(135deg, #EEF2FF 0%, #FAF5FF 100%);
        border: 1px solid #C7D2FE;
        border-radius: var(--radius-md);
        padding: 16px;
        margin-bottom: 20px;
    }
    .ai-rec-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
        flex-wrap: wrap;
        gap: 8px;
    }
    .ai-rec-title {
        font-size: 0.88rem;
        font-weight: 700;
        color: #3730A3;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .ai-rec-card {
        background: #FFFFFF;
        border: 1px solid #E0E7FF;
        border-radius: var(--radius-sm);
        padding: 12px 14px;
        margin-bottom: 10px;
        transition: all 0.2s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .ai-rec-card:hover {
        border-color: #6366F1;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.12);
        transform: translateY(-1px);
    }
    .ai-rec-badge {
        background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
        color: #FFFFFF;
        font-size: 0.72rem;
        font-weight: 700;
        padding: 3px 9px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        box-shadow: 0 1px 2px rgba(79, 70, 229, 0.2);
    }
    .ai-rec-btn {
        background: #EEF2FF;
        border: 1px solid #C7D2FE;
        color: #4338CA;
        font-size: 0.76rem;
        font-weight: 600;
        padding: 5px 12px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .ai-rec-btn:hover {
        background: #4F46E5;
        color: #FFFFFF;
        border-color: #4F46E5;
    }

    /* Styling Khusus Rekap Bimbingan Role Admin (Seragam dengan Panel Admin) */
    .admin-bimbingan-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }
    .admin-bimbingan-stat-card {
        background: #FFFFFF;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 18px 20px;
        box-shadow: var(--shadow-sm);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .admin-bimbingan-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }
    .admin-bimbingan-stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        margin-bottom: 12px;
    }
    .admin-bimbingan-stat-val {
        font-size: 1.65rem;
        font-weight: 800;
        color: var(--primary-dark);
        line-height: 1.1;
    }
    .admin-bimbingan-stat-label {
        font-size: 0.82rem;
        color: var(--text-muted);
        font-weight: 600;
        margin-top: 4px;
    }
    .admin-bimbingan-stat-sub {
        font-size: 0.72rem;
        color: var(--text-muted);
        margin-top: 10px;
        padding-top: 8px;
        border-top: 1px dashed #E2E8F0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* Filter Toolbar Admin */
    .admin-filter-card {
        background: #FFFFFF;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 16px 20px;
        margin-bottom: 22px;
        box-shadow: var(--shadow-sm);
    }
    .admin-filter-grid {
        display: grid;
        grid-template-columns: 1.5fr 1fr 1fr 1fr auto;
        gap: 12px;
        align-items: end;
    }

    /* Accordion Card Tim Admin */
    .admin-accordion-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .admin-accordion-card {
        background: #FFFFFF;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .admin-accordion-card:hover {
        border-color: #CBD5E1;
        box-shadow: var(--shadow-md);
    }
    .admin-accordion-card.is-open {
        border-color: #93C5FD;
        box-shadow: 0 6px 18px -4px rgba(30, 64, 175, 0.1);
    }
    .admin-accordion-trigger {
        width: 100%;
        background: #FFFFFF;
        border: none;
        padding: 18px 22px;
        text-align: left;
        cursor: pointer;
        font-family: inherit;
        transition: background-color 0.15s ease;
        display: block;
    }
    .admin-accordion-trigger:hover {
        background: #F8FAFC;
    }
    .admin-accordion-card.is-open .admin-accordion-trigger {
        background: linear-gradient(90deg, #F8FAFC 0%, #EFF6FF 100%);
        border-bottom: 1px solid var(--border);
    }
    .admin-accordion-header-grid {
        display: grid;
        grid-template-columns: minmax(220px, 1.45fr) minmax(165px, 1fr) minmax(195px, 1.15fr) auto;
        gap: 20px;
        align-items: center;
    }
    .admin-team-avatar {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, #1E40AF 0%, #3B82F6 100%);
        color: #FFFFFF;
        font-weight: 800;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 10px rgba(30, 64, 175, 0.2);
    }
    .admin-accordion-chevron {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: #F1F5F9;
        color: #475569;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.25s ease, background-color 0.2s ease, color 0.2s ease;
        flex-shrink: 0;
    }
    .admin-accordion-card.is-open .admin-accordion-chevron {
        transform: rotate(180deg);
        background: #DBEAFE;
        color: #1E40AF;
    }
    .admin-accordion-body {
        display: none;
        padding: 22px 24px;
        background: #FFFFFF;
    }
    .admin-accordion-card.is-open .admin-accordion-body {
        display: block;
    }

    @media (max-width: 1100px) {
        .admin-bimbingan-stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .admin-filter-grid {
            grid-template-columns: 1fr 1fr;
        }
        .admin-accordion-header-grid {
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }
    }
    @media (max-width: 640px) {
        .admin-bimbingan-stats-grid {
            grid-template-columns: 1fr;
        }
        .admin-filter-grid {
            grid-template-columns: 1fr;
        }
        .admin-accordion-trigger {
            padding: 16px;
        }
        .admin-accordion-header-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }
        .admin-accordion-body {
            padding: 16px;
        }
    }
</style>
@endpush

    <!-- Hero Header Modul Bimbingan (Konsisten dengan Dashboard Analitik) -->
    <div class="dashboard-header">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 20px;">
            <div>
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 14px;">
                    <div class="dashboard-header-badge" style="margin-bottom: 0;">
                        <i class="bi bi-mortarboard-fill"></i> Sistem Informasi Manajemen Prestasi JTI
                    </div>
                    @if($role === 'admin')
                        <span style="display: inline-flex; align-items: center; gap: 6px; background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.35); color: #FFFFFF; padding: 5px 12px; border-radius: 999px; font-size: 0.76rem; font-weight: 700; letter-spacing: 0.3px;">
                            <i class="bi bi-eye-fill"></i> Mode Admin: Hanya Lihat
                        </span>
                    @endif
                </div>
                <h1 style="font-size: 1.9rem; font-weight: 800; letter-spacing: -0.5px; margin-bottom: 8px;">
                    {{ $role === 'admin' ? 'Rekapitulasi Bimbingan Seluruh JTI' : 'Monitoring Bimbingan Tim' }}
                </h1>
                <p style="font-size: 0.95rem; opacity: 0.9; max-width: 650px; line-height: 1.6; margin: 0;">
                    @if($role === 'admin')
                        Pantau seluruh rekam jejak sesi konsultasi dan logbook bimbingan dari semua tim kompetisi mahasiswa JTI secara terpusat (read-only).
                    @else
                        Pantau proses pengajuan dosen pembimbing, konsultasi, logbook, dan progres keikutsertaan tim dalam kompetisi.
                    @endif
                </p>
            </div>
            @php
                $currentUser = auth()->user();
                $sessionUser = session('auth_user');
                $userName = $currentUser->name ?? ($sessionUser['nama'] ?? ($authUser['nama'] ?? null));
                $userRoleVal = $currentUser ? ($currentUser->role instanceof \App\Enums\UserRole ? $currentUser->role->value : (string) $currentUser->role) : ($sessionUser['role'] ?? ($authUser['role'] ?? null));
                $userRoleLabel = $currentUser ? ($currentUser->role instanceof \App\Enums\UserRole ? $currentUser->role->label() : ucfirst($userRoleVal ?? '')) : ucfirst($sessionUser['role'] ?? ($authUser['role'] ?? ''));
                $userId = $currentUser->nim_nip ?? ($sessionUser['id'] ?? ($authUser['id'] ?? null));
                if ($userRoleVal === 'mahasiswa' && \App\Models\Tim::ketuaDisetujui($userId)) {
                    $userRoleLabel = 'Ketua Tim';
                }
            @endphp
            @if($userName)
                <div class="dashboard-header-user">
                    <div style="font-size: 0.8rem; opacity: 0.85;">Selamat datang kembali,</div>
                    <div style="font-size: 1.1rem; font-weight: 800;">{{ $userName }}</div>
                    <div style="margin-top: 6px; display: flex; gap: 8px; justify-content: flex-end;">
                        <span class="badge {{ $userRoleVal === 'admin' ? 'badge-danger' : ($userRoleVal === 'dosen' ? 'badge-warning' : 'badge-primary') }}" style="font-size: 0.72rem; padding: 3px 10px;">
                            {{ $userRoleLabel }} {{ $userId ? '('.$userId.')' : '' }}
                        </span>
                    </div>
                </div>
            @endif
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

            <!-- Tiga Kartu Panduan Modul Bimbingan Mahasiswa (Sesuai Referensi Desain) -->
            <div class="guide-cards-grid">
                <!-- Kartu 1 — 01. Cari & Telusuri Lomba -->
                <div class="guide-card">
                    <div>
                        <div class="guide-icon-box icon-box-blue">
                            <i class="bi bi-search"></i>
                        </div>
                        <h3 class="guide-card-title">01. Cari & Telusuri Lomba</h3>
                        <p class="guide-card-desc">
                            Jelajahi kompetisi yang sedang membuka pendaftaran dan temukan lomba sesuai minat serta bidang keahlianmu.
                        </p>
                    </div>
                    <a href="{{ route('lomba.index') }}" class="btn btn-primary guide-card-btn">
                        Lihat Katalog Lomba <i class="bi bi-arrow-right"></i>
                    </a>
                </div>

                <!-- Kartu 2 — 02. Gabung Lowongan Tim -->
                <div class="guide-card">
                    <div>
                        <div class="guide-icon-box icon-box-amber">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <h3 class="guide-card-title">02. Gabung Lowongan Tim</h3>
                        <p class="guide-card-desc">
                            Temukan tim mahasiswa JTI Polinema yang sedang membuka lowongan anggota, lalu ajukan diri untuk bergabung sesuai kebutuhan tim.
                        </p>
                    </div>
                    <a href="{{ route('tim.index') }}" class="btn btn-outline-primary guide-card-btn" style="background: #fff;">
                        Modul Tim (Cari Tim) <i class="bi bi-arrow-right"></i>
                    </a>
                </div>

                <!-- Kartu 3 — 03. Bentuk Tim Sendiri -->
                <div class="guide-card">
                    <div>
                        <div class="guide-icon-box icon-box-green">
                            <i class="bi bi-flag-fill"></i>
                        </div>
                        <h3 class="guide-card-title">03. Bentuk Tim Sendiri</h3>
                        <p class="guide-card-desc">
                            Inisiasi tim delegasi baru, tentukan lomba yang ingin diikuti, dan ajukan diri sebagai Ketua Tim.
                        </p>
                    </div>
                    <a href="{{ route('tim.create') }}" class="btn btn-primary guide-card-btn" style="background: #EFF6FF; color: var(--primary); border: 1px solid #BFDBFE;">
                        <i class="bi bi-plus-circle-fill"></i> Bentuk Tim Baru
                    </a>
                </div>
            </div>
        </div>

        <!-- Daftar Lomba Aktif Terbuka -->
        <div style="margin-bottom: 40px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--primary-dark); margin: 0;">
                        <i class="bi bi-award" style="color: var(--primary);"></i> Lomba Terbuka yang Siap Diikuti
                    </h3>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin: 3px 0 0 0;">
                        Pilih kompetisi di bawah ini untuk langsung membentuk tim dan mengaktifkan modul bimbingan Anda.
                    </p>
                </div>
                <a href="{{ route('lomba.index') }}" style="font-size: 0.88rem; font-weight: 700; color: var(--primary); display: inline-flex; align-items: center; gap: 6px; text-decoration: none;">
                    Jelajahi Semua Lomba <i class="bi bi-arrow-right"></i>
                </a>
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

                            <div style="display: flex; gap: 8px;">
                                <a href="{{ route('lomba.show', $lomba->id_lomba) }}"
                                   class="btn btn-secondary btn-sm"
                                   style="font-weight: 600;"
                                   title="Lihat Detail Lomba">
                                    <i class="bi bi-info-circle"></i> Lihat Detail Lomba
                                </a>
                                <a href="{{ route('tim.create', ['lomba_id' => $lomba->id_lomba]) }}"
                                   class="btn btn-outline-primary btn-sm"
                                   style="flex: 1; font-weight: 700;">
                                    <i class="bi bi-flag-fill"></i> Bentuk Tim
                                </a>
                            </div>
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
                <div class="card" style="border-left: 4px solid var(--primary);">
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

                        <div style="background: #EFF6FF; border: 1px solid #DBEAFE; border-left: 3px solid var(--primary); border-radius: var(--radius-sm); padding: 14px; margin-bottom: 16px;">
                            <div style="font-size: 0.75rem; font-weight: 700; color: var(--primary); margin-bottom: 4px;">KOMPETISI YANG DIIKUTI</div>
                            <div style="font-weight: 700; color: var(--text-main); font-size: 0.95rem; margin-bottom: 4px;">
                                {{ $selectedTim->lomba->nama_lomba ?? 'Kompetisi JTI' }}
                            </div>
                            <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 8px;">
                                <span class="badge badge-primary" style="font-size: 0.72rem;">{{ $selectedTim->lomba->kategori ?? 'Umum' }}</span>
                                <span class="badge badge-purple" style="font-size: 0.72rem;">{{ $selectedTim->lomba->tingkat ?? 'Nasional' }}</span>
                            </div>
                            @if($selectedTim->lomba)
                                <div>
                                    <a href="{{ route('lomba.show', $selectedTim->lomba->id_lomba) }}" class="btn btn-secondary btn-sm" style="font-size: 0.75rem; padding: 3px 10px;" title="Lihat Detail Lomba">
                                        <i class="bi bi-info-circle"></i> Lihat Detail Lomba
                                    </a>
                                </div>
                            @endif
                        </div>

                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <span style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted);">SUSUNAN ANGGOTA TIM:</span>
                                <span style="font-size: 0.75rem; font-weight: 700; color: var(--primary);">
                                    {{ $selectedTim->anggotaDiterima->count() }} / {{ $selectedTim->kuota_anggota }} Kuota Terisi
                                </span>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.85rem; padding: 6px 10px; background: #fff; border: 1px solid var(--border); border-radius: 6px;">
                                    <div>
                                        <strong>{{ $selectedTim->ketua->nama ?? '-' }}</strong>
                                        <div style="font-size: 0.72rem; color: var(--text-muted);">NIM: {{ $selectedTim->nim }} ({{ $selectedTim->ketua->prodi ?? '' }})</div>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        @if($selectedTim->file_cv)
                                            <a href="{{ route('tim.cv', ['type' => 'ketua', 'id' => $selectedTim->id_tim]) }}" target="_blank" class="btn btn-outline-primary btn-sm" style="font-size: 0.7rem; padding: 2px 7px;" title="Unduh / Lihat CV Ketua">
                                                <i class="bi bi-file-earmark-pdf"></i> CV
                                            </a>
                                        @endif
                                        <span class="badge badge-primary" style="font-size: 0.7rem;">Ketua Tim</span>
                                    </div>
                                </div>

                                @foreach($selectedTim->anggotaDiterima as $ang)
                                    @if($ang->nim !== $selectedTim->nim)
                                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.85rem; padding: 6px 10px; background: #fff; border: 1px solid var(--border); border-radius: 6px;">
                                            <div>
                                                <strong>{{ $ang->mahasiswa->nama ?? '-' }}</strong>
                                                <div style="font-size: 0.72rem; color: var(--text-muted);">NIM: {{ $ang->nim }} ({{ $ang->mahasiswa->prodi ?? '' }})</div>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 6px;">
                                                @if($ang->file_cv)
                                                    <a href="{{ route('tim.cv', ['type' => 'anggota', 'id' => $ang->id_anggota]) }}" target="_blank" class="btn btn-outline-primary btn-sm" style="font-size: 0.7rem; padding: 2px 7px;" title="Unduh / Lihat CV Anggota">
                                                        <i class="bi bi-file-earmark-pdf"></i> CV
                                                    </a>
                                                @endif
                                                <span class="badge badge-gray" style="font-size: 0.7rem;">{{ $ang->peran ?? 'Anggota' }}</span>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>

                            @if($selectedTim->nim === $authUser['id'])
                                <!-- Khusus Ketua Tim: Manajemen Permohonan Calon Anggota -->
                                @php
                                    $pendingAnggota = $selectedTim->anggota->where('status_gabung', 'menunggu');
                                @endphp
                                @if($pendingAnggota->isNotEmpty())
                                    <div style="margin-top: 18px; background: #FFFBEB; border: 1px solid #FDE68A; border-radius: var(--radius-sm); padding: 12px;">
                                        <div style="font-size: 0.8rem; font-weight: 700; color: #92400E; margin-bottom: 8px;">
                                            <i class="bi bi-clock-history"></i> Permohonan Anggota Masuk ({{ $pendingAnggota->count() }}):
                                        </div>
                                        <div style="display: flex; flex-direction: column; gap: 8px;">
                                            @foreach($pendingAnggota as $pend)
                                                <div style="background: #fff; border: 1px solid #FDE68A; border-radius: 6px; padding: 10px; font-size: 0.82rem;">
                                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
                                                        <div style="width: 100%;">
                                                            <strong>{{ $pend->mahasiswa->nama ?? 'Mahasiswa Pelamar' }}</strong>
                                                            <div style="font-size: 0.72rem; color: var(--text-muted);">
                                                                {{ $pend->mahasiswa->prodi ?? '-' }}{{ isset($pend->mahasiswa->angkatan) ? ' • Angkatan '.$pend->mahasiswa->angkatan : '' }}
                                                            </div>
                                                            <div style="margin-top: 4px; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                                                <span class="badge badge-purple" style="font-size: 0.7rem;">Posisi: {{ $pend->posisi_dilamar ?: $pend->peran }}</span>
                                                                <a href="{{ route('tim.permintaan.profil', $pend->id_anggota) }}" class="btn btn-secondary btn-sm" style="font-size: 0.7rem; padding: 1px 8px;" title="Lihat Profil Terbatas Pelamar">
                                                                    <i class="bi bi-person-lines-fill"></i> Lihat Profil
                                                                </a>
                                                                @if($pend->file_cv)
                                                                    <a href="{{ route('tim.cv', ['type' => 'anggota', 'id' => $pend->id_anggota]) }}" target="_blank" class="btn btn-outline-primary btn-sm" style="font-size: 0.7rem; padding: 1px 6px;" title="Unduh / Lihat CV Pelamar">
                                                                        <i class="bi bi-file-earmark-pdf"></i> Lihat CV
                                                                    </a>
                                                                @endif
                                                            </div>
                                                            @if(!empty($pend->pesan))
                                                                <div style="margin-top: 6px; padding: 6px 10px; background: #F8FAFC; border: 1px solid var(--border); border-left: 3px solid var(--primary); border-radius: 4px; font-size: 0.76rem; color: var(--text-main);">
                                                                    <span style="font-weight: 700; color: var(--text-muted);">Pesan:</span> {{ $pend->pesan }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div style="display: flex; gap: 6px; justify-content: flex-end; margin-top: 8px; border-top: 1px dashed var(--border); padding-top: 6px;">
                                                        <form action="{{ route('tim.anggota.respon', $pend->id_anggota) }}" method="POST" style="margin: 0;">
                                                            @csrf
                                                            <input type="hidden" name="status" value="diterima">
                                                            <button type="submit" class="btn btn-success btn-sm" style="font-size: 0.72rem; padding: 3px 8px;">
                                                                <i class="bi bi-check-lg"></i> Setujui
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('tim.anggota.respon', $pend->id_anggota) }}" method="POST" style="margin: 0;">
                                                            @csrf
                                                            <input type="hidden" name="status" value="ditolak">
                                                            <button type="submit" class="btn btn-danger btn-sm" style="font-size: 0.72rem; padding: 3px 8px;">
                                                                <i class="bi bi-x-lg"></i> Tolak
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <div style="margin-top: 14px; display: flex; gap: 8px;">
                                    <a href="{{ route('tim.lowongan') }}" class="btn btn-secondary btn-sm" style="flex: 1; font-size: 0.75rem; text-align: center;">
                                        <i class="bi bi-door-open"></i> Papan Lowongan Tim
                                    </a>
                                    <a href="{{ route('tim.create') }}" class="btn btn-outline-primary btn-sm" style="flex: 1; font-size: 0.75rem; text-align: center;">
                                        <i class="bi bi-plus-circle"></i> Bentuk Tim Lain
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Kartu Status Dosen Pembimbing -->
                <div class="card" style="border-left: 4px solid var(--primary);">
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
                            @if($isKetua ?? ($selectedTim->nim === $authUser['id']))
                                <div style="background: #FEF3C7; border: 1px solid #FDE68A; border-radius: var(--radius-sm); padding: 14px; margin-bottom: 18px; color: #92400E; font-size: 0.85rem;">
                                    <div style="font-weight: 700; margin-bottom: 2px;">
                                        <i class="bi bi-exclamation-circle-fill"></i> Belum Memiliki Dosen Pembimbing
                                    </div>
                                    Sebagai <strong>Ketua Tim</strong>, silakan pilih salah satu dosen di Jurusan Teknologi Informasi untuk diajukan sebagai pembimbing karya tim Anda.
                                </div>
                            @else
                                <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: var(--radius-sm); padding: 16px; margin-bottom: 18px; color: #1E40AF; font-size: 0.85rem;">
                                    <div style="font-weight: 700; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                                        <i class="bi bi-shield-lock-fill"></i> Hak Akses Khusus Ketua Tim
                                    </div>
                                    Tim Anda saat ini belum memiliki dosen pembimbing. Hak memilih dan mengajukan dosen pembimbing hanya dimiliki oleh <strong>Ketua Tim ({{ $selectedTim->ketua->nama ?? 'Ketua Tim' }})</strong>.
                                </div>
                            @endif

                            @if(isset($aiRekomendasi) && !empty($aiRekomendasi['top']))
                                <div class="ai-rec-container">
                                    <div class="ai-rec-header">
                                        <div class="ai-rec-title">
                                            <i class="bi bi-stars" style="color: #6366F1;"></i>
                                            Rekomendasi AI Dosen Pembimbing
                                        </div>
                                        <span class="ai-rec-badge">
                                            <i class="bi bi-cpu-fill"></i> Gemini AI
                                        </span>
                                    </div>
                                    <p style="font-size: 0.79rem; color: #4B5563; margin-bottom: 12px; line-height: 1.4;">
                                        Berdasarkan analisis kompetensi dengan bidang lomba <strong>{{ $selectedTim->lomba->nama_lomba ?? 'Kompetisi' }}</strong> (Kategori: <em>{{ $selectedTim->lomba->kategori ?? '-' }}</em>), berikut dosen yang paling cocok:
                                    </p>

                                    @foreach($aiRekomendasi['top'] as $recDsn)
                                        <div class="ai-rec-card">
                                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; margin-bottom: 4px;">
                                                <div>
                                                    <strong style="font-size: 0.86rem; color: #1E293B;">{{ $recDsn['nama'] }}</strong>
                                                    <div style="font-size: 0.75rem; color: #64748B;">
                                                        Keahlian: {{ $recDsn['bidang_keahlian'] }} &bull; Sisa Kuota: {{ $recDsn['sisa_kuota'] }}
                                                    </div>
                                                </div>
                                                <span style="background: #EEF2FF; color: #4338CA; font-weight: 700; font-size: 0.76rem; padding: 2px 8px; border-radius: 6px; border: 1px solid #C7D2FE; white-space: nowrap;">
                                                    ⭐ {{ $recDsn['skor'] }}% Cocok
                                                </span>
                                            </div>
                                            <div style="font-size: 0.78rem; color: #475569; font-style: italic; margin-bottom: 8px; line-height: 1.35;">
                                                "{{ $recDsn['alasan'] }}"
                                            </div>
                                            <div style="text-align: right;">
                                                @if($isKetua ?? ($selectedTim->nim === $authUser['id']))
                                                    <button type="button" class="ai-rec-btn" onclick="pilihDosenAi('{{ $recDsn['nip'] }}')">
                                                        <i class="bi bi-check2-circle"></i> Pilih Dosen Ini
                                                    </button>
                                                @else
                                                    <span style="font-size: 0.74rem; color: var(--text-muted); font-style: italic;">
                                                        <i class="bi bi-info-circle"></i> Rekomendasi referensi anggota
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if($isKetua ?? ($selectedTim->nim === $authUser['id']))
                                <form action="{{ route('bimbingan.ajukan') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="id_tim" value="{{ $selectedTim->id_tim }}">

                                    <div class="form-group" style="margin-bottom: 14px;">
                                        <label class="form-label">Pilih Dosen Pembimbing *</label>
                                        <select name="nip" id="dosenSelect" class="form-select" required>
                                            <option value="" disabled selected>-- Pilih Dosen JTI --</option>
                                            @foreach($dosenList as $dsn)
                                                @php
                                                    $recItem = $aiRekomendasi['map'][(string) $dsn->nip] ?? null;
                                                @endphp
                                                <option value="{{ $dsn->nip }}" {{ $dsn->sisa_kuota <= 0 ? 'disabled' : '' }}>
                                                    @if($recItem && $recItem['is_top']) [⭐ AI Match: {{ $recItem['skor'] }}%] @endif
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
                            @endif

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
                                    NIP: {{ $pengajuan->nip }} &bull; Diajukan pada: {{ $pengajuan->tgl_pengajuan ? $pengajuan->tgl_pengajuan->format('d M Y') : '-' }}
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

                            @if($isKetua ?? ($selectedTim->nim === $authUser['id']))
                                <form action="{{ route('bimbingan.ajukan') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="id_tim" value="{{ $selectedTim->id_tim }}">

                                    <div class="form-group" style="margin-bottom: 14px;">
                                        <label class="form-label">Pilih Dosen Pengganti *</label>
                                        <select name="nip" id="dosenSelectPengganti" class="form-select" required>
                                            <option value="" disabled selected>-- Pilih Dosen JTI --</option>
                                            @foreach($dosenList as $dsn)
                                                @php
                                                    $recItem = $aiRekomendasi['map'][(string) $dsn->nip] ?? null;
                                                @endphp
                                                <option value="{{ $dsn->nip }}" {{ $dsn->sisa_kuota <= 0 ? 'disabled' : '' }}>
                                                    @if($recItem && $recItem['is_top']) [⭐ AI Match: {{ $recItem['skor'] }}%] @endif
                                                    {{ $dsn->nama }} (Keahlian: {{ $dsn->bidang_keahlian }}) — Sisa Kuota: {{ $dsn->sisa_kuota }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                                        <i class="bi bi-send-fill"></i> Ajukan Dosen Lain
                                    </button>
                                </form>
                            @else
                                <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: var(--radius-sm); padding: 14px; margin-bottom: 16px; color: #1E40AF; font-size: 0.85rem;">
                                    <div style="font-weight: 700; margin-bottom: 2px;">
                                        <i class="bi bi-info-circle-fill"></i> Menunggu Pemilihan Dosen Pengganti
                                    </div>
                                    Hanya <strong>Ketua Tim ({{ $selectedTim->ketua->nama ?? 'Ketua Tim' }})</strong> yang memiliki wewenang untuk mengajukan dosen pembimbing pengganti.
                                </div>
                            @endif

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
                                        NIP: {{ $pengajuan->dosen->nip }} &bull; {{ $pengajuan->dosen->email_kampus }}
                                    </div>
                                </div>
                            </div>

                            <div style="background: #EFF6FF; border: 1px solid #DBEAFE; border-left: 3px solid var(--primary); border-radius: var(--radius-sm); padding: 12px; font-size: 0.83rem; margin-bottom: 12px;">
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
                <div class="card" style="border-left: 4px solid var(--primary);">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div class="card-title">
                                <i class="bi bi-journal-check" style="color: var(--primary);"></i> Logbook Aktivitas Bimbingan
                            </div>
                            <div style="font-size: 0.78rem; color: var(--text-muted);">Rekam jejak evaluasi dan konsultasi bersama dosen</div>
                        </div>

                        @if($pengajuan && $pengajuan->status === 'disetujui' && ($isKetua ?? ($selectedTim->nim === $authUser['id'])))
                            @if($kompetisiSelesai ?? false)
                                <span class="badge badge-gray" title="Kompetisi telah selesai">
                                    <i class="bi bi-lock-fill"></i> Kompetisi Selesai
                                </span>
                            @else
                                <button type="button" class="btn btn-primary btn-sm" onclick="openModalLogbook({{ $pengajuan->id_pengajuan }})">
                                    <i class="bi bi-plus-lg"></i> Isi Logbook Baru
                                </button>
                            @endif
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

                            <div style="display: flex; justify-content: space-between; align-items: center; background: #EFF6FF; border: 1px solid #DBEAFE; border-left: 3px solid var(--primary); border-radius: var(--radius-sm); padding: 10px 14px; margin-bottom: 18px; font-size: 0.82rem;">
                                <span>Total Sesi Konsultasi: <strong>{{ $logbooks->count() }} Kali</strong></span>
                                <span class="badge badge-success">{{ $logbooks->where('status_validasi', 'disetujui')->count() }} Divalidasi</span>
                            </div>

                            @if($logbooks->isEmpty())
                                <div style="text-align: center; padding: 30px; color: var(--text-muted); font-size: 0.88rem;">
                                    <i class="bi bi-journal-x" style="font-size: 1.8rem; opacity: 0.5; display: block; margin-bottom: 6px;"></i>
                                    @if($isKetua ?? ($selectedTim->nim === $authUser['id']))
                                        Belum ada catatan logbook bimbingan. Klik tombol <strong>"Isi Logbook Baru"</strong> di atas setelah Anda berkonsultasi dengan dosen pembimbing.
                                    @else
                                        Belum ada catatan logbook bimbingan dari tim Anda. Logbook akan dicatat oleh Ketua Tim atau Dosen Pembimbing.
                                    @endif
                                </div>
                            @else
                                <div class="timeline-wrap">
                                    @foreach($logbooks as $log)
                                        @php
                                            $logStatusClass = match($log->status_validasi) {
                                                'disetujui', 'valid' => 'status-disetujui',
                                                'revisi', 'ditolak' => 'status-revisi',
                                                default => 'status-menunggu',
                                            };
                                        @endphp
                                        <div class="timeline-item">
                                            <div class="timeline-point"></div>
                                            <div class="timeline-content {{ $logStatusClass }}">
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

                                                @if($log->link_dokumentasi)
                                                    <div style="margin-top: 8px;">
                                                        <a href="{{ $log->link_dokumentasi }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm" style="font-size: 0.75rem; padding: 3px 10px;">
                                                            <i class="bi bi-google"></i> Lihat Dokumentasi (Google Drive)
                                                        </a>
                                                    </div>
                                                @endif

                                                @if($log->catatan_dosen)
                                                    <div style="margin-top: 8px; font-size: 0.8rem; background: {{ $log->status_validasi === 'revisi' ? '#FEF2F2' : '#F0FDF4' }}; border-left: 3px solid {{ $log->status_validasi === 'revisi' ? '#EF4444' : '#22C55E' }}; padding: 8px 12px; border-radius: 4px; color: {{ $log->status_validasi === 'revisi' ? '#991B1B' : '#166534' }};">
                                                        <i class="bi {{ $log->status_validasi === 'revisi' ? 'bi-exclamation-circle-fill' : 'bi-chat-left-quote-fill' }}"></i>
                                                        <strong>Catatan Dosen Pembimbing:</strong> "{{ $log->catatan_dosen }}"
                                                    </div>
                                                @endif

                                                @if($log->status_validasi === 'revisi' && ($isKetua ?? ($selectedTim->nim === $authUser['id'])))
                                                    <div style="margin-top: 8px; display: flex; justify-content: flex-end;">
                                                        <button type="button" class="btn btn-warning btn-sm" style="font-size: 0.78rem; padding: 3px 10px;" onclick="openModalEditLogbook({{ $log->id_logbook }}, '{{ $log->tanggal ? $log->tanggal->format('Y-m-d') : '' }}', '{{ addslashes($log->materi) }}', '{{ addslashes($log->tindak_lanjut ?? '') }}', '{{ addslashes($log->link_dokumentasi ?? '') }}')">
                                                            <i class="bi bi-pencil-square"></i> Perbaiki Logbook
                                                        </button>
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
                @if($pengajuan && $pengajuan->status === 'disetujui')
                    <div class="card" style="border-left: 4px solid var(--primary);">
                        <div class="card-header">
                            <div>
                                <div class="card-title">
                                    <i class="bi bi-flag-fill" style="color: var(--primary);"></i> Monitoring Progres Babak
                                </div>
                                <div style="font-size: 0.78rem; color: var(--text-muted);">Tahapan kompetisi yang ditempuh tim bimbingan</div>
                            </div>
                            @if($isKetua ?? ($selectedTim->nim === $authUser['id'] && \App\Models\Tim::ketuaDisetujui($authUser['id'])))
                                <div>
                                    @if($kompetisiSelesai ?? false)
                                        <span class="badge badge-success" title="Kompetisi telah selesai">
                                            <i class="bi bi-lock-fill"></i> Kompetisi Selesai
                                        </span>
                                    @else
                                        <button type="button" class="btn btn-primary btn-sm" onclick="openModalTambahProgres()">
                                            <i class="bi bi-plus-lg"></i> Lapor Progres Babak
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <div class="card-body">
                            @if($pengajuan->progresBabak->isEmpty())
                                <div style="text-align: center; padding: 25px 20px; color: var(--text-muted); font-size: 0.85rem;">
                                    <i class="bi bi-flag" style="font-size: 1.8rem; opacity: 0.5; display: block; margin-bottom: 6px;"></i>
                                    @if($isKetua ?? ($selectedTim->nim === $authUser['id'] && \App\Models\Tim::ketuaDisetujui($authUser['id'])))
                                        Belum ada laporan progres babak kompetisi. Klik tombol <strong>"Lapor Progres Babak"</strong> di atas setiap kali tim Anda menyelesaikan tahapan lomba.
                                    @else
                                        Belum ada laporan progres babak kompetisi dari tim Anda. Progres babak hanya dapat dilaporkan oleh Ketua Tim resmi.
                                    @endif
                                </div>
                            @else
                                <div style="display: flex; flex-direction: column; gap: 10px;">
                                    @foreach($pengajuan->progresBabak as $prog)
                                        @php
                                            $progStatusClass = match($prog->status_setuju) {
                                                'disetujui' => 'status-disetujui',
                                                'ditolak' => 'status-ditolak',
                                                default => 'status-menunggu',
                                            };
                                        @endphp
                                        <div class="progres-item {{ $progStatusClass }}">
                                            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                                                <div>
                                                    <div style="font-weight: 700; font-size: 0.9rem; color: var(--primary-dark);">
                                                        Babak: {{ $prog->babak }}
                                                    </div>
                                                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
                                                        Tanggal Update: {{ $prog->tanggal_update ? $prog->tanggal_update->format('d M Y') : '-' }}
                                                    </div>
                                                </div>
                                                <div style="display: flex; align-items: center; gap: 6px;">
                                                    <span class="badge {{ str_contains(strtolower($prog->hasil_akhir ?? ''), 'juara') ? 'badge-warning' : 'badge-primary' }}">
                                                        {{ $prog->hasil_akhir ?? 'Sedang Berlangsung' }}
                                                    </span>
                                                    @if($prog->status_setuju === 'disetujui')
                                                        <span class="badge badge-success"><i class="bi bi-check-circle-fill"></i> Disetujui Dosen</span>
                                                    @elseif($prog->status_setuju === 'ditolak')
                                                        <span class="badge badge-danger"><i class="bi bi-x-circle-fill"></i> Ditolak</span>
                                                    @else
                                                        <span class="badge badge-warning"><i class="bi bi-clock-history"></i> Menunggu Verifikasi</span>
                                                    @endif
                                                </div>
                                            </div>

                                            @if(!empty($prog->keterangan_hasil) || !empty($prog->file_bukti))
                                                <div style="margin-top: 8px; display: flex; align-items: center; flex-wrap: wrap; gap: 8px; font-size: 0.78rem; color: var(--text-main);">
                                                    @if(!empty($prog->keterangan_hasil))
                                                        <span><i class="bi bi-award"></i> <strong>Keterangan:</strong> {{ $prog->keterangan_hasil }}</span>
                                                    @endif
                                                    @if(!empty($prog->file_bukti))
                                                        <a href="{{ route('bimbingan.progres.bukti', $prog->id_progres) }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm" style="font-size: 0.72rem; padding: 2px 8px;">
                                                            <i class="bi bi-file-earmark-text"></i> Lihat Bukti
                                                        </a>
                                                    @endif
                                                </div>
                                            @endif

                                            @if($prog->status_setuju === 'ditolak')
                                                <div style="margin-top: 8px; font-size: 0.78rem; background: #FEF2F2; border-left: 3px solid #EF4444; padding: 6px 10px; border-radius: 4px; color: #991B1B;">
                                                    <strong>Alasan Penolakan Dosen:</strong> "{{ $prog->alasan_tolak ?? 'Tidak ada alasan yang dicantumkan.' }}"
                                                </div>
                                                @if(! ($kompetisiSelesai ?? false) && ($isKetua ?? ($selectedTim->nim === $authUser['id'] && \App\Models\Tim::ketuaDisetujui($authUser['id']))))
                                                    @php
                                                        $babakStandarList = \App\Models\ProgresBabak::BABAK_STANDAR;
                                                        $progresEditPayload = [
                                                            'id' => $prog->id_progres,
                                                            'babak' => in_array($prog->babak, $babakStandarList, true) ? $prog->babak : \App\Models\ProgresBabak::BABAK_LAINNYA,
                                                            'babak_lainnya' => in_array($prog->babak, $babakStandarList, true) ? '' : $prog->babak,
                                                            'tanggal_update' => $prog->tanggal_update ? $prog->tanggal_update->format('Y-m-d') : '',
                                                            'hasil_akhir' => $prog->hasil_akhir,
                                                            'keterangan_hasil' => $prog->keterangan_hasil ?? '',
                                                            'punya_bukti' => !empty($prog->file_bukti),
                                                        ];
                                                    @endphp
                                                    <div style="margin-top: 8px; display: flex; justify-content: flex-end;">
                                                        <button type="button" class="btn btn-warning btn-sm" style="font-size: 0.78rem; padding: 3px 10px;" data-progres='@json($progresEditPayload)' onclick="openModalEditProgres(this)">
                                                            <i class="bi bi-pencil-square"></i> Perbaiki &amp; Kirim Ulang
                                                        </button>
                                                    </div>
                                                @endif
                                            @endif

                                            @if($prog->catatan_dosen && $prog->status_setuju !== 'ditolak')
                                                <div style="margin-top: 8px; font-size: 0.78rem; background: {{ $prog->status_setuju === 'ditolak' ? '#FEF2F2' : '#F0FDF4' }}; border-left: 3px solid {{ $prog->status_setuju === 'ditolak' ? '#EF4444' : '#22C55E' }}; padding: 6px 10px; border-radius: 4px; color: {{ $prog->status_setuju === 'ditolak' ? '#991B1B' : '#166534' }};">
                                                    <strong>Catatan Dosen Pembimbing:</strong> "{{ $prog->catatan_dosen }}"
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Kartu Pengajuan RAB (Rencana Anggaran Biaya) -->
                @if($pengajuan && $pengajuan->status === 'disetujui')
                    @php
                        $rabItemsData = $rabList ?? collect();
                        $babakValidRab = $babakRabTervalidasi ?? [];
                        $existingBabakMap = $rabItemsData->keyBy('babak');
                        // Babak yang masih bisa dibuat baru (sudah tervalidasi dosen dan belum ada RAB-nya)
                        $babakTersediaBuatBaru = array_values(array_filter($babakValidRab, fn($b) => !$existingBabakMap->has($b)));
                        $bisaAjukanRabBaru = ! ($kompetisiSelesai ?? false) && ! empty($babakValidRab) && ! empty($babakTersediaBuatBaru);
                        $semuaBabakValidSudahAdaRab = !empty($babakValidRab) && empty($babakTersediaBuatBaru);
                    @endphp
                    <div class="card" style="border-left: 4px solid var(--primary);">
                        <div class="card-header" style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px;">
                            <div style="flex: 1; min-width: 0;">
                                <div class="card-title">
                                    <i class="bi bi-cash-stack" style="color: var(--primary);"></i> Pengajuan RAB
                                </div>
                                <div style="font-size: 0.78rem; color: var(--text-muted);">
                                    Rencana Anggaran Biaya sesuai SBM Keuangan TA 2026 (PMK Nomor 32 Tahun 2025)
                                </div>
                            </div>
                            <div style="display: flex; flex-direction: column; align-items: flex-end; text-align: right; margin-left: auto; flex-shrink: 0;">
                                <button type="button"
                                        class="btn {{ $bisaAjukanRabBaru ? 'btn-primary' : 'btn-secondary' }} btn-sm"
                                        @if($bisaAjukanRabBaru)
                                            onclick="openModalRabCreate()"
                                        @else
                                            disabled
                                            style="opacity: 0.65; cursor: not-allowed;"
                                            title="{{ $semuaBabakValidSudahAdaRab ? 'RAB untuk babak yang tervalidasi sudah dibuat' : 'Tersedia setelah lolos semi final/final dan divalidasi dosen' }}"
                                        @endif>
                                    <i class="bi bi-plus-lg"></i> Ajukan RAB
                                </button>
                                @if(!$bisaAjukanRabBaru)
                                    <div style="font-size: 0.72rem; color: {{ $semuaBabakValidSudahAdaRab ? 'var(--text-muted)' : '#B45309' }}; margin-top: 4px; max-width: 240px; text-align: right;">
                                        @if($kompetisiSelesai ?? false)
                                            <i class="bi bi-lock-fill"></i> Kompetisi telah selesai
                                        @elseif($semuaBabakValidSudahAdaRab)
                                            RAB untuk babak tervalidasi sudah dibuat
                                        @else
                                            <i class="bi bi-info-circle"></i> Tersedia setelah lolos semi final/final dan divalidasi dosen
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            @if($rabItemsData->isEmpty())
                                <div style="text-align: center; padding: 25px 20px; color: var(--text-muted); font-size: 0.85rem;">
                                    <i class="bi bi-receipt-cutoff" style="font-size: 1.8rem; opacity: 0.5; display: block; margin-bottom: 6px;"></i>
                                    @if(!empty($babakValidRab))
                                        Belum ada pengajuan RAB untuk tim Anda. Ketua Tim maupun Anggota Tim dapat menyusun dan mengajukan RAB dengan menekan tombol <strong>"+ Ajukan RAB"</strong> di atas.
                                    @else
                                        Pengajuan RAB belum tersedia. Laporkan progres babak <strong>Semi Final</strong> atau <strong>Final</strong> terlebih dahulu dan tunggu validasi Dosen Pembimbing.
                                    @endif
                                </div>
                            @else
                                <div style="display: flex; flex-direction: column; gap: 14px;">
                                    @foreach($rabItemsData as $rab)
                                        @php
                                            $jmlHariRab = ($rab->tanggal_mulai && $rab->tanggal_selesai)
                                                ? ($rab->tanggal_mulai->diffInDays($rab->tanggal_selesai) + 1)
                                                : 1;
                                            $rabStatusClass = match($rab->status) {
                                                'disetujui' => 'status-disetujui',
                                                'perlu_revisi', 'ditolak' => 'status-revisi',
                                                default => 'status-menunggu',
                                            };
                                            $rabJsonPayload = [
                                                'id' => $rab->id,
                                                'babak' => $rab->babak,
                                                'tanggal_mulai' => $rab->tanggal_mulai ? $rab->tanggal_mulai->format('Y-m-d') : '',
                                                'tanggal_selesai' => $rab->tanggal_selesai ? $rab->tanggal_selesai->format('Y-m-d') : '',
                                                'kota_tujuan' => $rab->kota_tujuan,
                                                'provinsi_tujuan' => $rab->provinsi_tujuan,
                                                'jumlah_peserta' => $rab->jumlah_peserta,
                                                'link_dokumen' => $rab->link_dokumen ?? '',
                                                'status' => $rab->status,
                                                'items' => $rab->items->map(fn($it) => [
                                                    'kategori' => $it->kategori,
                                                    'uraian' => $it->uraian,
                                                    'satuan' => $it->satuan,
                                                    'jumlah_orang' => (int) $it->jumlah_orang,
                                                    'frekuensi' => (int) $it->frekuensi,
                                                    'tarif_satuan' => (float) $it->tarif_satuan,
                                                    'sifat' => $it->sifat,
                                                    'subtotal' => (float) $it->subtotal,
                                                    'alasan_melebihi' => $it->alasan_melebihi ?? '',
                                                    'bukti_link' => $it->bukti_link ?? '',
                                                ])->values()->all(),
                                            ];
                                        @endphp
                                        <div class="rab-card-item {{ $rabStatusClass }}">
                                            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 8px; margin-bottom: 10px;">
                                                <div>
                                                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                                        <span style="font-weight: 800; font-size: 0.95rem; color: var(--primary-dark);">
                                                            RAB Babak {{ $rab->labelBabak() }}
                                                        </span>
                                                        @if($rab->status === 'disetujui')
                                                            <span class="badge badge-success"><i class="bi bi-check-circle-fill"></i> Disetujui Dosen</span>
                                                        @elseif($rab->status === 'perlu_revisi')
                                                            <span class="badge badge-danger"><i class="bi bi-exclamation-triangle-fill"></i> Perlu Revisi</span>
                                                        @elseif($rab->status === 'menunggu_validasi')
                                                            <span class="badge badge-warning"><i class="bi bi-clock-history"></i> Menunggu Validasi Dosen</span>
                                                        @else
                                                            <span class="badge badge-gray"><i class="bi bi-file-earmark-text"></i> Draft</span>
                                                        @endif
                                                    </div>
                                                    <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 4px;">
                                                        <i class="bi bi-geo-alt-fill" style="color: var(--primary);"></i> {{ $rab->kota_tujuan }}, {{ $rab->provinsi_tujuan }}
                                                        &bull;
                                                        <i class="bi bi-calendar-range"></i>
                                                        {{ $rab->tanggal_mulai ? $rab->tanggal_mulai->format('d M Y') : '-' }} s/d {{ $rab->tanggal_selesai ? $rab->tanggal_selesai->format('d M Y') : '-' }} ({{ $jmlHariRab }} Hari)
                                                        &bull;
                                                        <i class="bi bi-people-fill"></i> {{ $rab->jumlah_peserta }} Peserta
                                                    </div>
                                                </div>
                                                <div style="text-align: right;">
                                                    <div style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Total Anggaran</div>
                                                    <div style="font-size: 1.05rem; font-weight: 800; color: var(--primary);">
                                                        Rp {{ number_format((float) $rab->total_anggaran, 0, ',', '.') }}
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Tabel Ringkas Komponen RAB -->
                                            <div style="background: #fff; border: 1px solid var(--border); border-radius: 8px; overflow-x: auto; margin-bottom: 10px;">
                                                <table style="width: 100%; border-collapse: collapse; font-size: 0.78rem;">
                                                    <thead>
                                                        <tr style="background: #F1F5F9; color: var(--text-muted); text-align: left;">
                                                            <th style="padding: 7px 10px; border-bottom: 1px solid var(--border);">Komponen & Uraian</th>
                                                            <th style="padding: 7px 10px; border-bottom: 1px solid var(--border); text-align: center;">Volume</th>
                                                            <th style="padding: 7px 10px; border-bottom: 1px solid var(--border); text-align: right;">Tarif Satuan</th>
                                                            <th style="padding: 7px 10px; border-bottom: 1px solid var(--border); text-align: right;">Subtotal</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($rab->items as $rItem)
                                                            @php
                                                                $metaKat = \App\Models\RabItem::defaultMetaForKategori($rItem->kategori);
                                                                $unitFrekLabel = match ($rItem->kategori) {
                                                                    'uang_harian_luar_kota', 'uang_harian_dalam_kota' => 'Hari',
                                                                    'penginapan' => 'Malam',
                                                                    default => 'Kali',
                                                                };
                                                            @endphp
                                                            <tr style="border-bottom: 1px solid #F1F5F9;">
                                                                <td style="padding: 7px 10px;">
                                                                    <div style="font-weight: 700; color: var(--primary-dark);">{{ $rItem->uraian }}</div>
                                                                    <div style="font-size: 0.72rem; color: var(--text-muted);">
                                                                        {{ $metaKat['label'] }}
                                                                        @if($rItem->alasan_melebihi)
                                                                            &bull; <span style="color: #B45309;">Alasan: "{{ $rItem->alasan_melebihi }}"</span>
                                                                        @endif
                                                                        @if($rItem->bukti_link)
                                                                            &bull; <a href="{{ $rItem->bukti_link }}" target="_blank" rel="noopener noreferrer" style="color: var(--primary); font-weight: 600;"><i class="bi bi-link-45deg"></i> Bukti</a>
                                                                        @endif
                                                                    </div>
                                                                </td>
                                                                <td style="padding: 7px 10px; text-align: center; white-space: nowrap;">
                                                                    {{ $rItem->jumlah_orang }} Org &times; {{ $rItem->frekuensi }} {{ $unitFrekLabel }}
                                                                </td>
                                                                <td style="padding: 7px 10px; text-align: right; white-space: nowrap;">
                                                                    Rp {{ number_format((float) $rItem->tarif_satuan, 0, ',', '.') }}
                                                                </td>
                                                                <td style="padding: 7px 10px; text-align: right; font-weight: 700; white-space: nowrap;">
                                                                    Rp {{ number_format((float) $rItem->subtotal, 0, ',', '.') }}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>

                                            @if($rab->catatan_dosen)
                                                <div style="margin-bottom: 10px; font-size: 0.8rem; background: {{ $rab->status === 'perlu_revisi' ? '#FEF2F2' : '#F0FDF4' }}; border-left: 3px solid {{ $rab->status === 'perlu_revisi' ? '#EF4444' : '#22C55E' }}; padding: 8px 12px; border-radius: 4px; color: {{ $rab->status === 'perlu_revisi' ? '#991B1B' : '#166534' }};">
                                                    <i class="bi {{ $rab->status === 'perlu_revisi' ? 'bi-exclamation-circle-fill' : 'bi-chat-left-quote-fill' }}"></i>
                                                    <strong>Catatan Dosen Pembimbing:</strong> "{{ $rab->catatan_dosen }}"
                                                </div>
                                            @endif

                                            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; font-size: 0.75rem; color: var(--text-muted);">
                                                <div>
                                                    Diisi terakhir oleh: <strong>{{ $rab->pengisiTerakhir->nama ?? $rab->diisi_terakhir_oleh ?? '-' }}</strong>
                                                    @if($rab->status === 'disetujui' && $rab->divalidasi_pada)
                                                        &bull; Divalidasi pada {{ $rab->divalidasi_pada->format('d M Y H:i') }}
                                                    @endif
                                                </div>
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    @if($rab->link_dokumen)
                                                        <a href="{{ $rab->link_dokumen }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm" style="font-size: 0.74rem; padding: 3px 10px;">
                                                            <i class="bi bi-google"></i> Dokumen Pendukung
                                                        </a>
                                                    @endif

                                                    {{-- Setelah status disetujui (atau menunggu_validasi), sembunyikan tombol edit dan kirim --}}
                                                    @if($rab->isEditable())
                                                        <button type="button"
                                                                class="btn {{ $rab->status === 'perlu_revisi' ? 'btn-warning' : 'btn-primary' }} btn-sm"
                                                                style="font-size: 0.75rem; padding: 4px 10px;"
                                                                data-rab='@json($rabJsonPayload)'
                                                                onclick="openModalRabEdit(this)">
                                                            <i class="bi bi-pencil-square"></i>
                                                            {{ $rab->status === 'perlu_revisi' ? 'Perbaiki & Kirim Ulang RAB' : 'Edit / Kirim RAB' }}
                                                        </button>
                                                    @elseif($rab->status === 'disetujui')
                                                        <span style="font-size: 0.74rem; color: #059669; font-weight: 700;">
                                                            <i class="bi bi-lock-fill"></i> RAB Terkunci (Disetujui)
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

            </div>

        </div>

    {{-- ========================================================================= --}}
    {{-- KONDISI 3: ROLE DOSEN (Portal Pembimbingan Dosen)                          --}}
    {{-- ========================================================================= --}}
    @elseif($role === 'dosen')

        <!-- Statistik & Ringkasan Bimbingan Dosen -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin-bottom: 20px;">
            <div class="card" style="padding: 16px;">
                <div style="font-size: 0.72rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Total Kuota Bimbingan</div>
                <div style="font-size: 1.6rem; font-weight: 800; color: var(--primary-dark); margin: 2px 0;">{{ $dosen->kuota_bimbingan ?? 5 }} Tim</div>
                <div style="font-size: 0.72rem; color: var(--text-muted);">Kapasitas semester ini</div>
            </div>

            <div class="card" style="padding: 16px;">
                <div style="font-size: 0.72rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Bimbingan Aktif</div>
                <div style="font-size: 1.6rem; font-weight: 800; color: #10B981; margin: 2px 0;">{{ $timAktifCount ?? 0 }} Tim</div>
                <div style="font-size: 0.72rem; color: var(--text-muted);">Tim resmi disetujui</div>
            </div>

            <div class="card" style="padding: 16px;">
                <div style="font-size: 0.72rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Pengajuan Masuk</div>
                <div style="font-size: 1.6rem; font-weight: 800; color: #F59E0B; margin: 2px 0;">{{ $pengajuanMenungguCount ?? 0 }} Tim</div>
                <div style="font-size: 0.72rem; color: var(--text-muted);">Menunggu konfirmasi</div>
            </div>

            <div class="card" style="padding: 16px;">
                <div style="font-size: 0.72rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Total Sesi Logbook</div>
                <div style="font-size: 1.6rem; font-weight: 800; color: #3B82F6; margin: 2px 0;">{{ $totalSesiLogbook ?? 0 }} Sesi</div>
                <div style="font-size: 0.72rem; color: var(--text-muted);">Konsultasi tercatat</div>
            </div>

            <div class="card" style="padding: 16px;">
                <div style="font-size: 0.72rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Logbook Menunggu</div>
                <div style="font-size: 1.6rem; font-weight: 800; color: {{ ($logbookMenungguValidasi ?? 0) > 0 ? '#EF4444' : '#64748B' }}; margin: 2px 0;">{{ $logbookMenungguValidasi ?? 0 }} Sesi</div>
                <div style="font-size: 0.72rem; color: var(--text-muted);">Perlu validasi dosen</div>
            </div>

            <div class="card" style="padding: 16px;">
                <div style="font-size: 0.72rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Riwayat Ditolak</div>
                <div style="font-size: 1.6rem; font-weight: 800; color: #64748B; margin: 2px 0;">{{ $riwayatLamaCount ?? 0 }} Tim</div>
                <div style="font-size: 0.72rem; color: var(--text-muted);">Pengajuan masa lalu</div>
            </div>
        </div>

        <!-- Antrian Laporan Progres Babak Menunggu Verifikasi -->
        @if($errors->has('alasan_penolakan') || $errors->has('status_setuju'))
            <div style="background: #FEF2F2; border: 1px solid #FECACA; border-radius: var(--radius-sm); padding: 10px 14px; margin-bottom: 16px; font-size: 0.82rem; color: #991B1B;">
                <ul style="margin: 0; padding-left: 18px;">
                    @foreach($errors->all() as $errMsg)
                        <li>{{ $errMsg }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header">
                <div>
                    <div class="card-title"><i class="bi bi-flag-fill" style="color: var(--primary);"></i> Laporan Progres Babak Menunggu Verifikasi</div>
                    <div style="font-size: 0.78rem; color: var(--text-muted);">Laporan capaian babak dari tim bimbingan Anda yang perlu disetujui atau ditolak</div>
                </div>
                <span class="badge {{ ($progresMenunggu ?? collect())->isNotEmpty() ? 'badge-warning' : 'badge-gray' }}">{{ ($progresMenunggu ?? collect())->count() }} Menunggu</span>
            </div>
            <div class="card-body" style="padding: 0;">
                @if(($progresMenunggu ?? collect())->isEmpty())
                    <div style="text-align: center; padding: 28px; color: var(--text-muted); font-size: 0.85rem;">
                        Tidak ada laporan progres babak yang menunggu verifikasi.
                    </div>
                @else
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.82rem; min-width: 820px;">
                            <thead>
                                <tr style="background: #F8FAFC; color: var(--text-muted); text-align: left; text-transform: uppercase; font-size: 0.7rem;">
                                    <th style="padding: 10px 14px; border-bottom: 1px solid var(--border);">Tim &amp; Lomba</th>
                                    <th style="padding: 10px 14px; border-bottom: 1px solid var(--border);">Babak</th>
                                    <th style="padding: 10px 14px; border-bottom: 1px solid var(--border);">Hasil &amp; Keterangan</th>
                                    <th style="padding: 10px 14px; border-bottom: 1px solid var(--border);">Tanggal</th>
                                    <th style="padding: 10px 14px; border-bottom: 1px solid var(--border); text-align: right;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($progresMenunggu as $antrian)
                                    <tr style="border-bottom: 1px solid var(--border);">
                                        <td style="padding: 10px 14px;">
                                            <div style="font-weight: 700; color: var(--primary-dark);">{{ $antrian->pengajuan->tim->nama_tim ?? '-' }}</div>
                                            <div style="font-size: 0.74rem; color: var(--text-muted);">{{ $antrian->pengajuan->tim->lomba->nama_lomba ?? '-' }}</div>
                                        </td>
                                        <td style="padding: 10px 14px;"><span class="badge badge-purple">{{ $antrian->babak }}</span></td>
                                        <td style="padding: 10px 14px;">
                                            <span class="badge badge-primary">{{ $antrian->hasil_akhir ?? '-' }}</span>
                                            @if(!empty($antrian->keterangan_hasil))
                                                <div style="font-size: 0.74rem; color: var(--text-muted); margin-top: 3px;">{{ $antrian->keterangan_hasil }}</div>
                                            @endif
                                            @if(!empty($antrian->file_bukti))
                                                <a href="{{ route('bimbingan.progres.bukti', $antrian->id_progres) }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm" style="font-size: 0.72rem; padding: 2px 8px; margin-top: 4px;">
                                                    <i class="bi bi-file-earmark-text"></i> Lihat Bukti
                                                </a>
                                            @endif
                                        </td>
                                        <td style="padding: 10px 14px; white-space: nowrap;">{{ $antrian->tanggal_update ? $antrian->tanggal_update->format('d M Y') : '-' }}</td>
                                        <td style="padding: 10px 14px; text-align: right; white-space: nowrap;">
                                            <div style="display: inline-flex; gap: 6px;">
                                                <form action="{{ route('bimbingan.progres.respon', $antrian->id_progres) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Setujui laporan progres babak ini?');">
                                                    @csrf
                                                    <input type="hidden" name="status_setuju" value="disetujui">
                                                    <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-check-lg"></i> Setujui</button>
                                                </form>
                                                <button type="button" class="btn btn-danger btn-sm" onclick="openModalTolakProgres({{ $antrian->id_progres }}, @js($antrian->babak.' - '.($antrian->pengajuan->tim->nama_tim ?? 'Tim')))">
                                                    <i class="bi bi-x-lg"></i> Tolak
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Daftar Pengajuan & Tim Bimbingan -->
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title"><i class="bi bi-people-fill" style="color: var(--primary);"></i> Daftar Tim Mahasiswa Bimbingan (Rekap & Riwayat)</div>
                    <div style="font-size: 0.78rem; color: var(--text-muted);">Kelola persetujuan bimbingan, pencarian riwayat pengajuan, dan monitoring kompetisi</div>
                </div>
            </div>

            <!-- Toolbar Filter & Pencarian Rekap -->
            <div style="background: #FFFFFF; border-bottom: 1px solid var(--border); padding: 14px 18px;">
                <form action="{{ route('bimbingan.index') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between;">
                    <div style="display: flex; flex-wrap: wrap; gap: 10px; flex: 1; min-width: 300px;">
                        <!-- Pencarian Nama Tim / Ketua -->
                        <div style="flex: 1; min-width: 200px;">
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari nama tim atau ketua..." style="font-size: 0.85rem; padding: 7px 12px;">
                        </div>

                        <!-- Filter Status -->
                        <div style="width: 170px;">
                            <select name="status" class="form-control" style="font-size: 0.85rem; padding: 7px 10px;">
                                <option value="">-- Semua Status --</option>
                                <option value="menunggu" {{ request('status') === 'menunggu' ? 'selected' : '' }}>Menunggu Konfirmasi</option>
                                <option value="disetujui" {{ request('status') === 'disetujui' ? 'selected' : '' }}>Disetujui (Aktif)</option>
                                <option value="ditolak" {{ request('status') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                            </select>
                        </div>

                        <!-- Filter Lomba -->
                        <div style="width: 200px;">
                            <select name="lomba_id" class="form-control" style="font-size: 0.85rem; padding: 7px 10px;">
                                <option value="">-- Semua Kompetisi --</option>
                                @if(isset($lombaOptions))
                                    @foreach($lombaOptions as $lopt)
                                        <option value="{{ $lopt->id_lomba }}" {{ request('lomba_id') == $lopt->id_lomba ? 'selected' : '' }}>
                                            {{ Str::limit($lopt->nama_lomba, 25) }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>

                    <div style="display: flex; gap: 8px;">
                        <button type="submit" class="btn btn-primary btn-sm" style="padding: 7px 14px; font-size: 0.85rem;">
                            <i class="bi bi-filter"></i> Filter
                        </button>

                        @if(request()->hasAny(['search', 'status', 'lomba_id']))
                            <a href="{{ route('bimbingan.index') }}" class="btn btn-secondary btn-sm" style="padding: 7px 12px; font-size: 0.85rem;">
                                <i class="bi bi-arrow-counterclockwise"></i> Reset
                            </a>
                        @endif
                    </div>
                </form>
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
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 14px;">
                                    <div>
                                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                            <h4 style="font-size: 1.15rem; font-weight: 800; color: var(--primary-dark); margin: 0;">
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

                                        <!-- Info Lomba: Nama, Kategori, Tingkat, Tenggat -->
                                        <div style="font-size: 0.84rem; color: var(--text-muted); margin-top: 6px; display: flex; align-items: center; flex-wrap: wrap; gap: 8px;">
                                            <span>Kompetisi: <strong style="color: var(--primary-dark);">{{ $item->tim->lomba->nama_lomba ?? '-' }}</strong></span>
                                            @if($item->tim->lomba)
                                                <span class="badge badge-secondary" style="font-size: 0.72rem; padding: 3px 7px;">
                                                    <i class="bi bi-tag-fill"></i> {{ $item->tim->lomba->kategori ?? 'Umum' }}
                                                </span>
                                                <span class="badge badge-info" style="font-size: 0.72rem; padding: 3px 7px;">
                                                    <i class="bi bi-trophy"></i> {{ $item->tim->lomba->tingkat ?? 'Nasional' }}
                                                </span>
                                                <span class="badge badge-light text-dark" style="font-size: 0.72rem; padding: 3px 7px; border: 1px solid var(--border);">
                                                    <i class="bi bi-calendar-event"></i> Tenggat: {{ $item->tim->lomba->tenggat ? \Carbon\Carbon::parse($item->tim->lomba->tenggat)->format('d M Y') : '-' }}
                                                </span>
                                            @endif
                                        </div>

                                        @if($item->catatan)
                                            <div style="margin-top: 8px; font-size: 0.8rem; background: #FFFBEB; border-left: 3px solid #F59E0B; padding: 6px 12px; border-radius: 4px; color: #92400E;">
                                                <i class="bi bi-chat-quote-fill"></i> <strong>Catatan Pengajuan Tim:</strong> "{{ $item->catatan }}"
                                            </div>
                                        @endif
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

                                            <button type="button" class="btn btn-danger btn-sm" onclick="openModalTolakPengajuan({{ $item->id_pengajuan }}, '{{ addslashes($item->tim->nama_tim ?? 'Tim Mahasiswa') }}')">
                                                <i class="bi bi-x-lg"></i> Tolak Bimbingan
                                            </button>
                                        </div>
                                    @endif
                                </div>

                                <!-- Detail Susunan Anggota Tim & Unduh CV -->
                                <div style="background: #FFFFFF; border: 1px solid var(--border); border-radius: 8px; padding: 12px 14px; margin-bottom: 12px;">
                                    <div style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                                        <i class="bi bi-people-fill text-primary"></i> Susunan Anggota Tim Bimbingan
                                    </div>

                                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 10px;">
                                        <!-- Ketua Tim -->
                                        <div style="background: #F8FAFC; border: 1px solid var(--border); border-radius: 6px; padding: 10px 12px; display: flex; justify-content: space-between; align-items: center;">
                                            <div>
                                                <div style="display: flex; align-items: center; gap: 6px;">
                                                    <span class="badge badge-primary" style="font-size: 0.68rem; padding: 2px 6px;">Ketua Tim</span>
                                                    <strong style="font-size: 0.85rem; color: var(--primary-dark);">{{ $item->tim->ketua->nama ?? '-' }}</strong>
                                                </div>
                                                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 3px;">
                                                    NIM: {{ $item->tim->nim }} &bull; Prodi: {{ $item->tim->ketua->prodi ?? '-' }}
                                                </div>
                                            </div>
                                            <div>
                                                @if($item->tim->file_cv)
                                                    <a href="{{ route('tim.cv', ['type' => 'ketua', 'id' => $item->tim->id_tim]) }}" target="_blank" class="btn btn-outline-primary btn-sm" style="padding: 3px 8px; font-size: 0.75rem;">
                                                        <i class="bi bi-file-earmark-pdf"></i> Unduh CV
                                                    </a>
                                                @else
                                                    <span style="font-size: 0.72rem; color: var(--text-muted); font-style: italic;">CV -</span>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Anggota Tim Diterima -->
                                        @if($item->tim && $item->tim->anggotaDiterima->isNotEmpty())
                                            @foreach($item->tim->anggotaDiterima as $ang)
                                                <div style="background: #F8FAFC; border: 1px solid var(--border); border-radius: 6px; padding: 10px 12px; display: flex; justify-content: space-between; align-items: center;">
                                                    <div>
                                                        <div style="display: flex; align-items: center; gap: 6px;">
                                                            <span class="badge badge-secondary" style="font-size: 0.68rem; padding: 2px 6px;">{{ $ang->peran ?? 'Anggota' }}</span>
                                                            <strong style="font-size: 0.85rem; color: var(--text-main);">{{ $ang->mahasiswa->nama ?? '-' }}</strong>
                                                        </div>
                                                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 3px;">
                                                            NIM: {{ $ang->nim }} &bull; Prodi: {{ $ang->mahasiswa->prodi ?? '-' }}
                                                        </div>
                                                    </div>
                                                    <div>
                                                        @if($ang->file_cv)
                                                            <a href="{{ route('tim.cv', ['type' => 'anggota', 'id' => $ang->id_anggota]) }}" target="_blank" class="btn btn-outline-primary btn-sm" style="padding: 3px 8px; font-size: 0.75rem;">
                                                                <i class="bi bi-file-earmark-pdf"></i> Unduh CV
                                                            </a>
                                                        @else
                                                            <span style="font-size: 0.72rem; color: var(--text-muted); font-style: italic;">CV -</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>

                                <!-- Progres Babak Terakhir -->
                                @php
                                    $latestProgres = $item->progresBabak->first();
                                @endphp
                                <div style="background: #FFFFFF; border: 1px solid var(--border); border-radius: 8px; padding: 10px 14px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                        <div style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">
                                            <i class="bi bi-flag-fill text-warning"></i> Progres Babak Terakhir:
                                        </div>
                                        @if($latestProgres)
                                            <span style="font-weight: 700; font-size: 0.86rem; color: var(--primary-dark);">
                                                {{ $latestProgres->babak }}
                                            </span>
                                            <span style="font-size: 0.75rem; color: var(--text-muted);">
                                                ({{ $latestProgres->tanggal_update ? $latestProgres->tanggal_update->format('d M Y') : '-' }})
                                            </span>
                                        @else
                                            <span style="font-size: 0.8rem; color: var(--text-muted); font-style: italic;">
                                                Belum ada laporan progres babak kompetisi
                                            </span>
                                        @endif
                                    </div>

                                    @if($latestProgres)
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <span class="badge {{ str_contains(strtolower($latestProgres->hasil_akhir ?? ''), 'juara') ? 'badge-warning' : 'badge-primary' }}">
                                                {{ $latestProgres->hasil_akhir ?? 'Sedang Berlangsung' }}
                                            </span>
                                            @if($latestProgres->status_setuju === 'disetujui')
                                                <span class="badge badge-success"><i class="bi bi-check-circle"></i> Disetujui Dosen</span>
                                            @elseif($latestProgres->status_setuju === 'ditolak')
                                                <span class="badge badge-danger"><i class="bi bi-x-circle"></i> Ditolak</span>
                                            @else
                                                <span class="badge badge-warning"><i class="bi bi-clock"></i> Menunggu Validasi</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                <!-- Bagian Progres Babak Kompetisi -->
                                @if($item->status === 'disetujui')
                                    <div style="border-top: 1px solid var(--border); padding-top: 12px; margin-top: 10px;">
                                        <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;">
                                            <i class="bi bi-flag-fill text-primary"></i> TAHAPAN PROGRES BABAK KOMPETISI ({{ $item->progresBabak->count() }} Tahapan Dilaporkan):
                                        </div>

                                        @if($item->progresBabak->isEmpty())
                                            <div style="font-size: 0.8rem; color: var(--text-muted); font-style: italic;">
                                                Mahasiswa belum melaporkan capaian tahapan kompetisi.
                                            </div>
                                        @else
                                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                                @foreach($item->progresBabak as $pb)
                                                    <div style="background: #fff; border: 1px solid var(--border); border-radius: 6px; padding: 12px 14px; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 10px;">
                                                        <div style="max-width: 620px; flex: 1;">
                                                            <div style="font-size: 0.75rem; color: var(--text-muted); display: flex; align-items: center; gap: 8px;">
                                                                <span><i class="bi bi-calendar-event"></i> {{ $pb->tanggal_update ? $pb->tanggal_update->format('d/m/Y') : '-' }}</span>
                                                                <span>&bull;</span>
                                                                @if($pb->status_setuju === 'disetujui')
                                                                    <span class="badge badge-success" style="font-size: 0.7rem;"><i class="bi bi-check-all"></i> Disetujui Dosen</span>
                                                                @elseif($pb->status_setuju === 'ditolak')
                                                                    <span class="badge badge-danger" style="font-size: 0.7rem;"><i class="bi bi-x-circle-fill"></i> Ditolak</span>
                                                                @else
                                                                    <span class="badge badge-warning" style="font-size: 0.7rem;"><i class="bi bi-clock-history"></i> Menunggu Verifikasi</span>
                                                                @endif
                                                            </div>
                                                            <div style="font-size: 0.88rem; font-weight: 700; color: var(--primary-dark); margin-top: 4px;">
                                                                Babak: {{ $pb->babak }} &bull; <span class="badge {{ str_contains(strtolower($pb->hasil_akhir ?? ''), 'juara') ? 'badge-warning' : 'badge-primary' }}" style="font-size: 0.75rem;">{{ $pb->hasil_akhir ?? '-' }}</span>
                                                            </div>

                                                            @if($pb->catatan_dosen)
                                                                <div style="margin-top: 8px; font-size: 0.78rem; background: #FEF2F2; border-left: 3px solid #EF4444; padding: 6px 10px; border-radius: 4px; color: #991B1B;">
                                                                    <strong>Catatan/Feedback Anda:</strong> "{{ $pb->catatan_dosen }}"
                                                                </div>
                                                            @endif
                                                        </div>

                                                        <div style="display: flex; gap: 6px; align-items: center;">
                                                            @if($pb->status_setuju === 'ditolak')
                                                                <span class="badge badge-danger"><i class="bi bi-x-circle-fill"></i> Ditolak</span>
                                                            @elseif($pb->status_setuju !== 'disetujui')
                                                                <form action="{{ route('bimbingan.progres.respon', $pb->id_progres) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Setujui laporan progres babak ini?');">
                                                                    @csrf
                                                                    <input type="hidden" name="status_setuju" value="disetujui">
                                                                    <button type="submit" class="btn btn-success btn-sm" style="padding: 4px 10px; font-size: 0.78rem;">
                                                                        <i class="bi bi-check2-circle"></i> Setujui
                                                                    </button>
                                                                </form>

                                                                <button type="button" class="btn btn-outline-danger btn-sm" style="padding: 4px 10px; font-size: 0.78rem;" onclick="openModalTolakProgres({{ $pb->id_progres }}, '{{ addslashes($pb->babak) }}')">
                                                                    <i class="bi bi-x-circle"></i> Tolak Babak
                                                                </button>
                                                            @else
                                                                <span class="badge badge-success"><i class="bi bi-check-all"></i> Terverifikasi</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                <!-- Bagian Pengajuan RAB (Rencana Anggaran Biaya) Tim Bimbingan -->
                                @if($item->status === 'disetujui')
                                    @php
                                        $rabTimList = $item->tim->rabPengajuan ?? collect();
                                    @endphp
                                    <div style="border-top: 1px solid var(--border); padding-top: 12px; margin-top: 10px;">
                                        <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;">
                                            <i class="bi bi-cash-stack text-primary"></i> PENGAJUAN RAB / RENCANA ANGGARAN BIAYA ({{ $rabTimList->count() }} Diajukan):
                                        </div>

                                        @if($rabTimList->isEmpty())
                                            <div style="font-size: 0.8rem; color: var(--text-muted); font-style: italic;">
                                                Tim belum mengajukan Rencana Anggaran Biaya (RAB) untuk babak Semi Final maupun Final.
                                            </div>
                                        @else
                                            <div style="display: flex; flex-direction: column; gap: 10px;">
                                                @foreach($rabTimList as $rabDsn)
                                                    @php
                                                        $durasiHariDsn = ($rabDsn->tanggal_mulai && $rabDsn->tanggal_selesai)
                                                            ? ($rabDsn->tanggal_mulai->diffInDays($rabDsn->tanggal_selesai) + 1)
                                                            : 1;
                                                    @endphp
                                                    <div style="background: #fff; border: 1px solid var(--border); border-radius: 6px; padding: 12px 14px;">
                                                        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 10px; margin-bottom: 10px;">
                                                            <div>
                                                                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                                                    <span style="font-size: 0.9rem; font-weight: 800; color: var(--primary-dark);">
                                                                        RAB Babak {{ $rabDsn->labelBabak() }}
                                                                    </span>
                                                                    @if($rabDsn->status === 'disetujui')
                                                                        <span class="badge badge-success" style="font-size: 0.7rem;"><i class="bi bi-check-all"></i> Disetujui</span>
                                                                    @elseif($rabDsn->status === 'perlu_revisi')
                                                                        <span class="badge badge-danger" style="font-size: 0.7rem;"><i class="bi bi-exclamation-triangle-fill"></i> Perlu Revisi</span>
                                                                    @else
                                                                        <span class="badge badge-warning" style="font-size: 0.7rem;"><i class="bi bi-clock-history"></i> Menunggu Validasi Anda</span>
                                                                    @endif
                                                                    <span class="badge badge-primary" style="font-size: 0.75rem;">
                                                                        Total: Rp {{ number_format((float) $rabDsn->total_anggaran, 0, ',', '.') }}
                                                                    </span>
                                                                </div>
                                                                <div style="font-size: 0.76rem; color: var(--text-muted); margin-top: 4px;">
                                                                    <i class="bi bi-geo-alt-fill" style="color: var(--primary);"></i> {{ $rabDsn->kota_tujuan }}, {{ $rabDsn->provinsi_tujuan }}
                                                                    &bull;
                                                                    <i class="bi bi-calendar-range"></i>
                                                                    {{ $rabDsn->tanggal_mulai ? $rabDsn->tanggal_mulai->format('d/m/Y') : '-' }} s/d {{ $rabDsn->tanggal_selesai ? $rabDsn->tanggal_selesai->format('d/m/Y') : '-' }} ({{ $durasiHariDsn }} Hari)
                                                                    &bull;
                                                                    <i class="bi bi-people-fill"></i> {{ $rabDsn->jumlah_peserta }} Peserta
                                                                    &bull;
                                                                    Diisi oleh: <strong>{{ $rabDsn->pengisiTerakhir->nama ?? $rabDsn->diisi_terakhir_oleh ?? '-' }}</strong>
                                                                </div>
                                                            </div>

                                                            <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                                                                @if($rabDsn->link_dokumen)
                                                                    <a href="{{ $rabDsn->link_dokumen }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm" style="padding: 4px 10px; font-size: 0.75rem;">
                                                                        <i class="bi bi-google"></i> Dokumen Pendukung
                                                                    </a>
                                                                @endif

                                                                @if($rabDsn->status !== 'disetujui')
                                                                    <form action="{{ route('bimbingan.rab.respon', $rabDsn->id) }}" method="POST" style="margin: 0;">
                                                                        @csrf
                                                                        <input type="hidden" name="status" value="disetujui">
                                                                        <button type="submit" class="btn btn-success btn-sm" style="padding: 4px 10px; font-size: 0.78rem;">
                                                                            <i class="bi bi-check2-circle"></i> Validasi / Setujui
                                                                        </button>
                                                                    </form>

                                                                    <button type="button"
                                                                            class="btn btn-outline-danger btn-sm"
                                                                            style="padding: 4px 10px; font-size: 0.78rem;"
                                                                            onclick="openModalRevisiRab({{ $rabDsn->id }}, '{{ addslashes(($item->tim->nama_tim ?? 'Tim') . ' - Babak ' . $rabDsn->labelBabak()) }}')">
                                                                        <i class="bi bi-pencil-square"></i> Minta Revisi
                                                                    </button>
                                                                @else
                                                                    <span class="badge badge-success"><i class="bi bi-check-all"></i> Terverifikasi</span>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <!-- Tabel Detail Rincian Item RAB untuk Dosen -->
                                                        <div style="background: #F8FAFC; border: 1px solid var(--border); border-radius: 6px; overflow-x: auto;">
                                                            <table style="width: 100%; border-collapse: collapse; font-size: 0.76rem;">
                                                                <thead>
                                                                    <tr style="background: #F1F5F9; color: var(--text-muted); text-align: left;">
                                                                        <th style="padding: 6px 10px; border-bottom: 1px solid var(--border);">Kategori & Uraian Komponen</th>
                                                                        <th style="padding: 6px 10px; border-bottom: 1px solid var(--border); text-align: center;">Volume</th>
                                                                        <th style="padding: 6px 10px; border-bottom: 1px solid var(--border); text-align: right;">Tarif Satuan</th>
                                                                        <th style="padding: 6px 10px; border-bottom: 1px solid var(--border); text-align: right;">Subtotal</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($rabDsn->items as $dsnIt)
                                                                        @php
                                                                            $metaKatDsn = \App\Models\RabItem::defaultMetaForKategori($dsnIt->kategori);
                                                                            $unitFrekDsn = match ($dsnIt->kategori) {
                                                                                'uang_harian_luar_kota', 'uang_harian_dalam_kota' => 'Hari',
                                                                                'penginapan' => 'Malam',
                                                                                default => 'Kali',
                                                                            };
                                                                        @endphp
                                                                        <tr style="border-bottom: 1px solid var(--border);">
                                                                            <td style="padding: 6px 10px;">
                                                                                <div style="font-weight: 700; color: var(--primary-dark);">{{ $dsnIt->uraian }}</div>
                                                                                <div style="font-size: 0.7rem; color: var(--text-muted);">
                                                                                    {{ $metaKatDsn['label'] }} ({{ $dsnIt->sifat }})
                                                                                    @if($dsnIt->alasan_melebihi)
                                                                                        &bull; <span style="color: #B45309; font-weight: 600;">Alasan Melebihi SBM: "{{ $dsnIt->alasan_melebihi }}"</span>
                                                                                    @endif
                                                                                    @if($dsnIt->bukti_link)
                                                                                        &bull; <a href="{{ $dsnIt->bukti_link }}" target="_blank" rel="noopener noreferrer" style="color: var(--primary); font-weight: 600;"><i class="bi bi-link-45deg"></i> Lihat Bukti Riil</a>
                                                                                    @endif
                                                                                </div>
                                                                            </td>
                                                                            <td style="padding: 6px 10px; text-align: center; white-space: nowrap;">
                                                                                {{ $dsnIt->jumlah_orang }} Org &times; {{ $dsnIt->frekuensi }} {{ $unitFrekDsn }}
                                                                            </td>
                                                                            <td style="padding: 6px 10px; text-align: right; white-space: nowrap;">
                                                                                Rp {{ number_format((float) $dsnIt->tarif_satuan, 0, ',', '.') }}
                                                                            </td>
                                                                            <td style="padding: 6px 10px; text-align: right; font-weight: 700; white-space: nowrap;">
                                                                                Rp {{ number_format((float) $dsnIt->subtotal, 0, ',', '.') }}
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>

                                                        @if($rabDsn->catatan_dosen)
                                                            <div style="margin-top: 8px; font-size: 0.78rem; background: {{ $rabDsn->status === 'perlu_revisi' ? '#FEF2F2' : '#F0FDF4' }}; border-left: 3px solid {{ $rabDsn->status === 'perlu_revisi' ? '#EF4444' : '#22C55E' }}; padding: 6px 10px; border-radius: 4px; color: {{ $rabDsn->status === 'perlu_revisi' ? '#991B1B' : '#166534' }};">
                                                                <strong>Catatan/Feedback Anda:</strong> "{{ $rabDsn->catatan_dosen }}"
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                <!-- Bagian Logbook Mahasiswa Bimbingan -->
                                @if($item->status === 'disetujui')
                                    <div style="border-top: 1px solid var(--border); padding-top: 12px; margin-top: 10px;">
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; flex-wrap: wrap; gap: 8px;">
                                            <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted);">
                                                LOGBOOK BIMBINGAN ({{ $item->logbook->count() }} Sesi Tercatat):
                                            </div>
                                            <button type="button" class="btn btn-outline-primary btn-sm" style="font-size: 0.75rem; padding: 3px 10px;" onclick="openModalLogbook({{ $item->id_pengajuan }}, '{{ addslashes($item->tim->nama_tim) }}')">
                                                <i class="bi bi-journal-plus"></i> Isi Logbook Bimbingan
                                            </button>
                                        </div>

                                        @if($item->logbook->isEmpty())
                                            <div style="font-size: 0.8rem; color: var(--text-muted); font-style: italic;">
                                                Belum ada catatan logbook bimbingan untuk tim ini. Anda atau Ketua Tim dapat mengisi logbook konsultasi.
                                            </div>
                                        @else
                                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                                @foreach($item->logbook as $lb)
                                                    <div style="background: #fff; border: 1px solid var(--border); border-radius: 6px; padding: 12px 14px; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 10px;">
                                                        <div style="max-width: 620px; flex: 1;">
                                                            <div style="font-size: 0.75rem; color: var(--text-muted); display: flex; align-items: center; gap: 8px;">
                                                                <span><i class="bi bi-calendar3"></i> {{ $lb->tanggal ? $lb->tanggal->format('d/m/Y') : '-' }}</span>
                                                                <span>&bull;</span>
                                                                @if($lb->status_validasi === 'disetujui')
                                                                    <span class="badge badge-success" style="font-size: 0.7rem;"><i class="bi bi-check-all"></i> Disetujui</span>
                                                                @elseif($lb->status_validasi === 'revisi')
                                                                    <span class="badge badge-danger" style="font-size: 0.7rem;"><i class="bi bi-exclamation-triangle-fill"></i> Perlu Revisi</span>
                                                                @else
                                                                    <span class="badge badge-warning" style="font-size: 0.7rem;"><i class="bi bi-clock-history"></i> Menunggu Validasi</span>
                                                                @endif
                                                            </div>
                                                            <div style="font-size: 0.88rem; font-weight: 700; color: var(--primary-dark); margin-top: 4px;">
                                                                {{ $lb->materi }}
                                                            </div>
                                                            @if($lb->tindak_lanjut && $lb->tindak_lanjut !== '-')
                                                                <div style="font-size: 0.78rem; color: #475569; margin-top: 3px;">
                                                                    <strong>Tindak Lanjut:</strong> {{ $lb->tindak_lanjut }}
                                                                </div>
                                                            @endif

                                                            @if($lb->link_dokumentasi)
                                                                <div style="margin-top: 6px;">
                                                                    <a href="{{ $lb->link_dokumentasi }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm" style="font-size: 0.75rem; padding: 3px 10px;">
                                                                        <i class="bi bi-google"></i> Lihat Dokumentasi (Google Drive)
                                                                    </a>
                                                                </div>
                                                            @endif

                                                            @if($lb->catatan_dosen)
                                                                <div style="margin-top: 8px; font-size: 0.78rem; background: #FEF2F2; border-left: 3px solid #EF4444; padding: 6px 10px; border-radius: 4px; color: #991B1B;">
                                                                    <strong>Catatan/Feedback Anda:</strong> "{{ $lb->catatan_dosen }}"
                                                                </div>
                                                            @endif
                                                        </div>

                                                        <div style="display: flex; gap: 6px; align-items: center;">
                                                            @if($lb->status_validasi !== 'disetujui')
                                                                <form action="{{ route('bimbingan.logbook.validasi', $lb->id_logbook) }}" method="POST" style="margin: 0;">
                                                                    @csrf
                                                                    <input type="hidden" name="status_validasi" value="disetujui">
                                                                    <button type="submit" class="btn btn-success btn-sm" style="padding: 4px 10px; font-size: 0.78rem;">
                                                                        <i class="bi bi-check2-circle"></i> Setujui
                                                                    </button>
                                                                </form>

                                                                <button type="button" class="btn btn-outline-danger btn-sm" style="padding: 4px 10px; font-size: 0.78rem;" onclick="openModalRevisiLogbook({{ $lb->id_logbook }}, '{{ addslashes($lb->materi) }}')">
                                                                    <i class="bi bi-pencil-square"></i> Perlu Revisi
                                                                </button>
                                                            @else
                                                                <span class="badge badge-success"><i class="bi bi-check-all"></i> Terverifikasi</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if($pengajuanList->hasPages())
                        <div style="margin-top: 20px; display: flex; justify-content: center;">
                            {{ $pengajuanList->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>

    {{-- ========================================================================= --}}
    {{-- KONDISI 4: ROLE ADMIN (Rekapitulasi Seluruh Bimbingan JTI - Read Only)    --}}
    {{-- ========================================================================= --}}
    @elseif($role === 'admin')

        <!-- 4 Kartu Ringkasan Rekapitulasi Bimbingan JTI -->
        <div class="admin-bimbingan-stats-grid">
            <!-- 1. Total Tim Bimbingan -->
            <div class="admin-bimbingan-stat-card">
                <div>
                    <div class="admin-bimbingan-stat-icon" style="background: #EFF6FF; color: #2563EB;">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="admin-bimbingan-stat-val">{{ $totalTimBimbingan ?? 0 }}</div>
                    <div class="admin-bimbingan-stat-label">Total Tim Bimbingan</div>
                </div>
                <div class="admin-bimbingan-stat-sub">
                    <span>Status Bimbingan:</span>
                    <span class="badge badge-success" style="font-size: 0.7rem;">Disetujui</span>
                </div>
            </div>

            <!-- 2. Total Sesi Logbook -->
            <div class="admin-bimbingan-stat-card">
                <div>
                    <div class="admin-bimbingan-stat-icon" style="background: #EDE9FE; color: #6D28D9;">
                        <i class="bi bi-journal-text"></i>
                    </div>
                    <div class="admin-bimbingan-stat-val">{{ $totalSesiLogbook ?? 0 }}</div>
                    <div class="admin-bimbingan-stat-label">Total Sesi Logbook</div>
                </div>
                <div class="admin-bimbingan-stat-sub">
                    <span>Rekam Konsultasi:</span>
                    <span class="badge badge-purple" style="font-size: 0.7rem;">{{ $totalSesiLogbook ?? 0 }} Sesi</span>
                </div>
            </div>

            <!-- 3. Tim Belum Ada Logbook -->
            <div class="admin-bimbingan-stat-card">
                <div>
                    <div class="admin-bimbingan-stat-icon" style="background: #F1F5F9; color: #475569;">
                        <i class="bi bi-journal-x"></i>
                    </div>
                    <div class="admin-bimbingan-stat-val">{{ $timBelumAdaLogbook ?? 0 }}</div>
                    <div class="admin-bimbingan-stat-label">Tim Belum Ada Logbook</div>
                </div>
                <div class="admin-bimbingan-stat-sub">
                    <span>Catatan Sesi:</span>
                    <span class="badge badge-gray" style="font-size: 0.7rem;">0 Sesi Tercatat</span>
                </div>
            </div>

            <!-- 4. Dosen Aktif Membimbing -->
            <div class="admin-bimbingan-stat-card">
                <div>
                    <div class="admin-bimbingan-stat-icon" style="background: #ECFDF5; color: #059669;">
                        <i class="bi bi-person-workspace"></i>
                    </div>
                    <div class="admin-bimbingan-stat-val">{{ $dosenAktifMembimbing ?? 0 }}</div>
                    <div class="admin-bimbingan-stat-label">Dosen Aktif Membimbing</div>
                </div>
                <div class="admin-bimbingan-stat-sub">
                    <span>Pendamping Resmi:</span>
                    <span class="badge badge-success" style="font-size: 0.7rem;">Dosen JTI</span>
                </div>
            </div>
        </div>

        <!-- Toolbar Pencarian & Filter Responsif -->
        <div class="admin-filter-card">
            <form action="{{ route('bimbingan.index') }}" method="GET">
                <div class="admin-filter-grid">
                    <!-- Pencarian: Nama Tim / Ketua / NIM / Dosen -->
                    <div>
                        <label class="form-label" style="font-size: 0.76rem; color: var(--text-muted); margin-bottom: 4px;">
                            <i class="bi bi-search"></i> Cari Tim / Ketua / NIM / Dosen
                        </label>
                        <input type="text"
                               name="search"
                               value="{{ $search ?? request('search') }}"
                               class="form-control"
                               placeholder="Ketik nama tim, ketua, NIM, atau dosen..."
                               style="font-size: 0.85rem; padding: 8px 12px;">
                    </div>

                    <!-- Filter Lomba -->
                    <div>
                        <label class="form-label" style="font-size: 0.76rem; color: var(--text-muted); margin-bottom: 4px;">
                            <i class="bi bi-trophy"></i> Filter Lomba
                        </label>
                        <select name="lomba_id" class="form-select" style="font-size: 0.85rem; padding: 8px 12px;">
                            <option value="">Semua Lomba</option>
                            @foreach($lombaOptions ?? [] as $lopt)
                                <option value="{{ $lopt->id_lomba }}" {{ (string) ($lombaFilter ?? request('lomba_id')) === (string) $lopt->id_lomba ? 'selected' : '' }}>
                                    {{ $lopt->nama_lomba }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Dosen Pembimbing -->
                    <div>
                        <label class="form-label" style="font-size: 0.76rem; color: var(--text-muted); margin-bottom: 4px;">
                            <i class="bi bi-person-badge"></i> Filter Dosen Pembimbing
                        </label>
                        <select name="nip" class="form-select" style="font-size: 0.85rem; padding: 8px 12px;">
                            <option value="">Semua Dosen</option>
                            @foreach($dosenOptions ?? [] as $dopt)
                                <option value="{{ $dopt->nip }}" {{ (string) ($dosenFilter ?? request('nip')) === (string) $dopt->nip ? 'selected' : '' }}>
                                    {{ $dopt->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Status Logbook -->
                    <div>
                        <label class="form-label" style="font-size: 0.76rem; color: var(--text-muted); margin-bottom: 4px;">
                            <i class="bi bi-journal-check"></i> Status Logbook
                        </label>
                        <select name="status_logbook" class="form-select" style="font-size: 0.85rem; padding: 8px 12px;">
                            <option value="">Semua Tim</option>
                            <option value="sudah" {{ ($logbookFilter ?? request('status_logbook')) === 'sudah' ? 'selected' : '' }}>Sudah ada logbook</option>
                            <option value="belum" {{ ($logbookFilter ?? request('status_logbook')) === 'belum' ? 'selected' : '' }}>Belum ada logbook</option>
                        </select>
                    </div>

                    <!-- Tombol Aksi Filter -->
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <button type="submit" class="btn btn-primary btn-sm" style="padding: 9px 16px; font-size: 0.85rem;">
                            <i class="bi bi-funnel-fill"></i> Terapkan
                        </button>
                        @if(request()->filled('search') || request()->filled('lomba_id') || request()->filled('nip') || request()->filled('status_logbook'))
                            <a href="{{ route('bimbingan.index') }}" class="btn btn-secondary btn-sm" style="padding: 9px 14px; font-size: 0.85rem;" title="Reset Filter">
                                <i class="bi bi-arrow-counterclockwise"></i> Reset
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- Daftar Tim Bimbingan (Kartu Accordion Read-Only) -->
        <div class="card">
            <div class="card-header" style="flex-wrap: wrap; gap: 12px;">
                <div>
                    <div class="card-title">
                        <i class="bi bi-database-check" style="color: var(--primary);"></i> Rekapitulasi Bimbingan Seluruh JTI
                    </div>
                    <div style="font-size: 0.78rem; color: var(--text-muted);">
                        Klik pada kartu tim untuk membuka atau menutup detail riwayat sesi logbook bimbingan (Hanya Lihat)
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="badge badge-gray" style="font-size: 0.75rem;">
                        <i class="bi bi-shield-lock"></i> Read-Only Admin
                    </span>
                </div>
            </div>

            <div class="card-body">
                @if($pengajuanList->isEmpty())
                    <!-- Empty State Hasil Pencarian / Filter Kosong -->
                    <div style="text-align: center; padding: 48px 20px; color: var(--text-muted);">
                        <div style="width: 64px; height: 64px; border-radius: 50%; background: #F1F5F9; color: #64748B; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin: 0 auto 14px;">
                            <i class="bi bi-inbox"></i>
                        </div>
                        <div style="font-size: 1.05rem; font-weight: 800; color: var(--primary-dark); margin-bottom: 6px;">
                            Tidak Ada Tim Bimbingan yang Ditemukan
                        </div>
                        <p style="font-size: 0.86rem; max-width: 460px; margin: 0 auto 16px; line-height: 1.6;">
                            @if(request()->filled('search') || request()->filled('lomba_id') || request()->filled('nip') || request()->filled('status_logbook'))
                                Tidak ada tim bimbingan yang cocok dengan kriteria pencarian atau filter yang Anda pilih.
                            @else
                                Belum ada tim mahasiswa dengan pengajuan bimbingan berstatus disetujui yang tercatat saat ini.
                            @endif
                        </p>
                        @if(request()->filled('search') || request()->filled('lomba_id') || request()->filled('nip') || request()->filled('status_logbook'))
                            <a href="{{ route('bimbingan.index') }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-arrow-counterclockwise"></i> Tampilkan Semua Tim
                            </a>
                        @endif
                    </div>
                @else
                    <div class="admin-accordion-list">
                        @foreach($pengajuanList as $row)
                            @php
                                $namaTim = trim((string) ($row->tim->nama_tim ?? 'Tim'));
                                $words = preg_split('/\s+/', $namaTim, -1, PREG_SPLIT_NO_EMPTY);
                                if (count($words) >= 2) {
                                    $inisialTim = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                                } else {
                                    $inisialTim = strtoupper(substr($namaTim, 0, 2));
                                }
                                $jumlahSesi = $row->logbook_count ?? $row->logbook->count();
                            @endphp

                            <div class="admin-accordion-card" x-data="{ open: false }" :class="{ 'is-open': open }">
                                <!-- Header Kartu Accordion -->
                                <button type="button"
                                        class="admin-accordion-trigger"
                                        aria-expanded="false"
                                        @click="open = !open"
                                        onclick="toggleAdminAccordion(this)">
                                    <div style="overflow-x: auto;">
                                        <div class="admin-accordion-header-grid">
                                            <!-- Kolom 1: Avatar Inisial, Nama Tim, Nama Lomba -->
                                            <div style="display: flex; align-items: center; gap: 14px; min-width: 0; padding-right: 8px;">
                                                <div class="admin-team-avatar">
                                                    {{ $inisialTim }}
                                                </div>
                                                <div style="min-width: 0;">
                                                    <div style="font-size: 0.98rem; font-weight: 800; color: var(--primary-dark); line-height: 1.3; word-break: break-word;">
                                                        {{ $row->tim->nama_tim ?? '-' }}
                                                    </div>
                                                    <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 3px; line-height: 1.35;">
                                                        <i class="bi bi-trophy" style="color: var(--primary);"></i>
                                                        {{ $row->tim->lomba->nama_lomba ?? '-' }}
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Kolom 2: Ketua Tim + NIM -->
                                            <div style="padding: 0 8px;">
                                                <div style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted); margin-bottom: 2px;">
                                                    Ketua Tim
                                                </div>
                                                <div style="font-size: 0.86rem; font-weight: 700; color: var(--text-main);">
                                                    {{ $row->tim->ketua->nama ?? '-' }}
                                                </div>
                                                <div style="font-size: 0.75rem; color: var(--text-muted);">
                                                    NIM: {{ $row->tim->nim ?? ($row->tim->ketua->nim ?? '-') }}
                                                </div>
                                            </div>

                                            <!-- Kolom 3: Dosen Pembimbing + Bidang Keahlian -->
                                            <div style="padding: 0 8px;">
                                                <div style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted); margin-bottom: 2px;">
                                                    Dosen Pembimbing
                                                </div>
                                                <div style="font-size: 0.86rem; font-weight: 700; color: var(--primary-dark);">
                                                    {{ $row->dosen->nama ?? '-' }}
                                                </div>
                                                <div style="font-size: 0.75rem; color: var(--text-muted);">
                                                    {{ $row->dosen->bidang_keahlian ?? '-' }}
                                                </div>
                                            </div>

                                            <!-- Kolom 4: Badge Status Disetujui, Badge N Sesi, dan Ikon Panah -->
                                            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 10px; flex-wrap: wrap;">
                                                <span class="badge badge-success" style="padding: 5px 11px; font-size: 0.74rem;">
                                                    <i class="bi bi-check-circle-fill"></i> Disetujui
                                                </span>
                                                <span class="badge {{ $jumlahSesi > 0 ? 'badge-purple' : 'badge-gray' }}" style="padding: 5px 11px; font-size: 0.74rem;">
                                                    <i class="bi bi-journal-bookmark-fill"></i> {{ $jumlahSesi }} Sesi
                                                </span>
                                                <span class="admin-accordion-chevron" title="Buka / Tutup Detail Logbook">
                                                    <i class="bi bi-chevron-down"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </button>

                                <!-- Isi Kartu Accordion (Timeline Vertikal Logbook Read-Only) -->
                                <div class="admin-accordion-body" x-show="open">
                                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; margin-bottom: 16px; padding-bottom: 10px; border-bottom: 1px dashed var(--border);">
                                        <div style="font-size: 0.82rem; font-weight: 700; color: var(--primary-dark); display: flex; align-items: center; gap: 6px;">
                                            <i class="bi bi-clock-history" style="color: var(--primary);"></i>
                                            Riwayat Sesi Logbook Bimbingan ({{ $jumlahSesi }} Sesi)
                                        </div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">
                                            Disetujui sejak: <strong>{{ $row->tgl_pengajuan ? $row->tgl_pengajuan->format('d M Y') : '-' }}</strong>
                                        </div>
                                    </div>

                                    @if($row->logbook->isEmpty())
                                        <!-- Empty State jika Logbook Tim Kosong -->
                                        <div style="text-align: center; padding: 28px 18px; background: #F8FAFC; border: 1px dashed var(--border); border-radius: var(--radius-sm); color: var(--text-muted);">
                                            <i class="bi bi-journal-x" style="font-size: 1.75rem; color: #94A3B8; display: block; margin-bottom: 8px;"></i>
                                            <div style="font-size: 0.88rem; font-weight: 600; color: #475569;">
                                                Belum ada catatan logbook untuk tim ini
                                            </div>
                                        </div>
                                    @else
                                        <div style="overflow-x: auto;">
                                            <div class="timeline-wrap" style="margin-top: 4px;">
                                                @foreach($row->logbook as $lb)
                                                    @php
                                                        $dicatatOlehDosen = ($lb->catatan_dosen === 'Dicatat langsung oleh Dosen Pembimbing.');
                                                        $namaPencatat = $dicatatOlehDosen
                                                            ? (($row->dosen->nama ?? 'Dosen Pembimbing') . ' (Dosen Pembimbing)')
                                                            : (($row->tim->ketua->nama ?? 'Ketua Tim') . ' (Ketua Tim - ' . ($row->tim->nim ?? '-') . ')');
                                                    @endphp
                                                    <div class="timeline-item" style="margin-bottom: {{ $loop->last ? '0' : '18px' }};">
                                                        <div class="timeline-point"></div>
                                                        <div class="timeline-content">
                                                            <!-- Baris Atas: Tanggal, Pencatat, & Status Validasi -->
                                                            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; margin-bottom: 10px;">
                                                                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                                                    <span style="font-size: 0.8rem; font-weight: 700; color: var(--primary);">
                                                                        <i class="bi bi-calendar-event"></i>
                                                                        {{ $lb->tanggal ? $lb->tanggal->format('d M Y') : '-' }}
                                                                    </span>
                                                                    <span style="font-size: 0.75rem; color: var(--text-muted);">
                                                                        <i class="bi bi-person-lines-fill"></i> Dicatat oleh: <strong>{{ $namaPencatat }}</strong>
                                                                    </span>
                                                                </div>

                                                                <div>
                                                                    @if($lb->status_validasi === 'disetujui')
                                                                        <span class="badge badge-success" style="font-size: 0.7rem;">
                                                                            <i class="bi bi-check-circle-fill"></i> Divalidasi Dosen
                                                                        </span>
                                                                    @elseif($lb->status_validasi === 'revisi')
                                                                        <span class="badge badge-purple" style="font-size: 0.7rem;">
                                                                            <i class="bi bi-arrow-repeat"></i> Perlu Revisi
                                                                        </span>
                                                                    @else
                                                                        <span class="badge badge-gray" style="font-size: 0.7rem;">
                                                                            <i class="bi bi-clock-history"></i> Menunggu Validasi
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            <!-- Topik / Materi Bimbingan -->
                                                            <div style="margin-bottom: 8px;">
                                                                <div style="font-size: 0.74rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.3px; margin-bottom: 2px;">
                                                                    Topik / Catatan Bimbingan
                                                                </div>
                                                                <div style="font-size: 0.88rem; font-weight: 600; color: var(--primary-dark); line-height: 1.55;">
                                                                    {{ $lb->materi }}
                                                                </div>
                                                            </div>

                                                            <!-- Tindak Lanjut -->
                                                            <div style="font-size: 0.8rem; background: #FFFFFF; border: 1px solid var(--border); border-radius: 6px; padding: 8px 12px; color: #475569;">
                                                                <strong style="color: var(--primary-dark);">Tindak Lanjut:</strong>
                                                                {{ ($lb->tindak_lanjut && $lb->tindak_lanjut !== '-') ? $lb->tindak_lanjut : 'Tidak ada catatan tindak lanjut khusus.' }}
                                                            </div>

                                                            @if($lb->catatan_dosen && !$dicatatOlehDosen)
                                                                <div style="margin-top: 8px; font-size: 0.78rem; background: #F0FDF4; border-left: 3px solid #10B981; padding: 7px 12px; border-radius: 4px; color: #065F46;">
                                                                    <i class="bi bi-chat-left-quote-fill"></i>
                                                                    <strong>Feedback Dosen Pembimbing:</strong> "{{ $lb->catatan_dosen }}"
                                                                </div>
                                                            @endif

                                                            @if($lb->link_dokumentasi)
                                                                <div style="margin-top: 8px;">
                                                                    <a href="{{ $lb->link_dokumentasi }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm" style="font-size: 0.74rem; padding: 3px 10px;">
                                                                        <i class="bi bi-google"></i> Lihat Dokumentasi Sesi
                                                                    </a>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination di Bawah Daftar -->
                    @if($pengajuanList->hasPages())
                        <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                            <div style="font-size: 0.8rem; color: var(--text-muted);">
                                Menampilkan <strong>{{ $pengajuanList->firstItem() }}</strong> – <strong>{{ $pengajuanList->lastItem() }}</strong> dari <strong>{{ $pengajuanList->total() }}</strong> tim bimbingan
                            </div>
                            <div style="overflow-x: auto;">
                                {{ $pengajuanList->links() }}
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- MODAL 1: Pendaftaran Tim Cepat untuk Mahasiswa yang Belum Ikut Lomba      --}}
    {{-- ========================================================================= --}}
    @if($role === 'mahasiswa')
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
    @endif

    {{-- ========================================================================= --}}
    {{-- MODAL 2: Form Tambah Logbook Aktivitas Bimbingan                          --}}
    {{-- Hanya untuk Ketua Tim Mahasiswa atau Dosen Pembimbing                     --}}
    {{-- ========================================================================= --}}
    @if(($role === 'mahasiswa' && isset($pengajuan) && $pengajuan && $pengajuan->status === 'disetujui' && ($isKetua ?? ($selectedTim->nim === $authUser['id']))) || $role === 'dosen')
        <div id="modalLogbook" class="modal-backdrop-custom" style="display: none;">
            <div class="modal-box">
                <div class="modal-header-custom">
                    <div style="font-weight: 800; font-size: 1.05rem; color: var(--primary-dark);">
                        <i class="bi bi-journal-plus" style="color: var(--primary);"></i> Tambah Catatan Logbook Bimbingan
                    </div>
                    <button type="button" onclick="closeModalLogbook()" style="background: none; border: none; font-size: 1.4rem; cursor: pointer; color: var(--text-muted);">&times;</button>
                </div>
                <form action="{{ route('bimbingan.logbook.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id_pengajuan" id="inputLogbookIdPengajuan" value="{{ $pengajuan->id_pengajuan ?? '' }}">

                    <div class="modal-body-custom">
                        <div class="form-group" style="margin-bottom: 14px;">
                            <label class="form-label">Tanggal Pelaksanaan Bimbingan *</label>
                            <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="form-group" style="margin-bottom: 14px;">
                            <label class="form-label">Materi / Bahasan Konsultasi *</label>
                            <textarea name="materi" rows="3" class="form-control" placeholder="Contoh: Konsultasi arsitektur sistem, pemilihan database, atau review prototype UI/UX..." required></textarea>
                        </div>

                        <div class="form-group" style="margin-bottom: 14px;">
                            <label class="form-label">Arahan & Tindak Lanjut dari Dosen</label>
                            <textarea name="tindak_lanjut" rows="2" class="form-control" placeholder="Contoh: Menyempurnakan diagram sequence dan menyiapkan demo video MVP..."></textarea>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label"><i class="bi bi-google"></i> Link Dokumentasi (Google Drive)</label>
                            <input type="url" name="link_dokumentasi" class="form-control" maxlength="500" pattern="https://.*" placeholder="https://drive.google.com/drive/folders/...">
                            <small style="display: block; margin-top: 4px; font-size: 0.75rem; color: var(--text-muted);">
                                Opsional. Unggah foto/berkas bimbingan ke Google Drive, lalu tempel link-nya di sini (pastikan akses link: "Siapa saja yang memiliki link").
                            </small>
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

    {{-- ========================================================================= --}}
    {{-- MODAL 2B: Form Edit / Perbaiki Logbook Bimbingan                         --}}
    {{-- ========================================================================= --}}
    @if(($role === 'mahasiswa' && isset($pengajuan) && $pengajuan && $pengajuan->status === 'disetujui' && ($isKetua ?? ($selectedTim->nim === $authUser['id']))) || $role === 'dosen')
        <div id="modalEditLogbook" class="modal-backdrop-custom" style="display: none;">
            <div class="modal-box">
                <div class="modal-header-custom">
                    <div style="font-weight: 800; font-size: 1.05rem; color: var(--primary-dark);">
                        <i class="bi bi-pencil-square" style="color: var(--primary);"></i> Perbaiki Catatan Logbook
                    </div>
                    <button type="button" onclick="closeModalEditLogbook()" style="background: none; border: none; font-size: 1.4rem; cursor: pointer; color: var(--text-muted);">&times;</button>
                </div>
                <form id="formEditLogbook" action="" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="modal-body-custom">
                        <div class="form-group" style="margin-bottom: 14px;">
                            <label class="form-label">Tanggal Pelaksanaan Bimbingan *</label>
                            <input type="date" name="tanggal" id="editLogbookTanggal" class="form-control" required>
                        </div>

                        <div class="form-group" style="margin-bottom: 14px;">
                            <label class="form-label">Materi / Bahasan Konsultasi *</label>
                            <textarea name="materi" id="editLogbookMateri" rows="3" class="form-control" required maxlength="1000"></textarea>
                        </div>

                        <div class="form-group" style="margin-bottom: 14px;">
                            <label class="form-label">Arahan & Tindak Lanjut dari Dosen</label>
                            <textarea name="tindak_lanjut" id="editLogbookTindakLanjut" rows="2" class="form-control" maxlength="1000"></textarea>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label"><i class="bi bi-google"></i> Link Dokumentasi (Google Drive)</label>
                            <input type="url" name="link_dokumentasi" id="editLogbookLinkDokumentasi" class="form-control" maxlength="500" pattern="https://.*" placeholder="https://drive.google.com/drive/folders/...">
                            <small style="display: block; margin-top: 4px; font-size: 0.75rem; color: var(--text-muted);">
                                Opsional. Kosongkan untuk menghapus link dokumentasi.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer-custom">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="closeModalEditLogbook()">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-save"></i> Simpan & Ajukan Ulang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- MODAL 2C: Form Tambah Laporan Progres Babak Mahasiswa                     --}}
    {{-- Hanya untuk Ketua Tim Mahasiswa Resmi (memiliki badge Ketua Tim)          --}}
    {{-- ========================================================================= --}}
    @if(isset($pengajuan) && $pengajuan && $pengajuan->status === 'disetujui' && ($isKetua ?? ($selectedTim->nim === $authUser['id'] && \App\Models\Tim::ketuaDisetujui($authUser['id']))))
        <div id="modalTambahProgres" class="modal-backdrop-custom" style="display: none;">
            <div class="modal-box">
                <div class="modal-header-custom">
                    <div style="font-weight: 800; font-size: 1.05rem; color: var(--primary-dark);">
                        <i class="bi bi-flag-fill" style="color: var(--primary);"></i> Lapor Progres Babak Kompetisi
                    </div>
                    <button type="button" onclick="closeModalTambahProgres()" style="background: none; border: none; font-size: 1.4rem; cursor: pointer; color: var(--text-muted);">&times;</button>
                </div>
                <form id="formProgres" action="{{ route('bimbingan.progres.store') }}" method="POST" enctype="multipart/form-data" data-store-action="{{ route('bimbingan.progres.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="progresMethodField" value="PUT" disabled>
                    <input type="hidden" name="id_pengajuan" value="{{ $pengajuan->id_pengajuan }}">
                    <input type="hidden" name="form_progres_id" id="progresFormId" value="{{ old('form_progres_id') }}">

                    <div class="modal-body-custom">
                        @if($errors->hasAny(['babak', 'babak_lainnya', 'tanggal_update', 'hasil_akhir', 'keterangan_hasil', 'file_bukti']))
                            <div style="background: #FEF2F2; border: 1px solid #FECACA; border-radius: var(--radius-sm); padding: 10px 14px; margin-bottom: 14px; font-size: 0.8rem; color: #991B1B;">
                                <ul style="margin: 0; padding-left: 18px;">
                                    @foreach($errors->all() as $errMsg)
                                        <li>{{ $errMsg }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="form-group" style="margin-bottom: 14px;">
                            <label class="form-label">Tahapan / Babak Kompetisi *</label>
                            <select name="babak" id="progresBabak" class="form-select" required onchange="onProgresBabakChange()">
                                <option value="" disabled {{ old('babak') ? '' : 'selected' }}>-- Pilih Babak --</option>
                                @foreach(\App\Models\ProgresBabak::BABAK_OPTIONS as $opsiBabak)
                                    <option value="{{ $opsiBabak }}" {{ old('babak') === $opsiBabak ? 'selected' : '' }}>{{ $opsiBabak }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="babak_lainnya" id="progresBabakLainnya" class="form-control" maxlength="50" placeholder="Tuliskan nama babak (maks. 50 karakter)" value="{{ old('babak_lainnya') }}" style="display: none; margin-top: 8px;">
                        </div>

                        <div class="form-group" style="margin-bottom: 14px;">
                            <label class="form-label">Tanggal Capaian / Update *</label>
                            <input type="date" name="tanggal_update" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="form-group" style="margin-bottom: 14px;">
                            <label class="form-label">Hasil / Status Capaian Babak *</label>
                            <select name="hasil_akhir" id="progresHasil" class="form-select" required onchange="onProgresHasilChange()">
                                <option value="" disabled selected>-- Pilih babak terlebih dahulu --</option>
                            </select>
                        </div>

                        <div class="form-group" id="progresKeteranganWrap" style="margin-bottom: 14px; display: none;">
                            <label class="form-label">Keterangan Hasil (Opsional)</label>
                            <input type="text" name="keterangan_hasil" id="progresKeterangan" class="form-control" maxlength="100" placeholder="Contoh: Best Prototype &amp; Implementation" value="{{ old('keterangan_hasil') }}">
                            <small style="display: block; margin-top: 4px; font-size: 0.75rem; color: var(--text-muted);">Nama kategori/penghargaan, maksimal 100 karakter.</small>
                        </div>

                        <div class="form-group" id="progresBuktiWrap" style="margin-bottom: 0; display: none;">
                            <label class="form-label">Unggah Bukti <span id="progresBuktiWajibLabel" style="color: var(--danger);">*</span></label>
                            <input type="file" name="file_bukti" id="progresBukti" class="form-control" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png">
                            <small id="progresBuktiHint" style="display: block; margin-top: 4px; font-size: 0.75rem; color: var(--text-muted);">PDF/JPG/PNG, maksimal 2 MB.</small>
                        </div>
                    </div>
                    <div class="modal-footer-custom">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="closeModalTambahProgres()">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-send-fill"></i> <span id="progresSubmitLabel">Kirim Laporan Progres</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- MODAL 2D: Form Pengajuan / Edit RAB (Ketua & Anggota Tim Resmi)           --}}
    {{-- ========================================================================= --}}
    @if($role === 'mahasiswa' && isset($pengajuan) && $pengajuan && $pengajuan->status === 'disetujui' && isset($selectedTim))
        <div id="modalRab" class="modal-backdrop-custom" style="display: none;">
            <div class="modal-box" style="max-width: 920px; max-height: 92vh; display: flex; flex-direction: column;">
                <div class="modal-header-custom">
                    <div style="font-weight: 800; font-size: 1.05rem; color: var(--primary-dark);">
                        <i class="bi bi-cash-stack" style="color: var(--primary);"></i>
                        <span id="modalRabTitle">Form Pengajuan Rencana Anggaran Biaya (RAB)</span>
                    </div>
                    <button type="button" onclick="closeModalRab()" style="background: none; border: none; font-size: 1.4rem; cursor: pointer; color: var(--text-muted);">&times;</button>
                </div>

                <form id="formRab" action="{{ route('bimbingan.rab.store') }}" method="POST" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
                    @csrf
                    <input type="hidden" name="tim_id" value="{{ $selectedTim->id_tim }}">
                    <input type="hidden" name="aksi" id="rabInputAksi" value="draft">

                    <div class="modal-body-custom" style="overflow-y: auto; flex: 1;">
                        <!-- Banner Info SBM PMK 32/2025 -->
                        <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-left: 4px solid var(--primary); border-radius: var(--radius-sm); padding: 12px 14px; margin-bottom: 18px; font-size: 0.82rem; color: #1E3A8A; line-height: 1.55;">
                            <i class="bi bi-info-circle-fill" style="color: var(--primary); margin-right: 4px;"></i>
                            Berikut adalah Rencana Anggaran Biaya (RAB) yang disusun sesuai Standar Biaya Masukan (SBM) Keuangan Tahun Anggaran 2026 (PMK Nomor 32 Tahun 2025). Tarif uang harian dan penginapan terisi otomatis sesuai provinsi tujuan dan tidak boleh melebihi batas tertinggi SBM.
                        </div>

                        <!-- Bagian Header Form -->
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; margin-bottom: 16px;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Kompetisi / Lomba</label>
                                <input type="text" class="form-control" value="{{ $selectedTim->lomba->nama_lomba ?? '-' }}" readonly style="background: #F1F5F9; font-weight: 600;">
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Babak Kompetisi *</label>
                                <select name="babak" id="rabSelectBabak" class="form-select" required>
                                    @php
                                        $opsiBabakLabels = ['semi_final' => 'Semi Final', 'final' => 'Final'];
                                    @endphp
                                    @foreach($babakRabTervalidasi ?? [] as $kodeBabak)
                                        <option value="{{ $kodeBabak }}">{{ $opsiBabakLabels[$kodeBabak] ?? $kodeBabak }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Jumlah Peserta (Anggota Tim)</label>
                                <input type="number" id="rabJumlahPeserta" class="form-control" value="{{ $jumlahPesertaTim ?? 1 }}" readonly style="background: #F1F5F9; font-weight: 700;">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin-bottom: 18px; background: #F8FAFC; border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 14px;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Tanggal Mulai *</label>
                                <input type="date" name="tanggal_mulai" id="rabTanggalMulai" class="form-control" value="{{ date('Y-m-d') }}" required onchange="hitungDurasiHariRab()">
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Tanggal Selesai *</label>
                                <input type="date" name="tanggal_selesai" id="rabTanggalSelesai" class="form-control" value="{{ date('Y-m-d') }}" required onchange="hitungDurasiHariRab()">
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Durasi Kegiatan</label>
                                <input type="text" id="rabDurasiHariLabel" class="form-control" value="1 Hari" readonly style="background: #EFF6FF; color: var(--primary); font-weight: 700;">
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Kota Tujuan *</label>
                                <input type="text" name="kota_tujuan" id="rabKotaTujuan" class="form-control" placeholder="Contoh: Surabaya" required maxlength="100">
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Provinsi Tujuan *</label>
                                <select name="provinsi_tujuan" id="rabProvinsiTujuan" class="form-select" required onchange="onProvinsiRabChange()">
                                    <option value="" disabled>-- Pilih Provinsi Tujuan --</option>
                                    @foreach($provinsiSbmList ?? [] as $prov)
                                        <option value="{{ $prov }}" {{ $prov === 'Jawa Timur' ? 'selected' : '' }}>{{ $prov }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Bagian Rincian Komponen Biaya -->
                        <div style="margin-bottom: 16px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; flex-wrap: wrap; gap: 8px;">
                                <div>
                                    <div style="font-weight: 800; font-size: 0.92rem; color: var(--primary-dark);">
                                        <i class="bi bi-list-check" style="color: var(--primary);"></i> Rincian Komponen Biaya
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">
                                        Susun rincian kebutuhan anggaran keberangkatan tim per komponen
                                    </div>
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="tambahBarisRabItem()">
                                    <i class="bi bi-plus-lg"></i> Tambah Komponen
                                </button>
                            </div>

                            <div id="rabItemsContainer" style="display: flex; flex-direction: column; gap: 10px;">
                                <!-- Baris komponen dinamis dirender via JS -->
                            </div>

                            <!-- Total Keseluruhan di Bawah -->
                            <div style="margin-top: 12px; background: linear-gradient(90deg, #F8FAFC 0%, #EFF6FF 100%); border: 1px solid #BFDBFE; border-radius: var(--radius-sm); padding: 12px 16px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                                <div style="font-weight: 700; font-size: 0.88rem; color: var(--primary-dark);">
                                    Total Anggaran Keseluruhan
                                </div>
                                <div id="rabGrandTotalDisplay" style="font-weight: 800; font-size: 1.2rem; color: var(--primary);">
                                    Rp 0
                                </div>
                            </div>

                            <!-- Catatan Kecil At Cost -->
                            <div style="margin-top: 8px; font-size: 0.76rem; color: var(--text-muted); font-style: italic;">
                                <i class="bi bi-exclamation-circle"></i> Komponen transportasi dan biaya pendaftaran dicatat sesuai bukti riil (at cost). Sertakan bukti pada kolom dokumen pendukung.
                            </div>
                        </div>

                        <!-- Link Dokumen Pendukung (Google Drive) -->
                        <div class="form-group" style="margin-bottom: 0; border-top: 1px solid var(--border); padding-top: 14px;">
                            <label class="form-label"><i class="bi bi-google"></i> Link Dokumen Pendukung (Google Drive)</label>
                            <input type="url" name="link_dokumen" id="rabLinkDokumen" class="form-control" maxlength="500" pattern="https://.*" placeholder="https://drive.google.com/drive/folders/...">
                            <small style="display: block; margin-top: 4px; font-size: 0.74rem; color: var(--text-muted);">
                                Unggah rincian bukti pendukung / LOA / invoice pendaftaran ke folder Google Drive dan pastikan akses publik dibuka.
                            </small>
                        </div>
                    </div>

                    <div class="modal-footer-custom" style="justify-content: space-between; flex-wrap: wrap;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="closeModalRab()">Batal</button>
                        <div style="display: flex; gap: 8px;">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="submitFormRab('draft')">
                                <i class="bi bi-save"></i> Simpan Draft
                            </button>
                            <button type="button" class="btn btn-primary btn-sm" onclick="submitFormRab('kirim')">
                                <i class="bi bi-send-fill"></i> Kirim ke Dosen
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- MODAL 3: Form Penolakan Pengajuan Bimbingan dengan Catatan Alasan Dosen   --}}
    {{-- ========================================================================= --}}
    @if($role === 'dosen')
        <div id="modalTolakPengajuan" class="modal-backdrop-custom" style="display: none;">
            <div class="modal-box">
                <div class="modal-header-custom">
                    <div style="font-weight: 800; font-size: 1.05rem; color: #991B1B;">
                        <i class="bi bi-x-circle-fill" style="color: #DC2626;"></i> Tolak Pengajuan Bimbingan
                    </div>
                    <button type="button" onclick="closeModalTolakPengajuan()" style="background: none; border: none; font-size: 1.4rem; cursor: pointer; color: var(--text-muted);">&times;</button>
                </div>
                <form id="formTolakPengajuan" action="" method="POST">
                    @csrf
                    <input type="hidden" name="status" value="ditolak">

                    <div class="modal-body-custom">
                        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 12px;">
                            Anda akan menolak pengajuan bimbingan dari <strong id="namaTimTolak">Tim Mahasiswa</strong>. Silakan tuliskan alasan penolakan agar tim mahasiswa dapat memahami dan melakukan evaluasi.
                        </p>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Alasan Penolakan (Wajib) *</label>
                            <textarea name="catatan" id="catatanTolak" rows="4" class="form-control" placeholder="Contoh: Maaf, topik lomba kurang sesuai dengan bidang fokus saya saat ini, silakan berkonsultasi dengan dosen bidang AI/Cyber Security..." maxlength="500" required></textarea>
                            <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 4px; text-align: right;">Maksimal 500 karakter</div>
                        </div>
                    </div>
                    <div class="modal-footer-custom">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="closeModalTolakPengajuan()">Batal</button>
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="bi bi-x-circle-fill"></i> Konfirmasi Penolakan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL 4: Form Minta Revisi Logbook Mahasiswa oleh Dosen                  --}}
        <div id="modalRevisiLogbook" class="modal-backdrop-custom" style="display: none;">
            <div class="modal-box">
                <div class="modal-header-custom">
                    <div style="font-weight: 800; font-size: 1.05rem; color: #991B1B;">
                        <i class="bi bi-exclamation-triangle-fill" style="color: #DC2626;"></i> Minta Revisi & Catatan Feedback Logbook
                    </div>
                    <button type="button" onclick="closeModalRevisiLogbook()" style="background: none; border: none; font-size: 1.4rem; cursor: pointer; color: var(--text-muted);">&times;</button>
                </div>
                <form id="formRevisiLogbook" action="" method="POST">
                    @csrf
                    <input type="hidden" name="status_validasi" value="revisi">

                    <div class="modal-body-custom">
                        <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 12px;">
                            Materi Logbook: <strong id="materiLogbookRevisi" style="color: var(--primary-dark);">-</strong>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Catatan Perbaikan / Arahan Feedback (Wajib) *</label>
                            <textarea name="catatan_dosen" id="catatanDosenRevisi" rows="4" class="form-control" placeholder="Tuliskan poin perbaikan, materi yang belum lengkap, atau arahan tindak lanjut..." maxlength="1000" required></textarea>
                            <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 4px; text-align: right;">Maksimal 1000 karakter</div>
                        </div>
                    </div>
                    <div class="modal-footer-custom">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="closeModalRevisiLogbook()">Batal</button>
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="bi bi-send-fill"></i> Kirim Catatan Revisi
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL 5: Form Tolak Progres Babak Kompetisi oleh Dosen                   --}}
        <div id="modalTolakProgres" class="modal-backdrop-custom" style="display: none;">
            <div class="modal-box">
                <div class="modal-header-custom">
                    <div style="font-weight: 800; font-size: 1.05rem; color: #991B1B;">
                        <i class="bi bi-x-circle-fill" style="color: #DC2626;"></i> Tolak / Evaluasi Progres Babak
                    </div>
                    <button type="button" onclick="closeModalTolakProgres()" style="background: none; border: none; font-size: 1.4rem; cursor: pointer; color: var(--text-muted);">&times;</button>
                </div>
                <form id="formTolakProgres" action="" method="POST">
                    @csrf
                    <input type="hidden" name="status_setuju" value="ditolak">

                    <div class="modal-body-custom">
                        <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 12px;">
                            Babak Kompetisi: <strong id="namaBabakTolak" style="color: var(--primary-dark);">-</strong>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Alasan Penolakan (Wajib) *</label>
                            <textarea name="alasan_penolakan" id="catatanDosenProgres" rows="4" class="form-control" placeholder="Tuliskan alasan penolakan agar Ketua Tim dapat memperbaiki laporan..." minlength="10" maxlength="500" required oninput="document.getElementById('alasanProgresCount').textContent = this.value.length"></textarea>
                            <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 4px; text-align: right;">Minimal 10 karakter &bull; <span id="alasanProgresCount">0</span> / 500 karakter</div>
                        </div>
                    </div>
                    <div class="modal-footer-custom">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="closeModalTolakProgres()">Batal</button>
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="bi bi-send-fill"></i> Kirim Penolakan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL 6: Form Minta Revisi RAB Mahasiswa oleh Dosen                      --}}
        <div id="modalRevisiRab" class="modal-backdrop-custom" style="display: none;">
            <div class="modal-box">
                <div class="modal-header-custom">
                    <div style="font-weight: 800; font-size: 1.05rem; color: #991B1B;">
                        <i class="bi bi-exclamation-triangle-fill" style="color: #DC2626;"></i> Minta Revisi Pengajuan RAB
                    </div>
                    <button type="button" onclick="closeModalRevisiRab()" style="background: none; border: none; font-size: 1.4rem; cursor: pointer; color: var(--text-muted);">&times;</button>
                </div>
                <form id="formRevisiRab" action="" method="POST">
                    @csrf
                    <input type="hidden" name="status" value="perlu_revisi">

                    <div class="modal-body-custom">
                        <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 12px;">
                            Pengajuan RAB: <strong id="labelRabRevisi" style="color: var(--primary-dark);">-</strong>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Catatan Perbaikan / Revisi RAB (Wajib) *</label>
                            <textarea name="catatan_dosen" id="catatanDosenRab" rows="4" class="form-control" placeholder="Tuliskan komponen anggaran yang perlu diperbaiki, bukti yang belum sesuai SBM, atau arahan penyesuaian..." maxlength="1000" required></textarea>
                            <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 4px; text-align: right;">Maksimal 1000 karakter</div>
                        </div>
                    </div>
                    <div class="modal-footer-custom">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="closeModalRevisiRab()">Batal</button>
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="bi bi-send-fill"></i> Kirim Catatan Revisi RAB
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

@endsection

@php
    $bimbinganScriptPayload = [
        'progresHasilNonFinal' => \App\Models\ProgresBabak::HASIL_NON_FINAL,
        'progresHasilFinal' => \App\Models\ProgresBabak::HASIL_FINAL,
        'progresHasilWajibBukti' => \App\Models\ProgresBabak::HASIL_PRESTASI,
        'sbmTarifList' => $sbmTarifList ?? [],
        'babakValidRab' => $babakRabTervalidasi ?? [],
        'existingRabBabak' => isset($rabList) ? $rabList->pluck('babak')->values() : [],
        'defaultJumlahPeserta' => (int) ($jumlahPesertaTim ?? 1),
    ];
@endphp

@push('scripts')
<script id="bimbingan-page-data" type="application/json">{!! json_encode($bimbinganScriptPayload) !!}</script>
<script>
    const BIMBINGAN_PAGE_DATA = JSON.parse(document.getElementById('bimbingan-page-data')?.textContent || '{}');

    function toggleAdminAccordion(btnEl) {
        const card = btnEl.closest('.admin-accordion-card');
        if (!card) return;
        const isOpen = card.classList.toggle('is-open');
        btnEl.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    }

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

    function openModalLogbook(idPengajuan) {
        const modal = document.getElementById('modalLogbook');
        const inputId = document.getElementById('inputLogbookIdPengajuan');
        if (idPengajuan && inputId) {
            inputId.value = idPengajuan;
        }
        if (modal) modal.style.display = 'flex';
    }

    function closeModalLogbook() {
        const modal = document.getElementById('modalLogbook');
        if (modal) modal.style.display = 'none';
    }

    function openModalTolakPengajuan(idPengajuan, namaTim) {
        const modal = document.getElementById('modalTolakPengajuan');
        const form = document.getElementById('formTolakPengajuan');
        const labelTim = document.getElementById('namaTimTolak');
        const textarea = document.getElementById('catatanTolak');

        if (modal && form) {
            form.action = `/bimbingan/pengajuan/${idPengajuan}/respon`;
            if (labelTim) labelTim.textContent = namaTim;
            if (textarea) textarea.value = '';
            modal.style.display = 'flex';
        }
    }

    function closeModalTolakPengajuan() {
        const modal = document.getElementById('modalTolakPengajuan');
        if (modal) modal.style.display = 'none';
    }

    function openModalRevisiLogbook(idLogbook, materi) {
        const modal = document.getElementById('modalRevisiLogbook');
        const form = document.getElementById('formRevisiLogbook');
        const labelMateri = document.getElementById('materiLogbookRevisi');
        const textarea = document.getElementById('catatanDosenRevisi');

        if (modal && form) {
            form.action = `/bimbingan/logbook/${idLogbook}/validasi`;
            if (labelMateri) labelMateri.textContent = materi;
            if (textarea) textarea.value = '';
            modal.style.display = 'flex';
        }
    }

    function closeModalRevisiLogbook() {
        const modal = document.getElementById('modalRevisiLogbook');
        if (modal) modal.style.display = 'none';
    }

    function openModalEditLogbook(idLogbook, tanggal, materi, tindakLanjut, linkDokumentasi) {
        const modal = document.getElementById('modalEditLogbook');
        const form = document.getElementById('formEditLogbook');
        const inputTanggal = document.getElementById('editLogbookTanggal');
        const inputMateri = document.getElementById('editLogbookMateri');
        const inputTindak = document.getElementById('editLogbookTindakLanjut');
        const inputLink = document.getElementById('editLogbookLinkDokumentasi');

        if (modal && form) {
            form.action = `/bimbingan/logbook/${idLogbook}`;
            if (inputTanggal) inputTanggal.value = tanggal || '';
            if (inputMateri) inputMateri.value = materi || '';
            if (inputTindak) inputTindak.value = tindakLanjut || '';
            if (inputLink) inputLink.value = linkDokumentasi || '';
            modal.style.display = 'flex';
        }
    }

    function closeModalEditLogbook() {
        const modal = document.getElementById('modalEditLogbook');
        if (modal) modal.style.display = 'none';
    }

    // =========================================================================
    // FORM LAPOR PROGRES BABAK (dropdown dinamis; divalidasi ulang di server)
    // =========================================================================
    const PROGRES_HASIL_NON_FINAL = BIMBINGAN_PAGE_DATA.progresHasilNonFinal || [];
    const PROGRES_HASIL_FINAL = BIMBINGAN_PAGE_DATA.progresHasilFinal || [];
    const PROGRES_HASIL_WAJIB_BUKTI = BIMBINGAN_PAGE_DATA.progresHasilWajibBukti || [];

    function populateProgresHasil(selectedHasil = null) {
        const babak = document.getElementById('progresBabak')?.value;
        const select = document.getElementById('progresHasil');
        if (!select) return;

        const opsi = babak === 'Final' ? PROGRES_HASIL_FINAL : (babak ? PROGRES_HASIL_NON_FINAL : []);
        select.innerHTML = '';

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.disabled = true;
        placeholder.textContent = babak ? '-- Pilih Hasil --' : '-- Pilih babak terlebih dahulu --';
        placeholder.selected = !selectedHasil || !opsi.includes(selectedHasil);
        select.appendChild(placeholder);

        opsi.forEach(function (hasil) {
            const opt = document.createElement('option');
            opt.value = hasil;
            opt.textContent = hasil;
            if (selectedHasil && selectedHasil === hasil) opt.selected = true;
            select.appendChild(opt);
        });
    }

    function onProgresBabakChange() {
        const babak = document.getElementById('progresBabak')?.value;
        const lainnya = document.getElementById('progresBabakLainnya');
        const ketWrap = document.getElementById('progresKeteranganWrap');
        const buktiWrap = document.getElementById('progresBuktiWrap');
        const isFinal = babak === 'Final';

        if (lainnya) {
            lainnya.style.display = babak === 'Babak Lainnya' ? 'block' : 'none';
            lainnya.required = babak === 'Babak Lainnya';
            if (babak !== 'Babak Lainnya') lainnya.value = '';
        }

        if (ketWrap) ketWrap.style.display = isFinal ? 'block' : 'none';
        if (buktiWrap) buktiWrap.style.display = isFinal ? 'block' : 'none';

        if (!isFinal) {
            const ket = document.getElementById('progresKeterangan');
            const bukti = document.getElementById('progresBukti');
            if (ket) ket.value = '';
            if (bukti) { bukti.value = ''; bukti.required = false; }
        }

        populateProgresHasil(null);
        onProgresHasilChange();
    }

    function onProgresHasilChange() {
        const babak = document.getElementById('progresBabak')?.value;
        const hasil = document.getElementById('progresHasil')?.value;
        const bukti = document.getElementById('progresBukti');
        const label = document.getElementById('progresBuktiWajibLabel');
        const hint = document.getElementById('progresBuktiHint');
        if (!bukti) return;

        const wajib = babak === 'Final' && PROGRES_HASIL_WAJIB_BUKTI.includes(hasil) && !bukti.dataset.sudahAda;
        bukti.required = wajib;
        if (label) label.style.display = (babak === 'Final' && PROGRES_HASIL_WAJIB_BUKTI.includes(hasil)) ? 'inline' : 'none';
        if (hint) {
            hint.textContent = bukti.dataset.sudahAda
                ? 'PDF/JPG/PNG, maksimal 2 MB. Bukti sebelumnya sudah tersimpan; unggah berkas baru hanya jika ingin menggantinya.'
                : 'PDF/JPG/PNG, maksimal 2 MB.' + (PROGRES_HASIL_WAJIB_BUKTI.includes(hasil) ? ' Wajib untuk hasil ini.' : ' Opsional.');
        }
    }

    function resetFormProgres() {
        const form = document.getElementById('formProgres');
        if (!form) return;
        form.reset();
        form.action = form.dataset.storeAction;
        const methodField = document.getElementById('progresMethodField');
        if (methodField) methodField.disabled = true;
        const formId = document.getElementById('progresFormId');
        if (formId) formId.value = '';
        const bukti = document.getElementById('progresBukti');
        if (bukti) delete bukti.dataset.sudahAda;
        const submitLabel = document.getElementById('progresSubmitLabel');
        if (submitLabel) submitLabel.textContent = 'Kirim Laporan Progres';
    }

    function openModalTambahProgres() {
        const modal = document.getElementById('modalTambahProgres');
        if (!modal) return;
        resetFormProgres();
        onProgresBabakChange();
        modal.style.display = 'flex';
    }

    function openModalEditProgres(btnEl) {
        const modal = document.getElementById('modalTambahProgres');
        const form = document.getElementById('formProgres');
        if (!modal || !form || !btnEl) return;

        const data = JSON.parse(btnEl.getAttribute('data-progres') || '{}');
        resetFormProgres();

        form.action = `/bimbingan/progres/${data.id}`;
        document.getElementById('progresMethodField').disabled = false;
        document.getElementById('progresFormId').value = data.id;
        document.getElementById('progresSubmitLabel').textContent = 'Perbaiki & Kirim Ulang';

        document.getElementById('progresBabak').value = data.babak || '';
        form.querySelector('[name="tanggal_update"]').value = data.tanggal_update || '';
        onProgresBabakChange();
        document.getElementById('progresBabakLainnya').value = data.babak_lainnya || '';
        document.getElementById('progresKeterangan').value = data.keterangan_hasil || '';
        if (data.punya_bukti) document.getElementById('progresBukti').dataset.sudahAda = '1';

        populateProgresHasil(data.hasil_akhir);
        onProgresHasilChange();
        modal.style.display = 'flex';
    }

    function closeModalTambahProgres() {
        const modal = document.getElementById('modalTambahProgres');
        if (modal) modal.style.display = 'none';
    }

    @if($errors->hasAny(['babak', 'babak_lainnya', 'tanggal_update', 'hasil_akhir', 'keterangan_hasil', 'file_bukti']))
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('modalTambahProgres');
        const form = document.getElementById('formProgres');
        if (!modal || !form) return;

        const editId = document.getElementById('progresFormId')?.value;
        if (editId) {
            form.action = `/bimbingan/progres/${editId}`;
            document.getElementById('progresMethodField').disabled = false;
            document.getElementById('progresSubmitLabel').textContent = 'Perbaiki & Kirim Ulang';
        }
        onProgresBabakChange();
        populateProgresHasil(@json(old('hasil_akhir')));
        onProgresHasilChange();
        modal.style.display = 'flex';
    });
    @endif

    function openModalTolakProgres(idProgres, namaBabak) {
        const modal = document.getElementById('modalTolakProgres');
        const form = document.getElementById('formTolakProgres');
        const labelBabak = document.getElementById('namaBabakTolak');
        const textarea = document.getElementById('catatanDosenProgres');

        if (modal && form) {
            form.action = `/bimbingan/progres/${idProgres}/respon`;
            const counterAlasan = document.getElementById('alasanProgresCount');
            if (counterAlasan) counterAlasan.textContent = '0';
            if (labelBabak) labelBabak.textContent = namaBabak;
            if (textarea) textarea.value = '';
            modal.style.display = 'flex';
        }
    }

    function closeModalTolakProgres() {
        const modal = document.getElementById('modalTolakProgres');
        if (modal) modal.style.display = 'none';
    }

    function openModalRevisiRab(idRab, labelRab) {
        const modal = document.getElementById('modalRevisiRab');
        const form = document.getElementById('formRevisiRab');
        const labelEl = document.getElementById('labelRabRevisi');
        const textarea = document.getElementById('catatanDosenRab');

        if (modal && form) {
            form.action = `/bimbingan/rab/${idRab}/respon`;
            if (labelEl) labelEl.textContent = labelRab;
            if (textarea) textarea.value = '';
            modal.style.display = 'flex';
        }
    }

    function closeModalRevisiRab() {
        const modal = document.getElementById('modalRevisiRab');
        if (modal) modal.style.display = 'none';
    }

    function pilihDosenAi(nip) {
        const select = document.getElementById('dosenSelect') || document.getElementById('dosenSelectPengganti');
        if (select) {
            select.value = nip;
            select.focus();
            select.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    // =========================================================================
    // LOGIKA MODAL PENGAJUAN RAB (SBM TA 2026 - PMK 32/2025)
    // =========================================================================
    const SBM_TARIF_LIST = BIMBINGAN_PAGE_DATA.sbmTarifList || [];
    const BABAK_VALID_RAB = BIMBINGAN_PAGE_DATA.babakValidRab || [];
    const EXISTING_RAB_BABAK = BIMBINGAN_PAGE_DATA.existingRabBabak || [];
    const DEFAULT_JUMLAH_PESERTA = BIMBINGAN_PAGE_DATA.defaultJumlahPeserta || 1;

    const KATEGORI_RAB_OPTIONS = [
        { value: 'uang_harian_luar_kota', label: 'Uang Harian Luar Kota', satuan: 'OH', sifat: 'batas_tertinggi', freqLabel: 'Jml Hari *' },
        { value: 'uang_harian_dalam_kota', label: 'Uang Harian Dalam Kota (> 8 Jam)', satuan: 'OH', sifat: 'batas_tertinggi', freqLabel: 'Jml Hari *' },
        { value: 'penginapan', label: 'Penginapan (Tier Terendah)', satuan: 'OH', sifat: 'batas_tertinggi', freqLabel: 'Jml Malam *' },
        { value: 'tiket_antar_kota', label: 'Tiket Perjalanan Antar Kota (PP)', satuan: 'Orang/Kali', sifat: 'at_cost', freqLabel: 'Jml Kali *' },
        { value: 'transport_terminal', label: 'Transport ke Terminal/Stasiun/Bandara', satuan: 'Orang/Kali', sifat: 'dapat_dilampaui', freqLabel: 'Jml Kali *' },
        { value: 'transport_lokal', label: 'Transport Lokal (Dalam Kab/Kota PP)', satuan: 'Orang/Kali', sifat: 'dapat_dilampaui', freqLabel: 'Jml Kali *' },
        { value: 'biaya_pendaftaran', label: 'Biaya Pendaftaran Lomba', satuan: 'Paket', sifat: 'at_cost', freqLabel: 'Jml Kali *' },
        { value: 'lain_lain', label: 'Lain-lain', satuan: 'Paket', sifat: 'at_cost', freqLabel: 'Jml Kali *' },
    ];

    let rabRowCounter = 0;

    function cariTarifSbm(kategori, provinsi) {
        const provLower = (provinsi || '').toLowerCase().trim();
        let found = SBM_TARIF_LIST.find(item =>
            item.kode === kategori &&
            item.provinsi &&
            item.provinsi.toLowerCase().trim() === provLower
        );
        if (!found) {
            found = SBM_TARIF_LIST.find(item => item.kode === kategori && !item.provinsi);
        }
        return found || null;
    }

    function formatRupiah(angka) {
        const n = Math.round(Number(angka) || 0);
        return 'Rp ' + n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function hitungDurasiHariRab() {
        const tMulai = document.getElementById('rabTanggalMulai')?.value;
        const tSelesai = document.getElementById('rabTanggalSelesai')?.value;
        const label = document.getElementById('rabDurasiHariLabel');
        if (!label) return 1;

        if (tMulai && tSelesai) {
            const d1 = new Date(tMulai);
            const d2 = new Date(tSelesai);
            const diff = Math.floor((d2 - d1) / (1000 * 60 * 60 * 24)) + 1;
            const hari = diff > 0 ? diff : 1;
            label.value = hari + ' Hari';
            return hari;
        }
        label.value = '1 Hari';
        return 1;
    }

    function populateDropdownBabakRab(selectedBabak = null, isEdit = false) {
        const select = document.getElementById('rabSelectBabak');
        if (!select) return;
        select.innerHTML = '';

        const labels = { semi_final: 'Semi Final', final: 'Final' };
        BABAK_VALID_RAB.forEach(kode => {
            if (!isEdit && EXISTING_RAB_BABAK.includes(kode)) {
                return;
            }
            const opt = document.createElement('option');
            opt.value = kode;
            opt.textContent = labels[kode] || kode;
            if (selectedBabak && selectedBabak === kode) {
                opt.selected = true;
            }
            select.appendChild(opt);
        });
    }

    function tambahBarisRabItem(initialData = null) {
        const container = document.getElementById('rabItemsContainer');
        if (!container) return;

        const idx = rabRowCounter++;
        const provinsi = document.getElementById('rabProvinsiTujuan')?.value || 'Jawa Timur';
        const durasiHari = hitungDurasiHariRab();

        const katVal = initialData?.kategori || 'uang_harian_luar_kota';
        const katMeta = KATEGORI_RAB_OPTIONS.find(k => k.value === katVal) || KATEGORI_RAB_OPTIONS[0];
        const sbmRef = cariTarifSbm(katVal, provinsi);

        const uraianVal = initialData?.uraian ?? katMeta.label;
        const satuanVal = initialData?.satuan ?? (sbmRef?.satuan || katMeta.satuan);
        const jmlOrangVal = initialData?.jumlah_orang ?? DEFAULT_JUMLAH_PESERTA;
        const frekuensiVal = initialData?.frekuensi ?? durasiHari;
        const tarifVal = initialData?.tarif_satuan ?? (sbmRef ? Number(sbmRef.tarif) : 0);
        const alasanVal = initialData?.alasan_melebihi ?? '';
        const buktiVal = initialData?.bukti_link ?? '';

        const rowEl = document.createElement('div');
        rowEl.className = 'rab-item-row';
        rowEl.dataset.index = idx;
        rowEl.style.cssText = 'background: #FFFFFF; border: 1px solid var(--border); border-radius: 8px; padding: 12px;';

        let optionsHtml = '';
        KATEGORI_RAB_OPTIONS.forEach(opt => {
            const sel = opt.value === katVal ? 'selected' : '';
            optionsHtml += `<option value="${opt.value}" ${sel}>${opt.label}</option>`;
        });

        rowEl.innerHTML = `
            <div style="display: grid; grid-template-columns: 1.4fr 1.6fr 0.7fr 0.7fr 0.7fr 1.1fr 1.1fr auto; gap: 8px; align-items: end;">
                <div>
                    <label style="display:block; font-size:0.72rem; font-weight:700; color:var(--text-muted); margin-bottom:3px;">Kategori *</label>
                    <select name="items[${idx}][kategori]" class="form-select rab-input-kategori" style="padding:7px 9px; font-size:0.8rem;" onchange="onKategoriBarisChange(this)" required>
                        ${optionsHtml}
                    </select>
                </div>
                <div>
                    <label style="display:block; font-size:0.72rem; font-weight:700; color:var(--text-muted); margin-bottom:3px;">Uraian Komponen *</label>
                    <input type="text" name="items[${idx}][uraian]" class="form-control rab-input-uraian" value="${uraianVal.replace(/"/g, '&quot;')}" style="padding:7px 9px; font-size:0.8rem;" required maxlength="255">
                </div>
                <div>
                    <label style="display:block; font-size:0.72rem; font-weight:700; color:var(--text-muted); margin-bottom:3px;">Satuan</label>
                    <input type="text" name="items[${idx}][satuan]" class="form-control rab-input-satuan" value="${satuanVal.replace(/"/g, '&quot;')}" style="padding:7px 9px; font-size:0.8rem; background:#F8FAFC;" required maxlength="30">
                </div>
                <div>
                    <label style="display:block; font-size:0.72rem; font-weight:700; color:var(--text-muted); margin-bottom:3px;">Jml Org *</label>
                    <input type="number" name="items[${idx}][jumlah_orang]" class="form-control rab-input-orang" value="${jmlOrangVal}" min="1" max="50" style="padding:7px 9px; font-size:0.8rem;" oninput="recalcBarisRab(this)" required>
                </div>
                <div>
                    <label class="rab-label-frekuensi" style="display:block; font-size:0.72rem; font-weight:700; color:var(--text-muted); margin-bottom:3px;">${katMeta.freqLabel || 'Jml Hari *'}</label>
                    <input type="number" name="items[${idx}][frekuensi]" class="form-control rab-input-frekuensi" value="${frekuensiVal}" min="1" max="365" style="padding:7px 9px; font-size:0.8rem;" oninput="recalcBarisRab(this)" required>
                </div>
                <div>
                    <label style="display:block; font-size:0.72rem; font-weight:700; color:var(--text-muted); margin-bottom:3px;">Tarif Satuan (Rp) *</label>
                    <input type="number" name="items[${idx}][tarif_satuan]" class="form-control rab-input-tarif" value="${tarifVal}" min="0" step="1" style="padding:7px 9px; font-size:0.8rem;" oninput="recalcBarisRab(this)" required>
                </div>
                <div>
                    <label style="display:block; font-size:0.72rem; font-weight:700; color:var(--text-muted); margin-bottom:3px;">Subtotal</label>
                    <input type="text" class="form-control rab-display-subtotal" value="Rp 0" readonly style="padding:7px 9px; font-size:0.8rem; background:#F1F5F9; font-weight:700; color:var(--primary-dark);">
                </div>
                <div>
                    <button type="button" class="btn btn-danger btn-sm" style="padding:7px 10px;" onclick="hapusBarisRabItem(this)" title="Hapus Baris">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>

            <div class="rab-row-meta" style="margin-top: 8px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between; font-size: 0.74rem;">
                <span class="rab-info-sbm" style="color: var(--text-muted);"></span>
            </div>

            <div class="rab-extra-fields" style="margin-top: 8px; display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                <div class="rab-wrap-alasan" style="display: none;">
                    <label style="display:block; font-size:0.72rem; font-weight:700; color:#B45309; margin-bottom:3px;">
                        <i class="bi bi-exclamation-triangle-fill"></i> Alasan Melebihi Tarif SBM (Wajib jika melebihi SBM) *
                    </label>
                    <input type="text" name="items[${idx}][alasan_melebihi]" class="form-control rab-input-alasan" value="${alasanVal.replace(/"/g, '&quot;')}" placeholder="Jelaskan alasan tarif melebihi standar SBM..." style="padding:6px 9px; font-size:0.78rem;">
                </div>
                <div class="rab-wrap-bukti" style="display: none;">
                    <label style="display:block; font-size:0.72rem; font-weight:700; color:var(--primary); margin-bottom:3px;">
                        <i class="bi bi-link-45deg"></i> Link Bukti Riil / At Cost (Wajib untuk komponen At Cost) *
                    </label>
                    <input type="url" name="items[${idx}][bukti_link]" class="form-control rab-input-bukti" value="${buktiVal.replace(/"/g, '&quot;')}" pattern="https://.*" placeholder="https://drive.google.com/..." style="padding:6px 9px; font-size:0.78rem;">
                </div>
            </div>
        `;

        container.appendChild(rowEl);
        sinkronkanAturanBarisRab(rowEl, false);
    }

    function hapusBarisRabItem(btn) {
        const container = document.getElementById('rabItemsContainer');
        if (!container) return;
        const rows = container.querySelectorAll('.rab-item-row');
        if (rows.length <= 1) {
            alert('Minimal harus terdapat 1 komponen biaya pada RAB.');
            return;
        }
        const row = btn.closest('.rab-item-row');
        if (row) row.remove();
        recalcGrandTotalRab();
    }

    function onKategoriBarisChange(selectEl) {
        const row = selectEl.closest('.rab-item-row');
        if (!row) return;
        sinkronkanAturanBarisRab(row, true);
    }

    function onProvinsiRabChange() {
        const container = document.getElementById('rabItemsContainer');
        if (!container) return;
        container.querySelectorAll('.rab-item-row').forEach(row => {
            const kat = row.querySelector('.rab-input-kategori')?.value;
            const sbmRef = cariTarifSbm(kat, document.getElementById('rabProvinsiTujuan')?.value);
            if (sbmRef && ['uang_harian_luar_kota', 'uang_harian_dalam_kota', 'penginapan', 'transport_terminal'].includes(kat)) {
                const inputTarif = row.querySelector('.rab-input-tarif');
                if (inputTarif) inputTarif.value = Number(sbmRef.tarif);
            }
            sinkronkanAturanBarisRab(row, false);
        });
    }

    function sinkronkanAturanBarisRab(row, resetTarifAndUraian = false) {
        const kat = row.querySelector('.rab-input-kategori')?.value || 'lain_lain';
        const provinsi = document.getElementById('rabProvinsiTujuan')?.value || 'Jawa Timur';
        const katMeta = KATEGORI_RAB_OPTIONS.find(k => k.value === kat) || KATEGORI_RAB_OPTIONS[0];
        const sbmRef = cariTarifSbm(kat, provinsi);

        const inputUraian = row.querySelector('.rab-input-uraian');
        const inputSatuan = row.querySelector('.rab-input-satuan');
        const inputTarif = row.querySelector('.rab-input-tarif');
        const labelFrekuensi = row.querySelector('.rab-label-frekuensi');
        const infoSbm = row.querySelector('.rab-info-sbm');
        const wrapAlasan = row.querySelector('.rab-wrap-alasan');
        const inputAlasan = row.querySelector('.rab-input-alasan');
        const wrapBukti = row.querySelector('.rab-wrap-bukti');
        const inputBukti = row.querySelector('.rab-input-bukti');

        if (labelFrekuensi) {
            labelFrekuensi.textContent = katMeta.freqLabel || 'Jml Kali *';
        }

        if (resetTarifAndUraian) {
            if (inputUraian) inputUraian.value = katMeta.label;
            if (inputSatuan) inputSatuan.value = sbmRef?.satuan || katMeta.satuan;
            if (inputTarif) inputTarif.value = sbmRef ? Number(sbmRef.tarif) : 0;
        }

        // Atur batas maksimum & info SBM
        if (katMeta.sifat === 'batas_tertinggi') {
            const maxTarif = sbmRef ? Number(sbmRef.tarif) : 0;
            if (inputTarif && maxTarif > 0) {
                inputTarif.max = maxTarif;
            }
            if (infoSbm) {
                infoSbm.innerHTML = sbmRef
                    ? `<span class="badge badge-primary" style="font-size:0.68rem;">Batas Tertinggi SBM (${provinsi})</span> Maksimal: <strong>${formatRupiah(sbmRef.tarif)} / ${sbmRef.satuan}</strong>`
                    : `<span class="badge badge-warning" style="font-size:0.68rem;">Batas Tertinggi SBM</span> Tarif provinsi belum ditemukan`;
            }
            if (wrapBukti) wrapBukti.style.display = 'none';
            if (inputBukti) inputBukti.required = false;
        } else if (katMeta.sifat === 'dapat_dilampaui') {
            if (inputTarif) inputTarif.removeAttribute('max');
            if (infoSbm) {
                infoSbm.innerHTML = sbmRef
                    ? `<span class="badge badge-warning" style="font-size:0.68rem;">Dapat Dilampaui (SBM)</span> Standar SBM: <strong>${formatRupiah(sbmRef.tarif)} / ${sbmRef.satuan}</strong> (Jika melebihi, wajib isi alasan)`
                    : `<span class="badge badge-warning" style="font-size:0.68rem;">Dapat Dilampaui</span>`;
            }
            if (wrapBukti) wrapBukti.style.display = 'none';
            if (inputBukti) inputBukti.required = false;
        } else {
            if (inputTarif) inputTarif.removeAttribute('max');
            if (infoSbm) {
                infoSbm.innerHTML = `<span class="badge badge-purple" style="font-size:0.68rem;">Riil / At Cost</span> Dicatat sesuai pengeluaran riil, wajib menyertakan link bukti.`;
            }
            if (wrapBukti) wrapBukti.style.display = 'block';
            if (inputBukti) inputBukti.required = true;
        }

        recalcBarisRab(inputTarif);
    }

    function recalcBarisRab(el) {
        const row = el?.closest('.rab-item-row');
        if (!row) return;

        const kat = row.querySelector('.rab-input-kategori')?.value || 'lain_lain';
        const provinsi = document.getElementById('rabProvinsiTujuan')?.value || 'Jawa Timur';
        const katMeta = KATEGORI_RAB_OPTIONS.find(k => k.value === kat) || KATEGORI_RAB_OPTIONS[0];
        const sbmRef = cariTarifSbm(kat, provinsi);

        const jmlOrang = Number(row.querySelector('.rab-input-orang')?.value || 0);
        const frek = Number(row.querySelector('.rab-input-frekuensi')?.value || 0);
        const tarifInput = row.querySelector('.rab-input-tarif');
        const tarif = Number(tarifInput?.value || 0);

        const wrapAlasan = row.querySelector('.rab-wrap-alasan');
        const inputAlasan = row.querySelector('.rab-input-alasan');

        // Tampilkan field alasan jika sifat dapat_dilampaui dan tarif > SBM
        if (katMeta.sifat === 'dapat_dilampaui' && sbmRef && tarif > Number(sbmRef.tarif)) {
            if (wrapAlasan) wrapAlasan.style.display = 'block';
            if (inputAlasan) inputAlasan.required = true;
        } else {
            if (wrapAlasan) wrapAlasan.style.display = 'none';
            if (inputAlasan) inputAlasan.required = false;
        }

        const subtotal = jmlOrang * frek * tarif;
        const displaySubtotal = row.querySelector('.rab-display-subtotal');
        if (displaySubtotal) {
            displaySubtotal.value = formatRupiah(subtotal);
        }

        recalcGrandTotalRab();
    }

    function recalcGrandTotalRab() {
        const container = document.getElementById('rabItemsContainer');
        const grandDisplay = document.getElementById('rabGrandTotalDisplay');
        if (!container || !grandDisplay) return;

        let total = 0;
        container.querySelectorAll('.rab-item-row').forEach(row => {
            const jmlOrang = Number(row.querySelector('.rab-input-orang')?.value || 0);
            const frek = Number(row.querySelector('.rab-input-frekuensi')?.value || 0);
            const tarif = Number(row.querySelector('.rab-input-tarif')?.value || 0);
            total += (jmlOrang * frek * tarif);
        });

        grandDisplay.textContent = formatRupiah(total);
    }

    function openModalRabCreate() {
        const modal = document.getElementById('modalRab');
        if (!modal) return;

        document.getElementById('modalRabTitle').textContent = 'Ajukan Rencana Anggaran Biaya (RAB)';
        populateDropdownBabakRab(null, false);

        const container = document.getElementById('rabItemsContainer');
        if (container) container.innerHTML = '';
        rabRowCounter = 0;

        document.getElementById('rabKotaTujuan').value = '';
        document.getElementById('rabProvinsiTujuan').value = 'Jawa Timur';
        document.getElementById('rabLinkDokumen').value = '';
        hitungDurasiHariRab();

        // Tambahkan baris default Uang Harian & Penginapan
        tambahBarisRabItem({ kategori: 'uang_harian_luar_kota' });
        tambahBarisRabItem({ kategori: 'penginapan' });

        modal.style.display = 'flex';
    }

    function openModalRabEdit(btnEl) {
        const modal = document.getElementById('modalRab');
        if (!modal || !btnEl) return;

        const data = JSON.parse(btnEl.getAttribute('data-rab') || '{}');
        document.getElementById('modalRabTitle').textContent = 'Edit / Perbaiki RAB Babak ' + (data.babak === 'final' ? 'Final' : 'Semi Final');
        populateDropdownBabakRab(data.babak, true);

        if (data.tanggal_mulai) document.getElementById('rabTanggalMulai').value = data.tanggal_mulai;
        if (data.tanggal_selesai) document.getElementById('rabTanggalSelesai').value = data.tanggal_selesai;
        document.getElementById('rabKotaTujuan').value = data.kota_tujuan || '';
        if (data.provinsi_tujuan) document.getElementById('rabProvinsiTujuan').value = data.provinsi_tujuan;
        document.getElementById('rabLinkDokumen').value = data.link_dokumen || '';
        hitungDurasiHariRab();

        const container = document.getElementById('rabItemsContainer');
        if (container) container.innerHTML = '';
        rabRowCounter = 0;

        if (Array.isArray(data.items) && data.items.length > 0) {
            data.items.forEach(it => tambahBarisRabItem(it));
        } else {
            tambahBarisRabItem({ kategori: 'uang_harian_luar_kota' });
        }

        modal.style.display = 'flex';
    }

    function closeModalRab() {
        const modal = document.getElementById('modalRab');
        if (modal) modal.style.display = 'none';
    }

    function submitFormRab(aksi) {
        const form = document.getElementById('formRab');
        const inputAksi = document.getElementById('rabInputAksi');
        if (!form || !inputAksi) return;

        inputAksi.value = aksi;
        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit();
        } else {
            form.submit();
        }
    }

    // Tutup modal jika klik di luar box
    window.addEventListener('click', function(e) {
        const modalDaftar = document.getElementById('modalDaftarTim');
        const modalLogbook = document.getElementById('modalLogbook');
        const modalEditLogbook = document.getElementById('modalEditLogbook');
        const modalTambahProgres = document.getElementById('modalTambahProgres');
        const modalRab = document.getElementById('modalRab');
        const modalTolak = document.getElementById('modalTolakPengajuan');
        const modalRevisi = document.getElementById('modalRevisiLogbook');
        const modalTolakProgres = document.getElementById('modalTolakProgres');
        const modalRevisiRab = document.getElementById('modalRevisiRab');
        if (e.target === modalDaftar) closeModalDaftarTim();
        if (e.target === modalLogbook) closeModalLogbook();
        if (e.target === modalEditLogbook) closeModalEditLogbook();
        if (e.target === modalTambahProgres) closeModalTambahProgres();
        if (e.target === modalRab) closeModalRab();
        if (e.target === modalTolak) closeModalTolakPengajuan();
        if (e.target === modalRevisi) closeModalRevisiLogbook();
        if (e.target === modalTolakProgres) closeModalTolakProgres();
        if (e.target === modalRevisiRab) closeModalRevisiRab();
    });
</script>
@endpush
