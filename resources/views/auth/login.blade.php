<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Sistem - RuangLombaJTI Polinema</title>
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
            --shadow-md: 0 10px 25px -5px rgba(15, 39, 68, 0.08), 0 8px 10px -6px rgba(15, 39, 68, 0.04);
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
            padding: 30px 20px;
        }

        .auth-wrapper {
            width: 100%;
            max-width: 480px;
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
            padding: 36px 32px;
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
            background: linear-gradient(90deg, #1E40AF 0%, #3B82F6 50%, #0D9488 100%);
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
            background: linear-gradient(135deg, #1E40AF 0%, #3B82F6 100%);
            color: #fff;
            font-size: 1.7rem;
            margin-bottom: 12px;
            box-shadow: 0 8px 16px rgba(30, 64, 175, 0.25);
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
            font-size: 0.84rem;
            color: var(--text-muted);
            margin-top: 4px;
        }

        /* Role Selector Tabs */
        .role-tabs {
            display: flex;
            background: #F1F5F9;
            padding: 4px;
            border-radius: var(--radius-md);
            gap: 4px;
            margin-bottom: 22px;
        }
        .role-tab-btn {
            flex: 1;
            padding: 8px 10px;
            font-size: 0.82rem;
            font-weight: 700;
            border: none;
            background: transparent;
            color: var(--text-muted);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-family: inherit;
        }
        .role-tab-btn.active {
            background: #fff;
            color: var(--primary);
            box-shadow: 0 2px 5px rgba(0,0,0,0.08);
        }

        /* Forms */
        .form-group {
            margin-bottom: 18px;
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
        .form-control {
            width: 100%;
            padding: 11px 14px 11px 40px;
            font-size: 0.9rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            background-color: #fff;
            color: var(--text-main);
            font-family: inherit;
            transition: all 0.2s;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
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
            font-size: 1.1rem;
            padding: 4px;
        }
        .password-toggle:hover {
            color: var(--primary);
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #1E40AF 0%, #2563EB 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
            transition: all 0.2s;
            font-family: inherit;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 8px;
        }
        .btn-submit:hover {
            background: linear-gradient(135deg, #1D4ED8 0%, #1E40AF 100%);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4);
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
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* SSO Polinema Button */
        .btn-sso-polinema {
            display: flex;
            align-items: center;
            gap: 13px;
            padding: 13px 15px;
            background: linear-gradient(135deg, #002B66 0%, #0F3B82 100%);
            border: 1.5px solid #002B66;
            border-radius: var(--radius-md);
            color: #fff;
            text-decoration: none;
            box-shadow: 0 4px 14px rgba(0, 43, 102, 0.22);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        .btn-sso-polinema::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            width: 4px;
            background: #FFB800;
        }
        .btn-sso-polinema:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 43, 102, 0.35);
            border-color: #FFB800;
            background: linear-gradient(135deg, #00204D 0%, #0D326E 100%);
        }
        .sso-logo-emblem {
            width: 44px;
            height: 44px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #FFFFFF;
            border-radius: 10px;
            padding: 3px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }
        .sso-img-emblem {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .sso-content {
            flex: 1;
            min-width: 0;
        }
        .sso-badge-tag {
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.05em;
            color: #FFD54F;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-bottom: 2px;
        }
        .sso-title {
            font-size: 0.95rem;
            font-weight: 800;
            color: #FFFFFF;
            line-height: 1.2;
        }
        .sso-desc {
            font-size: 0.74rem;
            color: rgba(255, 255, 255, 0.8);
            margin-top: 2px;
        }
        .sso-arrow {
            color: #FFB800;
            font-size: 1.35rem;
            display: flex;
            align-items: center;
            transition: transform 0.2s ease;
        }
        .btn-sso-polinema:hover .sso-arrow {
            transform: translateX(3px);
            color: #FFF;
        }
        .sso-divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 18px 0 14px;
            color: #94A3B8;
            font-size: 0.74rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .sso-divider::before,
        .sso-divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #E2E8F0;
        }
        .sso-divider span {
            padding: 0 10px;
        }

        /* Alert */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
        }
        .alert-danger {
            background-color: #FEF2F2;
            color: #991B1B;
            border: 1px solid #FECACA;
        }
        .alert-success {
            background-color: #ECFDF5;
            color: #065F46;
            border: 1px solid #A7F3D0;
        }

        /* Card footer */
        .auth-footer {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
            text-align: center;
            font-size: 0.86rem;
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
                    <i class="bi bi-trophy-fill"></i>
                </div>
                <div class="brand-title">RuangLomba<span>JTI</span></div>
                <div class="brand-subtitle">Portal Masuk Sistem Monitoring & Analitik Lomba</div>
            </div>

            <!-- Flash Error / Success Alerts -->
            @if(session('error') && ! $errors->any())
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger" style="display: flex; flex-direction: column; align-items: flex-start; gap: 6px;">
                    <div style="display: flex; align-items: center; gap: 8px; font-weight: 700;">
                        <i class="bi bi-exclamation-triangle-fill" style="font-size: 1.1rem; color: #DC2626;"></i>
                        <span>Gagal Masuk:</span>
                    </div>
                    <ul style="margin: 0; padding-left: 24px; font-size: 0.85rem; line-height: 1.4;">
                        @foreach(array_unique($errors->all()) as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Role Selector Tabs -->
            <div class="role-tabs">
                <button type="button" class="role-tab-btn {{ old('role', 'mahasiswa') === 'mahasiswa' ? 'active' : '' }}" data-role="mahasiswa" onclick="selectRole('mahasiswa', this)">
                    <i class="bi bi-mortarboard"></i> Mahasiswa
                </button>
                <button type="button" class="role-tab-btn {{ old('role') === 'dosen' ? 'active' : '' }}" data-role="dosen" onclick="selectRole('dosen', this)">
                    <i class="bi bi-person-video3"></i> Dosen
                </button>
                <button type="button" class="role-tab-btn {{ old('role') === 'admin' ? 'active' : '' }}" data-role="admin" onclick="selectRole('admin', this)">
                    <i class="bi bi-shield-lock"></i> Admin JTI
                </button>
            </div>

            <!-- Tombol SSO Polinema (Khusus Mahasiswa SIAKAD) -->
            <div id="ssoMahasiswaSection" style="{{ old('role', 'mahasiswa') === 'mahasiswa' ? '' : 'display: none;' }} margin-bottom: 20px;">
                <a href="{{ route('sso.login') }}" class="btn-sso-polinema" id="ssoPolinemaBtn">
                    <div class="sso-logo-emblem">
                        <img src="{{ asset('images/logo-polinema.png') }}" alt="Logo Politeknik Negeri Malang" class="sso-img-emblem">
                    </div>
                    <div class="sso-content">
                        <div class="sso-badge-tag"><i class="bi bi-shield-check"></i> RESMI POLINEMA</div>
                        <div class="sso-title">Masuk dengan SSO Polinema</div>
                        <div class="sso-desc">Gunakan akun SIAKAD Politeknik Negeri Malang</div>
                    </div>
                    <div class="sso-arrow">
                        <i class="bi bi-arrow-right-circle-fill"></i>
                    </div>
                </a>

                <div class="sso-divider">
                    <span>atau masuk dengan akun lokal</span>
                </div>
            </div>

            <!-- Form Login -->
            <form action="{{ route('login') }}" method="POST">
                @csrf
                <input type="hidden" name="role" id="roleInput" value="{{ old('role', 'mahasiswa') }}">

                <div class="form-group">
                    <label class="form-label" id="loginLabel">Email Kampus Mahasiswa atau NIM</label>
                    <div class="input-group">
                        <i class="bi bi-person input-icon" id="loginIcon"></i>
                        <input type="text"
                               name="login"
                               id="loginInput"
                               class="form-control @error('login') is-invalid @enderror"
                               placeholder="Contoh: sastra22@student.polinema.ac.id atau 244107060116"
                               value="{{ old('login') }}"
                               required
                               autofocus>
                    </div>
                    @error('login')
                        <div class="invalid-feedback">
                            <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label class="form-label" style="margin-bottom: 0;">Kata Sandi</label>
                    </div>
                    <div class="input-group">
                        <i class="bi bi-key input-icon"></i>
                        <input type="password"
                               name="password"
                               id="passwordInput"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Masukkan kata sandi akun Anda"
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

                <button type="submit" class="btn-submit">
                    <i class="bi bi-box-arrow-in-right"></i> Masuk ke Sistem
                </button>
            </form>

            <!-- Footer Pendaftaran -->
            <div class="auth-footer">
                Belum memiliki akun mahasiswa? <a href="{{ route('register') }}">Daftar di sini</a>
            </div>
        </div>
    </div>

    <script>
        function selectRole(role, btn) {
            document.querySelectorAll('.role-tab-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById('roleInput').value = role;

            const label = document.getElementById('loginLabel');
            const input = document.getElementById('loginInput');
            const icon = document.getElementById('loginIcon');

            if (role === 'mahasiswa') {
                label.innerText = 'Email Kampus Mahasiswa atau NIM';
                input.placeholder = 'Contoh: sastra22@student.polinema.ac.id atau 244107060116';
                icon.className = 'bi bi-mortarboard input-icon';
            } else if (role === 'dosen') {
                label.innerText = 'Email Kampus Dosen atau NIP';
                input.placeholder = 'Contoh: vivin@polinema.ac.id atau 0012058501';
                icon.className = 'bi bi-person-video3 input-icon';
            } else if (role === 'admin') {
                label.innerText = 'Email Resmi Admin Kemahasiswaan';
                input.placeholder = 'Contoh: admin.jti@polinema.ac.id';
                icon.className = 'bi bi-shield-lock input-icon';
            }

            const ssoSec = document.getElementById('ssoMahasiswaSection');
            if (ssoSec) {
                ssoSec.style.display = (role === 'mahasiswa') ? 'block' : 'none';
            }
        }

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

        // Sinkronisasi tab awal berdasarkan old('role')
        document.addEventListener('DOMContentLoaded', function() {
            const currentRole = document.getElementById('roleInput').value;
            if (currentRole && currentRole !== 'mahasiswa') {
                const btn = document.querySelector(`.role-tab-btn[data-role="${currentRole}"]`);
                if (btn) selectRole(currentRole, btn);
            }
        });
    </script>
</body>
</html>
