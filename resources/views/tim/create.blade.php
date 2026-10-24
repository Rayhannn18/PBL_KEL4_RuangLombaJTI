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
        <a href="{{ route('tim.index') }}" style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); text-decoration: none;">
            <i class="bi bi-arrow-left"></i> Kembali ke Modul Tim
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

        <form action="{{ route('tim.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- 1. Lomba yang Ingin Diikuti -->
            <div class="form-group" style="margin-bottom: 22px;">
                <label class="form-label">
                    Pilih Kompetisi / Lomba yang Ingin Diikuti <span style="color: var(--danger);">*</span>
                </label>
                <select name="id_lomba" class="form-select @error('id_lomba') is-invalid @enderror" required>
                    <option value="" disabled {{ empty($selectedLombaId) ? 'selected' : '' }}>-- Pilih Kompetisi Terverifikasi Jurusan TI --</option>
                    @foreach($lombaTersedia as $lomba)
                        <option value="{{ $lomba->id_lomba }}" {{ (string) old('id_lomba', $selectedLombaId) === (string) $lomba->id_lomba ? 'selected' : '' }}>
                            {{ $lomba->nama_lomba }} — [Tingkat {{ $lomba->tingkat }} / {{ $lomba->kategori }}]
                        </option>
                    @endforeach
                </select>
                @error('id_lomba')
                    <div style="font-size: 0.8rem; color: var(--danger); margin-top: 5px; font-weight: 600;">
                        <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                    </div>
                @enderror
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
                       class="form-control @error('nama_tim') is-invalid @enderror"
                       value="{{ old('nama_tim') }}"
                       placeholder="Contoh: Garuda Tech SIB / Polinema Data Vanguard"
                       required>
                @error('nama_tim')
                    <div style="font-size: 0.8rem; color: var(--danger); margin-top: 5px; font-weight: 600;">
                        <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                    </div>
                @enderror
                <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 5px;">
                    Tentukan nama identitas resmi tim Anda yang akan dicatat pada sertifikat dan pendaftaran kompetisi.
                </div>
            </div>

            <!-- 3. Kuota Anggota Tim (Wajib & Khusus Ketua Tim) -->
            <div class="form-group" style="margin-bottom: 22px;">
                <label class="form-label">
                    Kuota Anggota Tim <span style="color: var(--danger);">*</span>
                </label>
                <select name="kuota_anggota" class="form-select @error('kuota_anggota') is-invalid @enderror" required>
                    <option value="2" {{ old('kuota_anggota') == 2 ? 'selected' : '' }}>2 Orang (Ketua + 1 Anggota)</option>
                    <option value="3" {{ old('kuota_anggota', 3) == 3 ? 'selected' : '' }}>3 Orang (Ketua + 2 Anggota - Standar Nasional)</option>
                    <option value="4" {{ old('kuota_anggota') == 4 ? 'selected' : '' }}>4 Orang (Ketua + 3 Anggota)</option>
                    <option value="5" {{ old('kuota_anggota') == 5 ? 'selected' : '' }}>5 Orang (Ketua + 4 Anggota - Maksimal)</option>
                </select>
                @error('kuota_anggota')
                    <div style="font-size: 0.8rem; color: var(--danger); margin-top: 5px; font-weight: 600;">
                        <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                    </div>
                @enderror
                <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 5px;">
                    Jumlah total mahasiswa dalam tim delegasi, termasuk Anda sendiri sebagai Ketua Tim.
                </div>
            </div>

            <!-- 4. Upload File CV Ketua Tim (Wajib, Format PDF, Max 2 MB) -->
            <div class="form-group" style="margin-bottom: 24px;">
                <label class="form-label">
                    Unggah Curiculum Vitae (CV) Ketua Tim <span style="color: var(--danger);">*</span>
                </label>
                <div style="background: #F8FAFC; border: 1.5px dashed var(--border); border-radius: var(--radius-sm); padding: 18px; text-align: center;" id="cvDropzoneKetua">
                    <i class="bi bi-file-earmark-pdf" style="font-size: 2rem; color: var(--primary); display: block; margin-bottom: 8px;"></i>
                    <div style="font-size: 0.88rem; font-weight: 600; color: var(--text-main); margin-bottom: 4px;">
                        Pilih file CV Anda dalam format PDF
                    </div>
                    <div style="font-size: 0.78rem; color: var(--text-muted); margin-bottom: 12px;">
                        Format wajib: <strong>.PDF</strong> &bull; Ukuran maksimal: <strong>2 MB</strong>
                    </div>
                    <input type="file"
                           name="cv"
                           id="cvKetuaInput"
                           class="form-control @error('cv') is-invalid @enderror"
                           accept="application/pdf,.pdf"
                           required
                           style="max-width: 380px; margin: 0 auto; font-size: 0.85rem;"
                           onchange="handleCvFileChange(this, 'cvKetuaPreview', 'cvKetuaError', 'cvKetuaFileName')">
                    <div id="cvKetuaPreview" style="display: none; margin-top: 10px; font-size: 0.82rem; color: var(--success); font-weight: 600;">
                        <i class="bi bi-check-circle-fill"></i> <span id="cvKetuaFileName"></span>
                    </div>
                    <div id="cvKetuaError" style="display: none; margin-top: 8px; font-size: 0.8rem; color: var(--danger); font-weight: 600;"></div>
                </div>
                @error('cv')
                    <div style="font-size: 0.82rem; color: var(--danger); margin-top: 6px; font-weight: 600;">
                        <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                    </div>
                @enderror
                <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 6px;">
                    File CV ini akan digunakan oleh pengelola dan calon Dosen Pembimbing untuk memverifikasi kesiapan Ketua Tim.
                </div>
            </div>

            <!-- 5. Informasi atau Deskripsi Tambahan Tim -->
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

@push('scripts')
<script>
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

        // Validasi ekstensi / tipe mime PDF
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
</script>
@endpush
