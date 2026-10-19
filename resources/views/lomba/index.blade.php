@extends('layouts.app')

@section('title', 'Katalog & Eksplorasi Lomba Mahasiswa')

@push('styles')
<style>
    /* Hero Banner Lomba */
    .lomba-hero {
        background: linear-gradient(135deg, #0F2744 0%, #1E3A8A 65%, #2563EB 100%);
        border-radius: var(--radius-lg);
        padding: 34px 36px;
        color: #fff;
        margin-bottom: 26px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 25px -10px rgba(15, 39, 68, 0.4);
    }
    .lomba-hero::after {
        content: "";
        position: absolute;
        right: -30px;
        top: -50px;
        width: 280px;
        height: 280px;
        background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, rgba(255,255,255,0) 70%);
        border-radius: 50%;
        pointer-events: none;
    }
    .lomba-hero-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(255, 255, 255, 0.16);
        backdrop-filter: blur(8px);
        padding: 5px 14px;
        border-radius: var(--radius-full);
        font-size: 0.8rem;
        font-weight: 600;
        margin-bottom: 12px;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

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
</style>
@endpush

@section('content')
    <!-- Hero Banner -->
    <div class="lomba-hero">
        <div style="max-width: 780px; position: relative; z-index: 1;">
            <div class="lomba-hero-tag">
                <i class="bi bi-compass"></i> Eksplorasi Prestasi Mahasiswa
            </div>
            <h1 style="font-size: 1.85rem; font-weight: 800; letter-spacing: -0.5px; margin-bottom: 10px; line-height: 1.25;">
                Katalog Lomba & Kompetisi Mahasiswa JTI
            </h1>
            <p style="font-size: 0.95rem; opacity: 0.9; line-height: 1.6; margin-bottom: 18px;">
                Temukan ajang kompetisi bidang Teknologi Informasi berskala kampus, nasional, hingga internasional. Bentuk tim terbaikmu dan ajukan bimbingan dosen untuk meraih prestasi gemilang.
            </p>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <span class="badge" style="background: rgba(255,255,255,0.2); color: #fff; padding: 6px 14px; font-size: 0.82rem;">
                    <i class="bi bi-check-circle-fill" style="color: #6EE7B7;"></i> {{ $totalAktif }} Kompetisi Buka Pendaftaran
                </span>
                <span class="badge" style="background: rgba(255,255,255,0.2); color: #fff; padding: 6px 14px; font-size: 0.82rem;">
                    <i class="bi bi-shield-check" style="color: #93C5FD;"></i> Terverifikasi Jurusan TI
                </span>
                <a href="{{ route('bimbingan.index') }}" class="btn btn-sm" style="background: #fff; color: var(--primary-dark); font-weight: 700; margin-left: 4px;">
                    <i class="bi bi-people-fill"></i> Modul Bimbingan Tim
                </a>
            </div>
        </div>
    </div>

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

                <!-- 3. Tipe Lomba (Individu / Tim) -->
                <div class="filter-field">
                    <label for="filter-tipe">
                        <i class="bi bi-person-lines-fill"></i> Tipe Peserta
                        @if(!$hasTipeColumn)
                            <span class="schema-notice" title="Kolom skema 'tipe' di database belum dimigrasi"><i class="bi bi-clock-history"></i> Pending Skema</span>
                        @endif
                    </label>
                    <select name="tipe" id="filter-tipe" class="filter-select" onchange="this.form.submit()" {{ !$hasTipeColumn ? 'title="Kolom tipe belum tersedia di database"' : '' }}>
                        <option value="semua" {{ request('tipe') == 'semua' || !request('tipe') ? 'selected' : '' }}>Semua Tipe</option>
                        <option value="individu" {{ request('tipe') == 'individu' ? 'selected' : '' }}>Individu</option>
                        <option value="tim" {{ request('tipe') == 'tim' ? 'selected' : '' }}>Tim / Kelompok</option>
                    </select>
                </div>

                <!-- 4. Mode Pelaksanaan (Online / Offline / Hybrid) -->
                <div class="filter-field">
                    <label for="filter-mode">
                        <i class="bi bi-broadcast-pin"></i> Mode Pelaksanaan
                        @if(!$hasModeColumn)
                            <span class="schema-notice" title="Kolom skema 'mode' di database belum dimigrasi"><i class="bi bi-clock-history"></i> Pending Skema</span>
                        @endif
                    </label>
                    <select name="mode" id="filter-mode" class="filter-select" onchange="this.form.submit()" {{ !$hasModeColumn ? 'title="Kolom mode belum tersedia di database"' : '' }}>
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
                        <option value="semua" {{ request('kategori') == 'semua' || !request('kategori') ? 'selected' : '' }}>Semua Kategori</option>
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
                            </div>

                            @if($lomba->tenggat)
                                @if($lomba->tenggat->isPast())
                                    <span class="badge badge-danger">Tutup</span>
                                @elseif($lomba->sisa_hari <= 7)
                                    <span class="badge badge-danger" title="{{ $lomba->sisa_hari }} hari tersisa">
                                        <i class="bi bi-clock-history"></i> Sisa {{ $lomba->sisa_hari }} Hari
                                    </span>
                                @else
                                    <span class="badge badge-success">
                                        <i class="bi bi-check2-circle"></i> Buka
                                    </span>
                                @endif
                            @endif
                        </div>

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
@endsection
