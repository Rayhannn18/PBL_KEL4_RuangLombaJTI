<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'RuangLombaJTI') - Polinema</title>
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --primary: #1E40AF;
            --primary-dark: #0F2744;
            --primary-light: #3B82F6;
            --primary-soft: #EFF6FF;
            --secondary: #0D9488;
            --accent: #F59E0B;
            --accent-soft: #FEF3C7;
            --success: #10B981;
            --success-soft: #D1FAE5;
            --danger: #EF4444;
            --danger-soft: #FEE2E2;
            --bg-body: #F8FAFC;
            --surface: #FFFFFF;
            --border: #E2E8F0;
            --text-main: #0F172A;
            --text-muted: #64748B;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.07), 0 2px 4px -1px rgba(0,0,0,0.04);
            --shadow-lg: 0 10px 25px -5px rgba(15, 39, 68, 0.1), 0 8px 10px -6px rgba(15, 39, 68, 0.05);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 18px;
            --radius-full: 9999px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* Container */
        .container {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Top Bar */
        .topbar {
            background: linear-gradient(90deg, #0F2744 0%, #1E3A8A 100%);
            color: rgba(255, 255, 255, 0.85);
            font-size: 0.815rem;
            padding: 7px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .topbar-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .topbar-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,0.15);
            padding: 2px 10px;
            border-radius: 999px;
            color: #fff;
            font-weight: 500;
        }

        /* Main Navbar */
        .navbar {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        }
        .nav-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 72px;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .brand-logo {
            width: 44px;
            height: 44px;
            border-radius: var(--radius-md);
            background: linear-gradient(135deg, #1E40AF 0%, #3B82F6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.3rem;
            box-shadow: 0 4px 10px rgba(30, 64, 175, 0.3);
        }
        .brand-title {
            font-size: 1.25rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: var(--primary-dark);
            line-height: 1.1;
        }
        .brand-title span {
            color: #2563EB;
        }
        .brand-subtitle {
            font-size: 0.72rem;
            color: var(--text-muted);
            font-weight: 500;
            letter-spacing: 0.2px;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 6px;
            list-style: none;
        }
        .nav-link {
            padding: 9px 15px;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }
        .nav-link:hover {
            color: var(--primary);
            background-color: var(--primary-soft);
        }
        .nav-link.active {
            color: var(--primary);
            background-color: var(--primary-soft);
            font-weight: 700;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 9px 18px;
            font-size: 0.88rem;
            font-weight: 600;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid transparent;
            font-family: inherit;
        }
        .btn-primary {
            background-color: var(--primary);
            color: #fff;
            box-shadow: 0 2px 6px rgba(30, 64, 175, 0.25);
        }
        .btn-primary:hover {
            background-color: #1D4ED8;
            box-shadow: 0 4px 12px rgba(30, 64, 175, 0.35);
            transform: translateY(-1px);
        }
        .btn-outline-primary {
            border-color: #BFDBFE;
            color: var(--primary);
            background-color: #EFF6FF;
        }
        .btn-outline-primary:hover {
            background-color: var(--primary);
            color: #fff;
            border-color: var(--primary);
        }
        .btn-secondary {
            background-color: #F1F5F9;
            color: var(--text-main);
            border-color: var(--border);
        }
        .btn-secondary:hover {
            background-color: #E2E8F0;
        }
        .btn-success {
            background-color: var(--success);
            color: #fff;
        }
        .btn-success:hover {
            background-color: #059669;
        }
        .btn-danger {
            background-color: var(--danger);
            color: #fff;
        }
        .btn-danger:hover {
            background-color: #DC2626;
        }
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8rem;
            border-radius: 6px;
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 700;
            line-height: 1;
        }
        .badge-primary { background: #DBEAFE; color: #1E40AF; }
        .badge-success { background: #D1FAE5; color: #065F46; }
        .badge-warning { background: #FEF3C7; color: #92400E; }
        .badge-danger { background: #FEE2E2; color: #991B1B; }
        .badge-purple { background: #EDE9FE; color: #5B21B6; }
        .badge-gray { background: #F1F5F9; color: #475569; }

        /* Alerts */
        .alert {
            padding: 14px 18px;
            border-radius: var(--radius-md);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .alert-success {
            background-color: #ECFDF5;
            color: #065F46;
            border: 1px solid #A7F3D0;
        }
        .alert-danger {
            background-color: #FEF2F2;
            color: #991B1B;
            border: 1px solid #FECACA;
        }
        .alert-close {
            background: none;
            border: none;
            cursor: pointer;
            color: inherit;
            font-size: 1.1rem;
        }

        /* Cards */
        .card {
            background: var(--surface);
            border-radius: var(--radius-md);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            transition: all 0.25s ease;
        }
        .card-hover:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
            border-color: #CBD5E1;
        }
        .card-header {
            padding: 18px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .card-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--primary-dark);
        }
        .card-body {
            padding: 24px;
        }

        /* Forms */
        .form-group {
            margin-bottom: 18px;
        }
        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--text-main);
        }
        .form-control, .form-select {
            width: 100%;
            padding: 10px 14px;
            font-size: 0.9rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            background-color: #fff;
            color: var(--text-main);
            font-family: inherit;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }
        textarea.form-control {
            resize: vertical;
            min-height: 90px;
        }

        /* Main Content */
        main {
            flex: 1;
            padding: 30px 0 60px;
        }

        /* Footer */
        footer {
            background-color: #0F172A;
            color: #94A3B8;
            padding: 40px 0 20px;
            font-size: 0.88rem;
            border-top: 1px solid #1E293B;
        }
        .footer-content {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 40px;
            margin-bottom: 30px;
        }
        .footer-brand {
            color: #fff;
            font-size: 1.2rem;
            font-weight: 800;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .footer-brand span { color: #60A5FA; }
        .footer-bottom {
            padding-top: 20px;
            border-top: 1px solid #1E293B;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8rem;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .nav-links { display: none; }
            .footer-content { grid-template-columns: 1fr; gap: 24px; }
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Topbar info -->
    <div class="topbar">
        <div class="container topbar-content">
            <div style="display: flex; align-items: center; gap: 15px;">
                <span><i class="bi bi-mortarboard-fill"></i> Jurusan Teknologi Informasi - Politeknik Negeri Malang</span>
                <span class="topbar-badge"><i class="bi bi-shield-check"></i> RuangLombaJTI v1.0</span>
            </div>
            <div style="display: flex; align-items: center; gap: 15px;">
                @if(session('auth_user'))
                    <span style="display: flex; align-items: center; gap: 6px;">
                        <i class="bi bi-person-check-fill" style="color: #6EE7B7;"></i>
                        <span>{{ session('auth_user.nama') }}</span>
                        <span class="badge {{ session('auth_user.role') == 'admin' ? 'badge-danger' : (session('auth_user.role') == 'dosen' ? 'badge-warning' : 'badge-primary') }}" style="font-size: 0.72rem; padding: 2px 8px;">
                            {{ ucfirst(session('auth_user.role')) }}
                        </span>
                    </span>
                    <form action="{{ route('auth.logout') }}" method="POST" style="display: inline; margin: 0;">
                        @csrf
                        <button type="submit" style="background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.4); color: #FCA5A5; padding: 2px 10px; border-radius: 999px; font-size: 0.75rem; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; font-family: inherit;">
                            <i class="bi bi-box-arrow-right"></i> Keluar
                        </button>
                    </form>
                @else
                    <span style="opacity: 0.85;"><i class="bi bi-person"></i> Tamu / Pengunjung</span>
                    <a href="{{ route('login') }}" style="color: #93C5FD; font-weight: 600; text-decoration: underline; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 4px;">
                        <i class="bi bi-box-arrow-in-right"></i> Masuk Akun
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Main Navigation Bar -->
    <nav class="navbar">
        <div class="container nav-content">
            <a href="{{ route('dashboard.analitik') }}" class="brand">
                <div class="brand-logo">
                    <i class="bi bi-trophy-fill"></i>
                </div>
                <div>
                    <div class="brand-title">RuangLomba<span>JTI</span></div>
                    <div class="brand-subtitle">Monitoring Lomba & Analitik Prestasi Mahasiswa</div>
                </div>
            </a>

            <ul class="nav-links">
                <li>
                    <a href="{{ route('dashboard.analitik') }}" class="nav-link {{ request()->routeIs('dashboard.*') ? 'active' : '' }}">
                        <i class="bi bi-graph-up-arrow"></i> Dashboard Analitik
                    </a>
                </li>
            </ul>

            <div class="nav-actions">
                @if(session('auth_user'))
                    <form action="{{ route('auth.logout') }}" method="POST" style="display: inline; margin: 0;">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm" title="Keluar dari sesi">
                            <i class="bi bi-box-arrow-right"></i> Keluar
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-box-arrow-in-right"></i> Masuk
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-person-plus-fill"></i> Daftar Mahasiswa
                    </a>
                @endif
            </div>
        </div>
    </nav>

    <!-- Main Body Container -->
    <main>
        <div class="container">
            <!-- Flash Message Alert -->
            @if(session('success'))
                <div class="alert alert-success">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <i class="bi bi-check-circle-fill" style="font-size: 1.2rem;"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <i class="bi bi-exclamation-triangle-fill" style="font-size: 1.2rem;"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <div>
                        <div style="font-weight: 700; margin-bottom: 5px;"><i class="bi bi-x-circle-fill"></i> Terdapat kesalahan input:</div>
                        <ul style="padding-left: 20px; font-size: 0.85rem;">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div>
                    <div class="footer-brand">
                        <i class="bi bi-trophy-fill" style="color: #60A5FA;"></i> RuangLomba<span>JTI</span>
                    </div>
                    <p style="margin-bottom: 12px; max-width: 480px; line-height: 1.7;">
                        Sistem Informasi Monitoring Lomba dan Prestasi Mahasiswa Jurusan Teknologi Informasi Politeknik Negeri Malang berbasis kerangka kerja Laravel MVC.
                    </p>
                    <div style="display: flex; gap: 10px; font-size: 0.8rem; color: #94A3B8;">
                        <span><i class="bi bi-geo-alt"></i> Gedung Sipil & JTI Polinema, Jl. Soekarno-Hatta No. 9 Malang</span>
                    </div>
                </div>

                <div>
                    <h4 style="color: #fff; font-size: 0.95rem; margin-bottom: 14px; font-weight: 700;">Navigasi Sistem</h4>
                    <ul style="list-style: none; display: flex; flex-direction: column; gap: 8px;">
                        <li><a href="{{ route('dashboard.analitik') }}" style="color: #94A3B8;"><i class="bi bi-chevron-right"></i> Dashboard Analitik</a></li>
                        <li><a href="{{ route('login') }}" style="color: #94A3B8;"><i class="bi bi-chevron-right"></i> Masuk Sistem</a></li>
                        <li><a href="{{ route('register') }}" style="color: #94A3B8;"><i class="bi bi-chevron-right"></i> Pendaftaran Mahasiswa</a></li>
                    </ul>
                </div>

                <div>
                    <h4 style="color: #fff; font-size: 0.95rem; margin-bottom: 14px; font-weight: 700;">Tim Pengembang PBL</h4>
                    <p style="font-size: 0.82rem; line-height: 1.6;">
                        <strong>Program Studi:</strong> D-IV Sistem Informasi Bisnis (SIB-3F)<br>
                        <strong>Sastra Maheva Zaky</strong> (Backend & DB)<br>
                        <strong>Nadya Syantika Naraya</strong> (Backend Lomba & Tim)<br>
                        <strong>Gempita Fitri Nurdini</strong> (Frontend & QA)<br>
                        <strong>Rayhan Giri Putra</strong> (UI/UX & PM)
                    </p>
                </div>
            </div>

            <div class="footer-bottom">
                <div>&copy; 2026 RuangLombaJTI - Politeknik Negeri Malang. All rights reserved.</div>
                <div>Arsitektur Model-View-Controller (MVC) Laravel Framework</div>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
