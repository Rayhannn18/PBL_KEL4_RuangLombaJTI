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

        /* Demo Login Section */
        .demo-section {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px dashed var(--border);
        }
        .demo-title {
            font-size: 0.78rem;
            text-transform: uppercase;
            font-weight: 700;
            color: var(--text-muted);
            letter-spacing: 0.5px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .demo-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 8px;
        }
        .demo-btn {
            background: #F8FAFC;
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 8px 6px;
            font-size: 0.76rem;
            font-weight: 600;
            color: var(--text-main);
            text-align: center;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            transition: all 0.2s;
        }
        .demo-btn:hover {
            background: var(--primary-soft);
            border-color: #BFDBFE;
            color: var(--primary);
            transform: translateY(-2px);
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
                    <i class="bi bi-trophy-fill"></i>
                </div>
                <div class="brand-title">RuangLomba<span>JTI</span></div>
                <div class="brand-subtitle">Portal Masuk Sistem Monitoring & Analitik Lomba</div>
            </div>

            <!-- Flash Error / Success Alerts -->
            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span>{{ session('error') }}</span>
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
                <button type="button" class="role-tab-btn active" onclick="selectRole('mahasiswa', this)">
                    <i class="bi bi-mortarboard"></i> Mahasiswa
                </button>
                <button type="button" class="role-tab-btn" onclick="selectRole('dosen', this)">
                    <i class="bi bi-person-video3"></i> Dosen
                </button>
                <button type="button" class="role-tab-btn" onclick="selectRole('admin', this)">
                    <i class="bi bi-shield-lock"></i> Admin JTI
                </button>
            </div>

            <!-- Form Login -->
            <form action="{{ route('auth.login') }}" method="POST">
                @csrf
                <input type="hidden" name="role" id="roleInput" value="mahasiswa">

                <div class="form-group">
                    <label class="form-label" id="identifierLabel">NIM atau Email</label>
                    <div class="input-group">
                        <i class="bi bi-person input-icon" id="identifierIcon"></i>
                        <input type="text"
                               name="identifier"
                               id="identifierInput"
                               class="form-control"
                               placeholder="Contoh: 244107060116 atau email mahasiswa"
                               value="{{ old('identifier') }}"
                               required
                               autofocus>
                    </div>
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
                               class="form-control"
                               placeholder="Masukkan kata sandi akun Anda"
                               required>
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="bi bi-box-arrow-in-right"></i> Masuk ke Sistem
                </button>
            </form>

            <!-- Quick Demo 1-Click Login (Sidang & Evaluasi PBL) -->
            <div class="demo-section">
                <div class="demo-title">
                    <i class="bi bi-lightning-charge-fill" style="color: var(--accent);"></i>
                    Quick Login Demo (1-Klik untuk Sidang PBL)
                </div>
                <div class="demo-grid">
                    <a href="{{ route('auth.quick', 'mahasiswa') }}" class="demo-btn">
                        <i class="bi bi-mortarboard-fill" style="color: #2563EB; font-size: 1.1rem;"></i>
                        <span>Mahasiswa</span>
                    </a>
                    <a href="{{ route('auth.quick', 'dosen') }}" class="demo-btn">
                        <i class="bi bi-person-badge-fill" style="color: #0D9488; font-size: 1.1rem;"></i>
                        <span>Dosen</span>
                    </a>
                    <a href="{{ route('auth.quick', 'admin') }}" class="demo-btn">
                        <i class="bi bi-shield-check" style="color: #DC2626; font-size: 1.1rem;"></i>
                        <span>Admin</span>
                    </a>
                </div>
            </div>

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

            const label = document.getElementById('identifierLabel');
            const input = document.getElementById('identifierInput');
            const icon = document.getElementById('identifierIcon');

            if (role === 'mahasiswa') {
                label.innerText = 'NIM atau Email';
                input.placeholder = 'Contoh: 244107060116 atau email mahasiswa';
                icon.className = 'bi bi-mortarboard input-icon';
            } else if (role === 'dosen') {
                label.innerText = 'NIDN atau Email';
                input.placeholder = 'Contoh: 0012058501 atau email@dosen.com';
                icon.className = 'bi bi-person-video3 input-icon';
            } else if (role === 'admin') {
                label.innerText = 'Email Resmi Admin Kemahasiswaan';
                input.placeholder = 'Contoh: admin.jti@polinema.ac.id';
                icon.className = 'bi bi-shield-lock input-icon';
            }
        }

        function togglePassword() {
            const pwd = document.getElementById('passwordInput');
            const icon = document.getElementById('toggleIcon');
            // [DEFECT-03 / BUG REPORT PBL]: Bug Front-End UI / State Interaction Defect
            // State toggle macet: hanya mengubah tipe input ke 'text' dan ikon ke 'bi-eye-slash',
            // namun tidak mengembalikan ke 'password' saat diklik ulang (password tetap terbuka).
            pwd.type = 'text';
            icon.className = 'bi bi-eye-slash';
        }
    </script>
</body>
</html>
