@extends('layouts.app')

@section('title', !empty($isLimitedView) ? 'Profil Pelamar Tim - RuangLombaJTI' : 'Profil Saya - RuangLombaJTI')

@push('styles')
<style>
    .profil-layout {
        display: grid;
        grid-template-columns: 360px 1fr;
        gap: 24px;
        align-items: start;
    }
    @media (max-width: 992px) {
        .profil-layout {
            grid-template-columns: 1fr;
        }
    }
    .profil-section-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-sm);
        margin-bottom: 24px;
        overflow: hidden;
    }
    .profil-section-header {
        padding: 18px 24px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
        background: #F8FAFC;
    }
    .profil-section-title {
        font-size: 1.02rem;
        font-weight: 700;
        color: var(--primary-dark);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .profil-section-body {
        padding: 24px;
    }

    /* Data Akun List */
    .account-avatar {
        width: 68px;
        height: 68px;
        border-radius: 50%;
        background: linear-gradient(135deg, #1E40AF 0%, #3B82F6 100%);
        color: #fff;
        font-size: 1.8rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 12px;
        box-shadow: 0 6px 14px rgba(30, 64, 175, 0.25);
    }
    .account-info-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-top: 18px;
    }
    .account-info-item {
        background: #F8FAFC;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 11px 14px;
    }
    .account-info-label {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        color: var(--text-muted);
        margin-bottom: 2px;
    }
    .account-info-value {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--text-main);
        word-break: break-word;
    }

    /* Portofolio Chips & Rows */
    .portfolio-chips-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 18px;
    }
    .portfolio-chip-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        background: var(--primary-soft);
        color: var(--primary);
        border: 1px solid #BFDBFE;
        border-radius: var(--radius-full);
        font-size: 0.84rem;
        font-weight: 700;
        transition: all 0.2s ease;
        max-width: 100%;
    }
    .portfolio-chip-link:hover {
        background: var(--primary);
        color: #fff;
        border-color: var(--primary);
        transform: translateY(-1px);
    }
    .portfolio-row {
        display: grid;
        grid-template-columns: 180px 1fr auto;
        gap: 10px;
        align-items: start;
        background: #F8FAFC;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 12px;
        margin-bottom: 10px;
    }
    @media (max-width: 640px) {
        .portfolio-row {
            grid-template-columns: 1fr;
        }
        .profil-section-body {
            padding: 18px;
        }
    }

    /* Ringkasan Aktivitas */
    .activity-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .activity-item {
        background: #F8FAFC;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 14px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }
    .empty-box {
        background: #F8FAFC;
        border: 1px dashed #CBD5E1;
        border-radius: var(--radius-sm);
        padding: 20px;
        text-align: center;
        color: var(--text-muted);
        font-size: 0.86rem;
    }
</style>
@endpush

@section('content')
@if(!empty($isLimitedView))
    {{-- ========================================================================= --}}
    {{-- MODE PROFIL TERBATAS UNTUK KETUA TIM (/tim/permintaan/{permintaan}/profil) --}}
    {{-- Read-only: hanya nama, prodi, angkatan, portofolio, posisi dilamar,       --}}
    {{-- pesan pelamar, dan CV pada permintaan gabung. Tanpa NIM & tanpa email.    --}}
    {{-- ========================================================================= --}}
    <div style="margin-bottom: 18px;">
        <a href="{{ route('bimbingan.index', ['tim_id' => $tim->id_tim]) }}"
           style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">
            <i class="bi bi-arrow-left"></i> Kembali ke Dashboard Tim
        </a>
    </div>

    <div class="dashboard-header">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 20px;">
            <div>
                <div class="dashboard-header-badge">
                    <i class="bi bi-person-lines-fill"></i> Profil Terbatas Pelamar Tim
                </div>
                <h1 style="font-size: 1.85rem; font-weight: 800; letter-spacing: -0.5px; margin-bottom: 8px;">
                    {{ $pelamar->nama }}
                </h1>
                <p style="font-size: 0.94rem; opacity: 0.9; max-width: 650px; line-height: 1.6;">
                    Tinjauan kualifikasi pelamar untuk tim <strong>{{ $tim->nama_tim }}</strong> ({{ $tim->lomba->nama_lomba ?? 'Kompetisi JTI' }}).
                </p>
            </div>
            <div class="dashboard-header-user">
                <div style="font-size: 0.8rem; opacity: 0.85;">Status Permintaan</div>
                <div style="font-size: 1.05rem; font-weight: 800;">
                    {{ $permintaan->status_gabung === 'diterima' ? 'Anggota Tim' : 'Menunggu Persetujuan' }}
                </div>
                <div style="margin-top: 6px; display: flex; gap: 8px; justify-content: flex-end;">
                    <span class="badge badge-primary" style="font-size: 0.72rem; padding: 3px 10px;">
                        Hanya Lihat
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="profil-layout">
        <!-- Kolom Kiri: Identitas Akademik Pelamar (Tanpa NIM & Tanpa Email) -->
        <div>
            <div class="profil-section-card">
                <div class="profil-section-header">
                    <div class="profil-section-title">
                        <i class="bi bi-person-vcard-fill" style="color: var(--primary);"></i> Profil Pelamar
                    </div>
                    <span class="badge badge-gray" style="font-size: 0.7rem;">
                        <i class="bi bi-lock-fill"></i> Hanya Lihat
                    </span>
                </div>
                <div class="profil-section-body">
                    <div style="text-align: center;">
                        <div class="account-avatar">
                            {{ strtoupper(substr($pelamar->nama, 0, 1)) }}
                        </div>
                        <div style="font-size: 1.08rem; font-weight: 800; color: var(--primary-dark);">
                            {{ $pelamar->nama }}
                        </div>
                    </div>

                    <div class="account-info-list">
                        <div class="account-info-item">
                            <div class="account-info-label">Nama Lengkap</div>
                            <div class="account-info-value">{{ $pelamar->nama }}</div>
                        </div>
                        <div class="account-info-item">
                            <div class="account-info-label">Program Studi</div>
                            <div class="account-info-value">{{ $pelamar->prodi }}</div>
                        </div>
                        <div class="account-info-item">
                            <div class="account-info-label">Angkatan</div>
                            <div class="account-info-value">{{ $pelamar->angkatan }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Rincian Lamaran, Portofolio, & CV Permintaan Gabung -->
        <div>
            <!-- Detail Lamaran (Posisi yang Dilamar & Pesan Pelamar) -->
            <div class="profil-section-card">
                <div class="profil-section-header">
                    <div class="profil-section-title">
                        <i class="bi bi-briefcase-fill" style="color: var(--primary);"></i> Rincian Permintaan Gabung
                    </div>
                </div>
                <div class="profil-section-body">
                    <div class="account-info-item" style="margin-bottom: 14px;">
                        <div class="account-info-label">Posisi / Bidang yang Dilamar</div>
                        <div class="account-info-value">
                            <span class="badge badge-purple" style="font-size: 0.82rem; padding: 5px 12px;">
                                {{ $posisiDilamar }}
                            </span>
                        </div>
                    </div>

                    <div class="account-info-item">
                        <div class="account-info-label">Pesan Singkat untuk Ketua</div>
                        <div class="account-info-value" style="font-weight: 500; line-height: 1.6;">
                            @if(!empty($pesanPelamar))
                                {{ $pesanPelamar }}
                            @else
                                <span style="color: var(--text-muted); font-style: italic;">Tidak ada pesan tambahan dari pelamar.</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Portofolio Pelamar -->
            <div class="profil-section-card">
                <div class="profil-section-header">
                    <div class="profil-section-title">
                        <i class="bi bi-link-45deg" style="color: var(--primary);"></i> Portofolio Pelamar
                    </div>
                </div>
                <div class="profil-section-body">
                    @if(!empty($portofolio))
                        <div class="portfolio-chips-wrap" style="margin-bottom: 0;">
                            @foreach($portofolio as $item)
                                <a href="{{ $item['url'] }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="portfolio-chip-link">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                    <span>{{ !empty($item['label']) ? $item['label'] : $item['url'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-box">
                            <i class="bi bi-folder2-open" style="font-size: 1.3rem; display: block; margin-bottom: 4px; color: var(--primary);"></i>
                            Belum ada portofolio yang dicantumkan oleh pelamar.
                        </div>
                    @endif
                </div>
            </div>

            <!-- CV pada Permintaan Gabung -->
            <div class="profil-section-card">
                <div class="profil-section-header">
                    <div class="profil-section-title">
                        <i class="bi bi-file-earmark-pdf-fill" style="color: var(--primary);"></i> CV Permintaan Gabung
                    </div>
                    @if($permintaan->file_cv)
                        <a href="{{ route('tim.cv', ['type' => 'anggota', 'id' => $permintaan->id_anggota]) }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="btn btn-primary btn-sm">
                            <i class="bi bi-file-earmark-pdf"></i> Lihat CV di Tab Baru
                        </a>
                    @endif
                </div>
                <div class="profil-section-body">
                    @if($permintaan->file_cv)
                        <div style="border: 1px solid var(--border); border-radius: var(--radius-sm); overflow: hidden; height: 460px; background: #F8FAFC;">
                            <iframe src="{{ route('tim.cv', ['type' => 'anggota', 'id' => $permintaan->id_anggota]) }}"
                                    title="CV Pelamar {{ $pelamar->nama }}"
                                    style="width: 100%; height: 100%; border: none;"></iframe>
                        </div>
                    @else
                        <div class="empty-box">
                            Berkas CV tidak tersedia pada permintaan gabung ini.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@else
    {{-- ========================================================================= --}}
    {{-- MODE PROFIL SAYA (/profil) - KHUSUS MAHASISWA & KETUA TIM PEMILIK AKUN    --}}
    {{-- ========================================================================= --}}
    <div class="dashboard-header">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 20px;">
            <div>
                <div class="dashboard-header-badge">
                    <i class="bi bi-person-circle"></i> Sistem Informasi Manajemen Prestasi JTI
                </div>
                <h1 style="font-size: 1.9rem; font-weight: 800; letter-spacing: -0.5px; margin-bottom: 8px;">
                    Profil Saya
                </h1>
                <p style="font-size: 0.95rem; opacity: 0.9; max-width: 650px; line-height: 1.6;">
                    @if(($role ?? '') === 'dosen')
                        Kelola data profil dosen pembimbing, batas kuota bimbingan, serta pengaturan keamanan kata sandi akun Anda.
                    @elseif(($role ?? '') === 'admin')
                        Kelola informasi akun Administrator RuangLombaJTI serta pengaturan keamanan kata sandi akun Anda.
                    @else
                        Kelola tautan portofolio karya, pantau ringkasan keikutsertaan kompetisi, serta pengaturan keamanan kata sandi akun Anda.
                    @endif
                </p>
            </div>
            <div class="dashboard-header-user">
                <div style="font-size: 0.8rem; opacity: 0.85;">
                    @if(($role ?? '') === 'dosen')
                        Akun Dosen Pembimbing
                    @elseif(($role ?? '') === 'admin')
                        Akun Administrator
                    @else
                        Akun Mahasiswa Aktif
                    @endif
                </div>
                <div style="font-size: 1.1rem; font-weight: 800;">
                    @if(($role ?? '') === 'dosen')
                        {{ $dosen->nama }}
                    @elseif(($role ?? '') === 'admin')
                        {{ $admin->nama }}
                    @else
                        {{ $mahasiswa->nama }}
                    @endif
                </div>
                <div style="margin-top: 6px; display: flex; gap: 8px; justify-content: flex-end;">
                    @if(($role ?? '') === 'dosen')
                        <span class="badge badge-warning" style="font-size: 0.72rem; padding: 3px 10px;">
                            {{ $roleLabel }} (NIP: {{ $dosen->nip }})
                        </span>
                    @elseif(($role ?? '') === 'admin')
                        <span class="badge badge-danger" style="font-size: 0.72rem; padding: 3px 10px;">
                            {{ $roleLabel }} (ID: {{ $admin->id_admin }})
                        </span>
                    @else
                        <span class="badge badge-primary" style="font-size: 0.72rem; padding: 3px 10px;">
                            {{ $roleLabel }} ({{ $mahasiswa->nim }})
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="profil-layout">
        <!-- KOLOM KIRI: 1. Data Akun (Hanya Lihat) -->
        <div>
            <div class="profil-section-card">
                <div class="profil-section-header">
                    <div class="profil-section-title">
                        <i class="bi bi-person-vcard-fill" style="color: var(--primary);"></i> Data Akun
                    </div>
                    <span class="badge badge-gray" style="font-size: 0.7rem;">
                        <i class="bi bi-lock-fill"></i> Hanya Lihat
                    </span>
                </div>
                <div class="profil-section-body">
                    @if(($role ?? '') === 'dosen')
                        <div style="text-align: center;">
                            <div class="account-avatar">
                                {{ strtoupper(substr($dosen->nama, 0, 1)) }}
                            </div>
                            <div style="font-size: 1.08rem; font-weight: 800; color: var(--primary-dark);">
                                {{ $dosen->nama }}
                            </div>
                            <div style="margin-top: 6px;">
                                <span class="badge badge-warning" style="font-size: 0.76rem; padding: 4px 12px;">
                                    <i class="bi bi-patch-check-fill"></i> {{ $roleLabel }}
                                </span>
                            </div>
                        </div>

                        <div class="account-info-list">
                            <div class="account-info-item">
                                <div class="account-info-label">Nama Lengkap</div>
                                <div class="account-info-value">{{ $dosen->nama }}</div>
                            </div>
                            <div class="account-info-item">
                                <div class="account-info-label">NIP</div>
                                <div class="account-info-value">{{ $dosen->nip }}</div>
                            </div>
                            <div class="account-info-item">
                                <div class="account-info-label">Email Kampus</div>
                                <div class="account-info-value">{{ $dosen->email_kampus ?? $dosen->email ?? '-' }}</div>
                            </div>
                            <div class="account-info-item">
                                <div class="account-info-label">Bidang Keahlian</div>
                                <div class="account-info-value">{{ $dosen->bidang_keahlian ?: 'Belum diatur' }}</div>
                            </div>
                            <div class="account-info-item">
                                <div class="account-info-label">Batas Kuota Bimbingan</div>
                                <div class="account-info-value">{{ $dosen->kuota_bimbingan ?? 10 }} Tim (Sisa: {{ $dosen->sisa_kuota ?? 10 }})</div>
                            </div>
                            <div class="account-info-item">
                                <div class="account-info-label">Peran Akun</div>
                                <div class="account-info-value">
                                    <span class="badge badge-warning">{{ $roleLabel }}</span>
                                </div>
                            </div>
                        </div>
                    @elseif(($role ?? '') === 'admin')
                        <div style="text-align: center;">
                            <div class="account-avatar" style="background: linear-gradient(135deg, #DC2626 0%, #EF4444 100%);">
                                {{ strtoupper(substr($admin->nama, 0, 1)) }}
                            </div>
                            <div style="font-size: 1.08rem; font-weight: 800; color: var(--primary-dark);">
                                {{ $admin->nama }}
                            </div>
                            <div style="margin-top: 6px;">
                                <span class="badge badge-danger" style="font-size: 0.76rem; padding: 4px 12px;">
                                    <i class="bi bi-shield-lock-fill"></i> {{ $roleLabel }}
                                </span>
                            </div>
                        </div>

                        <div class="account-info-list">
                            <div class="account-info-item">
                                <div class="account-info-label">Nama Administrator</div>
                                <div class="account-info-value">{{ $admin->nama }}</div>
                            </div>
                            <div class="account-info-item">
                                <div class="account-info-label">ID Administrator</div>
                                <div class="account-info-value">{{ $admin->id_admin }}</div>
                            </div>
                            <div class="account-info-item">
                                <div class="account-info-label">Email Administrator</div>
                                <div class="account-info-value">{{ $admin->email }}</div>
                            </div>
                            <div class="account-info-item">
                                <div class="account-info-label">Peran Akun</div>
                                <div class="account-info-value">
                                    <span class="badge badge-danger">{{ $roleLabel }}</span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div style="text-align: center;">
                            <div class="account-avatar">
                                {{ strtoupper(substr($mahasiswa->nama, 0, 1)) }}
                            </div>
                            <div style="font-size: 1.08rem; font-weight: 800; color: var(--primary-dark);">
                                {{ $mahasiswa->nama }}
                            </div>
                            <div style="margin-top: 6px;">
                                <span class="badge badge-primary" style="font-size: 0.76rem; padding: 4px 12px;">
                                    <i class="bi bi-patch-check-fill"></i> {{ $roleLabel }}
                                </span>
                            </div>
                        </div>

                        <div class="account-info-list">
                            <div class="account-info-item">
                                <div class="account-info-label">Nama Lengkap</div>
                                <div class="account-info-value">{{ $mahasiswa->nama }}</div>
                            </div>
                            <div class="account-info-item">
                                <div class="account-info-label">NIM</div>
                                <div class="account-info-value">{{ $mahasiswa->nim }}</div>
                            </div>
                            <div class="account-info-item">
                                <div class="account-info-label">Program Studi</div>
                                <div class="account-info-value">{{ $mahasiswa->prodi }}</div>
                            </div>
                            <div class="account-info-item">
                                <div class="account-info-label">Angkatan</div>
                                <div class="account-info-value">{{ $mahasiswa->angkatan }}</div>
                            </div>
                            <div class="account-info-item">
                                <div class="account-info-label">Email Kampus</div>
                                <div class="account-info-value">{{ $mahasiswa->email_kampus }}</div>
                            </div>
                            <div class="account-info-item">
                                <div class="account-info-label">Peran Akun</div>
                                <div class="account-info-value">
                                    <span class="badge badge-primary">{{ $roleLabel }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div style="margin-top: 14px; font-size: 0.76rem; color: var(--text-muted); line-height: 1.5;">
                        <i class="bi bi-info-circle"></i> Data identitas disinkronkan dari pangkalan data Jurusan Teknologi Informasi dan tidak dapat diubah secara mandiri.
                    </div>
                </div>
            </div>
        </div>

        <!-- KOLOM KANAN: Fitur Berdasarkan Role & Ganti Kata Sandi -->
        <div>
            @if(($role ?? '') === 'dosen')
                <!-- Form Pengaturan Keahlian & Kuota Dosen -->
                <div class="profil-section-card">
                    <div class="profil-section-header">
                        <div>
                            <div class="profil-section-title">
                                <i class="bi bi-sliders" style="color: var(--primary);"></i> Pengaturan Keahlian &amp; Kuota Bimbingan
                            </div>
                            <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 2px;">
                                Bidang keahlian digunakan oleh Sistem Rekomendasi AI untuk mencocokkan tim lomba mahasiswa.
                            </div>
                        </div>
                    </div>
                    <div class="profil-section-body">
                        <form action="{{ route('profil.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="form-group" style="margin-bottom: 18px;">
                                <label class="form-label" style="font-weight: 700;">
                                    Bidang Keahlian / Kompetensi Utama <span style="color: var(--danger);">*</span>
                                </label>
                                <textarea name="bidang_keahlian" rows="3" class="form-control @error('bidang_keahlian') is-invalid @enderror" placeholder="Contoh: Web Development, Machine Learning, UI/UX, Cloud Computing" required>{{ old('bidang_keahlian', $dosen->bidang_keahlian) }}</textarea>
                                @error('bidang_keahlian')
                                    <div style="color: var(--danger); font-size: 0.78rem; margin-top: 4px;">{{ $message }}</div>
                                @enderror
                                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">
                                    Pisahkan topik kompetensi dengan koma atau tanda sambung (&amp;).
                                </div>
                            </div>

                            <div class="form-group" style="margin-bottom: 20px;">
                                <label class="form-label" style="font-weight: 700;">
                                    Batas Maksimal Kuota Bimbingan (Tim) <span style="color: var(--danger);">*</span>
                                </label>
                                <input type="number" name="kuota_bimbingan" min="1" max="30" class="form-control @error('kuota_bimbingan') is-invalid @enderror" value="{{ old('kuota_bimbingan', $dosen->kuota_bimbingan ?? 10) }}" required style="max-width: 200px;">
                                @error('kuota_bimbingan')
                                    <div style="color: var(--danger); font-size: 0.78rem; margin-top: 4px;">{{ $message }}</div>
                                @enderror
                                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">
                                    Saat ini Anda membimbing <strong>{{ $bimbinganAktif->count() }} tim aktif</strong>.
                                </div>
                            </div>

                            <div style="display: flex; justify-content: flex-end;">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save-fill"></i> Simpan Pengaturan Dosen
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Ringkasan Tim Bimbingan Aktif -->
                <div class="profil-section-card">
                    <div class="profil-section-header">
                        <div class="profil-section-title">
                            <i class="bi bi-people-fill" style="color: var(--primary);"></i> Tim yang Sedang Dibimbing ({{ $bimbinganAktif->count() }})
                        </div>
                        <a href="{{ route('bimbingan.index') }}" class="btn btn-secondary btn-sm">
                            <i class="bi bi-box-arrow-up-right"></i> Modul Bimbingan
                        </a>
                    </div>
                    <div class="profil-section-body">
                        @if($bimbinganAktif->isEmpty())
                            <div class="empty-box">
                                <i class="bi bi-info-circle" style="font-size: 1.3rem; display: block; margin-bottom: 4px; color: var(--text-muted);"></i>
                                Belum ada tim bimbingan lomba yang aktif saat ini.
                            </div>
                        @else
                            <div class="activity-list">
                                @foreach($bimbinganAktif as $akt)
                                    <div class="activity-item">
                                        <div>
                                            <div style="font-weight: 700; color: var(--primary-dark); font-size: 0.92rem;">
                                                {{ $akt->tim->nama_tim ?? 'Tim' }}
                                            </div>
                                            <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 3px;">
                                                <i class="bi bi-trophy"></i> {{ $akt->tim->lomba->nama_lomba ?? 'Lomba' }}
                                            </div>
                                        </div>
                                        <span class="badge badge-success">Bimbingan Aktif</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @elseif(($role ?? '') === 'admin')
                <!-- Ringkasan Hak Akses Panel Administrator -->
                <div class="profil-section-card">
                    <div class="profil-section-header">
                        <div class="profil-section-title">
                            <i class="bi bi-shield-check" style="color: var(--primary);"></i> Hak Akses &amp; Wewenang Administrator
                        </div>
                        <a href="{{ route('admin.index') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-speedometer2"></i> Buka Panel Admin
                        </a>
                    </div>
                    <div class="profil-section-body">
                        <p style="font-size: 0.88rem; color: var(--text-muted); line-height: 1.6; margin-bottom: 16px;">
                            Sebagai Administrator RuangLombaJTI, akun Anda memiliki wewenang penuh dalam mengelola sistem kompetisi mahasiswa:
                        </p>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; margin-bottom: 16px;">
                            <div style="background: #F8FAFC; border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 12px;">
                                <div style="font-weight: 700; color: var(--primary-dark); font-size: 0.88rem; margin-bottom: 4px;">
                                    <i class="bi bi-check2-circle" style="color: var(--primary);"></i> Verifikasi Lomba
                                </div>
                                <div style="font-size: 0.78rem; color: var(--text-muted);">
                                    Memvalidasi dan mengesahkan info lomba yang diajukan oleh mahasiswa.
                                </div>
                            </div>
                            <div style="background: #F8FAFC; border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 12px;">
                                <div style="font-weight: 700; color: var(--primary-dark); font-size: 0.88rem; margin-bottom: 4px;">
                                    <i class="bi bi-people" style="color: var(--primary);"></i> Persetujuan Tim
                                </div>
                                <div style="font-size: 0.78rem; color: var(--text-muted);">
                                    Menyetujui atau menolak permohonan pembentukan tim baru delegasi JTI.
                                </div>
                            </div>
                            <div style="background: #F8FAFC; border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 12px;">
                                <div style="font-weight: 700; color: var(--primary-dark); font-size: 0.88rem; margin-bottom: 4px;">
                                    <i class="bi bi-database-gear" style="color: var(--primary);"></i> Kelola Master Data
                                </div>
                                <div style="font-size: 0.78rem; color: var(--text-muted);">
                                    Pengelolaan data dosen pembimbing, mahasiswa aktif, dan akun sistem.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- FORM: Portofolio (Mahasiswa) -->
                <form action="{{ route('profil.update') }}" method="POST" id="profilForm">
                    @csrf
                    @method('PUT')

                    <!-- Portofolio -->
                    <div class="profil-section-card">
                        <div class="profil-section-header">
                            <div>
                                <div class="profil-section-title">
                                    <i class="bi bi-link-45deg" style="color: var(--primary);"></i> Portofolio
                                </div>
                                <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 2px;">
                                    Maksimal 3 tautan karya (GitHub, Behance, Figma, Google Drive, atau situs pribadi)
                                </div>
                            </div>
                            <button type="button"
                                    class="btn btn-outline-primary btn-sm"
                                    id="btnAddPortfolioRow"
                                    onclick="addPortfolioRow()">
                                <i class="bi bi-plus-lg"></i> Tambah Tautan
                            </button>
                        </div>
                        <div class="profil-section-body">
                            <!-- Tampilan Chip/Tombol Portofolio Tersimpan -->
                            <div style="margin-bottom: 18px;">
                                <div style="font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted); margin-bottom: 8px;">
                                    Tautan Karya Aktif
                                </div>
                                @if(!empty($portofolio))
                                    <div class="portfolio-chips-wrap">
                                        @foreach($portofolio as $item)
                                            <a href="{{ $item['url'] }}"
                                               target="_blank"
                                               rel="noopener noreferrer"
                                               class="portfolio-chip-link">
                                                <i class="bi bi-box-arrow-up-right"></i>
                                                <span>{{ !empty($item['label']) ? $item['label'] : $item['url'] }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="empty-box" style="margin-bottom: 16px;">
                                        <i class="bi bi-folder2-open" style="font-size: 1.3rem; display: block; margin-bottom: 4px; color: var(--primary);"></i>
                                        Belum ada portofolio. Tambahkan tautan karya Anda.
                                    </div>
                                @endif
                            </div>

                            <!-- Form Baris Portofolio (Maksimal 3 Baris) -->
                            @php
                                $formPortofolio = old('portofolio', $portofolio ?? []);
                                if (! is_array($formPortofolio)) {
                                    $formPortofolio = [];
                                }
                                $formPortofolio = array_values(array_slice($formPortofolio, 0, 3));
                            @endphp

                            <div id="portfolioRowsContainer">
                                @foreach($formPortofolio as $idx => $row)
                                    <div class="portfolio-row" data-portfolio-row>
                                        <div>
                                            <label class="form-label" style="font-size: 0.76rem; margin-bottom: 4px;">Label (Opsional)</label>
                                            <input type="text"
                                                   name="portofolio[{{ $idx }}][label]"
                                                   value="{{ $row['label'] ?? '' }}"
                                                   maxlength="40"
                                                   class="form-control"
                                                   placeholder="Mis. GitHub / Figma">
                                            @error("portofolio.$idx.label")
                                                <div style="color: var(--danger); font-size: 0.75rem; margin-top: 4px; font-weight: 600;">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div>
                                            <label class="form-label" style="font-size: 0.76rem; margin-bottom: 4px;">URL Portofolio (Wajib https://) <span style="color: var(--danger);">*</span></label>
                                            <input type="url"
                                                   name="portofolio[{{ $idx }}][url]"
                                                   value="{{ $row['url'] ?? '' }}"
                                                   maxlength="255"
                                                   required
                                                   class="form-control"
                                                   placeholder="https://github.com/username">
                                            @error("portofolio.$idx.url")
                                                <div style="color: var(--danger); font-size: 0.75rem; margin-top: 4px; font-weight: 600;">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div style="padding-top: 22px;">
                                            <button type="button"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="removePortfolioRow(this)"
                                                    title="Hapus baris tautan">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @error('portofolio')
                                <div style="color: var(--danger); font-size: 0.8rem; margin-top: 6px; font-weight: 600;">
                                    <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                                </div>
                            @enderror

                            <div style="font-size: 0.76rem; color: var(--text-muted); margin-top: 6px; margin-bottom: 18px;">
                                Format URL wajib diawali <strong>https://</strong> (maksimal 255 karakter) dan label maksimal 40 karakter.
                            </div>

                            <div style="display: flex; justify-content: flex-end;">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save-fill"></i> Simpan Portofolio
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Ringkasan Aktivitas (Hanya Lihat Mahasiswa) -->
                <div class="profil-section-card">
                    <div class="profil-section-header">
                        <div>
                            <div class="profil-section-title">
                                <i class="bi bi-activity" style="color: var(--primary);"></i> Ringkasan Aktivitas
                            </div>
                            <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 2px;">
                                Daftar tim kompetisi, peran Anda, serta capaian prestasi yang tercatat
                            </div>
                        </div>
                        <span class="badge badge-gray" style="font-size: 0.7rem;">
                            <i class="bi bi-eye-fill"></i> Hanya Lihat
                        </span>
                    </div>
                    <div class="profil-section-body">
                        <!-- Subbagian A: Tim & Lomba yang Diikuti -->
                        <div style="font-size: 0.85rem; font-weight: 700; color: var(--primary-dark); margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                            <i class="bi bi-people-fill" style="color: var(--primary);"></i> Tim &amp; Lomba yang Diikuti ({{ $timDiikuti->count() }})
                        </div>

                        @if($timDiikuti->isNotEmpty())
                            <div class="activity-list" style="margin-bottom: 24px;">
                                @foreach($timDiikuti as $tim)
                                    @php
                                        $isKetuaTim = ($tim->nim === $mahasiswa->nim);
                                        $anggotaRow = $tim->anggota->first();
                                        $peranAnggota = $anggotaRow?->posisi_dilamar ?: $anggotaRow?->peran;
                                        $statusTimBadge = match($tim->status_tim) {
                                            'disetujui' => ['class' => 'badge-success', 'label' => 'Tim Disetujui'],
                                            'ditolak' => ['class' => 'badge-danger', 'label' => 'Tim Ditolak'],
                                            default => ['class' => 'badge-warning', 'label' => 'Menunggu Persetujuan'],
                                        };
                                    @endphp
                                    <div class="activity-item">
                                        <div>
                                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                                <span style="font-weight: 700; font-size: 0.94rem; color: var(--primary-dark);">
                                                    {{ $tim->nama_tim }}
                                                </span>
                                                <span class="badge {{ $isKetuaTim ? 'badge-primary' : 'badge-purple' }}">
                                                    {{ $isKetuaTim ? 'Ketua' : 'Anggota' }}
                                                    @if(!$isKetuaTim && $peranAnggota && strtolower($peranAnggota) !== 'anggota')
                                                        &bull; {{ $peranAnggota }}
                                                    @endif
                                                </span>
                                                <span class="badge {{ $statusTimBadge['class'] }}">
                                                    {{ $statusTimBadge['label'] }}
                                                </span>
                                            </div>
                                            <div style="font-size: 0.82rem; color: var(--text-muted); margin-top: 4px;">
                                                <i class="bi bi-trophy-fill" style="color: var(--accent);"></i>
                                                <strong>{{ $tim->lomba->nama_lomba ?? 'Kompetisi JTI' }}</strong>
                                                @if($tim->lomba)
                                                    <span>&bull; {{ $tim->lomba->tingkat }} &bull; {{ $tim->lomba->kategori }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        @if($tim->lomba)
                                            <a href="{{ route('lomba.show', $tim->lomba->id_lomba) }}" class="btn btn-secondary btn-sm">
                                                <i class="bi bi-arrow-up-right-square"></i> Detail Lomba
                                            </a>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-box" style="margin-bottom: 24px;">
                                <i class="bi bi-people" style="font-size: 1.4rem; display: block; margin-bottom: 4px; color: var(--text-muted);"></i>
                                Anda belum tergabung dalam tim kompetisi mana pun.
                            </div>
                        @endif

                        <!-- Subbagian B: Capaian Prestasi -->
                        <div style="font-size: 0.85rem; font-weight: 700; color: var(--primary-dark); margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                            <i class="bi bi-trophy-fill" style="color: #D97706;"></i> Prestasi &amp; Penghargaan ({{ $daftarPrestasi->count() }})
                        </div>

                        @if($daftarPrestasi->isNotEmpty())
                            <div class="activity-list">
                                @foreach($daftarPrestasi as $prestasi)
                                    <div class="activity-item">
                                        <div>
                                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                                <span class="badge badge-warning" style="font-size: 0.8rem; padding: 5px 10px;">
                                                    <i class="bi bi-award-fill"></i> {{ $prestasi->hasil_akhir }}
                                                </span>
                                                <span style="font-weight: 700; color: var(--primary-dark); font-size: 0.9rem;">
                                                    {{ $prestasi->pengajuan->tim->nama_tim ?? '-' }}
                                                </span>
                                            </div>
                                            <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px;">
                                                {{ $prestasi->pengajuan->tim->lomba->nama_lomba ?? '-' }} (Babak: {{ $prestasi->babak }})
                                            </div>
                                        </div>
                                        <div style="font-size: 0.78rem; color: var(--text-muted);">
                                            {{ $prestasi->tanggal_update ? $prestasi->tanggal_update->format('d M Y') : '-' }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-box">
                                <i class="bi bi-award" style="font-size: 1.4rem; display: block; margin-bottom: 4px; color: var(--text-muted);"></i>
                                Belum ada catatan prestasi juara yang terverifikasi.
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Ganti Kata Sandi (id="kata-sandi") -->
            <div class="profil-section-card" id="kata-sandi">
                <div class="profil-section-header">
                    <div>
                        <div class="profil-section-title">
                            <i class="bi bi-key-fill" style="color: var(--primary);"></i> Ganti Kata Sandi
                        </div>
                        <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 2px;">
                            Perbarui kata sandi akun Anda secara berkala (minimal 6 karakter)
                        </div>
                    </div>
                </div>
                <div class="profil-section-body">
                    <form action="{{ route('profil.password') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label class="form-label" for="password_lama">
                                Kata Sandi Lama <span style="color: var(--danger);">*</span>
                            </label>
                            <input type="password"
                                   id="password_lama"
                                   name="password_lama"
                                   class="form-control"
                                   placeholder="Masukkan kata sandi saat ini"
                                   required>
                            @error('password_lama')
                                <div style="color: var(--danger); font-size: 0.78rem; margin-top: 4px; font-weight: 600;">
                                    <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
                            <div class="form-group">
                                <label class="form-label" for="password_baru">
                                    Kata Sandi Baru <span style="color: var(--danger);">*</span>
                                </label>
                                <input type="password"
                                       id="password_baru"
                                       name="password_baru"
                                       minlength="6"
                                       class="form-control"
                                       placeholder="Minimal 6 karakter"
                                       required>
                                @error('password_baru')
                                    <div style="color: var(--danger); font-size: 0.78rem; margin-top: 4px; font-weight: 600;">
                                        <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="password_baru_confirmation">
                                    Konfirmasi Kata Sandi Baru <span style="color: var(--danger);">*</span>
                                </label>
                                <input type="password"
                                       id="password_baru_confirmation"
                                       name="password_baru_confirmation"
                                       minlength="6"
                                       class="form-control"
                                       placeholder="Ulangi kata sandi baru"
                                       required>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: flex-end; margin-top: 6px;">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-shield-lock-fill"></i> Perbarui Kata Sandi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
    function updatePortfolioButtonsState() {
        const container = document.getElementById('portfolioRowsContainer');
        const addBtn = document.getElementById('btnAddPortfolioRow');
        if (!container || !addBtn) return;

        const rows = container.querySelectorAll('[data-portfolio-row]');
        rows.forEach((row, index) => {
            const labelInput = row.querySelector('input[type="text"]');
            const urlInput = row.querySelector('input[type="url"]');
            if (labelInput) labelInput.name = `portofolio[${index}][label]`;
            if (urlInput) urlInput.name = `portofolio[${index}][url]`;
        });

        addBtn.disabled = rows.length >= 3;
        addBtn.style.opacity = rows.length >= 3 ? '0.55' : '1';
        addBtn.style.cursor = rows.length >= 3 ? 'not-allowed' : 'pointer';
    }

    function addPortfolioRow() {
        const container = document.getElementById('portfolioRowsContainer');
        if (!container) return;

        const rows = container.querySelectorAll('[data-portfolio-row]');
        if (rows.length >= 3) return;

        const idx = rows.length;
        const row = document.createElement('div');
        row.className = 'portfolio-row';
        row.setAttribute('data-portfolio-row', '');
        row.innerHTML = `
            <div>
                <label class="form-label" style="font-size: 0.76rem; margin-bottom: 4px;">Label (Opsional)</label>
                <input type="text"
                       name="portofolio[${idx}][label]"
                       maxlength="40"
                       class="form-control"
                       placeholder="Mis. GitHub / Figma">
            </div>
            <div>
                <label class="form-label" style="font-size: 0.76rem; margin-bottom: 4px;">URL Portofolio (Wajib https://) <span style="color: var(--danger);">*</span></label>
                <input type="url"
                       name="portofolio[${idx}][url]"
                       maxlength="255"
                       required
                       class="form-control"
                       placeholder="https://github.com/username">
            </div>
            <div style="padding-top: 22px;">
                <button type="button"
                        class="btn btn-danger btn-sm"
                        onclick="removePortfolioRow(this)"
                        title="Hapus baris tautan">
                    <i class="bi bi-trash"></i> Hapus
                </button>
            </div>
        `;
        container.appendChild(row);
        updatePortfolioButtonsState();
    }

    function removePortfolioRow(button) {
        const row = button.closest('[data-portfolio-row]');
        if (row) {
            row.remove();
            updatePortfolioButtonsState();
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        updatePortfolioButtonsState();
    });
</script>
@endpush
