@extends('layouts.app')

@section('title', $lomba->nama_lomba . ' - Detail Kompetisi')

@push('styles')
<style>
    .breadcrumb-nav {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.85rem;
        color: var(--text-muted);
        margin-bottom: 20px;
    }
    .breadcrumb-nav a {
        color: var(--primary);
        text-decoration: none;
        font-weight: 600;
    }
    .breadcrumb-nav a:hover {
        text-decoration: underline;
    }

    .detail-layout {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 28px;
        align-items: start;
        margin-bottom: 40px;
    }
    @media (max-width: 900px) {
        .detail-layout {
            grid-template-columns: 1fr;
        }
    }

    .detail-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 28px;
        box-shadow: var(--shadow-sm);
        margin-bottom: 24px;
    }

    .section-title {
        font-size: 1.15rem;
        font-weight: 800;
        color: var(--primary-dark);
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
        border-bottom: 1px solid var(--border);
        padding-bottom: 8px;
    }

    .info-list {
        list-style: none;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .info-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.88rem;
        border-bottom: 1px dashed var(--border);
        padding-bottom: 8px;
    }
    .info-label {
        color: var(--text-muted);
        font-weight: 500;
    }
    .info-value {
        font-weight: 700;
        color: var(--text-main);
    }

    .team-badge-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #F8FAFC;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 10px 14px;
        margin-bottom: 8px;
        font-size: 0.85rem;
    }
</style>
@endpush

@section('content')
    <!-- Breadcrumb Nav -->
    <div class="breadcrumb-nav">
        <a href="{{ route('dashboard.analitik') }}"><i class="bi bi-house-door"></i> Beranda</a>
        <span>/</span>
        <a href="{{ route('lomba.index') }}">Katalog Lomba</a>
        <span>/</span>
        <span style="color: var(--text-main); font-weight: 600;">{{ Str::limit($lomba->nama_lomba, 40) }}</span>
    </div>

    <div class="detail-layout">
        <!-- Kolom Kiri: Rincian Lengkap Lomba -->
        <div>
            <div class="detail-card">
                @if($lomba->poster_url)
                    <div style="margin-bottom: 20px; border-radius: var(--radius-sm); overflow: hidden; background: #F8FAFC; border: 1px solid var(--border); text-align: center; padding: 12px;">
                        <img src="{{ $lomba->poster_url }}" alt="Poster {{ $lomba->nama_lomba }}" style="max-height: 400px; max-width: 100%; border-radius: var(--radius-sm); object-fit: contain;">
                    </div>
                @endif

                <!-- Header Lomba -->
                <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 14px;">
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
                        <i class="bi bi-geo-alt-fill"></i> Tingkat {{ $lomba->tingkat }}
                    </span>

                    @if(!empty($lomba->tipe_peserta))
                        <span class="badge" style="background: #F1F5F9; color: #475569; border: 1px solid #CBD5E1; font-size: 0.75rem;">
                            <i class="bi bi-person-fill"></i> {{ $lomba->tipe_peserta }}
                        </span>
                    @endif

                    @if(!empty($lomba->mode_pelaksanaan))
                        <span class="badge" style="background: #E0F2FE; color: #0369A1; font-size: 0.75rem;">
                            <i class="bi bi-broadcast"></i> {{ $lomba->mode_pelaksanaan_label ?? $lomba->mode_pelaksanaan }}
                        </span>
                    @endif

                    @if($lomba->tenggat)
                        @if($lomba->tenggat->isPast())
                            <span class="badge badge-danger">Telah Berakhir</span>
                        @elseif($lomba->sisa_hari <= 7)
                            <span class="badge badge-danger">
                                <i class="bi bi-clock-history"></i> Sisa {{ $lomba->sisa_hari }} Hari
                            </span>
                        @else
                            <span class="badge badge-success">
                                <i class="bi bi-check2-circle"></i> Pendaftaran Aktif
                            </span>
                        @endif
                    @endif
                </div>

                <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--primary-dark); line-height: 1.35; margin-bottom: 10px;">
                    {{ $lomba->nama_lomba }}
                </h1>

                <div style="display: flex; align-items: center; gap: 18px; font-size: 0.9rem; color: var(--text-muted); margin-bottom: 24px; flex-wrap: wrap;">
                    <span><i class="bi bi-building"></i> {{ $lomba->penyelenggara }}</span>
                    @if($lomba->pengaju)
                        <span><i class="bi bi-person-check"></i> Diajukan oleh: {{ $lomba->pengaju->nama }}</span>
                    @endif
                </div>

                <!-- Deskripsi Lomba -->
                <div style="margin-bottom: 26px;">
                    <h3 class="section-title">
                        <i class="bi bi-file-text"></i> Deskripsi Kompetisi
                    </h3>
                    <div style="font-size: 0.92rem; line-height: 1.7; color: #334155; white-space: pre-line;">
                        {{ $lomba->deskripsi ?: 'Belum ada deskripsi tambahan yang dicantumkan untuk kompetisi ini.' }}
                    </div>
                </div>

                <!-- Persyaratan Peserta -->
                <div style="margin-bottom: 26px;">
                    <h3 class="section-title">
                        <i class="bi bi-list-check"></i> Persyaratan & Ketentuan Delegasi
                    </h3>
                    <div style="font-size: 0.92rem; line-height: 1.7; color: #334155; white-space: pre-line;">
                        {{ $lomba->persyaratan ?: '1. Mahasiswa aktif Jurusan Teknologi Informasi Politeknik Negeri Malang.\n2. Mematuhi panduan resmi dan etika kompetisi yang ditetapkan oleh penyelenggara.' }}
                    </div>
                </div>

                <!-- Benefit & Penghargaan -->
                @if($lomba->benefit)
                    <div style="margin-bottom: 26px;">
                        <h3 class="section-title">
                            <i class="bi bi-award-fill" style="color: #F59E0B;"></i> Benefit & Penghargaan
                        </h3>
                        <div style="font-size: 0.92rem; line-height: 1.7; color: #334155; white-space: pre-line;">
                            {{ $lomba->benefit }}
                        </div>
                    </div>
                @endif
            </div>

            <!-- Tim Delegasi JTI yang Mengikuti Lomba Ini -->
            <div class="detail-card">
                <h3 class="section-title">
                    <i class="bi bi-people"></i> Delegasi Tim JTI Terdaftar ({{ $timList->count() }})
                </h3>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 16px;">
                    Tim mahasiswa yang telah resmi disetujui untuk mewakili jurusan pada kompetisi ini:
                </p>

                @forelse($timList as $tim)
                    <div class="team-badge-item">
                        <div>
                            <div style="font-weight: 700; color: var(--primary-dark);">
                                {{ $tim->nama_tim }}
                            </div>
                            <div style="font-size: 0.78rem; color: var(--text-muted);">
                                Ketua: {{ $tim->ketua ? $tim->ketua->nama : 'N/A' }} &bull; Anggota: {{ $tim->anggotaDiterima->count() }} / {{ $tim->kuota_anggota }} Orang
                                @if($tim->sisa_kuota_anggota > 0)
                                    &bull; <span style="color: var(--success); font-weight: 600;">Sisa {{ $tim->sisa_kuota_anggota }} slot</span>
                                @endif
                            </div>
                        </div>
                        <div style="display: flex; gap: 6px; align-items: center;">
                            @if(auth()->check() && (auth()->user()->role === \App\Enums\UserRole::Mahasiswa || auth()->user()->role === 'mahasiswa'))
                                @if($tim->nim === (auth()->user()->nim_nip ?? (string)auth()->user()->id))
                                    <span class="badge badge-primary">Ketua Tim</span>
                                @elseif($tim->anggotaDiterima->contains('nim', auth()->user()->nim_nip ?? (string)auth()->user()->id))
                                    <span class="badge badge-success">Terdaftar</span>
                                @elseif($tim->sisa_kuota_anggota > 0)
                                    <a href="{{ route('tim.gabung.form', $tim->id_tim) }}" class="btn btn-outline-primary btn-sm" style="font-size: 0.72rem; padding: 2px 8px;">
                                        <i class="bi bi-person-plus-fill"></i> Gabung Tim
                                    </a>
                                @endif
                            @endif
                            <span class="badge badge-success">Disetujui Jurusan</span>
                        </div>
                    </div>
                @empty
                    <div style="background: #F8FAFC; border: 1px dashed var(--border); border-radius: var(--radius-sm); padding: 18px; text-align: center; color: var(--text-muted); font-size: 0.88rem;">
                        Belum ada tim yang terdaftar. Jadilah tim delegasi pertama dari JTI untuk kompetisi ini!
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Kolom Kanan / Sidebar: Info Kunci & Tindakan -->
        <div>
            <!-- Box Ringkasan Pendaftaran -->
            <div class="detail-card">
                <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--primary-dark); margin-bottom: 16px;">
                    Informasi Pendaftaran
                </h3>

                <ul class="info-list">
                    <li class="info-item">
                        <span class="info-label">Batas Pendaftaran</span>
                        <span class="info-value">
                            {{ $lomba->tenggat ? $lomba->tenggat->format('d F Y') : 'Segera' }}
                        </span>
                    </li>
                    <li class="info-item">
                        <span class="info-label">Sisa Waktu</span>
                        <span class="info-value" style="color: {{ $lomba->sisa_hari <= 7 ? 'var(--danger)' : 'var(--primary)' }};">
                            {{ $lomba->is_buka ? $lomba->sisa_hari . ' Hari Lagi' : 'Pendaftaran Tutup' }}
                        </span>
                    </li>
                    <li class="info-item">
                        <span class="info-label">Biaya Pendaftaran</span>
                        <span class="info-value" style="color: {{ $lomba->biaya == 0 ? 'var(--success)' : 'var(--primary-dark)' }};">
                            {{ $lomba->formatted_biaya }}
                        </span>
                    </li>
                    <li class="info-item">
                        <span class="info-label">Tingkat Kompetisi</span>
                        <span class="info-value">{{ $lomba->tingkat }}</span>
                    </li>
                    <li class="info-item">
                        <span class="info-label">Kategori Lomba</span>
                        <span class="info-value">{{ $lomba->kategori }}</span>
                    </li>
                    <li class="info-item">
                        <span class="info-label">Tipe Peserta</span>
                        <span class="info-value">{{ $lomba->tipe_peserta ?? 'Kelompok' }}</span>
                    </li>
                    <li class="info-item">
                        <span class="info-label">Mode Pelaksanaan</span>
                        <span class="info-value">{{ $lomba->mode_pelaksanaan_label ?? ($lomba->mode_pelaksanaan ?? 'Online') }}</span>
                    </li>
                    @if($lomba->buku_pedoman_path)
                        <li class="info-item">
                            <span class="info-label">Buku Pedoman</span>
                            <span class="info-value">
                                <a href="{{ route('lomba.pedoman.download', $lomba->id_lomba) }}" target="_blank" style="color: var(--primary); font-weight: 600; text-decoration: underline; font-size: 0.82rem;">
                                    <i class="bi bi-file-earmark-arrow-down-fill"></i> Unduh File
                                </a>
                            </span>
                        </li>
                    @endif
                </ul>

                <!-- Tombol Aksi Utama -->
                <div style="margin-top: 22px; display: flex; flex-direction: column; gap: 10px;">
                    @if($lomba->buku_pedoman_path)
                        <a href="{{ route('lomba.pedoman.download', $lomba->id_lomba) }}"
                           target="_blank"
                           class="btn btn-outline-secondary"
                           style="width: 100%; font-weight: 700;">
                            <i class="bi bi-file-earmark-arrow-down-fill" style="color: #0284C7;"></i> Unduh Buku Pedoman
                        </a>
                    @endif

                    @if($lomba->tautan_daftar)
                        <a href="{{ $lomba->tautan_daftar }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="btn btn-outline-primary"
                           style="width: 100%; font-weight: 700;">
                            <i class="bi bi-box-arrow-up-right"></i> Buka Website Resmi Lomba
                        </a>
                    @endif

                    <a href="{{ route('tim.create', ['lomba_id' => $lomba->id_lomba]) }}"
                       class="btn btn-primary"
                       style="width: 100%; font-weight: 700;">
                        <i class="bi bi-flag-fill"></i> Bentuk Tim Baru (Ketua Tim)
                    </a>

                    <a href="{{ route('tim.index', ['q' => $lomba->nama_lomba]) }}"
                       class="btn btn-outline-primary btn-sm"
                       style="width: 100%; font-weight: 600; text-align: center;">
                        <i class="bi bi-people-fill"></i> Cari Tim di Modul Tim
                    </a>

                    <a href="{{ route('lomba.index') }}"
                       class="btn btn-secondary btn-sm"
                       style="width: 100%; text-align: center;">
                        <i class="bi bi-arrow-left"></i> Kembali ke Katalog
                    </a>
                </div>
            </div>

            <!-- Card Bantuan Pembimbingan -->
            <div class="detail-card" style="background: linear-gradient(180deg, #EFF6FF 0%, #FFFFFF 100%); border-color: #BFDBFE;">
                <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--primary); margin-bottom: 8px;">
                    <i class="bi bi-lightbulb-fill"></i> Butuh Pendampingan Dosen?
                </h4>
                <p style="font-size: 0.82rem; color: #475569; line-height: 1.5; margin-bottom: 12px;">
                    Setelah membentuk tim, Anda dapat mengajukan Dosen Pembimbing terakreditasi JTI Polinema melalui <strong>Modul Bimbingan</strong> untuk bimbingan proposal dan prototype.
                </p>
                <a href="{{ route('bimbingan.index') }}" style="font-size: 0.8rem; font-weight: 700; color: var(--primary); text-decoration: underline;">
                    Akses Modul Bimbingan &rarr;
                </a>
            </div>
        </div>
    </div>
@endsection
