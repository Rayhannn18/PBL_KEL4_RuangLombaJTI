@extends('layouts.app')

@section('title', 'Papan Lowongan Tim Kompetisi - RuangLombaJTI')

@push('styles')
<style>
    .lowongan-hero {
        background: linear-gradient(135deg, #0F2744 0%, #1E40AF 100%);
        border-radius: var(--radius-lg);
        color: #fff;
        padding: 32px 36px;
        margin-bottom: 28px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 25px -5px rgba(15, 39, 68, 0.15);
    }
    .lowongan-hero::after {
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
    .hero-badge-tag {
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

    .filter-panel {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 20px 24px;
        margin-bottom: 28px;
        box-shadow: var(--shadow-sm);
    }
    .filter-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr auto;
        gap: 14px;
        align-items: end;
    }
    @media (max-width: 900px) {
        .filter-grid {
            grid-template-columns: 1fr;
        }
    }

    .tim-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
        gap: 22px;
        margin-bottom: 35px;
    }
    .tim-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-sm);
        padding: 22px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: all 0.2s ease;
    }
    .tim-card:hover {
        border-color: #93C5FD;
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
    }

    /* Modal Custom */
    .modal-backdrop-custom {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(4px);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .modal-box {
        background: #fff;
        border-radius: var(--radius-lg);
        max-width: 540px;
        width: 100%;
        box-shadow: var(--shadow-lg);
        overflow: hidden;
        animation: modalFadeIn 0.2s ease-out;
    }
    @keyframes modalFadeIn {
        from { opacity: 0; transform: scale(0.96); }
        to { opacity: 1; transform: scale(1); }
    }
    .modal-header-custom {
        padding: 18px 24px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
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

@section('content')

    <!-- Hero Header -->
    <div class="lowongan-hero">
        <div style="position: relative; z-index: 1;">
            <div class="hero-badge-tag">
                <i class="bi bi-people-fill"></i> Modul Tim Mahasiswa
            </div>
            <h1 style="font-size: 1.85rem; font-weight: 800; margin-bottom: 8px; letter-spacing: -0.5px;">
                Papan Lowongan Tim Kompetisi JTI
            </h1>
            <p style="font-size: 0.95rem; opacity: 0.9; max-width: 720px; line-height: 1.6; margin-bottom: 18px;">
                Temukan tim mahasiswa JTI Polinema yang sedang membuka lowongan anggota, lalu ajukan diri untuk bergabung sesuai kebutuhan tim dan bidang keahlianmu.
            </p>

            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <a href="{{ route('tim.create') }}" class="btn btn-primary" style="background: #2563EB; border-color: #3B82F6;">
                    <i class="bi bi-plus-circle-fill"></i> Ingin Jadi Ketua Tim? Bentuk Tim Baru
                </a>
                <a href="{{ route('lomba.index') }}" class="btn btn-outline-primary" style="background: rgba(255,255,255,0.15); color: #fff; border-color: rgba(255,255,255,0.3);">
                    <i class="bi bi-award"></i> Lihat Katalog Lomba
                </a>
            </div>
        </div>
    </div>

    <!-- Filter & Pencarian Panel -->
    <div class="filter-panel">
        <form action="{{ route('tim.lowongan') }}" method="GET" class="filter-grid">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.5px;">
                    Cari Nama Tim / Kompetisi
                </label>
                <div style="position: relative;">
                    <i class="bi bi-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                    <input type="text"
                           name="q"
                           value="{{ $currentSearch }}"
                           class="form-control"
                           style="padding-left: 36px;"
                           placeholder="Ketik kata kunci nama tim atau lomba...">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.5px;">
                    Tingkat Lomba
                </label>
                <select name="tingkat" class="form-select">
                    <option value="">Semua Tingkat</option>
                    @foreach($tingkatList as $tingkat)
                        <option value="{{ $tingkat }}" {{ $currentTingkat === $tingkat ? 'selected' : '' }}>
                            {{ $tingkat }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.5px;">
                    Status Kuota
                </label>
                <select name="status_kuota" class="form-select">
                    <option value="terbuka" {{ $currentStatusKuota === 'terbuka' ? 'selected' : '' }}>Hanya Lowongan Terbuka</option>
                    <option value="semua" {{ $currentStatusKuota === 'semua' ? 'selected' : '' }}>Semua Tim Terdaftar</option>
                </select>
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary" style="height: 42px;">
                    <i class="bi bi-funnel-fill"></i> Terapkan
                </button>
                @if($currentSearch || $currentTingkat || $currentKategori || $currentStatusKuota !== 'terbuka')
                    <a href="{{ route('tim.lowongan') }}" class="btn btn-secondary" style="height: 42px;" title="Reset Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Ringkasan Hasil -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 10px;">
        <div style="font-size: 0.95rem; font-weight: 700; color: var(--primary-dark);">
            <i class="bi bi-grid-fill" style="color: var(--primary);"></i> Menampilkan {{ $teams->count() }} Tim Kompetisi
            @if($currentStatusKuota === 'terbuka')
                <span class="badge badge-success" style="font-size: 0.75rem; margin-left: 6px;">
                    {{ $totalLowongan }} Tim Membuka Lowongan
                </span>
            @endif
        </div>
        <div style="font-size: 0.82rem; color: var(--text-muted);">
            Klik <strong>Ajukan Gabung</strong> untuk mengirimkan permohonan ke Ketua Tim
        </div>
    </div>

    <!-- Grid Kartu Tim -->
    @if($teams->isNotEmpty())
        <div class="tim-grid">
            @foreach($teams as $tim)
                @php
                    $isKetua = ($tim->nim === $authUser['id']);
                    $isDiterima = $tim->anggotaDiterima->contains('nim', $authUser['id']);
                    $isPending = $tim->anggota->where('nim', $authUser['id'])->where('status_gabung', 'menunggu')->isNotEmpty();
                    $sisaKuota = $tim->sisa_kuota_anggota;
                @endphp
                <div class="tim-card">
                    <div>
                        <!-- Header Kartu: Status Lowongan & Tingkat -->
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; gap: 8px;">
                            @if($sisaKuota > 0)
                                <span class="badge badge-success" style="font-size: 0.75rem; padding: 4px 10px;">
                                    <i class="bi bi-door-open-fill"></i> Buka Lowongan: Sisa {{ $sisaKuota }} Anggota
                                </span>
                            @else
                                <span class="badge badge-gray" style="font-size: 0.75rem; padding: 4px 10px;">
                                    <i class="bi bi-lock-fill"></i> Kuota Penuh ({{ $tim->kuota_anggota }}/{{ $tim->kuota_anggota }})
                                </span>
                            @endif

                            <span class="badge badge-purple" style="font-size: 0.72rem;">
                                {{ $tim->lomba->tingkat ?? 'Nasional' }}
                            </span>
                        </div>

                        <!-- Nama Tim -->
                        <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--primary-dark); margin-bottom: 6px;">
                            {{ $tim->nama_tim }}
                        </h3>

                        <!-- Info Ketua Tim -->
                        <div style="display: flex; align-items: center; gap: 8px; font-size: 0.82rem; color: var(--text-muted); margin-bottom: 14px;">
                            <i class="bi bi-person-badge-fill" style="color: var(--primary);"></i>
                            <span>Ketua: <strong>{{ $tim->ketua->nama ?? '-' }}</strong> (NIM: {{ $tim->nim }})</span>
                        </div>

                        <!-- Info Lomba yang Diikuti -->
                        <div style="background: #F8FAFC; border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 12px 14px; margin-bottom: 16px;">
                            <div style="font-size: 0.72rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px;">
                                KOMPETISI YANG DIIKUTI
                            </div>
                            <div style="font-weight: 700; color: var(--text-main); font-size: 0.92rem; margin-bottom: 6px; line-height: 1.4;">
                                {{ $tim->lomba->nama_lomba ?? 'Kompetisi JTI' }}
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 6px; font-size: 0.78rem; color: var(--text-muted);">
                                <span><i class="bi bi-building"></i> {{ $tim->lomba->penyelenggara ?? '-' }}</span>
                                <span><i class="bi bi-calendar3"></i> Tenggat: {{ $tim->lomba->tenggat ? $tim->lomba->tenggat->format('d M Y') : '-' }}</span>
                            </div>

                            <!-- Tombol Rincian Lomba Konsisten -->
                            @if($tim->lomba)
                                <div style="margin-top: 10px; border-top: 1px dashed var(--border); padding-top: 8px;">
                                    <a href="{{ route('lomba.show', $tim->lomba->id_lomba) }}"
                                       class="btn btn-secondary btn-sm"
                                       style="font-size: 0.75rem; padding: 4px 10px; width: 100%; justify-content: center;"
                                       title="Lihat Detail Lomba">
                                        <i class="bi bi-info-circle"></i> Lihat Detail Lomba
                                    </a>
                                </div>
                            @endif
                        </div>

                        <!-- Status Anggota Terisi -->
                        <div style="margin-bottom: 18px;">
                            <div style="display: flex; justify-content: space-between; font-size: 0.8rem; font-weight: 700; color: var(--text-muted); margin-bottom: 6px;">
                                <span>SUSUNAN ANGGOTA ({{ $tim->anggotaDiterima->count() }}/{{ $tim->kuota_anggota }})</span>
                                <span style="color: {{ $sisaKuota > 0 ? 'var(--success)' : 'var(--danger)' }};">
                                    {{ $sisaKuota > 0 ? "Sisa {$sisaKuota} slot" : 'Penuh' }}
                                </span>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                @foreach($tim->anggotaDiterima as $ang)
                                    <div style="display: flex; justify-content: space-between; align-items: center; background: #fff; border: 1px solid var(--border); border-radius: 6px; padding: 6px 10px; font-size: 0.8rem;">
                                        <span>
                                            <strong>{{ $ang->mahasiswa->nama ?? $ang->nim }}</strong>
                                            @if($ang->nim === $tim->nim)
                                                <span style="color: var(--primary); font-size: 0.7rem; font-weight: 700;">(Ketua)</span>
                                            @endif
                                        </span>
                                        <span class="badge {{ $ang->nim === $tim->nim ? 'badge-primary' : 'badge-gray' }}" style="font-size: 0.7rem;">
                                            {{ $ang->peran ?? 'Anggota' }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Aksi Anggota Tim -->
                    <div style="border-top: 1px solid var(--border); padding-top: 14px;">
                        @if($isKetua)
                            <div style="text-align: center; padding: 8px 12px; background: #EFF6FF; border-radius: var(--radius-sm); color: var(--primary); font-size: 0.83rem; font-weight: 700;">
                                <i class="bi bi-person-check-fill"></i> Anda adalah Ketua Tim ini
                            </div>
                        @elseif($isDiterima)
                            <div style="text-align: center; padding: 8px 12px; background: #D1FAE5; border-radius: var(--radius-sm); color: #065F46; font-size: 0.83rem; font-weight: 700;">
                                <i class="bi bi-check-circle-fill"></i> Anda Terdaftar di Tim Ini
                            </div>
                        @elseif($isPending)
                            <div style="text-align: center; padding: 8px 12px; background: #FEF3C7; border-radius: var(--radius-sm); color: #92400E; font-size: 0.83rem; font-weight: 700;">
                                <i class="bi bi-hourglass-split"></i> Pengajuan Sedang Menunggu Respon Ketua
                            </div>
                        @elseif($sisaKuota <= 0)
                            <button type="button" class="btn btn-secondary btn-sm" style="width: 100%;" disabled>
                                <i class="bi bi-dash-circle"></i> Kuota Tim Sudah Terpenuhi
                            </button>
                        @else
                            <button type="button"
                                    class="btn btn-primary btn-sm"
                                    style="width: 100%; font-weight: 700;"
                                    onclick="openModalGabungTim('{{ $tim->id_tim }}', '{{ addslashes($tim->nama_tim) }}', '{{ addslashes($tim->lomba->nama_lomba ?? '') }}', '{{ $sisaKuota }}')">
                                <i class="bi bi-person-plus-fill"></i> Ajukan Gabung Tim Ini
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div style="background: #fff; border: 1px solid var(--border); border-radius: var(--radius-md); padding: 50px 30px; text-align: center; margin-bottom: 30px;">
            <div style="font-size: 2.5rem; color: var(--text-muted); margin-bottom: 12px;">
                <i class="bi bi-inbox"></i>
            </div>
            <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--primary-dark); margin-bottom: 6px;">
                Tidak Ditemukan Lowongan Tim yang Sesuai
            </h3>
            <p style="font-size: 0.88rem; color: var(--text-muted); max-width: 500px; margin: 0 auto 18px;">
                Coba ubah kata kunci pencarian atau filter status kuota Anda. Anda juga dapat menginisiasi pembentukan tim baru sebagai Ketua Tim.
            </p>
            <a href="{{ route('tim.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle-fill"></i> Bentuk Tim Baru Sekarang
            </a>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- MODAL FORMULIR ANGGOTA TIM: AJUKAN GABUNG TIM LOWONGAN                   --}}
    {{-- KETENTUAN: ANGGOTA TIDAK DIMINTA MENGISI NAMA TIM / KUOTA ANGGOTA TIM    --}}
    {{-- ========================================================================= --}}
    <div id="modalGabungTim" class="modal-backdrop-custom">
        <div class="modal-box">
            <div class="modal-header-custom">
                <div style="font-weight: 800; font-size: 1.05rem; color: var(--primary-dark);">
                    <i class="bi bi-person-plus-fill" style="color: var(--primary);"></i> Ajukan Diri Bergabung ke Tim
                </div>
                <button type="button" onclick="closeModalGabungTim()" style="background: none; border: none; font-size: 1.4rem; cursor: pointer; color: var(--text-muted);">&times;</button>
            </div>
            <form id="formGabungTim" method="POST" action="" enctype="multipart/form-data">
                @csrf
                <div class="modal-body-custom">
                    <!-- Ringkasan Tim Sasaran -->
                    <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: var(--radius-sm); padding: 14px; margin-bottom: 18px;">
                        <div style="font-size: 0.75rem; font-weight: 700; color: var(--primary); text-transform: uppercase; margin-bottom: 2px;">
                            TIM SASARAN
                        </div>
                        <div id="modalNamaTimText" style="font-size: 1.1rem; font-weight: 800; color: var(--primary-dark);">
                            -
                        </div>
                        <div id="modalNamaLombaText" style="font-size: 0.82rem; color: var(--text-muted); margin-top: 4px;">
                            -
                        </div>
                        <div style="margin-top: 8px;">
                            <span id="modalSisaKuotaBadge" class="badge badge-success" style="font-size: 0.72rem;">
                                Sisa Kuota: -
                            </span>
                        </div>
                    </div>

                    <!-- Peran / Bidang Keahlian yang Ditawarkan (Khusus Anggota) -->
                    <div class="form-group" style="margin-bottom: 16px;">
                        <label class="form-label">Peran / Bidang Keahlian yang Anda Tawarkan <span style="color: var(--danger);">*</span></label>
                        <select name="peran" class="form-select @error('peran') is-invalid @enderror" required>
                            <option value="" disabled {{ old('peran') ? '' : 'selected' }}>-- Pilih Peran Anda dalam Tim --</option>
                            <option value="Frontend Developer" {{ old('peran') === 'Frontend Developer' ? 'selected' : '' }}>Frontend Developer (Web/Mobile UI)</option>
                            <option value="Backend Developer" {{ old('peran') === 'Backend Developer' ? 'selected' : '' }}>Backend & Database Engineer</option>
                            <option value="UI/UX Designer" {{ old('peran') === 'UI/UX Designer' ? 'selected' : '' }}>UI/UX Designer (Figma / Prototyping)</option>
                            <option value="Machine Learning / AI Engineer" {{ old('peran') === 'Machine Learning / AI Engineer' ? 'selected' : '' }}>Machine Learning / AI Engineer</option>
                            <option value="Mobile App Developer" {{ old('peran') === 'Mobile App Developer' ? 'selected' : '' }}>Mobile App Developer (Flutter/React Native)</option>
                            <option value="Cyber Security / Pentester" {{ old('peran') === 'Cyber Security / Pentester' ? 'selected' : '' }}>Cyber Security / Pentester</option>
                            <option value="IoT / Embedded Hardware" {{ old('peran') === 'IoT / Embedded Hardware' ? 'selected' : '' }}>IoT / Embedded Hardware</option>
                            <option value="Pitch Deck & Business Analyst" {{ old('peran') === 'Pitch Deck & Business Analyst' ? 'selected' : '' }}>Pitch Deck & Business Analyst</option>
                            <option value="Anggota Tim" {{ old('peran') === 'Anggota Tim' ? 'selected' : '' }}>Anggota Tim Umum</option>
                        </select>
                        @error('peran')
                            <div style="font-size: 0.8rem; color: var(--danger); margin-top: 5px; font-weight: 600;">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </div>
                        @enderror
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">
                            Pilihlah peran sesuai kebutuhan kompetisi dan keahlian spesifik Anda.
                        </div>
                    </div>

                    <!-- Upload CV Pelamar Anggota (Wajib, PDF, Max 2MB) -->
                    <div class="form-group" style="margin-bottom: 16px;">
                        <label class="form-label">
                            Unggah CV / Portofolio Singkat (Format PDF, Maks. 2 MB) <span style="color: var(--danger);">*</span>
                        </label>
                        <div style="background: #F8FAFC; border: 1.5px dashed var(--border); border-radius: var(--radius-sm); padding: 14px; text-align: center;" id="cvDropzoneAnggota">
                            <i class="bi bi-file-earmark-pdf" style="font-size: 1.8rem; color: var(--primary); display: block; margin-bottom: 4px;"></i>
                            <div style="font-size: 0.82rem; font-weight: 600; color: var(--text-main); margin-bottom: 2px;">
                                Pilih file CV dalam format PDF
                            </div>
                            <div style="font-size: 0.72rem; color: var(--text-muted); margin-bottom: 8px;">
                                Format: <strong>.PDF</strong> &bull; Ukuran maksimal: <strong>2 MB</strong>
                            </div>
                            <input type="file"
                                   name="cv"
                                   id="cvAnggotaInput"
                                   class="form-control @error('cv') is-invalid @enderror"
                                   accept="application/pdf,.pdf"
                                   required
                                   style="font-size: 0.82rem;"
                                   onchange="handleCvFileChange(this, 'cvAnggotaPreview', 'cvAnggotaError', 'cvAnggotaFileName')">
                            <div id="cvAnggotaPreview" style="display: none; margin-top: 8px; font-size: 0.78rem; color: var(--success); font-weight: 600;">
                                <i class="bi bi-check-circle-fill"></i> <span id="cvAnggotaFileName"></span>
                            </div>
                            <div id="cvAnggotaError" style="display: none; margin-top: 6px; font-size: 0.78rem; color: var(--danger); font-weight: 600;"></div>
                        </div>
                        @error('cv')
                            <div style="font-size: 0.8rem; color: var(--danger); margin-top: 5px; font-weight: 600;">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </div>
                        @enderror
                        <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 4px;">
                            CV Anda akan ditinjau langsung oleh Ketua Tim untuk mempertimbangkan penerimaan.
                        </div>
                    </div>

                    <!-- Catatan / Pesan untuk Ketua Tim -->
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Catatan Singkat untuk Ketua Tim (Opsional)</label>
                        <textarea name="catatan" rows="3" class="form-control" placeholder="Tuliskan pengalaman relevan, portofolio singkat, atau motivasi Anda bergabung...">{{ old('catatan') }}</textarea>
                    </div>
                </div>

                <div class="modal-footer-custom">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="closeModalGabungTim()">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="btnSubmitGabungTim">
                        <i class="bi bi-send-fill"></i> Kirim Pengajuan Gabung
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    function openModalGabungTim(idTim, namaTim, namaLomba, sisaKuota) {
        const modal = document.getElementById('modalGabungTim');
        const form = document.getElementById('formGabungTim');
        const namaTimText = document.getElementById('modalNamaTimText');
        const namaLombaText = document.getElementById('modalNamaLombaText');
        const sisaKuotaBadge = document.getElementById('modalSisaKuotaBadge');

        if (modal && form) {
            form.action = '/tim/' + idTim + '/gabung';
            if (namaTimText) namaTimText.textContent = namaTim;
            if (namaLombaText) namaLombaText.textContent = "Kompetisi: " + namaLomba;
            if (sisaKuotaBadge) sisaKuotaBadge.textContent = "Tersedia " + sisaKuota + " slot anggota";
            modal.style.display = 'flex';
        }
    }

    function closeModalGabungTim() {
        const modal = document.getElementById('modalGabungTim');
        if (modal) modal.style.display = 'none';
    }

    window.addEventListener('click', function(e) {
        const modal = document.getElementById('modalGabungTim');
        if (e.target === modal) closeModalGabungTim();
    });

    function handleCvFileChange(input, previewId, errorId, fileNameId) {
        const preview = document.getElementById(previewId);
        const error = document.getElementById(errorId);
        const fileNameSpan = document.getElementById(fileNameId);

        if (!input.files || input.files.length === 0) {
            if (preview) preview.style.display = 'none';
            if (error) error.style.display = 'none';
            return;
        }

        const file = input.files[0];
        const maxSizeBytes = 2 * 1024 * 1024; // 2MB

        // Validasi format PDF
        const isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
        if (!isPdf) {
            if (preview) preview.style.display = 'none';
            if (error) {
                error.textContent = 'Format file tidak sesuai! Wajib mengunggah file berekstensi .PDF.';
                error.style.display = 'block';
            }
            input.value = '';
            return;
        }

        // Validasi ukuran maksimal 2MB
        if (file.size > maxSizeBytes) {
            if (preview) preview.style.display = 'none';
            if (error) {
                const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
                error.textContent = `Ukuran file terlalu besar (${sizeMb} MB)! Batas maksimal adalah 2 MB.`;
                error.style.display = 'block';
            }
            input.value = '';
            return;
        }

        // File valid
        if (error) error.style.display = 'none';
        if (preview && fileNameSpan) {
            const sizeKb = (file.size / 1024).toFixed(1);
            fileNameSpan.textContent = `${file.name} (${sizeKb} KB) - File PDF valid`;
            preview.style.display = 'block';
        }
    }

    @if($errors->any() && session('modal_tim_id'))
        document.addEventListener('DOMContentLoaded', function() {
            const timId = '{{ session('modal_tim_id') }}';
            const form = document.getElementById('formGabungTim');
            const modal = document.getElementById('modalGabungTim');
            if (form && timId) {
                form.action = '/tim/' + timId + '/gabung';
            }
            if (modal) {
                modal.style.display = 'flex';
            }
        });
    @endif
</script>
@endpush
