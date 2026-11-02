<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Single Sign-On (SSO) — Politeknik Negeri Malang</title>
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --polinema-blue: #002B66;
            --polinema-blue-light: #0B4391;
            --polinema-gold: #FFB800;
            --polinema-gold-dark: #D99B00;
            --surface: #FFFFFF;
            --border: #E2E8F0;
            --text-main: #0F172A;
            --text-muted: #64748B;
            --danger: #EF4444;
            --shadow-lg: 0 25px 45px -12px rgba(0, 43, 102, 0.22);
            --radius-md: 12px;
            --radius-lg: 20px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: radial-gradient(circle at top, #0A3575 0%, #001F4D 60%, #001330 100%);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            padding: 24px 16px;
            position: relative;
        }

        /* Background university pattern */
        body::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: 
                radial-gradient(rgba(255, 184, 0, 0.08) 1px, transparent 1px),
                radial-gradient(rgba(255, 255, 255, 0.04) 1px, transparent 1px);
            background-size: 32px 32px;
            background-position: 0 0, 16px 16px;
            pointer-events: none;
        }

        .sso-container {
            width: 100%;
            max-width: 520px;
            margin: auto;
            position: relative;
            z-index: 2;
        }

        /* Top Government/Campus Header */
        .campus-header {
            text-align: center;
            margin-bottom: 22px;
            color: #fff;
        }
        .campus-emblem-wrap {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 86px;
            height: 86px;
            border-radius: 50%;
            background: #ffffff;
            padding: 8px;
            margin-bottom: 14px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.35);
            border: 3px solid var(--polinema-gold);
        }
        .campus-emblem-wrap img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .campus-ministry {
            font-size: 0.72rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #CBD5E1;
            font-weight: 600;
        }
        .campus-name {
            font-size: 1.35rem;
            font-weight: 800;
            color: #FFFFFF;
            letter-spacing: -0.3px;
            margin-top: 2px;
        }
        .campus-badge-sso {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 184, 0, 0.15);
            border: 1px solid rgba(255, 184, 0, 0.4);
            color: #FFD54F;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 4px 14px;
            border-radius: 999px;
            margin-top: 8px;
            letter-spacing: 0.03em;
        }

        /* Card SSO */
        .sso-card {
            background: #FFFFFF;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 34px 32px 30px;
            position: relative;
            overflow: hidden;
        }
        .sso-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #002B66 0%, #0B4391 50%, #FFB800 100%);
        }

        /* Target App Banner */
        .target-app-box {
            background: #F8FAFC;
            border: 1px dashed #CBD5E1;
            border-radius: var(--radius-md);
            padding: 12px 14px;
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .target-app-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: #002B66;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }
        .target-app-info {
            flex: 1;
            min-width: 0;
        }
        .target-app-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            font-weight: 700;
            color: var(--text-muted);
            letter-spacing: 0.05em;
        }
        .target-app-title {
            font-size: 0.88rem;
            font-weight: 700;
            color: var(--polinema-blue);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 18px;
        }
        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 700;
            font-size: 0.85rem;
            color: var(--text-main);
        }
        .input-group {
            position: relative;
        }
        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
            font-size: 1.05rem;
        }
        .form-control {
            width: 100%;
            padding: 12px 14px 12px 42px;
            font-size: 0.92rem;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            background-color: #fff;
            color: var(--text-main);
            font-family: inherit;
            transition: all 0.2s ease;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--polinema-blue-light);
            box-shadow: 0 0 0 3px rgba(11, 67, 145, 0.15);
        }
        .form-control.is-invalid {
            border-color: #EF4444 !important;
            background-color: #FEF2F2;
        }
        .form-control.is-invalid:focus {
            border-color: #DC2626 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2) !important;
        }
        .invalid-feedback {
            color: #DC2626;
            font-size: 0.82rem;
            font-weight: 600;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94A3B8;
            cursor: pointer;
            font-size: 1.15rem;
            padding: 4px;
        }
        .password-toggle:hover {
            color: var(--polinema-blue);
        }

        /* Buttons */
        .btn-sso-submit {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #002B66 0%, #0A408A 100%);
            color: #FFFFFF;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.96rem;
            cursor: pointer;
            box-shadow: 0 6px 18px rgba(0, 43, 102, 0.28);
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-family: inherit;
            margin-top: 6px;
        }
        .btn-sso-submit:hover {
            background: linear-gradient(135deg, #001F4D 0%, #002B66 100%);
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(0, 43, 102, 0.38);
        }

        /* Alert Banner */
        .alert {
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-weight: 500;
        }
        .alert-danger {
            background-color: #FEF2F2;
            color: #991B1B;
            border: 1px solid #FECACA;
        }

        /* Demo Quick Chips */
        .demo-helper {
            margin-top: 22px;
            padding-top: 18px;
            border-top: 1px dashed var(--border);
        }
        .demo-title {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .demo-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .demo-chip-btn {
            background: #F1F5F9;
            border: 1px solid #CBD5E1;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 0.76rem;
            font-weight: 600;
            color: #334155;
            cursor: pointer;
            transition: all 0.15s ease;
            font-family: inherit;
        }
        .demo-chip-btn:hover {
            background: #E2E8F0;
            border-color: #94A3B8;
            color: var(--polinema-blue);
        }

        /* Back to Main System Link */
        .sso-cancel-wrap {
            margin-top: 20px;
            text-align: center;
        }
        .sso-cancel-link {
            color: #94A3B8;
            font-size: 0.85rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: color 0.2s;
        }
        .sso-cancel-link:hover {
            color: #FFFFFF;
        }

        /* Footer */
        .sso-footer {
            margin-top: 24px;
            text-align: center;
            font-size: 0.76rem;
            color: rgba(255, 255, 255, 0.6);
            line-height: 1.5;
        }
    </style>
</head>
<body>

    <div class="sso-container">
        <!-- Official Campus Header -->
        <div class="campus-header">
            <div class="campus-emblem-wrap">
                <img src="{{ asset('images/logo-polinema.png') }}" alt="Logo Resmi Politeknik Negeri Malang">
            </div>
            <div class="campus-ministry">Kementerian Pendidikan Tinggi, Sains, dan Teknologi</div>
            <h1 class="campus-name">POLITEKNIK NEGERI MALANG</h1>
            <div class="campus-badge-sso">
                <i class="bi bi-shield-lock-fill"></i> Single Sign-On (SSO) SIAKAD Mahasiswa
            </div>
        </div>

        <!-- SSO Card -->
        <div class="sso-card">
            <!-- Target App Info Banner -->
            <div class="target-app-box">
                <div class="target-app-icon">
                    <i class="bi bi-trophy-fill"></i>
                </div>
                <div class="target-app-info">
                    <div class="target-app-label">Aplikasi yang Dituju</div>
                    <div class="target-app-title">RuangLombaJTI — Prestasi Mahasiswa</div>
                </div>
                <span style="font-size: 0.72rem; color: #10B981; font-weight: 700; background: #ECFDF5; padding: 2px 8px; border-radius: 999px;">
                    <i class="bi bi-check-circle"></i> Terverifikasi
                </span>
            </div>

            <!-- Error Alerts -->
            @if(isset($errors) && $errors->any())
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill" style="font-size: 1.1rem; color: #DC2626; flex-shrink: 0; margin-top: 1px;"></i>
                    <div>
                        <div style="font-weight: 700; margin-bottom: 2px;">Gagal Masuk SSO SIAKAD:</div>
                        <ul style="margin: 0; padding-left: 18px; font-size: 0.82rem; line-height: 1.4;">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <!-- Form SSO Polinema -->
            <form action="{{ route('sso.authenticate') }}" method="POST">
                @csrf

                <!-- Input NIM -->
                <div class="form-group">
                    <label class="form-label" for="nimInput">Nomor Induk Mahasiswa (NIM)</label>
                    <div class="input-group">
                        <i class="bi bi-person-badge input-icon"></i>
                        <input type="text"
                               name="nim"
                               id="nimInput"
                               class="form-control @error('nim') is-invalid @enderror"
                               placeholder="Contoh: 244107060116"
                               value="{{ old('nim') }}"
                               autocomplete="username"
                               required
                               autofocus>
                    </div>
                    @error('nim')
                        <div class="invalid-feedback">
                            <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Input Password SIAKAD -->
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label class="form-label" for="passwordInput" style="margin-bottom: 0;">Kata Sandi SIAKAD</label>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Sama dengan akun SIAKAD</span>
                    </div>
                    <div class="input-group">
                        <i class="bi bi-key input-icon"></i>
                        <input type="password"
                               name="password"
                               id="passwordInput"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Masukkan kata sandi SIAKAD Anda"
                               autocomplete="current-password"
                               required>
                        <button type="button" class="password-toggle" onclick="togglePassword()" title="Tampilkan/sembunyikan sandi">
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="invalid-feedback">
                            <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-sso-submit">
                    <i class="bi bi-box-arrow-in-right"></i> Masuk SSO & Lanjutkan ke RuangLombaJTI
                </button>
            </form>

            <!-- Helper Akun Demo Mahasiswa -->
            @if(isset($demoMahasiswa) && $demoMahasiswa->isNotEmpty())
                <div class="demo-helper">
                    <div class="demo-title">
                        <i class="bi bi-lightning-charge-fill" style="color: var(--polinema-gold);"></i>
                        Pilihan Cepat Akun Demo (Klik untuk Isi Otomatis):
                    </div>
                    <div class="demo-chips">
                        @foreach($demoMahasiswa as $demo)
                            <button type="button"
                                    class="demo-chip-btn"
                                    onclick="fillDemo('{{ $demo->nim }}', 'password123')"
                                    title="Masuk sebagai {{ $demo->nama }} ({{ $demo->prodi }})">
                                <strong>{{ $demo->nim }}</strong> — {{ Str::words($demo->nama, 2, '') }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Cancel Link -->
        <div class="sso-cancel-wrap">
            <a href="{{ route('login') }}" class="sso-cancel-link">
                <i class="bi bi-arrow-left"></i> Batalkan dan kembali ke halaman login utama
            </a>
        </div>

        <!-- Footer -->
        <div class="sso-footer">
            &copy; {{ date('Y') }} Politeknik Negeri Malang &bull; UPT Sistem Informasi & Komunikasi (TIK)<br>
            Autentikasi Terpadu Layanan Akademik & Kemahasiswaan
        </div>
    </div>

    <script>
        function togglePassword() {
            const pwd = document.getElementById('passwordInput');
            const icon = document.getElementById('toggleIcon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                pwd.type = 'password';
                icon.className = 'bi bi-eye';
            }
        }

        function fillDemo(nim, pwd) {
            const nimInput = document.getElementById('nimInput');
            const pwdInput = document.getElementById('passwordInput');
            nimInput.value = nim;
            pwdInput.value = pwd;
            pwdInput.focus();
        }
    </script>
</body>
</html>
