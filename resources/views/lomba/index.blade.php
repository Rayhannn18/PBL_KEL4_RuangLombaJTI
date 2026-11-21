@extends('layouts.app')

@section('title', 'Katalog & Eksplorasi Lomba Mahasiswa')

@push('styles')
<style>

    /* Filter Card & Controls */
    .filter-panel {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 22px 24px;
        margin-bottom: 24px;
        box-shadow: var(--shadow-sm);
    }
    .search-input-group {
        position: relative;
        display: flex;
        align-items: center;
        margin-bottom: 18px;
    }
    .search-input-group i {
        position: absolute;
        left: 16px;
        color: var(--text-muted);
        font-size: 1.15rem;
    }
    .search-input {
        width: 100%;
        padding: 13px 18px 13px 46px;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        font-family: inherit;
        font-size: 0.95rem;
        color: var(--text-main);
        background: #F8FAFC;
        transition: all 0.2s;
    }
    .search-input:focus {
        outline: none;
        border-color: #3B82F6;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    }
    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 14px;
    }
    .filter-field label {
        display: block;
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-muted);
        margin-bottom: 6px;
    }
    .filter-select {
        width: 100%;
        padding: 9px 12px;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        font-family: inherit;
        font-size: 0.88rem;
        color: var(--text-main);
        background-color: #fff;
        transition: border-color 0.2s;
    }
    .filter-select:focus {
        outline: none;
        border-color: var(--primary);
    }

    /* Result Meta & Chips */
    .result-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 20px;
        font-size: 0.88rem;
        color: var(--text-muted);
    }
    .filter-chips {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }
    .chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: var(--primary-soft);
        color: var(--primary);
        padding: 3px 10px;
        border-radius: var(--radius-full);
        font-size: 0.78rem;
        font-weight: 600;
    }
    .chip a {
        color: var(--primary);
        opacity: 0.7;
    }
    .chip a:hover {
        opacity: 1;
    }

    /* Lomba Grid & Cards */
    .catalog-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 22px;
        margin-bottom: 35px;
    }
    .lomba-box {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-sm);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 22px;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        position: relative;
    }
    .lomba-box:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
        border-color: #BFDBFE;
    }
    .lomba-box-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 8px;
        margin-bottom: 12px;
    }
    .lomba-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--primary-dark);
        line-height: 1.4;
        margin-bottom: 6px;
    }
    .lomba-title a {
        color: inherit;
        text-decoration: none;
    }
    .lomba-title a:hover {
        color: var(--primary);
    }
    .lomba-organizer {
        font-size: 0.82rem;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 12px;
    }
    .lomba-desc {
        font-size: 0.85rem;
        color: #334155;
        line-height: 1.55;
        margin-bottom: 18px;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .lomba-meta-row {
        background: #F8FAFC;
        border: 1px solid #EEF2F6;
        border-radius: var(--radius-sm);
        padding: 10px 14px;
        margin-bottom: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.8rem;
    }
    .lomba-meta-item {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .lomba-meta-label {
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .lomba-meta-val {
        font-weight: 700;
        color: var(--text-main);
    }

    /* Custom Clean Pagination */
    .custom-pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 6px;
        margin-top: 25px;
        list-style: none;
    }
    .custom-pagination li a,
    .custom-pagination li span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 38px;
        height: 38px;
        padding: 0 12px;
        border-radius: var(--radius-sm);
        font-size: 0.85rem;
        font-weight: 600;
        border: 1px solid var(--border);
        background: #fff;
        color: var(--text-main);
        text-decoration: none;
        transition: all 0.15s;
    }
    .custom-pagination li.active span {
        background: var(--primary);
        color: #fff;
        border-color: var(--primary);
    }
    .custom-pagination li.disabled span {
        color: #CBD5E1;
        cursor: not-allowed;
    }
    .custom-pagination li a:hover {
        background: var(--primary-soft);
        color: var(--primary);
        border-color: #BFDBFE;
    }

    /* Notice Pill for Pending Schema */
    .schema-notice {
        font-size: 0.7rem;
        color: #92400E;
        background: #FEF3C7;
        padding: 2px 6px;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    /* Empty State */
    .empty-state-wrap {
        background: #fff;
        border: 1px dashed #CBD5E1;
        border-radius: var(--radius-md);
        padding: 50px 24px;
        text-align: center;
        margin: 20px 0;
    }
    .empty-icon {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: var(--primary-soft);
        color: var(--primary);
        font-size: 2rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 16px;
    }

    /* Tab Navigasi Katalog & Pengajuan Saya */
    .lomba-tabs-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 2px solid var(--border);
        margin-bottom: 24px;
        gap: 16px;
        flex-wrap: wrap;
    }
    .lomba-tabs {
        display: flex;
        gap: 8px;
        overflow: visible;
        margin-bottom: -2px;
    }
    .lomba-tabs::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }
    .lomba-tab-btn {
        padding: 11px 18px;
        background: transparent;
        border: none;
        border-bottom: 3px solid transparent;
        margin-bottom: -2px;
        font-family: inherit;
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--text-muted);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
        white-space: nowrap;
        text-decoration: none;
    }
    .lomba-tab-btn:hover {
        color: var(--primary);
    }
    .lomba-tab-btn.active {
        color: var(--primary);
        border-bottom-color: var(--primary);
        background: rgba(30, 64, 175, 0.04);
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
    }
    .lomba-tabs-action {
        display: flex;
        align-items: center;
        margin-bottom: 6px;
    }
    .btn-ajukan-lomba {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 700;
        font-size: 0.815rem;
        padding: 6px 14px;
        border-radius: var(--radius-sm);
        white-space: nowrap;
        box-shadow: 0 1px 3px rgba(30, 64, 175, 0.18);
        transition: all 0.15s ease;
    }
    .btn-ajukan-lomba:hover {
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(30, 64, 175, 0.28);
    }

    /* Modal Form (Seragam dengan Modal Logbook Bimbingan) */
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
        max-width: 660px;
        width: 100%;
        max-height: 92vh;
        display: flex;
        flex-direction: column;
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
        overflow-y: auto;
        flex: 1;
    }
    .modal-footer-custom {
        padding: 16px 24px;
        background: #F8FAFC;
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
    .modal-form-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }
    @media (max-width: 640px) {
        .catalog-grid {
            grid-template-columns: 1fr;
        }
        .filter-panel {
            padding: 16px;
        }
        .result-bar {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }
        .modal-backdrop-custom {
            padding: 12px;
        }
        .modal-header-custom,
        .modal-body-custom,
        .modal-footer-custom {
            padding: 16px;
        }
        .modal-form-grid-2 {
            grid-template-columns: 1fr;
            gap: 0;
        }
        .lomba-poster-card {
            height: 175px;
        }
        .lomba-tabs-bar {
            gap: 12px;
        }
        .lomba-tabs {
            width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .lomba-tabs::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }
        .lomba-tabs-action {
            width: 100%;
            margin-bottom: 12px;
        }
        .btn-ajukan-lomba {
            width: 100%;
            justify-content: center;
        }
    }

    /* Dual-Layer Poster Container (Blurred Background + Object Contain) */
    .lomba-poster-card {
        position: relative;
        display: block;
        width: 100%;
        height: 190px;
        margin-bottom: 14px;
        border-radius: var(--radius-sm);
        overflow: hidden;
        background: #0B132B;
        text-decoration: none;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.08);
        isolation: isolate;
    }
    .lomba-poster-media {
        position: relative;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    /* Layer 1: Blurred Backdrop (fills container, 12px blur, darkened) */
    .lomba-poster-bg {
        position: absolute;
        top: -16px;
        left: -16px;
        width: calc(100% + 32px);
        height: calc(100% + 32px);
        object-fit: cover;
        object-position: center;
        filter: blur(12px) brightness(0.6) saturate(1.25);
        transform: scale(1.1);
        pointer-events: none;
        user-select: none;
        z-index: 1;
    }
    /* Dark gradient overlay for visual depth and contrast */
    .lomba-poster-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(15, 23, 42, 0.25) 0%, rgba(15, 23, 42, 0.5) 100%);
        pointer-events: none;
        z-index: 2;
    }
    /* Layer 2: Foreground Poster (object-contain, centered, uncropped) */
    .lomba-poster-fg {
        position: relative;
        max-width: 100%;
        max-height: 100%;
        width: auto;
        height: auto;
        object-fit: contain;
        object-position: center;
        z-index: 3;
        display: block;
        margin: auto;
        filter: drop-shadow(0 4px 14px rgba(0, 0, 0, 0.45));
        transition: transform 0.25s ease;
    }
    .lomba-box:hover .lomba-poster-fg {
        transform: scale(1.02);
    }
    /* Floating Status Badge at Top-Right Corner of Poster */
    .lomba-poster-status {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 4;
        pointer-events: none;
    }
    .lomba-poster-status .badge {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        font-weight: 700;
        letter-spacing: 0.2px;
        padding: 4px 10px;
        font-size: 0.72rem;
        backdrop-filter: blur(4px);
    }
    /* Fallback Placeholder when image is missing or fails to load */
    .lomba-poster-fallback {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #F8FAFC 0%, #EFF6FF 100%);
        color: var(--text-muted);
        border: 1px dashed var(--border);
        border-radius: var(--radius-sm);
    }
    .lomba-poster-fallback i {
        font-size: 2.2rem;
        color: #94A3B8;
        margin-bottom: 4px;
    }
    .lomba-poster-fallback span {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-muted);
    }
</style>
@endpush

@section('content')
    @php
        $currentUser = auth()->user();
        $sessionUser = session('auth_user');
        $userName = $currentUser->name ?? ($sessionUser['nama'] ?? null);
        $userRoleVal = $currentUser ? ($currentUser->role instanceof \App\Enums\UserRole ? $currentUser->role->value : (string) $currentUser->role) : ($sessionUser['role'] ?? null);
        $userRoleLabel = $currentUser ? ($currentUser->role instanceof \App\Enums\UserRole ? $currentUser->role->label() : ucfirst($userRoleVal ?? '')) : ucfirst($sessionUser['role'] ?? '');
        $userId = $currentUser->nim_nip ?? ($sessionUser['id'] ?? null);
        if ($userRoleVal === 'mahasiswa' && \App\Models\Tim::ketuaDisetujui($userId)) {
            $userRoleLabel = 'Ketua Tim';
        }
        $canAjukanLomba = in_array($userRoleVal, ['mahasiswa', 'ketua_tim'], true);
        $activeTab = in_array(request('tab'), ['pengajuan_saya', 'pengajuan'], true) ? 'pengajuan_saya' : 'katalog';
        $hasActiveCatalogQuery = request()->filled('q')
            || request()->filled('search')
            || (request()->filled('tingkat') && request('tingkat') !== 'semua')
            || (request()->filled('biaya') && request('biaya') !== 'semua')
            || (request()->filled('tipe') && request('tipe') !== 'semua')
            || (request()->filled('mode') && request('mode') !== 'semua')
            || (request()->filled('kategori') && request('kategori') !== 'semua')
            || (request()->filled('tenggat') && request('tenggat') !== 'semua');
    @endphp

    <!-- Hero Header Modul Lomba (Konsisten dengan Dashboard Analitik) -->
    <div class="dashboard-header">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 20px;">
            <div>
                <div class="dashboard-header-badge">
                    <i class="bi bi-award-fill"></i> Sistem Informasi Manajemen Prestasi JTI
                </div>
                <h1 style="font-size: 1.9rem; font-weight: 800; letter-spacing: -0.5px; margin-bottom: 8px;">
                    Direktori dan Informasi Lomba
                </h1>
                <p style="font-size: 0.95rem; opacity: 0.9; max-width: 650px; line-height: 1.6; margin-bottom: 14px;">
                    Jelajahi berbagai kompetisi mahasiswa berdasarkan tingkat, kategori, dan informasi penyelenggaraan lomba.
                </p>
                <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                    <span class="badge" style="background: rgba(255,255,255,0.2); color: #fff; padding: 6px 14px; font-size: 0.82rem;">
                        <i class="bi bi-shield-check" style="color: #93C5FD;"></i> Terverifikasi Jurusan TI
                    </span>
                </div>
            </div>
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

    @if($canAjukanLomba)
        <!-- Navigasi Tab Katalog Lomba & Pengajuan Saya + Tombol Aksi Ajukan Info Lomba -->
        <div class="lomba-tabs-bar">
            <div class="lomba-tabs" role="tablist">
                <button type="button"
                        id="tabBtnKatalog"
                        class="lomba-tab-btn {{ $activeTab === 'katalog' ? 'active' : '' }}"
                        onclick="switchLombaTab('katalog')">
                    <i class="bi bi-grid-fill"></i> Katalog Lomba
                    <span class="badge badge-primary" style="font-size: 0.7rem; padding: 2px 8px;">{{ $lombaList->total() }}</span>
                </button>
                <button type="button"
                        id="tabBtnPengajuanSaya"
                        class="lomba-tab-btn {{ $activeTab === 'pengajuan_saya' ? 'active' : '' }}"
                        onclick="switchLombaTab('pengajuan_saya')">
                    <i class="bi bi-send-check-fill"></i> Pengajuan Saya
                    <span class="badge {{ ($pengajuanSaya ?? collect())->count() > 0 ? 'badge-warning' : 'badge-gray' }}" style="font-size: 0.7rem; padding: 2px 8px;">
                        {{ ($pengajuanSaya ?? collect())->count() }}
                    </span>
                </button>
            </div>

            <div class="lomba-tabs-action">
                <button type="button"
                        onclick="openModalAjukanLomba()"
                        class="btn btn-primary btn-ajukan-lomba"
                        id="btnAjukanLombaTop">
                    <i class="bi bi-plus-lg"></i> Ajukan Info Lomba
                </button>
            </div>
        </div>
    @endif

    <div id="panelKatalogLomba" style="{{ $canAjukanLomba && $activeTab === 'pengajuan_saya' ? 'display: none;' : '' }}">

    <!-- Filter & Search Panel -->
    <div class="filter-panel">
        <form action="{{ route('lomba.index') }}" method="GET" id="filterForm">
            <!-- Search Box Utama -->
            <div class="search-input-group">
                <i class="bi bi-search"></i>
                <input type="text"
                       name="q"
                       value="{{ request('q', request('search')) }}"
                       placeholder="Cari nama kompetisi, bidang/topik, atau penyelenggara..."
                       class="search-input"
                       autocomplete="off">
                @if(request('q') || request('search'))
                    <a href="{{ route('lomba.index') }}"
                       style="position: absolute; right: 14px; color: var(--text-muted); text-decoration: none;"
                       title="Hapus kata kunci pencarian">
                        <i class="bi bi-x-circle-fill"></i>
                    </a>
                @endif
            </div>

            <!-- Filter Grid -->
            <div class="filter-grid">
                <!-- 1. Tingkat Lomba -->
                <div class="filter-field">
                    <label for="filter-tingkat"><i class="bi bi-trophy"></i> Tingkat Lomba</label>
                    <select name="tingkat" id="filter-tingkat" class="filter-select" onchange="this.form.submit()">
                        <option value="semua" {{ request('tingkat') == 'semua' || !request('tingkat') ? 'selected' : '' }}>Semua Tingkat</option>
                        <option value="internasional" {{ strtolower(request('tingkat', '')) == 'internasional' ? 'selected' : '' }}>Internasional</option>
                        <option value="nasional" {{ strtolower(request('tingkat', '')) == 'nasional' ? 'selected' : '' }}>Nasional</option>
                        <option value="kampus" {{ strtolower(request('tingkat', '')) == 'kampus' ? 'selected' : '' }}>Kampus</option>
                        @if(strtolower(request('tingkat', '')) == 'regional')
                            <option value="regional" selected>Regional</option>
                        @endif
                    </select>
                </div>

                <!-- 2. Biaya -->
                <div class="filter-field">
                    <label for="filter-biaya"><i class="bi bi-cash-stack"></i> Biaya Pendaftaran</label>
                    <select name="biaya" id="filter-biaya" class="filter-select" onchange="this.form.submit()">
                        <option value="semua" {{ request('biaya') == 'semua' || !request('biaya') ? 'selected' : '' }}>Semua Biaya</option>
                        <option value="gratis" {{ request('biaya') == 'gratis' ? 'selected' : '' }}>Gratis (Tanpa Biaya)</option>
                        <option value="berbayar" {{ request('biaya') == 'berbayar' ? 'selected' : '' }}>Berbayar</option>
                    </select>
                </div>

                <!-- 3. Tipe Lomba (Individu / Kelompok) -->
                <div class="filter-field">
                    <label for="filter-tipe">
                        <i class="bi bi-person-lines-fill"></i> Tipe Peserta
                    </label>
                    <select name="tipe" id="filter-tipe" class="filter-select" onchange="this.form.submit()">
                        <option value="semua" {{ request('tipe') == 'semua' || !request('tipe') ? 'selected' : '' }}>Semua Tipe</option>
                        <option value="individu" {{ request('tipe') == 'individu' ? 'selected' : '' }}>Individu</option>
                        <option value="kelompok" {{ request('tipe') == 'kelompok' || request('tipe') == 'tim' ? 'selected' : '' }}>Kelompok</option>
                    </select>
                </div>

                <!-- 4. Mode Pelaksanaan (Online / Offline / Hybrid) -->
                <div class="filter-field">
                    <label for="filter-mode">
                        <i class="bi bi-broadcast-pin"></i> Mode Pelaksanaan
                    </label>
                    <select name="mode" id="filter-mode" class="filter-select" onchange="this.form.submit()">
                        <option value="semua" {{ request('mode') == 'semua' || !request('mode') ? 'selected' : '' }}>Semua Mode</option>
                        <option value="online" {{ request('mode') == 'online' ? 'selected' : '' }}>Online (Daring)</option>
                        <option value="offline" {{ request('mode') == 'offline' ? 'selected' : '' }}>Offline (Luring)</option>
                        <option value="hybrid" {{ request('mode') == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                    </select>
                </div>

                <!-- 5. Kategori Bidang -->
                <div class="filter-field">
                    <label for="filter-kategori"><i class="bi bi-tags"></i> Kategori Minat</label>
                    <select name="kategori" id="filter-kategori" class="filter-select" onchange="this.form.submit()">
                        <option value="" {{ !request('kategori') || request('kategori') == 'semua' ? 'selected' : '' }}>Semua Kategori</option>
                        @foreach($kategoriOptions as $kat)
                            <option value="{{ $kat }}" {{ request('kategori') == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- 6. Status Tenggat Waktu -->
                <div class="filter-field">
                    <label for="filter-tenggat"><i class="bi bi-calendar-event"></i> Status Waktu</label>
                    <select name="tenggat" id="filter-tenggat" class="filter-select" onchange="this.form.submit()">
                        <option value="semua" {{ request('tenggat') == 'semua' || !request('tenggat') ? 'selected' : '' }}>Semua Status</option>
                        <option value="buka" {{ request('tenggat') == 'buka' ? 'selected' : '' }}>Pendaftaran Buka</option>
                        <option value="segera" {{ request('tenggat') == 'segera' ? 'selected' : '' }}>Segera Berakhir (≤ 7 Hari)</option>
                        <option value="tutup" {{ request('tenggat') == 'tutup' ? 'selected' : '' }}>Pendaftaran Tutup</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 16px;">
                <a href="{{ route('lomba.index') }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset Filter
                </a>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-funnel-fill"></i> Terapkan Pencarian
                </button>
            </div>
        </form>
    </div>

    <!-- Result Meta & Active Chips -->
    <div class="result-bar">
        <div>
            Menampilkan <strong>{{ $lombaList->firstItem() ?? 0 }} - {{ $lombaList->lastItem() ?? 0 }}</strong> dari total <strong>{{ $lombaList->total() }}</strong> lomba terverifikasi
        </div>

        <div class="filter-chips">
            @if(request('q') || request('search'))
                <span class="chip">
                    Pencarian: "{{ request('q', request('search')) }}"
                    <a href="{{ request()->fullUrlWithQuery(['q' => null, 'search' => null]) }}">&times;</a>
                </span>
            @endif

            @if(request('tingkat') && request('tingkat') !== 'semua')
                <span class="chip">
                    Tingkat: {{ ucfirst(request('tingkat')) }}
                    <a href="{{ request()->fullUrlWithQuery(['tingkat' => null]) }}">&times;</a>
                </span>
            @endif

            @if(request('biaya') && request('biaya') !== 'semua')
                <span class="chip">
                    Biaya: {{ ucfirst(request('biaya')) }}
                    <a href="{{ request()->fullUrlWithQuery(['biaya' => null]) }}">&times;</a>
                </span>
            @endif

            @if(request('tipe') && request('tipe') !== 'semua')
                <span class="chip">
                    Tipe: {{ ucfirst(request('tipe')) }}
                    <a href="{{ request()->fullUrlWithQuery(['tipe' => null]) }}">&times;</a>
                </span>
            @endif

            @if(request('mode') && request('mode') !== 'semua')
                <span class="chip">
                    Mode: {{ ucfirst(request('mode')) }}
                    <a href="{{ request()->fullUrlWithQuery(['mode' => null]) }}">&times;</a>
                </span>
            @endif

            @if(request('kategori') && request('kategori') !== 'semua')
                <span class="chip">
                    Kategori: {{ request('kategori') }}
                    <a href="{{ request()->fullUrlWithQuery(['kategori' => null]) }}">&times;</a>
                </span>
            @endif

            @if(request('tenggat') && request('tenggat') !== 'semua')
                <span class="chip">
                    Status: {{ ucfirst(request('tenggat')) }}
                    <a href="{{ request()->fullUrlWithQuery(['tenggat' => null]) }}">&times;</a>
                </span>
            @endif
        </div>
    </div>

    <!-- Grid Kartu Lomba -->
    @if($lombaList->count() > 0)
        <div class="catalog-grid">
            @foreach($lombaList as $lomba)
                <div class="lomba-box">
                    <div>
                        <!-- Header Badges -->
                        <div class="lomba-box-header">
                            <div style="display: flex; gap: 6px; flex-wrap: wrap; align-items: center;">
                                <span class="badge badge-primary">{{ $lomba->kategori }}</span>

                                @php
                                    $tingkatLower = strtolower($lomba->tingkat);
                                    $badgeClass = 'badge-primary';
                                    if ($tingkatLower === 'internasional') {
                                        $badgeClass = 'badge-purple';
                                    } elseif ($tingkatLower === 'kampus') {
                                        $badgeClass = 'badge-success';
                                    } elseif ($tingkatLower === 'regional') {
                                        $badgeClass = 'badge-warning';
                                    }
                                @endphp
                                <span class="badge {{ $badgeClass }}">
                                    <i class="bi bi-geo-alt-fill"></i> {{ $lomba->tingkat }}
                                </span>

                                @if(!empty($lomba->tipe_peserta))
                                    <span class="badge" style="background: #F1F5F9; color: #475569; border: 1px solid #CBD5E1; font-size: 0.72rem;">
                                        <i class="bi bi-person-fill"></i> {{ $lomba->tipe_peserta }}
                                    </span>
                                @endif

                                @if(!empty($lomba->mode_pelaksanaan))
                                    <span class="badge" style="background: #E0F2FE; color: #0369A1; font-size: 0.72rem;">
                                        <i class="bi bi-broadcast"></i> {{ $lomba->mode_pelaksanaan }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Poster Card (Dual Layer: Blurred Backdrop + Object Contain) -->
                        <a href="{{ route('lomba.show', $lomba->id_lomba) }}"
                           class="lomba-poster-card"
                           aria-label="Lihat detail {{ $lomba->nama_lomba }}">
                            @if($lomba->poster_url)
                                <div class="lomba-poster-media">
                                    <!-- Layer 1: Blurred Background -->
                                    <img src="{{ $lomba->poster_url }}"
                                         alt=""
                                         aria-hidden="true"
                                         class="lomba-poster-bg"
                                         loading="lazy">
                                    <div class="lomba-poster-overlay"></div>

                                    <!-- Layer 2: Foreground Image (Object Contain) -->
                                    <img src="{{ $lomba->poster_url }}"
                                         alt="Poster {{ $lomba->nama_lomba }}"
                                         class="lomba-poster-fg"
                                         loading="lazy"
                                         onerror="handlePosterError(this)">
                                </div>

                                <!-- Fallback jika gambar gagal dimuat -->
                                <div class="lomba-poster-fallback" style="display: none;">
                                    <i class="bi bi-trophy"></i>
                                    <span>Pamflet Kompetisi</span>
                                </div>
                            @else
                                <div class="lomba-poster-fallback">
                                    <i class="bi bi-trophy"></i>
                                    <span>Pamflet Kompetisi</span>
                                </div>
                            @endif

                            <!-- Floating Badge Status Pendaftaran di Pojok Kanan Atas Poster -->
                            @if($lomba->tenggat)
                                <div class="lomba-poster-status">
                                    @if($lomba->tenggat->isPast())
                                        <span class="badge badge-danger">
                                            <i class="bi bi-x-circle-fill"></i> Tutup
                                        </span>
                                    @elseif($lomba->sisa_hari <= 7)
                                        <span class="badge badge-danger" title="{{ $lomba->sisa_hari }} hari tersisa">
                                            <i class="bi bi-clock-history"></i> Sisa {{ $lomba->sisa_hari }} Hari
                                        </span>
                                    @else
                                        <span class="badge badge-success">
                                            <i class="bi bi-check2-circle"></i> Buka
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </a>

                        <!-- Judul & Penyelenggara -->
                        <h2 class="lomba-title">
                            <a href="{{ route('lomba.show', $lomba->id_lomba) }}">
                                {{ $lomba->nama_lomba }}
                            </a>
                        </h2>

                        <div class="lomba-organizer">
                            <i class="bi bi-building"></i> {{ $lomba->penyelenggara }}
                        </div>

                        <!-- Deskripsi Singkat -->
                        <p class="lomba-desc">
                            {{ $lomba->deskripsi ?: 'Informasi panduan, pendaftaran, dan mekanisme pelaksanaan kompetisi resmi terdaftar di Jurusan Teknologi Informasi Polinema.' }}
                        </p>
                    </div>

                    <div>
                        <!-- Meta Box (Tenggat, Biaya, Tipe/Mode jika ada) -->
                        <div class="lomba-meta-row">
                            <div class="lomba-meta-item">
                                <span class="lomba-meta-label">Batas Tenggat</span>
                                <span class="lomba-meta-val">
                                    <i class="bi bi-calendar3"></i> {{ $lomba->tenggat ? $lomba->tenggat->format('d M Y') : '-' }}
                                </span>
                            </div>
                            <div class="lomba-meta-item" style="text-align: right;">
                                <span class="lomba-meta-label">Biaya Pendaftaran</span>
                                <span class="lomba-meta-val" style="color: {{ $lomba->biaya == 0 ? 'var(--success)' : 'var(--primary-dark)' }};">
                                    {{ $lomba->formatted_biaya }}
                                </span>
                            </div>
                        </div>

                        <!-- Tombol Aksi -->
                        <div style="display: flex; gap: 8px;">
                            <a href="{{ route('lomba.show', $lomba->id_lomba) }}"
                               class="btn btn-secondary btn-sm"
                               style="flex: 1; font-weight: 600;"
                               title="Lihat Detail Lomba">
                                <i class="bi bi-info-circle"></i> Lihat Detail Lomba
                            </a>

                            <a href="{{ route('tim.create', ['lomba_id' => $lomba->id_lomba]) }}"
                               class="btn btn-outline-primary btn-sm"
                               style="flex: 1; font-weight: 700;"
                               title="Bentuk tim delegasi untuk lomba ini">
                                <i class="bi bi-flag-fill"></i> Bentuk Tim
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div style="display: flex; justify-content: center; margin-bottom: 20px;">
            {{ $lombaList->links('pagination::bootstrap-4') }}
        </div>
    @else
        <!-- Empty State -->
        <div class="empty-state-wrap">
            <div class="empty-icon">
                <i class="bi bi-search"></i>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--primary-dark); margin-bottom: 8px;">
                Tidak Ditemukan Lomba yang Sesuai
            </h3>
            <p style="font-size: 0.9rem; color: var(--text-muted); max-width: 500px; margin: 0 auto 20px; line-height: 1.6;">
                Kriteria pencarian atau kombinasi filter Anda saat ini tidak menghasilkan kompetisi. Silakan coba gunakan kata kunci lain atau bersihkan filter.
            </p>
            <a href="{{ route('lomba.index') }}" class="btn btn-primary">
                <i class="bi bi-arrow-counterclockwise"></i> Bersihkan Semua Filter
            </a>
        </div>
    @endif
    </div>

    @if($canAjukanLomba)
        {{-- ========================================================================= --}}
        {{-- TAB PENGAJUAN SAYA: Daftar Lomba yang Diajukan User Login (Read-Only)     --}}
        {{-- ========================================================================= --}}
        <div id="panelPengajuanSaya" style="{{ $activeTab === 'pengajuan_saya' ? '' : 'display: none;' }}">
            <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: var(--radius-md); padding: 14px 18px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div style="font-size: 0.86rem; color: #1E3A8A; line-height: 1.5;">
                    <i class="bi bi-info-circle-fill" style="color: var(--primary); margin-right: 4px;"></i>
                    Daftar pengajuan info lomba yang Anda kirimkan. Pengajuan yang sedang <strong>menunggu verifikasi</strong> bersifat <em>read-only</em> dan hanya dapat dikelola oleh Administrator Kemahasiswaan JTI.
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="openModalAjukanLomba()">
                    <i class="bi bi-plus-circle-fill"></i> Ajukan Info Lomba
                </button>
            </div>

            @if(!$hasActiveCatalogQuery || $activeTab === 'pengajuan_saya')
                @if(($pengajuanSaya ?? collect())->count() > 0)
                    <div class="catalog-grid">
                        @foreach($pengajuanSaya as $itemPengajuan)
                            @php
                                $statusItem = strtolower((string) ($itemPengajuan->status ?? $itemPengajuan->status_verifikasi ?? 'menunggu'));
                            @endphp
                            <div class="lomba-box">
                                <div>
                                    <div class="lomba-box-header">
                                        <div style="display: flex; gap: 6px; flex-wrap: wrap; align-items: center;">
                                            <span class="badge badge-primary">{{ $itemPengajuan->kategori }}</span>
                                            <span class="badge badge-purple">
                                                <i class="bi bi-geo-alt-fill"></i> {{ $itemPengajuan->tingkat }}
                                            </span>
                                            @if(!empty($itemPengajuan->tipe_peserta))
                                                <span class="badge" style="background: #F1F5F9; color: #475569; border: 1px solid #CBD5E1; font-size: 0.72rem;">
                                                    <i class="bi bi-person-fill"></i> {{ $itemPengajuan->tipe_peserta }}
                                                </span>
                                            @endif
                                            @if(!empty($itemPengajuan->mode_pelaksanaan))
                                                <span class="badge" style="background: #E0F2FE; color: #0369A1; font-size: 0.72rem;">
                                                    <i class="bi bi-broadcast"></i> {{ $itemPengajuan->mode_pelaksanaan }}
                                                </span>
                                            @endif
                                        </div>

                                        @if($statusItem === 'terverifikasi')
                                            <span class="badge badge-success">
                                                <i class="bi bi-check-circle-fill"></i> Terverifikasi
                                            </span>
                                        @elseif($statusItem === 'ditolak')
                                            <span class="badge badge-danger">
                                                <i class="bi bi-x-circle-fill"></i> Ditolak
                                            </span>
                                        @else
                                            <span class="badge badge-warning">
                                                <i class="bi bi-hourglass-split"></i> Menunggu
                                            </span>
                                        @endif
                                    </div>

                                    @if($itemPengajuan->poster_url)
                                        <div class="lomba-poster-card" style="margin-bottom: 12px;">
                                            <div class="lomba-poster-media">
                                                <img src="{{ $itemPengajuan->poster_url }}"
                                                     alt=""
                                                     aria-hidden="true"
                                                     class="lomba-poster-bg"
                                                     loading="lazy">
                                                <div class="lomba-poster-overlay"></div>
                                                <img src="{{ $itemPengajuan->poster_url }}"
                                                     alt="Poster {{ $itemPengajuan->nama_lomba }}"
                                                     class="lomba-poster-fg"
                                                     loading="lazy"
                                                     onerror="handlePosterError(this)">
                                            </div>
                                            <div class="lomba-poster-fallback" style="display: none;">
                                                <i class="bi bi-trophy"></i>
                                                <span>Pamflet Kompetisi</span>
                                            </div>
                                        </div>
                                    @endif

                                    <h2 class="lomba-title">
                                        @if($statusItem === 'terverifikasi')
                                            <a href="{{ route('lomba.show', $itemPengajuan->id_lomba) }}">
                                                {{ $itemPengajuan->nama_lomba }}
                                            </a>
                                        @else
                                            {{ $itemPengajuan->nama_lomba }}
                                        @endif
                                    </h2>

                                    <div class="lomba-organizer">
                                        <i class="bi bi-building"></i> {{ $itemPengajuan->penyelenggara }}
                                    </div>

                                    <p class="lomba-desc">
                                        {{ $itemPengajuan->deskripsi ?: '-' }}
                                    </p>

                                    @if($statusItem === 'ditolak')
                                        <div style="margin-bottom: 16px; background: #FEF2F2; border: 1px solid #FECACA; border-left: 4px solid var(--danger); border-radius: var(--radius-sm); padding: 10px 12px; font-size: 0.82rem; color: #991B1B;">
                                            <div style="font-weight: 700; margin-bottom: 2px;">
                                                <i class="bi bi-exclamation-triangle-fill"></i> Alasan Penolakan:
                                            </div>
                                            <div>{{ $itemPengajuan->alasan_penolakan ?: 'Pengajuan belum memenuhi kriteria kurasi admin.' }}</div>
                                        </div>
                                    @endif
                                </div>

                                <div>
                                    <div class="lomba-meta-row">
                                        <div class="lomba-meta-item">
                                            <span class="lomba-meta-label">Batas Tenggat</span>
                                            <span class="lomba-meta-val">
                                                <i class="bi bi-calendar3"></i> {{ $itemPengajuan->tenggat ? $itemPengajuan->tenggat->format('d M Y') : '-' }}
                                            </span>
                                        </div>
                                        <div class="lomba-meta-item" style="text-align: right;">
                                            <span class="lomba-meta-label">Biaya Pendaftaran</span>
                                            <span class="lomba-meta-val" style="color: {{ $itemPengajuan->biaya == 0 ? 'var(--success)' : 'var(--primary-dark)' }};">
                                                {{ $itemPengajuan->formatted_biaya }}
                                            </span>
                                        </div>
                                    </div>

                                    <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; flex-wrap: wrap;">
                                        @if(!empty($itemPengajuan->tautan_daftar))
                                            <a href="{{ $itemPengajuan->tautan_daftar }}"
                                               target="_blank"
                                               rel="noopener noreferrer"
                                               class="btn btn-secondary btn-sm"
                                               style="font-size: 0.78rem;">
                                                <i class="bi bi-box-arrow-up-right"></i> Link Pendaftaran
                                            </a>
                                        @endif

                                        @if($statusItem === 'terverifikasi')
                                            <a href="{{ route('lomba.show', $itemPengajuan->id_lomba) }}"
                                               class="btn btn-outline-primary btn-sm"
                                               style="margin-left: auto; font-weight: 700;">
                                                <i class="bi bi-info-circle"></i> Lihat Detail
                                            </a>
                                        @elseif($statusItem === 'menunggu')
                                            <span style="margin-left: auto; font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">
                                                <i class="bi bi-lock-fill"></i> Menunggu Verifikasi Admin
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state-wrap">
                        <div class="empty-icon">
                            <i class="bi bi-inbox"></i>
                        </div>
                        <h3 style="font-size: 1.2rem; font-weight: 700; color: var(--primary-dark); margin-bottom: 8px;">
                            Belum Ada Pengajuan Info Lomba
                        </h3>
                        <p style="font-size: 0.9rem; color: var(--text-muted); max-width: 500px; margin: 0 auto 20px; line-height: 1.6;">
                            Anda belum pernah mengajukan informasi kompetisi baru. Klik tombol di bawah jika Anda menemukan info lomba menarik untuk diverifikasi oleh Admin.
                        </p>
                        <button type="button" class="btn btn-primary" onclick="openModalAjukanLomba()">
                            <i class="bi bi-plus-circle-fill"></i> Ajukan Info Lomba Pertama Anda
                        </button>
                    </div>
                @endif
            @endif
        </div>

        {{-- ========================================================================= --}}
        {{-- MODAL: Form Ajukan Info Lomba (Khusus Mahasiswa & Ketua Tim)              --}}
        {{-- ========================================================================= --}}
        <div id="modalAjukanLomba" class="modal-backdrop-custom" style="display: {{ $errors->any() ? 'flex' : 'none' }};">
            <div class="modal-box">
                <div class="modal-header-custom">
                    <div style="font-weight: 800; font-size: 1.05rem; color: var(--primary-dark);">
                        <i class="bi bi-award-fill" style="color: var(--primary);"></i> Ajukan Info Lomba
                    </div>
                    <button type="button" onclick="closeModalAjukanLomba()" style="background: none; border: none; font-size: 1.4rem; cursor: pointer; color: var(--text-muted);">&times;</button>
                </div>

                <form action="{{ route('lomba.store') }}" method="POST" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
                    @csrf
                    <div class="modal-body-custom">
                        <p style="font-size: 0.84rem; color: var(--text-muted); margin-bottom: 16px; line-height: 1.5;">
                            Lengkapi informasi lomba di bawah ini. Pengajuan Anda akan ditinjau oleh Administrator Kemahasiswaan JTI sebelum ditampilkan di katalog utama.
                        </p>

                        @if($errors->any())
                            <div class="alert alert-danger" style="margin-bottom: 16px; padding: 12px 14px; font-size: 0.84rem;">
                                <div>
                                    <div style="font-weight: 700; margin-bottom: 4px;">
                                        <i class="bi bi-exclamation-triangle-fill"></i> Mohon periksa kembali input Anda:
                                    </div>
                                    <ul style="padding-left: 18px; margin: 0;">
                                        @foreach($errors->all() as $err)
                                            <li>{{ $err }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif

                        <!-- 1. Nama Lomba -->
                        <div class="form-group" style="margin-bottom: 14px;">
                            <label class="form-label" for="ajukan_nama_lomba">Nama Lomba <span style="color: var(--danger);">*</span></label>
                            <input type="text"
                                   name="nama_lomba"
                                   id="ajukan_nama_lomba"
                                   class="form-control"
                                   value="{{ old('nama_lomba') }}"
                                   placeholder="Contoh: GEMASTIK XVIII 2026"
                                   maxlength="255"
                                   required>
                            @error('nama_lomba')
                                <small style="color: var(--danger); font-size: 0.78rem; display: block; margin-top: 4px;">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- 2. Penyelenggara -->
                        <div class="form-group" style="margin-bottom: 14px;">
                            <label class="form-label" for="ajukan_penyelenggara">Penyelenggara <span style="color: var(--danger);">*</span></label>
                            <input type="text"
                                   name="penyelenggara"
                                   id="ajukan_penyelenggara"
                                   class="form-control"
                                   value="{{ old('penyelenggara') }}"
                                   placeholder="Contoh: Puspresnas Kemendikbudristek"
                                   maxlength="150"
                                   required>
                            @error('penyelenggara')
                                <small style="color: var(--danger); font-size: 0.78rem; display: block; margin-top: 4px;">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- 3 & 4. Kategori Minat & Tingkat -->
                        <div class="modal-form-grid-2">
                            <div class="form-group" style="margin-bottom: 14px;">
                                <label class="form-label" for="ajukan_kategori">Kategori Minat <span style="color: var(--danger);">*</span></label>
                                <select name="kategori" id="ajukan_kategori" class="form-select" required>
                                    <option value="" disabled {{ old('kategori') ? '' : 'selected' }}>-- Pilih Kategori --</option>
                                    @foreach($kategoriOptions as $kat)
                                        <option value="{{ $kat }}" {{ old('kategori') === $kat ? 'selected' : '' }}>{{ $kat }}</option>
                                    @endforeach
                                </select>
                                @error('kategori')
                                    <small style="color: var(--danger); font-size: 0.78rem; display: block; margin-top: 4px;">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="form-group" style="margin-bottom: 14px;">
                                <label class="form-label" for="ajukan_tingkat">Tingkat Lomba <span style="color: var(--danger);">*</span></label>
                                <select name="tingkat" id="ajukan_tingkat" class="form-select" required>
                                    <option value="" disabled {{ old('tingkat') ? '' : 'selected' }}>-- Pilih Tingkat --</option>
                                    <option value="Internasional" {{ strtolower((string) old('tingkat')) === 'internasional' ? 'selected' : '' }}>Internasional</option>
                                    <option value="Nasional" {{ strtolower((string) old('tingkat')) === 'nasional' ? 'selected' : '' }}>Nasional</option>
                                    <option value="Kampus" {{ strtolower((string) old('tingkat')) === 'kampus' ? 'selected' : '' }}>Kampus</option>
                                    <option value="Regional" {{ strtolower((string) old('tingkat')) === 'regional' ? 'selected' : '' }}>Regional</option>
                                </select>
                                @error('tingkat')
                                    <small style="color: var(--danger); font-size: 0.78rem; display: block; margin-top: 4px;">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <!-- 5 & 6. Tipe Peserta & Mode Pelaksanaan -->
                        <div class="modal-form-grid-2">
                            <div class="form-group" style="margin-bottom: 14px;">
                                <label class="form-label" for="ajukan_tipe_peserta">Tipe Peserta <span style="color: var(--danger);">*</span></label>
                                <select name="tipe_peserta" id="ajukan_tipe_peserta" class="form-select" required>
                                    <option value="Kelompok" {{ strtolower((string) old('tipe_peserta', 'Kelompok')) === 'kelompok' ? 'selected' : '' }}>Kelompok</option>
                                    <option value="Individu" {{ strtolower((string) old('tipe_peserta')) === 'individu' ? 'selected' : '' }}>Individu</option>
                                </select>
                                @error('tipe_peserta')
                                    <small style="color: var(--danger); font-size: 0.78rem; display: block; margin-top: 4px;">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="form-group" style="margin-bottom: 14px;">
                                <label class="form-label" for="ajukan_mode_pelaksanaan">Mode Pelaksanaan <span style="color: var(--danger);">*</span></label>
                                <select name="mode_pelaksanaan" id="ajukan_mode_pelaksanaan" class="form-select" required>
                                    <option value="Online" {{ strtolower((string) old('mode_pelaksanaan', 'Online')) === 'online' ? 'selected' : '' }}>Online (Daring)</option>
                                    <option value="Offline" {{ strtolower((string) old('mode_pelaksanaan')) === 'offline' ? 'selected' : '' }}>Offline (Luring)</option>
                                    <option value="Hybrid" {{ strtolower((string) old('mode_pelaksanaan')) === 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                                </select>
                                @error('mode_pelaksanaan')
                                    <small style="color: var(--danger); font-size: 0.78rem; display: block; margin-top: 4px;">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <!-- 7 & 8. Biaya Pendaftaran & Tanggal/Tenggat -->
                        <div class="modal-form-grid-2">
                            <div class="form-group" style="margin-bottom: 14px;">
                                <label class="form-label" for="ajukan_biaya">
                                    Biaya Pendaftaran (Rp) <span style="color: var(--danger);">*</span>
                                    <span style="font-size: 0.74rem; color: var(--text-muted); font-weight: 400;">(0 jika gratis)</span>
                                </label>
                                <input type="number"
                                       name="biaya"
                                       id="ajukan_biaya"
                                       class="form-control"
                                       min="0"
                                       step="1"
                                       value="{{ old('biaya', 0) }}"
                                       placeholder="0"
                                       required>
                                @error('biaya')
                                    <small style="color: var(--danger); font-size: 0.78rem; display: block; margin-top: 4px;">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="form-group" style="margin-bottom: 14px;">
                                <label class="form-label" for="ajukan_tenggat">Tanggal / Tenggat Pendaftaran <span style="color: var(--danger);">*</span></label>
                                <input type="date"
                                       name="tenggat"
                                       id="ajukan_tenggat"
                                       class="form-control"
                                       value="{{ old('tenggat') }}"
                                       required>
                                @error('tenggat')
                                    <small style="color: var(--danger); font-size: 0.78rem; display: block; margin-top: 4px;">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <!-- 9. Link Pendaftaran -->
                        <div class="form-group" style="margin-bottom: 14px;">
                            <label class="form-label" for="ajukan_tautan_daftar">Link Pendaftaran <span style="color: var(--danger);">*</span></label>
                            <input type="url"
                                   name="tautan_daftar"
                                   id="ajukan_tautan_daftar"
                                   class="form-control"
                                   value="{{ old('tautan_daftar') }}"
                                   placeholder="https://..."
                                   maxlength="255"
                                   required>
                            @error('tautan_daftar')
                                <small style="color: var(--danger); font-size: 0.78rem; display: block; margin-top: 4px;">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- 10. Deskripsi Singkat -->
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" for="ajukan_deskripsi">Deskripsi Singkat <span style="color: var(--danger);">*</span></label>
                            <textarea name="deskripsi"
                                      id="ajukan_deskripsi"
                                      rows="3"
                                      class="form-control"
                                      placeholder="Tuliskan deskripsi singkat mengenai bidang lomba, tahapan, atau informasi penting lainnya..."
                                      maxlength="2000"
                                      required>{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <small style="color: var(--danger); font-size: 0.78rem; display: block; margin-top: 4px;">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="modal-footer-custom">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="closeModalAjukanLomba()">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-send-fill"></i> Kirim Pengajuan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection

@if($canAjukanLomba)
@push('scripts')
<script>
    function openModalAjukanLomba() {
        const modal = document.getElementById('modalAjukanLomba');
        if (modal) {
            modal.style.display = 'flex';
        }
    }

    function closeModalAjukanLomba() {
        const modal = document.getElementById('modalAjukanLomba');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    function switchLombaTab(tab) {
        const panelKatalog = document.getElementById('panelKatalogLomba');
        const panelPengajuan = document.getElementById('panelPengajuanSaya');
        const btnKatalog = document.getElementById('tabBtnKatalog');
        const btnPengajuan = document.getElementById('tabBtnPengajuanSaya');

        if (!panelKatalog || !panelPengajuan) {
            return;
        }

        if (tab === 'pengajuan_saya') {
            @if($hasActiveCatalogQuery && $activeTab !== 'pengajuan_saya')
                window.location.href = "{{ route('lomba.index', ['tab' => 'pengajuan_saya']) }}";
                return;
            @endif
            panelKatalog.style.display = 'none';
            panelPengajuan.style.display = 'block';
            if (btnKatalog) btnKatalog.classList.remove('active');
            if (btnPengajuan) btnPengajuan.classList.add('active');
        } else {
            panelKatalog.style.display = 'block';
            panelPengajuan.style.display = 'none';
            if (btnKatalog) btnKatalog.classList.add('active');
            if (btnPengajuan) btnPengajuan.classList.remove('active');
        }
    }

    window.addEventListener('click', function(e) {
        const modalAjukan = document.getElementById('modalAjukanLomba');
        if (e.target === modalAjukan) {
            closeModalAjukanLomba();
        }
    });

    @if($errors->any())
        document.addEventListener('DOMContentLoaded', function() {
            openModalAjukanLomba();
        });
    @endif
</script>
@endpush
@endif

@push('scripts')
<script>
    /**
     * Fallback handler apabila poster lomba gagal dimuat dari server/URL eksternal.
     * Otomatis menyembunyikan media container dan menampilkan fallback placeholder.
     */
    function handlePosterError(img) {
        if (!img) return;
        const card = img.closest('.lomba-poster-card');
        if (!card) return;
        const media = card.querySelector('.lomba-poster-media');
        const fallback = card.querySelector('.lomba-poster-fallback');
        if (media) {
            media.style.display = 'none';
        }
        if (fallback) {
            fallback.style.display = 'flex';
        }
    }
</script>
@endpush
