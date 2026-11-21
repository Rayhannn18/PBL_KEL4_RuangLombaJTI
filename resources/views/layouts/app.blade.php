<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'RuangLombaJTI') - Polinema</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-ruanglombajti.png') }}">
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
            position: relative;
            z-index: 60;
        }
        .topbar-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
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
        .topbar-user-dropdown {
            position: relative;
            display: inline-flex;
            align-items: center;
        }
        .topbar-user-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 999px;
            padding: 3px 10px;
            color: #fff;
            font-family: inherit;
            font-size: 0.815rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s ease, border-color 0.2s ease, transform 0.15s ease;
        }
        .topbar-user-chip:hover,
        .topbar-user-chip[aria-expanded="true"] {
            background: rgba(255, 255, 255, 0.18);
            border-color: rgba(255, 255, 255, 0.38);
        }
        .topbar-user-arrow {
            font-size: 0.7rem;
            opacity: 0.85;
            transition: transform 0.2s ease;
            margin-left: 2px;
        }
        .topbar-user-chip[aria-expanded="true"] .topbar-user-arrow {
            transform: rotate(180deg);
        }
        .topbar-dropdown-menu {
            display: none;
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            min-width: 195px;
            background: var(--surface);
            color: var(--text-main);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-lg);
            padding: 6px;
            z-index: 100;
        }
        .topbar-dropdown-menu.open {
            display: block;
        }
        .topbar-dropdown-item {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 8px 12px;
            border-radius: var(--radius-sm);
            font-size: 0.84rem;
            font-weight: 600;
            color: var(--text-main);
            transition: background-color 0.15s ease, color 0.15s ease;
        }
        .topbar-dropdown-item i {
            color: var(--primary);
            font-size: 0.95rem;
        }
        .topbar-dropdown-item:hover {
            background-color: var(--primary-soft);
            color: var(--primary);
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
            width: 46px;
            height: 46px;
            border-radius: var(--radius-md);
            background: transparent;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .brand-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
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

        /* Hero Header / Dashboard Header Style (Consistent across Modules) */
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
        .dashboard-header-user {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(8px);
            padding: 14px 20px;
            border-radius: var(--radius-md);
            border: 1px solid rgba(255, 255, 255, 0.25);
            text-align: right;
        }
        @media (max-width: 992px) {
            .dashboard-header {
                padding: 24px 20px;
            }
            .dashboard-header-user {
                text-align: left;
                width: 100%;
            }
        }

        /* Main Content */
        main {
            flex: 1;
            padding: 30px 0 60px;
        }

        /* ========================================================================= */
        /* Redesigned Site Footers (Guest & Authenticated with Header Gradient)     */
        /* ========================================================================= */
        .site-footer-guest {
            background: linear-gradient(135deg, #0F2744 0%, #142F5B 50%, #1E3A8A 100%);
            color: #CBD5E1;
            border-top: 1px solid rgba(255, 255, 255, 0.12);
            padding: 48px 0 24px;
            font-size: 0.88rem;
            width: 100%;
            margin-top: auto;
            position: relative;
        }
        .site-footer-guest .footer-container {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .site-footer-guest .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1.15fr;
            gap: 40px;
            padding-bottom: 32px;
        }
        .site-footer-guest .footer-brand-title {
            color: #FFFFFF;
            font-size: 1.25rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            margin-bottom: 12px;
        }
        .site-footer-guest .footer-brand-title span {
            color: #60A5FA;
        }
        .site-footer-guest .footer-heading {
            color: #FFFFFF;
            font-size: 0.82rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 14px;
        }
        .site-footer-guest .footer-nav-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .site-footer-guest .footer-nav-link {
            color: #CBD5E1;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.86rem;
            transition: all 0.2s ease;
        }
        .site-footer-guest .footer-nav-link:hover {
            color: #FFFFFF;
            transform: translateX(3px);
        }
        .site-footer-guest .footer-info-item {
            color: #CBD5E1;
            font-size: 0.86rem;
            line-height: 1.6;
            margin-bottom: 6px;
        }
        .site-footer-guest .footer-external-link {
            color: #CBD5E1;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.82rem;
            transition: color 0.2s ease;
        }
        .site-footer-guest .footer-external-link:hover {
            color: #FFFFFF;
        }
        .site-footer-guest .footer-bottom-bar {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.7);
        }

        /* Authenticated Footer (Compact with Topbar Gradient) */
        .site-footer-auth {
            background: linear-gradient(90deg, #0F2744 0%, #1E3A8A 100%);
            color: rgba(255, 255, 255, 0.85);
            border-top: 1px solid rgba(255, 255, 255, 0.12);
            padding: 12px 0;
            font-size: 0.78rem;
            width: 100%;
            margin-top: auto;
        }
        .site-footer-auth .footer-container {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }
        .site-footer-auth .footer-auth-left {
            color: rgba(255, 255, 255, 0.95);
            font-weight: 500;
        }
        .site-footer-auth .footer-auth-right {
            color: rgba(255, 255, 255, 0.75);
            text-align: right;
            font-size: 0.76rem;
        }

        /* Mobile Toggle Button */
        .mobile-toggle-btn {
            display: none;
            background: transparent;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 6px 10px;
            font-size: 1.25rem;
            color: var(--text-main);
            cursor: pointer;
            transition: all 0.2s ease;
            line-height: 1;
        }
        .mobile-toggle-btn:hover {
            background-color: var(--primary-soft);
            color: var(--primary);
            border-color: #BFDBFE;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .mobile-toggle-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }
            .nav-links {
                display: none;
                position: absolute;
                top: 72px;
                left: 0;
                right: 0;
                background: var(--surface);
                border-bottom: 1px solid var(--border);
                box-shadow: 0 12px 28px rgba(15, 39, 68, 0.1);
                flex-direction: column;
                align-items: stretch;
                padding: 16px 20px 20px;
                gap: 8px;
                z-index: 100;
            }
            .nav-links.mobile-open {
                display: flex;
                animation: navSlideDown 0.2s ease-out;
            }
            @keyframes navSlideDown {
                from {
                    opacity: 0;
                    transform: translateY(-8px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            .nav-link {
                padding: 11px 16px;
                border-radius: var(--radius-sm);
                font-size: 0.92rem;
                background-color: #F8FAFC;
                border: 1px solid #F1F5F9;
            }
            .nav-link.active {
                background-color: var(--primary-soft);
                border-color: #BFDBFE;
            }
            .site-footer-guest .footer-grid {
                grid-template-columns: 1fr;
                gap: 28px;
            }
        }

        @media (max-width: 640px) {
            .brand-subtitle {
                display: none;
            }
            .brand-title {
                font-size: 1.15rem;
            }
            .brand-logo {
                width: 38px;
                height: 38px;
            }
            .topbar-content {
                gap: 6px;
            }
            .topbar-univ-long {
                display: none;
            }
            .topbar {
                font-size: 0.75rem;
                padding: 5px 0;
            }
            .topbar-badge {
                font-size: 0.7rem;
                padding: 2px 7px;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 14px;
            }
            .nav-actions {
                gap: 8px;
            }
            .nav-actions .btn {
                padding: 6px 10px;
                font-size: 0.78rem;
            }
            .site-footer-guest .footer-bottom-bar {
                flex-direction: column;
                gap: 8px;
                text-align: center;
            }
            .site-footer-auth .footer-container {
                flex-direction: column;
                gap: 6px;
                text-align: center;
            }
            .site-footer-auth .footer-auth-right {
                text-align: center;
            }
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Topbar info -->
    <div class="topbar">
        <div class="container topbar-content">
            <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                <span><i class="bi bi-mortarboard-fill"></i> <span class="topbar-univ-long">Jurusan Teknologi Informasi - </span>Polinema</span>
                <span class="topbar-badge"><i class="bi bi-shield-check"></i> RuangLombaJTI v1.0</span>
            </div>
            <div style="display: flex; align-items: center; gap: 15px;">
                @auth
                    <x-topbar-user-dropdown />
                @else
                    <span style="opacity: 0.85;"><i class="bi bi-person"></i> Tamu / Pengunjung</span>
                @endauth
            </div>
        </div>
    </div>

    <!-- Main Navigation Bar -->
    <nav class="navbar">
        <div class="container nav-content">
            <a href="{{ route('dashboard.analitik') }}" class="brand">
                <div class="brand-logo">
                    <img src="{{ asset('images/logo-ruanglombajti.png') }}" alt="Logo RuangLombaJTI">
                </div>
                <div>
                    <div class="brand-title">RuangLomba<span>JTI</span></div>
                    <div class="brand-subtitle">Monitoring Lomba & Analitik Prestasi Mahasiswa</div>
                </div>
            </a>

            <ul class="nav-links">
                <li>
                    <a href="{{ route('dashboard.analitik') }}" class="nav-link {{ (request()->routeIs('dashboard*') || request()->is('/') || request()->is('dashboard') || request()->is('analitik')) ? 'active' : '' }}">
                        <i class="bi bi-graph-up-arrow"></i> Dashboard Analitik
                    </a>
                </li>
                @auth
                    @php
                        $userRole = null;
                        if (auth()->check()) {
                            $r = auth()->user()->role ?? null;
                            $userRole = $r instanceof \App\Enums\UserRole ? $r->value : (string) $r;
                            if (! $userRole && session()->has('auth_user')) {
                                $userRole = session('auth_user')['role'] ?? null;
                            }
                        }
                    @endphp

                    @if($userRole === 'mahasiswa')
                        <li>
                            <a href="{{ route('lomba.index') }}" class="nav-link {{ request()->routeIs('lomba.*') ? 'active' : '' }}">
                                <i class="bi bi-award"></i> Modul Lomba
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('tim.index') }}" class="nav-link {{ request()->routeIs('tim.*') ? 'active' : '' }}">
                                <i class="bi bi-people-fill"></i> Modul Tim
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('bimbingan.index') }}" class="nav-link {{ request()->routeIs('bimbingan.*') ? 'active' : '' }}">
                                <i class="bi bi-person-video3"></i> Modul Bimbingan
                            </a>
                        </li>
                    @elseif($userRole === 'dosen')
                        <li>
                            <a href="{{ route('bimbingan.index') }}" class="nav-link {{ request()->routeIs('bimbingan.*') ? 'active' : '' }}">
                                <i class="bi bi-person-video3"></i> Modul Bimbingan
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('dosen.profil') }}" class="nav-link {{ request()->routeIs('dosen.profil*') ? 'active' : '' }}">
                                <i class="bi bi-person-badge"></i> Profil Pembimbing
                            </a>
                        </li>
                    @elseif($userRole === 'admin')
                        <li>
                            <a href="{{ route('bimbingan.index') }}" class="nav-link {{ request()->routeIs('bimbingan.*') ? 'active' : '' }}">
                                <i class="bi bi-person-video3"></i> Modul Bimbingan
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.index') }}" class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                                <i class="bi bi-speedometer2"></i> Panel Admin
                            </a>
                        </li>
                    @endif
                @endauth
            </ul>

            <div class="nav-actions">
                @auth
                    <form action="{{ route('logout') }}" method="POST" style="display: inline; margin: 0;">
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
                        <i class="bi bi-person-plus-fill"></i> Daftar
                    </a>
                @endauth

                <button type="button" class="mobile-toggle-btn" id="mobileNavToggle" aria-label="Menu Navigasi" title="Menu Navigasi">
                    <i class="bi bi-list"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Main Body Container -->
    <main>
        <div class="container">
            {{-- Flash Message Alert: dilewati bila child view menangani alert-nya sendiri --}}
            @unless(View::hasSection('hide_layout_alerts'))
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
            @endunless

            @yield('content')
        </div>
    </main>

    <!-- Site Footer (Guest & Authenticated Component) -->
    <x-footer />

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dropdown = document.getElementById('topbarUserDropdown');
            const chipBtn = document.getElementById('topbarUserChipBtn');
            const menu = document.getElementById('topbarUserMenu');
            const linkKataSandi = document.getElementById('topbarLinkKataSandi');

            if (!dropdown || !chipBtn || !menu) return;

            function closeDropdown() {
                menu.classList.remove('open');
                chipBtn.setAttribute('aria-expanded', 'false');
            }

            chipBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                const isOpen = menu.classList.toggle('open');
                chipBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });

            if (linkKataSandi) {
                linkKataSandi.addEventListener('click', function () {
                    closeDropdown();
                });
            }

            document.addEventListener('click', function (e) {
                if (!dropdown.contains(e.target)) {
                    closeDropdown();
                }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    closeDropdown();
                }
            });

            // Mobile Navigation Toggle
            const mobileNavToggle = document.getElementById('mobileNavToggle');
            const navLinks = document.querySelector('.nav-links');

            if (mobileNavToggle && navLinks) {
                mobileNavToggle.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const isOpen = navLinks.classList.toggle('mobile-open');
                    const icon = mobileNavToggle.querySelector('i');
                    if (icon) {
                        if (isOpen) {
                            icon.classList.remove('bi-list');
                            icon.classList.add('bi-x-lg');
                        } else {
                            icon.classList.remove('bi-x-lg');
                            icon.classList.add('bi-list');
                        }
                    }
                });

                document.addEventListener('click', function (e) {
                    if (!navLinks.contains(e.target) && !mobileNavToggle.contains(e.target)) {
                        navLinks.classList.remove('mobile-open');
                        const icon = mobileNavToggle.querySelector('i');
                        if (icon) {
                            icon.classList.remove('bi-x-lg');
                            icon.classList.add('bi-list');
                        }
                    }
                });

                navLinks.querySelectorAll('.nav-link').forEach(function (link) {
                    link.addEventListener('click', function () {
                        navLinks.classList.remove('mobile-open');
                        const icon = mobileNavToggle.querySelector('i');
                        if (icon) {
                            icon.classList.remove('bi-x-lg');
                            icon.classList.add('bi-list');
                        }
                    });
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>