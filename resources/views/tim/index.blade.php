@extends('layouts.app')

@section('title', 'Modul Tim - Pusat Pengelolaan Tim Mahasiswa - RuangLombaJTI')

@push('styles')
<style>

    /* Bagian A: Banner Bentuk Tim */
    .section-create-team {
        background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%);
        border: 1.5px solid #BFDBFE;
        border-radius: var(--radius-lg);
        padding: 24px 28px;
        margin-bottom: 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
        box-shadow: var(--shadow-sm);
    }
    .section-create-info {
        flex: 1;
        min-width: 280px;
    }
    .section-create-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #1D4ED8;
        color: #fff;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 3px 10px;
        border-radius: 999px;
        margin-bottom: 10px;
    }

    /* Tim yang Diikuti Mahasiswa */
    .user-teams-bar {
        background: #F8FAFC;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 16px 20px;
        margin-bottom: 28px;
    }

    /* Bagian: Pengajuan Gabung Tim Saya */
    .user-applications-card {
        background: #FFFFFF;
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 22px 24px;
        margin-bottom: 30px;
        box-shadow: var(--shadow-sm);
    }
    .user-applications-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 18px;
        flex-wrap: wrap;
        gap: 12px;
        border-bottom: 1px solid var(--border);
        padding-bottom: 14px;
    }
    .application-item {
        background: #F8FAFC;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 16px 18px;
        transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 12px;
    }
    .application-item:hover {
        border-color: #93C5FD;
        box-shadow: var(--shadow-sm);
        transform: translateY(-2px);
    }
    .application-item.status-menunggu {
        border-left: 4px solid #F59E0B;
    }
    .application-item.status-diterima {
        border-left: 4px solid #10B981;
    }
    .application-item.status-ditolak {
        border-left: 4px solid #EF4444;
    }
    .application-badge-menunggu {
        background: #FEF3C7;
        color: #92400E;
        border: 1px solid #FDE68A;
    }
    .application-badge-diterima {
        background: #DCFCE7;
        color: #166534;
        border: 1px solid #BBF7D0;
    }
    .application-badge-ditolak {
        background: #FEE2E2;
        color: #991B1B;
        border: 1px solid #FECACA;
    }
    .application-note-box {
        font-size: 0.82rem;
        padding: 8px 12px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .application-note-box.note-menunggu {
        background: #FFFBEB;
        color: #92400E;
        border: 1px solid #FDE68A;
    }
    .application-note-box.note-diterima {
        background: #F0FDF4;
        color: #166534;
        border: 1px solid #BBF7D0;
    }
    .application-note-box.note-ditolak {
        background: #FEF2F2;
        color: #991B1B;
        border: 1px solid #FECACA;
    }


    /* Grid Kartu Tim */
    .tim-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 22px;
        margin-bottom: 40px;
    }
    @media (max-width: 768px) {
        .tim-grid {
            grid-template-columns: 1fr;
        }
    }

    .tim-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 22px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        position: relative;
    }
    .tim-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
        border-color: #93C5FD;
    }

    /* Modal Detail Tim */
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
        max-width: 620px;
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
        max-height: 75vh;
        overflow-y: auto;
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

    <!-- Hero Header Modul Tim (Konsisten dengan Dashboard Analitik) -->
    <div class="dashboard-header">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 20px;">
            <div>
                <div class="dashboard-header-badge">
                    <i class="bi bi-people-fill"></i> Sistem Informasi Manajemen Prestasi JTI
                </div>
                <h1 style="font-size: 1.9rem; font-weight: 800; letter-spacing: -0.5px; margin-bottom: 8px;">
                    Eksplorasi dan Manajemen Tim
                </h1>
                <p style="font-size: 0.95rem; opacity: 0.9; max-width: 650px; line-height: 1.6; margin-bottom: 0;">
                    Temukan tim kompetisi, bentuk tim baru, dan ajukan diri untuk bergabung dengan tim mahasiswa JTI.
                </p>
            </div>
            @php
                $currentUser = auth()->user();
                $sessionUser = session('auth_user');
                $userName = $currentUser->name ?? ($sessionUser['nama'] ?? ($authUser['nama'] ?? null));
                $userRoleVal = $currentUser ? ($currentUser->role instanceof \App\Enums\UserRole ? $currentUser->role->value : (string) $currentUser->role) : ($sessionUser['role'] ?? ($authUser['role'] ?? null));
                $userRoleLabel = $currentUser ? ($currentUser->role instanceof \App\Enums\UserRole ? $currentUser->role->label() : ucfirst($userRoleVal ?? '')) : ucfirst($sessionUser['role'] ?? ($authUser['role'] ?? ''));
                $userId = $currentUser->nim_nip ?? ($sessionUser['id'] ?? ($authUser['id'] ?? null));
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
    {{-- BAGIAN A: BENTUK TIM BARU (JALUR KETUA TIM)                               --}}
    {{-- ========================================================================= --}}
    <div class="section-create-team">
        <div class="section-create-info">
            <span class="section-create-tag">
                <i class="bi bi-flag-fill"></i> Bagian A &bull; Inisiasi Tim Baru
            </span>
            <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--primary-dark); margin-bottom: 6px;">
                Ingin Memimpin Tim Delegasi? Bentuk Tim Baru
            </h2>
            <p style="font-size: 0.9rem; color: #334155; line-height: 1.6; margin-bottom: 12px;">
                Sebagai inisiator dan <strong>Ketua Tim</strong>, tentukan nama tim, pilih kompetisi yang ingin diikuti, atur kuota anggota, dan unggah berkas CV Anda. Setelah tim resmi dibentuk, Anda dapat merekrut anggota dan mengajukan Dosen Pembimbing di Modul Bimbingan.
            </p>
            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap; font-size: 0.8rem; color: var(--text-muted);">
                <span><i class="bi bi-check-circle-fill" style="color: var(--success);"></i> Kuota tim fleksibel (2-5 orang)</span>
                <span>&bull;</span>
                <span><i class="bi bi-file-earmark-pdf" style="color: var(--primary);"></i> Wajib unggah CV Ketua (PDF maks. 2MB)</span>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 8px; min-width: 240px;">
            <a href="{{ route('tim.create') }}" class="btn btn-primary" style="padding: 12px 22px; font-weight: 700; font-size: 0.92rem; box-shadow: var(--shadow-md);">
                <i class="bi bi-plus-circle-fill"></i> Bentuk Tim Baru (/tim/buat)
            </a>
            <a href="{{ route('lomba.index') }}" class="btn btn-outline-primary btn-sm" style="font-weight: 600; text-align: center;">
                <i class="bi bi-award"></i> Cari Ide Lomba di Katalog
            </a>
        </div>
    </div>

    {{-- Ringkasan Tim Aktif Mahasiswa Login (Jika Sudah Bergabung/Memimpin Tim) --}}
    @if(isset($userTeams) && $userTeams->isNotEmpty())
        <div class="user-teams-bar">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
                <div style="font-size: 0.88rem; font-weight: 700; color: var(--primary-dark); display: flex; align-items: center; gap: 6px;">
                    <i class="bi bi-person-check-fill" style="color: var(--primary);"></i> Tim yang Anda Ikuti Saat Ini ({{ $userTeams->count() }}):
                </div>
                <div style="font-size: 0.8rem; color: var(--text-muted);">
                    Lanjutkan progres dan konsultasi proposal di <strong>Modul Bimbingan</strong>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 12px;">
                @foreach($userTeams as $uTeam)
                    @php
                        $isKetua = ($uTeam->nim === $authUser['id']);
                    @endphp
                    <div style="background: #fff; border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 12px 14px; display: flex; justify-content: space-between; align-items: center; gap: 10px;">
                        <div>
                            <div style="font-weight: 700; color: var(--primary-dark); font-size: 0.92rem;">
                                {{ $uTeam->nama_tim }}
                                <span class="badge {{ $isKetua ? 'badge-primary' : 'badge-gray' }}" style="font-size: 0.7rem; margin-left: 4px;">
                                    {{ $isKetua ? 'Ketua Tim' : 'Anggota' }}
                                </span>
                            </div>
                            <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 2px;">
                                <i class="bi bi-award"></i> {{ Str::limit($uTeam->lomba->nama_lomba ?? 'Kompetisi', 35) }}
                            </div>
                        </div>
                        <a href="{{ route('bimbingan.index', ['tim_id' => $uTeam->id_tim]) }}" class="btn btn-outline-primary btn-sm" style="font-size: 0.75rem; padding: 4px 10px; white-space: nowrap;">
                            <i class="bi bi-person-video3"></i> Bimbingan Tim &rarr;
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- BAGIAN: PENGAJUAN GABUNG TIM SAYA                                         --}}
    {{-- ========================================================================= --}}
    @if(($authUser['role'] ?? '') === 'mahasiswa')
        <div class="user-applications-card">
            <div class="user-applications-header">
                <div>
                    <div style="font-size: 0.72rem; font-weight: 700; color: var(--primary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">
                        <i class="bi bi-send-check"></i> Status Permohonan Anggota
                    </div>
                    <h3 style="font-size: 1.18rem; font-weight: 800; color: var(--primary-dark); margin: 0; display: flex; align-items: center; gap: 8px;">
                        <span>Pengajuan Gabung Tim Saya</span>
                        @if(isset($pengajuanGabung) && $pengajuanGabung->isNotEmpty())
                            <span class="badge badge-primary" style="font-size: 0.72rem; padding: 2px 8px;">
                                {{ $pengajuanGabung->count() }} Pengajuan
                            </span>
                        @endif
                    </h3>
                    <p style="font-size: 0.82rem; color: var(--text-muted); margin: 3px 0 0 0;">
                        Pantau status persetujuan permohonan bergabung ke tim yang kamu ajukan.
                    </p>
                </div>
            </div>

            @if(isset($pengajuanGabung) && $pengajuanGabung->isNotEmpty())
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 14px;">
                    @foreach($pengajuanGabung as $pg)
                        @php
                            $status = $pg->status_gabung;
                            $statusClass = match($status) {
                                'diterima' => 'status-diterima',
                                'ditolak' => 'status-ditolak',
                                default => 'status-menunggu',
                            };
                            $badgeClass = match($status) {
                                'diterima' => 'application-badge-diterima',
                                'ditolak' => 'application-badge-ditolak',
                                default => 'application-badge-menunggu',
                            };
                            $badgeIcon = match($status) {
                                'diterima' => 'bi-check-circle-fill',
                                'ditolak' => 'bi-x-circle-fill',
                                default => 'bi-clock-history',
                            };
                            $statusLabel = match($status) {
                                'diterima' => 'Diterima',
                                'ditolak' => 'Ditolak',
                                default => 'Menunggu Persetujuan',
                            };
                            $noteClass = match($status) {
                                'diterima' => 'note-diterima',
                                'ditolak' => 'note-ditolak',
                                default => 'note-menunggu',
                            };
                            $noteIcon = match($status) {
                                'diterima' => 'bi-check-circle-fill',
                                'ditolak' => 'bi-exclamation-circle-fill',
                                default => 'bi-hourglass-split',
                            };
                            $statusNote = match($status) {
                                'diterima' => 'Pengajuan kamu diterima. Silakan cek status keanggotaan tim kamu.',
                                'ditolak' => 'Pengajuan kamu ditolak oleh ketua tim.',
                                default => 'Pengajuan kamu sedang menunggu keputusan ketua tim.',
                            };
                            $timTujuan = $pg->tim;
                            $lombaTujuan = $timTujuan?->lomba;
                            $ketuaTujuan = $timTujuan?->ketua;
                        @endphp
                        <div class="application-item {{ $statusClass }}">
                            <div>
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; margin-bottom: 8px;">
                                    <div>
                                        <div style="font-weight: 800; color: var(--primary-dark); font-size: 1rem; line-height: 1.3;">
                                            {{ $timTujuan->nama_tim ?? 'Tim Tidak Ditemukan' }}
                                        </div>
                                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">
                                            <i class="bi bi-award"></i> {{ $lombaTujuan->nama_lomba ?? 'Kompetisi JTI' }}
                                        </div>
                                    </div>
                                    <span class="badge {{ $badgeClass }}" style="font-size: 0.74rem; padding: 4px 10px; white-space: nowrap;">
                                        <i class="bi {{ $badgeIcon }}"></i> {{ $statusLabel }}
                                    </span>
                                </div>

                                <div style="display: flex; flex-wrap: wrap; gap: 10px; font-size: 0.78rem; color: var(--text-muted); margin-bottom: 10px;">
                                    <span>
                                        <i class="bi bi-person-fill" style="color: var(--primary);"></i>
                                        Ketua: <strong>{{ $ketuaTujuan->nama ?? ($timTujuan->nim ?? '-') }}</strong>
                                    </span>
                                    <span>&bull;</span>
                                    <span>
                                        <i class="bi bi-briefcase-fill" style="color: var(--primary);"></i>
                                        Posisi: <strong>{{ $pg->peran ?? 'Anggota' }}</strong>
                                    </span>
                                    @if($pg->created_at)
                                        <span>&bull;</span>
                                        <span>
                                            <i class="bi bi-calendar3"></i>
                                            Diajukan: <strong>{{ $pg->created_at->format('d M Y, H:i') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="application-note-box {{ $noteClass }}">
                                    <i class="bi {{ $noteIcon }}" style="font-size: 0.95rem;"></i>
                                    <span>{{ $statusNote }}</span>
                                </div>
                            </div>

                            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px dashed var(--border); padding-top: 10px; gap: 8px; flex-wrap: wrap;">
                                <div>
                                    @if($pg->file_cv)
                                        <a href="{{ route('tim.cv', ['type' => 'anggota', 'id' => $pg->id_anggota]) }}"
                                           target="_blank"
                                           class="btn btn-secondary btn-sm"
                                           style="font-size: 0.74rem; padding: 4px 10px;"
                                           title="Lihat Berkas CV yang Anda Lampirkan">
                                            <i class="bi bi-file-earmark-pdf"></i> Lihat CV Saya
                                        </a>
                                    @endif
                                </div>

                                <div style="display: flex; gap: 6px;">
                                    @if($status === 'diterima' && $timTujuan)
                                        <a href="{{ route('bimbingan.index', ['tim_id' => $timTujuan->id_tim]) }}"
                                           class="btn btn-outline-primary btn-sm"
                                           style="font-size: 0.74rem; font-weight: 700; padding: 4px 10px;">
                                            <i class="bi bi-person-video3"></i> Buka Bimbingan Tim &rarr;
                                        </a>
                                    @elseif($status === 'ditolak' && $timTujuan && $timTujuan->sisa_kuota_anggota > 0)
                                        <a href="{{ route('tim.gabung.form', $timTujuan->id_tim) }}"
                                           class="btn btn-outline-primary btn-sm"
                                           style="font-size: 0.74rem; font-weight: 700; padding: 4px 10px;"
                                           title="Ajukan permohonan baru dengan berkas atau peran yang diperbarui">
                                            <i class="bi bi-arrow-clockwise"></i> Ajukan Ulang &rarr;
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="background: #F8FAFC; border: 1px dashed var(--border); border-radius: var(--radius-md); padding: 24px 20px; text-align: center;">
                    <div style="width: 44px; height: 44px; border-radius: 50%; background: #EFF6FF; color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.3rem; margin: 0 auto 10px;">
                        <i class="bi bi-inbox-fill"></i>
                    </div>
                    <div style="font-size: 0.95rem; font-weight: 700; color: var(--primary-dark); margin-bottom: 4px;">
                        Kamu belum memiliki pengajuan gabung tim.
                    </div>
                    <p style="font-size: 0.82rem; color: var(--text-muted); max-width: 460px; margin: 0 auto;">
                        Temukan tim yang cocok dan masih membuka lowongan pada daftar tim di bawah, lalu ajukan diri untuk bergabung sebagai anggota.
                    </p>
                </div>
            @endif
        </div>
    @endif

    <!-- Daftar Tim Kompetisi Mahasiswa -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
        <div>
            <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--primary-dark); margin: 0;">
                <i class="bi bi-people-fill" style="color: var(--primary);"></i> Daftar Tim Kompetisi Mahasiswa
            </h3>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin: 2px 0 0 0;">
                Menampilkan <strong>{{ $teams->count() }} tim</strong> yang terdaftar dalam kompetisi.
            </p>
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
                    $isDitolak = $tim->anggota->where('nim', $authUser['id'])->where('status_gabung', 'ditolak')->isNotEmpty();
                    $sisaKuota = $tim->sisa_kuota_anggota;
                @endphp
                <div class="tim-card">
                    <div>
                        <!-- Header Kartu: Status Kuota & Tingkat -->
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
                        <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--primary-dark); margin-bottom: 6px; line-height: 1.3;">
                            {{ $tim->nama_tim }}
                        </h3>

                        <!-- Info Ketua Tim -->
                        <div style="display: flex; align-items: center; gap: 8px; font-size: 0.82rem; color: var(--text-muted); margin-bottom: 14px;">
                            <i class="bi bi-person-badge-fill" style="color: var(--primary);"></i>
                            <span>Ketua: <strong>{{ $tim->ketua->nama ?? '-' }}</strong> (NIM: {{ $tim->nim }})</span>
                        </div>

                        <!-- Box Info Lomba yang Diikuti -->
                        <div style="background: #F8FAFC; border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 12px 14px; margin-bottom: 16px;">
                            <div style="font-size: 0.7rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px; letter-spacing: 0.3px;">
                                KOMPETISI YANG DIIKUTI
                            </div>
                            <div style="font-weight: 700; color: var(--text-main); font-size: 0.92rem; margin-bottom: 6px; line-height: 1.4;">
                                {{ $tim->lomba->nama_lomba ?? 'Kompetisi JTI' }}
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 6px; font-size: 0.78rem; color: var(--text-muted);">
                                <span><i class="bi bi-building"></i> {{ $tim->lomba->penyelenggara ?? '-' }}</span>
                                <span><i class="bi bi-calendar3"></i> Tenggat: {{ $tim->lomba->tenggat ? $tim->lomba->tenggat->format('d M Y') : '-' }}</span>
                            </div>

                            @if($tim->lomba)
                                <div style="margin-top: 10px; border-top: 1px dashed var(--border); padding-top: 8px;">
                                    <a href="{{ route('lomba.show', $tim->lomba->id_lomba) }}"
                                       class="btn btn-secondary btn-sm"
                                       style="font-size: 0.75rem; padding: 4px 10px; width: 100%; justify-content: center;"
                                       title="Lihat Detail Lomba">
                                        <i class="bi bi-info-circle"></i> Lihat Informasi Lomba
                                    </a>
                                </div>
                            @endif
                        </div>

                        <!-- Susunan Anggota Tim -->
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

                    <!-- Tombol Aksi: Detail Tim & Formulir Gabung Tim Tersendiri -->
                    <div style="border-top: 1px solid var(--border); padding-top: 14px; margin-top: 6px; display: flex; gap: 8px;">
                        <button type="button"
                                class="btn btn-secondary btn-sm"
                                style="font-weight: 600;"
                                onclick="openDetailModal({{ $tim->id_tim }})"
                                title="Lihat rincian lengkap profil tim">
                            <i class="bi bi-eye"></i> Detail
                        </button>

                        @if($isKetua)
                            <a href="{{ route('bimbingan.index', ['tim_id' => $tim->id_tim]) }}"
                               class="btn btn-primary btn-sm"
                               style="flex: 1; font-weight: 700; justify-content: center;">
                                <i class="bi bi-shield-check"></i> Kelola Tim Saya
                            </a>
                        @elseif($isDiterima)
                            <a href="{{ route('bimbingan.index', ['tim_id' => $tim->id_tim]) }}"
                               class="btn btn-outline-primary btn-sm"
                               style="flex: 1; font-weight: 700; justify-content: center;">
                                <i class="bi bi-check-circle-fill"></i> Sudah Terdaftar
                            </a>
                        @elseif($isPending)
                            <button type="button"
                                    class="btn btn-secondary btn-sm"
                                    style="flex: 1; font-weight: 700; justify-content: center; opacity: 0.85;"
                                    disabled
                                    title="Pengajuan bergabung sedang menunggu keputusan ketua tim">
                                <i class="bi bi-clock-history"></i> Menunggu Persetujuan
                            </button>
                        @elseif($isDitolak && $sisaKuota > 0)
                            <a href="{{ route('tim.gabung.form', $tim->id_tim) }}"
                               class="btn btn-outline-primary btn-sm"
                               style="flex: 1; font-weight: 700; justify-content: center;"
                               title="Pengajuan sebelumnya ditolak oleh ketua tim. Anda dapat mengajukan ulang.">
                                <i class="bi bi-arrow-clockwise"></i> Ajukan Ulang &rarr;
                            </a>
                        @elseif($sisaKuota > 0)
                            <a href="{{ route('tim.gabung.form', $tim->id_tim) }}"
                               class="btn btn-outline-primary btn-sm"
                               style="flex: 1; font-weight: 700; justify-content: center;">
                                <i class="bi bi-person-plus-fill"></i> Ajukan Gabung Tim &rarr;
                            </a>
                        @else
                            <button type="button"
                                    class="btn btn-secondary btn-sm"
                                    style="flex: 1; font-weight: 600; justify-content: center; opacity: 0.6;"
                                    disabled>
                                <i class="bi bi-lock-fill"></i> Kuota Penuh
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div style="background: var(--surface); border: 1px dashed var(--border); border-radius: var(--radius-lg); padding: 48px 24px; text-align: center; margin-bottom: 40px;">
            <div style="width: 64px; height: 64px; border-radius: 50%; background: #EFF6FF; color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin: 0 auto 16px;">
                <i class="bi bi-people"></i>
            </div>
            <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--primary-dark); margin-bottom: 6px;">
                Tidak Ditemukan Tim yang Membuka Lowongan
            </h3>
            <p style="font-size: 0.9rem; color: var(--text-muted); max-width: 480px; margin: 0 auto 18px;">
                Coba sesuaikan kata kunci atau filter pencarian Anda, atau inisiasi tim delegasi baru sebagai Ketua Tim.
            </p>
            <a href="{{ route('tim.create') }}" class="btn btn-primary btn-sm" style="font-weight: 700;">
                <i class="bi bi-plus-circle-fill"></i> Bentuk Tim Baru Sekarang
            </a>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- MODAL DETAIL TIM LENGKAP                                                  --}}
    {{-- ========================================================================= --}}
    @foreach($teams as $tim)
        @php
            $sisaKuota = $tim->sisa_kuota_anggota;
        @endphp
        <div id="detailModal-{{ $tim->id_tim }}" class="modal-backdrop-custom">
            <div class="modal-box">
                <div class="modal-header-custom">
                    <div>
                        <div style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: var(--text-muted); letter-spacing: 0.5px;">Detail Tim Kompetisi</div>
                        <h4 style="font-size: 1.15rem; font-weight: 800; color: var(--primary-dark); margin: 2px 0 0 0;">
                            {{ $tim->nama_tim }}
                        </h4>
                    </div>
                    <button type="button" onclick="closeDetailModal({{ $tim->id_tim }})" style="background: none; border: none; font-size: 1.3rem; color: var(--text-muted); cursor: pointer;">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="modal-body-custom">
                    <div style="background: #F8FAFC; border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 14px; margin-bottom: 16px;">
                        <div style="font-size: 0.72rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px;">Kompetisi yang Diikuti</div>
                        <div style="font-weight: 800; color: var(--primary-dark); font-size: 1rem; margin-bottom: 6px;">
                            {{ $tim->lomba->nama_lomba ?? 'Kompetisi JTI' }}
                        </div>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 8px;">
                            <span class="badge badge-primary">{{ $tim->lomba->kategori ?? 'Umum' }}</span>
                            <span class="badge badge-purple">{{ $tim->lomba->tingkat ?? 'Nasional' }}</span>
                            @if($tim->lomba && $tim->lomba->biaya == 0)
                                <span class="badge badge-success">Gratis</span>
                            @endif
                        </div>
                        <div style="font-size: 0.8rem; color: var(--text-muted); line-height: 1.5;">
                            {{ $tim->lomba->deskripsi ?? 'Kompetisi mahasiswa resmi yang terdaftar di lingkungan Jurusan Teknologi Informasi Polinema.' }}
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 18px;">
                        <div style="background: #fff; border: 1px solid var(--border); border-radius: 6px; padding: 10px 14px;">
                            <div style="font-size: 0.72rem; color: var(--text-muted);">Ketua Tim</div>
                            <div style="font-weight: 700; color: var(--text-main); font-size: 0.9rem;">{{ $tim->ketua->nama ?? '-' }}</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">NIM: {{ $tim->nim }}</div>
                        </div>
                        <div style="background: #fff; border: 1px solid var(--border); border-radius: 6px; padding: 10px 14px;">
                            <div style="font-size: 0.72rem; color: var(--text-muted);">Kapasitas Kuota</div>
                            <div style="font-weight: 700; color: var(--text-main); font-size: 0.9rem;">
                                {{ $tim->anggotaDiterima->count() }} / {{ $tim->kuota_anggota }} Anggota
                            </div>
                            <div style="font-size: 0.75rem; color: {{ $sisaKuota > 0 ? 'var(--success)' : 'var(--danger)' }}; font-weight: 600;">
                                {{ $sisaKuota > 0 ? "Sisa {$sisaKuota} slot lowongan" : 'Kuota Penuh' }}
                            </div>
                        </div>
                    </div>

                    <div>
                        <div style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px;">
                            Daftar Anggota Tim Terdaftar:
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 6px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; background: #F8FAFC; border: 1px solid var(--border); border-radius: 6px; padding: 8px 12px; font-size: 0.85rem;">
                                <div>
                                    <strong>{{ $tim->ketua->nama ?? '-' }}</strong>
                                    <span style="font-size: 0.75rem; color: var(--text-muted); margin-left: 6px;">({{ $tim->ketua->prodi ?? 'Ketua' }})</span>
                                </div>
                                <span class="badge badge-primary">Ketua Tim</span>
                            </div>

                            @foreach($tim->anggotaDiterima as $ang)
                                @if($ang->nim !== $tim->nim)
                                    <div style="display: flex; justify-content: space-between; align-items: center; background: #fff; border: 1px solid var(--border); border-radius: 6px; padding: 8px 12px; font-size: 0.85rem;">
                                        <div>
                                            <strong>{{ $ang->mahasiswa->nama ?? $ang->nim }}</strong>
                                            <span style="font-size: 0.75rem; color: var(--text-muted); margin-left: 6px;">({{ $ang->mahasiswa->prodi ?? 'Anggota' }})</span>
                                        </div>
                                        <span class="badge badge-gray">{{ $ang->peran ?? 'Anggota' }}</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer-custom">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="closeDetailModal({{ $tim->id_tim }})">
                        Tutup
                    </button>
                    @if($sisaKuota > 0 && $tim->nim !== $authUser['id'] && !$tim->anggotaDiterima->contains('nim', $authUser['id']))
                        @if($tim->anggota->where('nim', $authUser['id'])->where('status_gabung', 'menunggu')->isNotEmpty())
                            <button type="button" class="btn btn-secondary btn-sm" disabled style="opacity: 0.85;">
                                <i class="bi bi-clock-history"></i> Menunggu Persetujuan
                            </button>
                        @elseif($tim->anggota->where('nim', $authUser['id'])->where('status_gabung', 'ditolak')->isNotEmpty())
                            <a href="{{ route('tim.gabung.form', $tim->id_tim) }}" class="btn btn-primary btn-sm" style="font-weight: 700;">
                                <i class="bi bi-arrow-clockwise"></i> Ajukan Ulang Gabung Tim &rarr;
                            </a>
                        @else
                            <a href="{{ route('tim.gabung.form', $tim->id_tim) }}" class="btn btn-primary btn-sm" style="font-weight: 700;">
                                <i class="bi bi-person-plus-fill"></i> Buka Formulir Gabung Tim &rarr;
                            </a>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    @endforeach

@endsection

@push('scripts')
<script>
    function openDetailModal(id) {
        const modal = document.getElementById('detailModal-' + id);
        if (modal) {
            modal.style.display = 'flex';
        }
    }

    function closeDetailModal(id) {
        const modal = document.getElementById('detailModal-' + id);
        if (modal) {
            modal.style.display = 'none';
        }
    }

    // Tutup modal ketika klik di luar box modal
    window.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-backdrop-custom')) {
            e.target.style.display = 'none';
        }
    });
</script>
@endpush
