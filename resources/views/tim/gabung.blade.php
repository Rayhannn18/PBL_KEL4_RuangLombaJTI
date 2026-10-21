@extends('layouts.app')

@section('title', 'Ajukan Bergabung ke Tim - RuangLombaJTI')

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
    .team-summary-box {
        background: #F8FAFC;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 18px 20px;
        margin-bottom: 26px;
    }
</style>
@endpush

@section('content')
<div class="create-tim-wrapper">
    <div style="margin-bottom: 20px;">
        <a href="{{ route('tim.lowongan') }}" style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); text-decoration: none;">
            <i class="bi bi-arrow-left"></i> Kembali ke Papan Lowongan Tim
        </a>
    </div>

    <div class="form-card">
        <div class="header-icon-box">
            <i class="bi bi-person-plus-fill"></i>
        </div>

        <div style="display: inline-flex; align-items: center; gap: 6px; background: #EFF6FF; color: var(--primary); padding: 4px 12px; border-radius: 999px; font-size: 0.75rem; font-weight: 700; margin-bottom: 10px;">
            <i class="bi bi-people-fill"></i> Alur Pengajuan Anggota Tim
        </div>

        <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--primary-dark); margin-bottom: 8px;">
            Ajukan Diri Bergabung ke Tim
        </h1>
        <p style="font-size: 0.92rem; color: var(--text-muted); line-height: 1.6; margin-bottom: 24px;">
            Kirimkan permohonan delegasi Anda dengan menentukan bidang keahlian dan mengunggah CV. Ketua Tim akan meninjau kualifikasi Anda sebelum menyetujui bergabung ke dalam susunan tim.
        </p>

        <!-- Ringkasan Informasi Tim Sasaran -->
        <div class="team-summary-box">
            <div style="font-size: 0.72rem; font-weight: 700; color: var(--primary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                <i class="bi bi-flag-fill"></i> Tim Sasaran & Kompetisi
            </div>
            <div style="font-size: 1.25rem; font-weight: 800; color: var(--primary-dark); margin-bottom: 4px;">
                {{ $tim->nama_tim }}
            </div>
            <div style="font-size: 0.9rem; font-weight: 600; color: var(--text-main); margin-bottom: 10px;">
                <i class="bi bi-award"></i> {{ $tim->lomba->nama_lomba ?? 'Kompetisi Mahasiswa' }}
                @if(isset($tim->lomba->tingkat))
                    <span class="badge badge-purple" style="font-size: 0.7rem; margin-left: 6px;">{{ $tim->lomba->tingkat }}</span>
                @endif
            </div>

            <div style="display: flex; flex-wrap: wrap; gap: 16px; font-size: 0.8rem; color: var(--text-muted); border-top: 1px dashed var(--border); padding-top: 10px;">
                <div>
                    <i class="bi bi-person-badge-fill" style="color: var(--primary);"></i>
                    Ketua Tim: <strong>{{ $tim->ketua->nama ?? '-' }}</strong> (NIM: {{ $tim->nim }})
                </div>
                <div>
                    <i class="bi bi-people-fill" style="color: var(--success);"></i>
                    Sisa Kuota: <strong>{{ $tim->sisa_kuota_anggota }} slot</strong> ({{ $tim->anggotaDiterima->count() }}/{{ $tim->kuota_anggota }} terisi)
                </div>
            </div>
        </div>

        <form action="{{ route('tim.gabung', $tim->id_tim) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- 1. Peran / Bidang Keahlian yang Ditawarkan (Wajib) -->
            <div class="form-group" style="margin-bottom: 22px;">
                <label class="form-label">
                    Peran / Bidang Keahlian yang Anda Tawarkan <span style="color: var(--danger);">*</span>
                </label>
                <select name="peran" class="form-select @error('peran') is-invalid @enderror" required>
                    <option value="" disabled {{ old('peran') ? '' : 'selected' }}>-- Pilih Peran / Posisi Anda dalam Tim --</option>
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
                <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 5px;">
                    Pilihlah keahlian spesifik yang paling sesuai dengan kebutuhan kompetisi tim sasaran.
                </div>
            </div>

            <!-- 2. Upload File CV Anggota Tim (Wajib, Format PDF, Max 2 MB) -->
            <div class="form-group" style="margin-bottom: 24px;">
                <label class="form-label">
                    Unggah Curiculum Vitae (CV) / Portofolio Singkat <span style="color: var(--danger);">*</span>
                </label>
                <div style="background: #F8FAFC; border: 1.5px dashed var(--border); border-radius: var(--radius-sm); padding: 18px; text-align: center;" id="cvDropzoneAnggota">
                    <i class="bi bi-file-earmark-pdf" style="font-size: 2rem; color: var(--primary); display: block; margin-bottom: 8px;"></i>
                    <div style="font-size: 0.88rem; font-weight: 600; color: var(--text-main); margin-bottom: 4px;">
                        Pilih file CV Anda dalam format PDF
                    </div>
                    <div style="font-size: 0.78rem; color: var(--text-muted); margin-bottom: 12px;">
                        Format wajib: <strong>.PDF</strong> &bull; Ukuran maksimal: <strong>2 MB</strong>
                    </div>
                    <input type="file"
                           name="cv"
                           id="cvAnggotaInput"
                           class="form-control @error('cv') is-invalid @enderror"
                           accept="application/pdf,.pdf"
                           required
                           style="max-width: 380px; margin: 0 auto; font-size: 0.85rem;"
                           onchange="handleCvFileChange(this, 'cvAnggotaPreview', 'cvAnggotaError', 'cvAnggotaFileName')">
                    <div id="cvAnggotaPreview" style="display: none; margin-top: 10px; font-size: 0.82rem; color: var(--success); font-weight: 600;">
                        <i class="bi bi-check-circle-fill"></i> <span id="cvAnggotaFileName"></span>
                    </div>
                    <div id="cvAnggotaError" style="display: none; margin-top: 8px; font-size: 0.8rem; color: var(--danger); font-weight: 600;"></div>
                </div>
                @error('cv')
                    <div style="font-size: 0.82rem; color: var(--danger); margin-top: 6px; font-weight: 600;">
                        <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                    </div>
                @enderror
                <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 6px;">
                    File CV ini akan ditinjau langsung oleh Ketua Tim untuk mempertimbangkan penerimaan Anda ke dalam tim.
                </div>
            </div>

            <!-- 3. Catatan Singkat untuk Ketua Tim (Opsional) -->
            <div class="form-group" style="margin-bottom: 28px;">
                <label class="form-label">Catatan Singkat / Pesan untuk Ketua Tim (Opsional)</label>
                <textarea name="catatan"
                          rows="3"
                          class="form-control @error('catatan') is-invalid @enderror"
                          placeholder="Tuliskan pengalaman relevan, tautan portofolio/GitHub, atau motivasi Anda bergabung ke tim ini...">{{ old('catatan') }}</textarea>
                @error('catatan')
                    <div style="font-size: 0.8rem; color: var(--danger); margin-top: 5px; font-weight: 600;">
                        <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                    </div>
                @enderror
                <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 5px;">
                    Ceritakan pengalaman singkat Anda yang dapat meyakinkan Ketua Tim untuk menerima lamaran delegasi Anda.
                </div>
            </div>

            <!-- Informasi Alur Pengajuan Anggota -->
            <div style="background: #F8FAFC; border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 16px; margin-bottom: 28px;">
                <div style="font-size: 0.82rem; font-weight: 700; color: var(--primary-dark); margin-bottom: 6px;">
                    <i class="bi bi-info-circle-fill" style="color: var(--primary);"></i> Alur Setelah Pengajuan Dikirim:
                </div>
                <ul style="padding-left: 20px; font-size: 0.8rem; color: var(--text-muted); line-height: 1.6; margin-bottom: 0;">
                    <li>Pengajuan Anda akan masuk ke dashboard Ketua Tim dengan status <em>Menunggu Persetujuan</em>.</li>
                    <li>Ketua Tim dapat meninjau CV Anda dan memutuskan untuk menerima atau menolak pengajuan.</li>
                    <li>Status pengajuan Anda dapat dipantau langsung pada Papan Lowongan Tim.</li>
                </ul>
            </div>

            <!-- Tombol Aksi Form -->
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap;">
                <a href="{{ route('tim.lowongan') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Batal & Kembali
                </a>
                <button type="submit" class="btn btn-primary" style="padding: 10px 24px;">
                    <i class="bi bi-send-fill"></i> Kirim Pengajuan Gabung
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
