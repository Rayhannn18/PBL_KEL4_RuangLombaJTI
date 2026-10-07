<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Akun Mahasiswa - RuangLombaJTI Polinema</title>
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --primary: #1E40AF;
            --primary-dark: #0F2744;
            --primary-light: #3B82F6;
            --primary-soft: #EFF6FF;
            --secondary: #0D9488;
            --accent: #F59E0B;
            --success: #10B981;
            --danger: #EF4444;
            --bg-body: #F8FAFC;
            --surface: #FFFFFF;
            --border: #E2E8F0;
            --text-main: #0F172A;
            --text-muted: #64748B;
            --shadow-lg: 0 20px 35px -10px rgba(15, 39, 68, 0.15);
            --radius-md: 12px;
            --radius-lg: 18px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #F0F4F8 0%, #E2E8F0 100%);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .auth-wrapper {
            width: 100%;
            max-width: 600px;
        }

        .auth-top-nav {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--primary);
            font-weight: 600;
            font-size: 0.88rem;
            text-decoration: none;
            transition: transform 0.2s;
        }
        .back-link:hover {
            transform: translateX(-3px);
            color: #1D4ED8;
        }

        .auth-card {
            background: var(--surface);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-lg);
            padding: 36px 36px;
            position: relative;
            overflow: hidden;
        }

        .auth-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #10B981 0%, #3B82F6 50%, #1E40AF 100%);
        }

        .brand-header {
            text-align: center;
            margin-bottom: 26px;
        }
        .brand-logo-wrap {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 58px;
            height: 58px;
            border-radius: var(--radius-md);
            background: linear-gradient(135deg, #10B981 0%, #3B82F6 100%);
            color: #fff;
            font-size: 1.7rem;
            margin-bottom: 12px;
            box-shadow: 0 8px 16px rgba(16, 185, 129, 0.25);
        }
        .brand-title {
            font-size: 1.45rem;
            font-weight: 800;
            color: var(--primary-dark);
            letter-spacing: -0.5px;
        }
        .brand-title span {
            color: #2563EB;
        }
        .brand-subtitle {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 4px;
        }

        /* Forms */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
            .auth-card {
                padding: 26px 20px;
            }
        }

        .form-group {
            margin-bottom: 16px;
        }
        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--text-main);
        }
        .input-group {
            position: relative;
        }
        .input-group i.input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
            font-size: 1.05rem;
        }
        .form-control, .form-select {
            width: 100%;
            padding: 10px 14px 10px 38px;
            font-size: 0.88rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            background-color: #fff;
            color: var(--text-main);
            font-family: inherit;
            transition: all 0.2s;
        }
        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            transition: all 0.2s;
            font-family: inherit;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 10px;
        }
        .btn-submit:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
        }

        /* Alert */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .alert-danger {
            background-color: #FEF2F2;
            color: #991B1B;
            border: 1px solid #FECACA;
        }

        /* Card footer */
        .auth-footer {
            margin-top: 22px;
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-muted);
        }
        .auth-footer a {
            color: var(--primary);
            font-weight: 700;
            text-decoration: none;
        }
        .auth-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="auth-wrapper">
        <div class="auth-top-nav">
            <a href="{{ route('dashboard.analitik') }}" class="back-link">
                <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
            </a>
            <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">JTI Polinema</span>
        </div>

        <div class="auth-card">
            <!-- Header Brand -->
            <div class="brand-header">
                <div class="brand-logo-wrap">
                    <i class="bi bi-person-plus-fill"></i>
                </div>
                <div class="brand-title">Pendaftaran Akun <span>Mahasiswa</span></div>
                <div class="brand-subtitle">Daftarkan akun mahasiswa Anda untuk berkolaborasi dan berkompetisi</div>
            </div>

            <!-- Flash Error Alerts -->
            @if($errors->any())
                <div class="alert alert-danger">
                    <div style="font-weight: 700; margin-bottom: 4px;">
                        <i class="bi bi-x-circle-fill"></i> Periksa kembali data formulir:
                    </div>
                    <ul style="padding-left: 20px; font-size: 0.82rem;">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Form Registrasi -->
            <form action="{{ route('auth.register') }}" method="POST">
                @csrf

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">NIM (Nomor Induk Mahasiswa) *</label>
                        <div class="input-group">
                            <i class="bi bi-hash input-icon"></i>
                            <input type="text"
                                   name="nim"
                                   class="form-control"
                                   placeholder="Contoh: 244107060116"
                                   value="{{ old('nim') }}"
                                   required
                                   autofocus>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nama Lengkap Mahasiswa *</label>
                        <div class="input-group">
                            <i class="bi bi-person input-icon"></i>
                            <input type="text"
                                   name="nama"
                                   class="form-control"
                                   placeholder="Contoh: Sastra Maheva Zaky"
                                   value="{{ old('nama') }}"
                                   required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Kampus Polinema *</label>
                    <div class="input-group">
                        <i class="bi bi-envelope input-icon"></i>
                        <input type="email"
                               name="email_kampus"
                               class="form-control"
                               placeholder="Contoh: sastra@student.polinema.ac.id"
                               value="{{ old('email_kampus') }}"
                               required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Program Studi *</label>
                        <div class="input-group">
                            <i class="bi bi-mortarboard input-icon"></i>
                            <select name="prodi" class="form-select" required>
                                <option value="" disabled {{ old('prodi') ? '' : 'selected' }}>Pilih Program Studi</option>
                                <option value="D-IV Sistem Informasi Bisnis" {{ old('prodi') == 'D-IV Sistem Informasi Bisnis' ? 'selected' : '' }}>D-IV Sistem Informasi Bisnis</option>
                                <option value="D-IV Teknik Informatika" {{ old('prodi') == 'D-IV Teknik Informatika' ? 'selected' : '' }}>D-IV Teknik Informatika</option>
                                <option value="D-III Manajemen Informatika" {{ old('prodi') == 'D-III Manajemen Informatika' ? 'selected' : '' }}>D-III Manajemen Informatika</option>
                                <option value="D-III Teknik Informatika" {{ old('prodi') == 'D-III Teknik Informatika' ? 'selected' : '' }}>D-III Teknik Informatika</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tahun Angkatan *</label>
                        <div class="input-group">
                            <i class="bi bi-calendar-event input-icon"></i>
                            <select name="angkatan" class="form-select" required>
                                <option value="2026" {{ old('angkatan') == 2026 ? 'selected' : '' }}>2026</option>
                                <option value="2025" {{ old('angkatan') == 2025 ? 'selected' : '' }}>2025</option>
                                <option value="2024" {{ old('angkatan', 2024) == 2024 ? 'selected' : '' }}>2024</option>
                                <option value="2023" {{ old('angkatan') == 2023 ? 'selected' : '' }}>2023</option>
                                <option value="2022" {{ old('angkatan') == 2022 ? 'selected' : '' }}>2022</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Kata Sandi (Min. 6 Karakter) *</label>
                        <div class="input-group">
                            <i class="bi bi-key input-icon"></i>
                            <input type="password"
                                   name="password"
                                   class="form-control"
                                   placeholder="Minimal 6 karakter"
                                   required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Konfirmasi Kata Sandi *</label>
                        <div class="input-group">
                            <i class="bi bi-shield-check input-icon"></i>
                            <input type="password"
                                   name="password_confirmation"
                                   class="form-control"
                                   placeholder="Ulangi kata sandi"
                                   required>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="bi bi-person-check-fill"></i> Daftarkan Akun Mahasiswa
                </button>
            </form>

            <!-- Footer Pendaftaran -->
            <div class="auth-footer">
                Sudah memiliki akun? <a href="{{ route('login') }}">Masuk ke Sistem</a>
            </div>
        </div>
    </div>

</body>
</html>
