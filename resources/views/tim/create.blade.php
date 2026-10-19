@extends('layouts.app')

@section('title', 'Bentuk Tim Baru (Ketua Tim) - RuangLombaJTI')

@push('styles')
<style>
    .create-tim-wrapper {
        max-width: 760px;
        margin: 0 auto;
    }
    .form-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        padding: 35px 40px;
    }
    .header-icon-box {
        width: 56px;
        height: 56px;
        border-radius: var(--radius-md);
        background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.7rem;
        margin-bottom: 16px;
    }
</style>
@endpush

@section('content')
<div class="create-tim-wrapper">
    <div style="margin-bottom: 20px;">
        <a href="{{ route('bimbingan.index') }}" style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); text-decoration: none;">
            <i class="bi bi-arrow-left"></i> Kembali ke Modul Bimbingan
        </a>
    </div>

    <div class="form-card">
        <div class="header-icon-box">
            <i class="bi bi-flag-fill"></i>
        </div>

        <div style="display: inline-flex; align-items: center; gap: 6px; background: #ECFDF5; color: #065F46; padding: 4px 12px; border-radius: 999px; font-size: 0.75rem; font-weight: 700; margin-bottom: 10px;">
            <i class="bi bi-award-fill"></i> Alur Pendaftaran Ketua Tim
        </div>

        <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--primary-dark); margin-bottom: 8px;">
            Bentuk Tim Delegasi Baru
        </h1>
        <p style="font-size: 0.92rem; color: var(--text-muted); line-height: 1.6; margin-bottom: 28px;">
            Sebagai inisiator dan <strong>Ketua Tim</strong>, Anda menentukan nama delegasi tim, ajang kompetisi yang dituju, serta kuota maksimal anggota. Setelah tim dibentuk, Anda dapat mengajukan Dosen Pembimbing dan mengelola anggota delegasi.
        </p>

        <form action="{{ route('tim.store') }}" method="POST">
            @csrf

            <!-- 1. Lomba yang Ingin Diikuti -->
            <div class="form-group" style="margin-bottom: 22px;">
                <label class="form-label">
                    Pilih Kompetisi / Lomba yang Ingin Diikuti <span style="color: var(--danger);">*</span>
                </label>
                <select name="id_lomba" class="form-select" required>
                    <option value="" disabled {{ empty($selectedLombaId) ? 'selected' : '' }}>-- Pilih Kompetisi Terverifikasi Jurusan TI --</option>
                    @foreach($lombaTersedia as $lomba)
                        <option value="{{ $lomba->id_lomba }}" {{ (string) $selectedLombaId === (string) $lomba->id_lomba ? 'selected' : '' }}>
                            {{ $lomba->nama_lomba }} — [Tingkat {{ $lomba->tingkat }} / {{ $lomba->kategori }}]
                        </option>
                    @endforeach
                </select>
                <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 5px;">
                    Belum menemukan lomba yang cocok? <a href="{{ route('lomba.index') }}" target="_blank" style="color: var(--primary); font-weight: 600; text-decoration: underline;">Cari di Katalog Lomba &rarr;</a>
                </div>
            </div>

            <!-- 2. Nama Tim (Wajib & Khusus Ketua Tim) -->
            <div class="form-group" style="margin-bottom: 22px;">
                <label class="form-label">
                    Nama Tim Baru <span style="color: var(--danger);">*</span>
                </label>
                <input type="text"
                       name="nama_tim"
                       class="form-control"
                       value="{{ old('nama_tim') }}"
                       placeholder="Contoh: Garuda Tech SIB / Polinema Data Vanguard"
                       required>
                <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 5px;">
                    Tentukan nama identitas resmi tim Anda yang akan dicatat pada sertifikat dan pendaftaran kompetisi.
                </div>
            </div>

            <!-- 3. Kuota Anggota Tim (Wajib & Khusus Ketua Tim) -->
            <div class="form-group" style="margin-bottom: 22px;">
                <label class="form-label">
                    Kuota Anggota Tim <span style="color: var(--danger);">*</span>
                </label>
                <select name="kuota_anggota" class="form-select" required>
                    <option value="2" {{ old('kuota_anggota') == 2 ? 'selected' : '' }}>2 Orang (Ketua + 1 Anggota)</option>
                    <option value="3" {{ old('kuota_anggota', 3) == 3 ? 'selected' : '' }}>3 Orang (Ketua + 2 Anggota - Standar Nasional)</option>
                    <option value="4" {{ old('kuota_anggota') == 4 ? 'selected' : '' }}>4 Orang (Ketua + 3 Anggota)</option>
                    <option value="5" {{ old('kuota_anggota') == 5 ? 'selected' : '' }}>5 Orang (Ketua + 4 Anggota - Maksimal)</option>
                </select>
                <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 5px;">
                    Jumlah total mahasiswa dalam tim delegasi, termasuk Anda sendiri sebagai Ketua Tim.
                </div>
            </div>

            <!-- 4. Informasi atau Deskripsi Tambahan Tim -->
            <div class="form-group" style="margin-bottom: 28px;">
                <label class="form-label">Deskripsi Singkat / Posisi Anggota yang Dicari (Opsional)</label>
                <textarea name="catatan"
                          rows="3"
                          class="form-control"
                          placeholder="Contoh: Tim ini berfokus pada pengembangan prototype aplikasi IoT dan membutuhkan rekan mahasiswa dengan keahlian UI/UX Designer atau Mobile Developer...">{{ old('catatan') }}</textarea>
                <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 5px;">
                    Informasi ini membantu rekan mahasiswa lain memahami fokus dan kebutuhan anggota delegasi Anda di Papan Lowongan Tim.
                </div>
            </div>

            <!-- Kotak Ringkasan Peran Ketua -->
            <div style="background: #F8FAFC; border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 16px; margin-bottom: 28px;">
                <div style="font-size: 0.82rem; font-weight: 700; color: var(--primary-dark); margin-bottom: 6px;">
                    <i class="bi bi-shield-check" style="color: var(--primary);"></i> Tanggung Jawab Ketua Tim:
                </div>
                <ul style="padding-left: 20px; font-size: 0.8rem; color: var(--text-muted); line-height: 1.6;">
                    <li>Mengelola penerimaan permohonan anggota yang melamar di Papan Lowongan Tim.</li>
                    <li>Mengajukan Dosen Pembimbing resmi Jurusan TI setelah susunan tim terbentuk.</li>
                    <li>Mengisi dan memperbarui logbook konsultasi bimbingan bersama Dosen Pembimbing.</li>
                </ul>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap;">
                <a href="{{ route('tim.lowongan') }}" class="btn btn-secondary">
                    <i class="bi bi-door-open"></i> Buka Papan Lowongan Tim
                </a>
                <button type="submit" class="btn btn-primary" style="padding: 10px 24px;">
                    <i class="bi bi-check2-circle"></i> Bentuk Tim Baru & Buka Bimbingan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
