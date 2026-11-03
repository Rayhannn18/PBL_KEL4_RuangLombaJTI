@extends('layouts.app')

@section('content')

@push('styles')
<style>
    .profil-grid {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 24px;
        margin-top: 20px;
    }
    @media (max-width: 992px) {
        .profil-grid {
            grid-template-columns: 1fr;
        }
    }
    .profil-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        padding: 24px;
        margin-bottom: 24px;
    }
    .profil-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-bottom: 16px;
        margin-bottom: 20px;
        border-bottom: 1px solid var(--border);
    }
    .profil-card-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--primary-dark);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .quota-stat-box {
        background: #F8FAFC;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 16px;
        display: flex;
        justify-content: space-around;
        text-align: center;
        margin-bottom: 20px;
    }
    .quota-stat-val {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--primary);
        line-height: 1.2;
    }
    .quota-stat-lbl {
        font-size: 0.75rem;
        color: var(--text-muted);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .ai-notice-box {
        background: linear-gradient(135deg, #EEF2FF 0%, #FAF5FF 100%);
        border: 1px solid #C7D2FE;
        border-radius: var(--radius-sm);
        padding: 14px;
        margin-bottom: 18px;
        font-size: 0.82rem;
        color: #3730A3;
        line-height: 1.5;
    }
</style>
@endpush

<div class="container" style="padding-top: 25px; padding-bottom: 50px;">
    <!-- Page Header -->
    <div style="margin-bottom: 15px;">
        <div style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 4px;">
            <a href="{{ route('dashboard') }}" style="color: var(--primary);"><i class="bi bi-house"></i> Beranda</a>
            <span style="margin: 0 6px;">&bull;</span>
            <span>Dosen Pembimbing</span>
            <span style="margin: 0 6px;">&bull;</span>
            <span style="color: var(--text-main); font-weight: 600;">Profil & Pengaturan Kuota</span>
        </div>
        <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--primary-dark); margin: 0;">
            <i class="bi bi-person-badge-fill" style="color: var(--primary);"></i> Profil Dosen Pembimbing
        </h1>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 4px;">
            Kelola bidang keahlian kompetensi dan batas kuota bimbingan karya lomba mahasiswa JTI.
        </p>
    </div>

    @if(session('info'))
        <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: var(--radius-sm); padding: 14px; margin-bottom: 20px; color: #1E40AF; font-size: 0.88rem; display: flex; align-items: center; gap: 8px;">
            <i class="bi bi-info-circle-fill" style="font-size: 1.1rem;"></i>
            {{ session('info') }}
        </div>
    @endif

    <div class="profil-grid">
        <!-- Kolom Kiri: Formulir Pengaturan Keahlian & Kuota -->
        <div>
            <div class="profil-card">
                <div class="profil-card-header">
                    <div class="profil-card-title">
                        <i class="bi bi-sliders" style="color: var(--primary);"></i> Pengaturan Keahlian & Kuota
                    </div>
                    <span class="badge badge-primary" style="font-size: 0.75rem;">
                        <i class="bi bi-shield-check"></i> Akun Terverifikasi
                    </span>
                </div>

                <div class="ai-notice-box">
                    <div style="font-weight: 700; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                        <i class="bi bi-stars" style="color: #6366F1;"></i> Terhubung ke Sistem Rekomendasi AI
                    </div>
                    Data bidang keahlian Anda akan dianalisis secara otomatis oleh AI (Gemini) saat mahasiswa mencari pembimbing lomba. Pastikan menuliskan kata kunci spesifik (contoh: <em>Web Development, Machine Learning, UI/UX, dsb.</em>) agar sistem merekomendasikan karya tim yang paling sesuai.
                </div>

                <form action="{{ route('dosen.profil.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Bidang Keahlian -->
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label" style="font-weight: 700;">
                            Bidang Keahlian / Kompetensi Utama *
                        </label>
                        <textarea name="bidang_keahlian" rows="3" class="form-control @error('bidang_keahlian') is-invalid @enderror" placeholder="Contoh: Artificial Intelligence, Computer Vision, Software Engineering, Mobile Development" required>{{ old('bidang_keahlian', $dosen->bidang_keahlian) }}</textarea>
                        @error('bidang_keahlian')
                            <div style="color: #DC2626; font-size: 0.8rem; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">
                            Pisahkan topik dengan koma atau tanda sambung (&).
                        </div>
                    </div>

                    <!-- Kuota Bimbingan -->
                    <div class="form-group" style="margin-bottom: 24px;">
                        <label class="form-label" style="font-weight: 700;">
                            Batas Maksimal Kuota Bimbingan (Tim) *
                        </label>
                        <input type="number" name="kuota_bimbingan" min="1" max="30" class="form-control @error('kuota_bimbingan') is-invalid @enderror" value="{{ old('kuota_bimbingan', $dosen->kuota_bimbingan) }}" required style="max-width: 220px;">
                        @error('kuota_bimbingan')
                            <div style="color: #DC2626; font-size: 0.8rem; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">
                            Saat ini Anda membimbing <strong>{{ $bimbinganAktif->count() }} tim aktif</strong>. Kuota baru tidak boleh lebih kecil dari {{ $bimbinganAktif->count() }}.
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="padding: 10px 24px;">
                        <i class="bi bi-save-fill"></i> Simpan Pembaruan Profil
                    </button>
                </form>
            </div>
        </div>

        <!-- Kolom Kanan: Identitas Resmi & Ringkasan Kuota -->
        <div>
            <!-- Kartu Identitas Resmi -->
            <div class="profil-card">
                <div class="profil-card-header">
                    <div class="profil-card-title">
                        <i class="bi bi-person-fill" style="color: var(--primary);"></i> Identitas Dosen
                    </div>
                </div>

                <div style="text-align: center; margin-bottom: 18px;">
                    <div style="width: 64px; height: 64px; border-radius: 50%; background: linear-gradient(135deg, #1E40AF 0%, #3B82F6 100%); color: #fff; font-size: 1.8rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px;">
                        <i class="bi bi-person-video3"></i>
                    </div>
                    <div style="font-weight: 800; font-size: 1.05rem; color: var(--primary-dark);">
                        {{ $dosen->nama }}
                    </div>
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">
                        NIDN / NIP: <strong>{{ $dosen->nidn }}</strong>
                    </div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);">
                        {{ $dosen->email_kampus }}
                    </div>
                </div>

                <!-- Ringkasan Kuota Saat Ini -->
                <div class="quota-stat-box">
                    <div>
                        <div class="quota-stat-val">{{ $dosen->kuota_bimbingan }}</div>
                        <div class="quota-stat-lbl">Maksimal</div>
                    </div>
                    <div style="border-left: 1px solid var(--border); border-right: 1px solid var(--border); padding: 0 14px;">
                        <div class="quota-stat-val" style="color: #D97706;">{{ $bimbinganAktif->count() }}</div>
                        <div class="quota-stat-lbl">Dibimbing</div>
                    </div>
                    <div>
                        <div class="quota-stat-val" style="color: #059669;">{{ $dosen->sisa_kuota }}</div>
                        <div class="quota-stat-lbl">Sisa Kuota</div>
                    </div>
                </div>

                <div style="text-align: center;">
                    <a href="{{ route('bimbingan.index') }}" class="btn btn-outline-primary btn-sm" style="width: 100%;">
                        <i class="bi bi-box-arrow-up-right"></i> Buka Modul Bimbingan
                    </a>
                </div>
            </div>

            <!-- Daftar Tim yang Sedang Aktif Dibimbing -->
            <div class="profil-card">
                <div class="profil-card-header">
                    <div class="profil-card-title" style="font-size: 0.95rem;">
                        <i class="bi bi-people-fill" style="color: var(--primary);"></i> Tim Aktif ({{ $bimbinganAktif->count() }})
                    </div>
                </div>

                @if($bimbinganAktif->isEmpty())
                    <p style="font-size: 0.82rem; color: var(--text-muted); text-align: center; margin: 10px 0;">
                        Belum ada tim bimbingan yang berstatus resmi disetujui.
                    </p>
                @else
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        @foreach($bimbinganAktif as $akt)
                            <div style="background: #F8FAFC; border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 10px 12px; font-size: 0.82rem;">
                                <div style="font-weight: 700; color: var(--primary-dark);">
                                    {{ $akt->tim->nama_tim ?? 'Tim' }}
                                </div>
                                <div style="font-size: 0.74rem; color: var(--text-muted); margin-top: 2px;">
                                    {{ $akt->tim->lomba->nama_lomba ?? '-' }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

