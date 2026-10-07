@extends('layouts.app')

@section('title', 'Dashboard Analitik Prestasi - RuangLombaJTI')

@push('styles')
<style>
    /* Dashboard Specific Styles */
    .dashboard-header {
        background: linear-gradient(135deg, #0F2744 0%, #1E3A8A 60%, #2563EB 100%);
        border-radius: var(--radius-lg);
        padding: 36px 36px;
        color: #fff;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 30px -10px rgba(15, 39, 68, 0.4);
    }
    .dashboard-header::after {
        content: "";
        position: absolute;
        right: -40px;
        top: -60px;
        width: 320px;
        height: 320px;
        background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, rgba(255,255,255,0) 70%);
        border-radius: 50%;
        pointer-events: none;
    }
    .dashboard-header-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(8px);
        padding: 6px 14px;
        border-radius: var(--radius-full);
        font-size: 0.8rem;
        font-weight: 600;
        margin-bottom: 14px;
        border: 1px solid rgba(255,255,255,0.2);
    }

    /* KPI Grid */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .kpi-card {
        background: #fff;
        border-radius: var(--radius-md);
        border: 1px solid var(--border);
        padding: 22px 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: var(--shadow-sm);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }
    .kpi-icon {
        width: 54px;
        height: 54px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        flex-shrink: 0;
    }
    .kpi-icon.gold { background: #FEF3C7; color: #D97706; }
    .kpi-icon.blue { background: #DBEAFE; color: #1E40AF; }
    .kpi-icon.teal { background: #CCFBF1; color: #0F766E; }
    .kpi-icon.emerald { background: #D1FAE5; color: #047857; }
    .kpi-icon.purple { background: #EDE9FE; color: #6D28D9; }

    .kpi-value {
        font-size: 1.85rem;
        font-weight: 800;
        color: var(--primary-dark);
        line-height: 1.1;
    }
    .kpi-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .kpi-sub {
        font-size: 0.72rem;
        color: var(--success);
        display: flex;
        align-items: center;
        gap: 4px;
        margin-top: 3px;
        font-weight: 600;
    }

    /* Charts Layout */
    .charts-grid {
        display: grid;
        grid-template-columns: 1.6fr 1fr;
        gap: 24px;
        margin-bottom: 30px;
    }
    .charts-grid-secondary {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin-bottom: 30px;
    }

    /* Tables */
    .table-container {
        overflow-x: auto;
    }
    .custom-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 0.88rem;
    }
    .custom-table th {
        background-color: #F8FAFC;
        color: var(--text-muted);
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.72rem;
        letter-spacing: 0.5px;
        padding: 12px 16px;
        border-bottom: 1px solid var(--border);
        text-align: left;
    }
    .custom-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #F1F5F9;
        vertical-align: middle;
    }
    .custom-table tr:hover td {
        background-color: #F8FAFC;
    }

    /* Monitoring Cards */
    .stage-timeline {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .stage-item {
        background: #F8FAFC;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 14px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        transition: all 0.2s;
    }
    .stage-item:hover {
        border-color: #BFDBFE;
        background: #F0F7FF;
    }

    @media (max-width: 992px) {
        .charts-grid, .charts-grid-secondary {
            grid-template-columns: 1fr;
        }
        .dashboard-header {
            padding: 24px 20px;
        }
    }
</style>
@endpush

@section('content')

    <!-- Dashboard Hero Header -->
    <div class="dashboard-header">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 20px;">
            <div>
                <div class="dashboard-header-badge">
                    <i class="bi bi-bar-chart-fill"></i> Sistem Informasi Manajemen Prestasi JTI
                </div>
                <h1 style="font-size: 1.9rem; font-weight: 800; letter-spacing: -0.5px; margin-bottom: 8px;">
                    Dashboard Analitik & Rekapitulasi Lomba
                </h1>
                <p style="font-size: 0.95rem; opacity: 0.9; max-width: 650px; line-height: 1.6;">
                    Pemantauan terpusat keikutsertaan kompetisi, progres pembimbingan babak, serta rekapitulasi capaian prestasi mahasiswa Jurusan Teknologi Informasi Polinema.
                </p>
            </div>

            <!-- Quick Filter Bar -->
            <div style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); padding: 12px 16px; border-radius: var(--radius-md); border: 1px solid rgba(255,255,255,0.2);">
                <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; margin-bottom: 8px; letter-spacing: 0.5px;">
                    <i class="bi bi-funnel-fill"></i> Filter Laporan SIM
                </div>
                <form method="GET" action="{{ route('dashboard.analitik') }}" style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <select name="tahun" class="form-select form-select-sm" style="width: auto; background: rgba(255,255,255,0.9); font-size: 0.8rem; padding: 6px 12px;" onchange="this.form.submit()">
                        <option value="semua" {{ $tahunFilter == 'semua' ? 'selected' : '' }}>Semua Tahun</option>
                        <option value="2026" {{ $tahunFilter == '2026' ? 'selected' : '' }}>Tahun 2026</option>
                        <option value="2025" {{ $tahunFilter == '2025' ? 'selected' : '' }}>Tahun 2025</option>
                        <option value="2024" {{ $tahunFilter == '2024' ? 'selected' : '' }}>Tahun 2024</option>
                    </select>

                    <button type="submit" class="btn btn-primary btn-sm" style="background: #fff; color: var(--primary); font-weight: 700;">
                        <i class="bi bi-funnel"></i> Terapkan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- 1. KPI Cards Grid -->
    <div class="kpi-grid">
        <!-- KPI 1: Prestasi Juara -->
        <div class="kpi-card">
            <div class="kpi-icon gold">
                <i class="bi bi-trophy-fill"></i>
            </div>
            <div>
                <div class="kpi-value">{{ $totalPrestasi }}</div>
                <div class="kpi-label">Prestasi Juara</div>
                <div class="kpi-sub">
                    <i class="bi bi-award-fill"></i> Juara 1, 2, 3 & Harapan
                </div>
            </div>
        </div>

        <!-- KPI 2: Total Tim Terdaftar -->
        <div class="kpi-card">
            <div class="kpi-icon blue">
                <i class="bi bi-people-fill"></i>
            </div>
            <div>
                <div class="kpi-value">{{ $totalTim }}</div>
                <div class="kpi-label">Tim Kompetisi</div>
                <div class="kpi-sub">
                    <i class="bi bi-check-circle-fill"></i> Disetujui Admin
                </div>
            </div>
        </div>

        <!-- KPI 3: Lomba Aktif -->
        <div class="kpi-card">
            <div class="kpi-icon teal">
                <i class="bi bi-calendar-check-fill"></i>
            </div>
            <div>
                <div class="kpi-value">{{ $lombaAktif }} <span style="font-size: 1rem; color: var(--text-muted); font-weight: 500;">/ {{ $totalLomba }}</span></div>
                <div class="kpi-label">Lomba Terbuka</div>
                <div class="kpi-sub" style="color: var(--primary);">
                    <i class="bi bi-patch-check-fill"></i> Terverifikasi Resmi
                </div>
            </div>
        </div>

        <!-- KPI 4: Mahasiswa Aktif -->
        <div class="kpi-card">
            <div class="kpi-icon emerald">
                <i class="bi bi-mortarboard-fill"></i>
            </div>
            <div>
                <div class="kpi-value">{{ $totalMahasiswaAktif }}</div>
                <div class="kpi-label">Mahasiswa Aktif</div>
                <div class="kpi-sub">
                    <i class="bi bi-person-fill"></i> Partisipan Lomba JTI
                </div>
            </div>
        </div>

        <!-- KPI 5: Dosen Pembimbing Aktif -->
        <div class="kpi-card">
            <div class="kpi-icon purple">
                <i class="bi bi-person-badge-fill"></i>
            </div>
            <div>
                <div class="kpi-value">{{ $totalDosenAktif }}</div>
                <div class="kpi-label">Dosen Pembimbing</div>
                <div class="kpi-sub" style="color: #6D28D9;">
                    <i class="bi bi-journal-check"></i> Sesuai Bidang Keahlian
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Charts Section (SIM Analytics Visualization) -->
    <div class="charts-grid">
        <!-- Chart 1: Perbandingan Program Studi -->
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title"><i class="bi bi-bar-chart-line-fill" style="color: var(--primary);"></i> Partisipasi & Prestasi per Program Studi</div>
                    <div style="font-size: 0.78rem; color: var(--text-muted);">Perbandingan keikutsertaan tim dan perolehan juara antara D-IV SIB vs D-IV TI</div>
                </div>
                <span class="badge badge-primary">SIM JTI</span>
            </div>
            <div class="card-body">
                <div style="height: 290px;">
                    <canvas id="chartProdi"></canvas>
                </div>
            </div>
        </div>

        <!-- Chart 2: Distribusi Tingkat Lomba -->
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title"><i class="bi bi-pie-chart-fill" style="color: var(--accent);"></i> Distribusi Tingkat Kompetisi</div>
                    <div style="font-size: 0.78rem; color: var(--text-muted);">Nasional, Internasional, & Regional</div>
                </div>
            </div>
            <div class="card-body">
                <div style="height: 290px; display: flex; align-items: center; justify-content: center;">
                    <canvas id="chartTingkat"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Charts: Tren Angkatan & Kategori Lomba -->
    <div class="charts-grid-secondary">
        <!-- Chart 3: Paling Sering Juara per Angkatan -->
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                <div>
                    <div class="card-title"><i class="bi bi-trophy-fill" style="color: var(--success);"></i> Paling Sering Juara per Angkatan</div>
                    <div style="font-size: 0.78rem; color: var(--text-muted);">Perolehan prestasi juara kompetisi berdasarkan 4 angkatan akademik</div>
                </div>
                @if(isset($angkatanTerbaik) && $angkatanTerbaik->total_juara > 0)
                    <span class="badge badge-success" style="font-size: 0.72rem; padding: 4px 10px;">
                        <i class="bi bi-award-fill"></i> Terbanyak: Angkatan {{ $angkatanTerbaik->angkatan }} ({{ $angkatanTerbaik->total_juara }} Juara)
                    </span>
                @endif
            </div>
            <div class="card-body">
                <div style="height: 250px;">
                    <canvas id="chartAngkatan"></canvas>
                </div>
            </div>
        </div>

        <!-- Chart 4: Kategori Lomba -->
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title"><i class="bi bi-tags-fill" style="color: #6D28D9;"></i> Kategori Kompetisi Paling Diminati</div>
                    <div style="font-size: 0.78rem; color: var(--text-muted);">Sebaran bidang lomba yang tersedia di JTI</div>
                </div>
            </div>
            <div class="card-body">
                <div style="height: 250px;">
                    <canvas id="chartKategori"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Monitoring & Leaderboard Tables Section -->
    <div style="display: grid; grid-template-columns: 1.4fr 1fr; gap: 24px; margin-bottom: 30px;">
        <!-- Left: Leaderboard Hall of Fame Prestasi -->
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title"><i class="bi bi-trophy" style="color: #D97706;"></i> Rekapitulasi Prestasi & Juara Terbaru</div>
                    <div style="font-size: 0.78rem; color: var(--text-muted);">Daftar tim dan perolehan penghargaan resmi mahasiswa JTI</div>
                </div>
                <span class="badge badge-success"><i class="bi bi-check2-circle"></i> Terverifikasi Pembimbing</span>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Tim & Kompetisi</th>
                                <th>Hasil Prestasi</th>
                                <th>Dosen Pembimbing</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($daftarJuara as $prestasi)
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; color: var(--primary-dark);">
                                            {{ $prestasi->pengajuan->tim->nama_tim ?? '-' }}
                                        </div>
                                        <div style="font-size: 0.78rem; color: var(--text-muted);">
                                            {{ $prestasi->pengajuan->tim->lomba->nama_lomba ?? '-' }}
                                        </div>
                                        <span class="badge badge-primary" style="font-size: 0.68rem; margin-top: 4px;">
                                            {{ $prestasi->pengajuan->tim->ketua->prodi ?? 'JTI Polinema' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="badge badge-warning" style="font-size: 0.8rem; padding: 6px 10px;">
                                            <i class="bi bi-award-fill"></i> {{ $prestasi->hasil_akhir }}
                                        </div>
                                        <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 4px;">
                                            Babak: {{ $prestasi->babak }}
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; font-size: 0.85rem;">
                                            {{ $prestasi->pengajuan->dosen->nama ?? '-' }}
                                        </div>
                                        <div style="font-size: 0.72rem; color: var(--text-muted);">
                                            NIDN: {{ $prestasi->pengajuan->dosen->nidn ?? '-' }}
                                        </div>
                                    </td>
                                    <td style="font-size: 0.8rem; color: var(--text-muted);">
                                        {{ $prestasi->tanggal_update ? $prestasi->tanggal_update->format('d M Y') : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                        Belum ada data prestasi juara yang tercatat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right: Monitoring Babak Aktif Berjalan -->
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title"><i class="bi bi-activity" style="color: var(--primary);"></i> Monitoring Progres Babak</div>
                    <div style="font-size: 0.78rem; color: var(--text-muted);">Status tahapan lomba yang sedang berlangsung</div>
                </div>
            </div>
            <div class="card-body">
                <div class="stage-timeline">
                    @forelse($babakMonitoring as $progres)
                        <div class="stage-item">
                            <div>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <strong style="color: var(--primary-dark); font-size: 0.9rem;">
                                        {{ $progres->pengajuan->tim->nama_tim ?? '-' }}
                                    </strong>
                                    <span class="badge badge-purple" style="font-size: 0.7rem;">
                                        {{ $progres->babak }}
                                    </span>
                                </div>
                                <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 3px;">
                                    <i class="bi bi-flag-fill" style="color: var(--accent);"></i> {{ $progres->pengajuan->tim->lomba->nama_lomba ?? '-' }}
                                </div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
                                    Pembimbing: {{ $progres->pengajuan->dosen->nama ?? '-' }}
                                </div>
                            </div>
                            <div style="text-align: right; flex-shrink: 0;">
                                <span class="badge {{ str_contains(strtolower($progres->hasil_akhir ?? ''), 'juara') ? 'badge-warning' : 'badge-success' }}">
                                    {{ $progres->hasil_akhir ?? 'Berlangsung' }}
                                </span>
                                <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 4px;">
                                    {{ $progres->tanggal_update ? $progres->tanggal_update->format('d/m/Y') : '-' }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div style="text-align: center; padding: 20px; color: var(--text-muted);">
                            Belum ada aktivitas progres babak yang terekam.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>


@endsection

@push('scripts')
<script>
    // Initialize Chart.js when DOM is ready
    document.addEventListener('DOMContentLoaded', function () {
        // Data from PHP Controller
        const prodiLabels = ['D-IV Sistem Informasi Bisnis', 'D-IV Teknik Informatika'];
        const prodiJuaraData = [
            {{ $prestasiPerProdi['D-IV Sistem Informasi Bisnis'] ?? 0 }},
            {{ $prestasiPerProdi['D-IV Teknik Informatika'] ?? 1 }}
        ];
        const prodiTimData = [
            {{ $prodiStats->firstWhere('prodi', 'D-IV Sistem Informasi Bisnis')->total_tim ?? 2 }},
            {{ $prodiStats->firstWhere('prodi', 'D-IV Teknik Informatika')->total_tim ?? 2 }}
        ];

        // 1. Chart Prodi (Bar Chart: Prestasi vs Partisipasi)
        const ctxProdi = document.getElementById('chartProdi').getContext('2d');
        new Chart(ctxProdi, {
            type: 'bar',
            data: {
                labels: prodiLabels,
                datasets: [
                    {
                        label: 'Total Tim Berkompetisi',
                        data: prodiTimData,
                        backgroundColor: '#3B82F6',
                        borderRadius: 6,
                    },
                    {
                        label: 'Prestasi Juara Diraih',
                        data: prodiJuaraData,
                        backgroundColor: '#F59E0B',
                        borderRadius: 6,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { font: { family: 'Plus Jakarta Sans', weight: '600', size: 12 } }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });

        // 2. Chart Tingkat Lomba (Doughnut)
        const tingkatLabels = {!! json_encode(array_keys($tingkatStats)) !!};
        const tingkatData = {!! json_encode(array_values($tingkatStats)) !!};
        const ctxTingkat = document.getElementById('chartTingkat').getContext('2d');
        new Chart(ctxTingkat, {
            type: 'doughnut',
            data: {
                labels: tingkatLabels,
                datasets: [{
                    data: tingkatData,
                    backgroundColor: ['#1E40AF', '#10B981', '#F59E0B', '#6366F1'],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { family: 'Plus Jakarta Sans', weight: '600' } }
                    }
                },
                cutout: '65%'
            }
        });

        // 3. Chart Angkatan - Paling Sering Juara (Curva Hijau 4 Angkatan)
        const angkatanLabels = {!! json_encode($angkatanStats->pluck('angkatan')) !!};
        const angkatanData = {!! json_encode($angkatanStats->pluck('total_juara')) !!};
        const ctxAngkatan = document.getElementById('chartAngkatan').getContext('2d');
        new Chart(ctxAngkatan, {
            type: 'line',
            data: {
                labels: angkatanLabels,
                datasets: [{
                    label: 'Prestasi Juara Diraih',
                    data: angkatanData,
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.12)',
                    tension: 0.38,
                    fill: true,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    pointBackgroundColor: '#10B981',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' ' + context.parsed.y + ' Prestasi Juara';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });

        // 4. Chart Kategori (Horizontal Bar)
        const katLabels = {!! json_encode($kategoriStats->pluck('kategori')) !!};
        const katData = {!! json_encode($kategoriStats->pluck('count')) !!};
        const ctxKategori = document.getElementById('chartKategori').getContext('2d');
        new Chart(ctxKategori, {
            type: 'bar',
            data: {
                labels: katLabels,
                datasets: [{
                    data: katData,
                    backgroundColor: '#8B5CF6',
                    borderRadius: 6
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    });
</script>
@endpush
