// Interaksi halaman login & registrasi (tanpa dependensi).

const ANY_EMAIL = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
const ROLE_CONFIG = {
    mahasiswa: {
        idLabel: 'NIM',
        idAside: 'Nomor Induk Mahasiswa POLINEMA',
        idPlaceholder: 'cth. 2241720088',
        emailLabel: 'Email',
        emailPlaceholder: 'cth. nama@student.polinema.ac.id',
        // Mahasiswa wajib memakai email Polinema.
        emailPattern: /^[^\s@]+@(?:[\w-]+\.)*polinema\.ac\.id$/i,
        namePlaceholder: 'cth. Muhammad Farhan',
    },
    dosen: {
        idLabel: 'NIP',
        idAside: 'Nomor Induk Pegawai POLINEMA (18 digit)',
        idPlaceholder: 'cth. 198010102005012001',
        emailLabel: 'Email',
        emailPlaceholder: 'cth. nama@polinema.ac.id',
        // Dosen wajib memakai email Polinema.
        emailPattern: /^[^\s@]+@(?:[\w-]+\.)*polinema\.ac\.id$/i,
        namePlaceholder: 'cth. Dr. Eng. Rosa Andrie Asmara, S.T., M.T.',
    },
};

// Teks kolom login per peran (halaman masuk). Admin tidak punya NIM/NIP, jadi hanya email.
const LOGIN_CONFIG = {
    mahasiswa: { label: 'Email Kampus / NIM', placeholder: 'cth. nama@student.polinema.ac.id atau 2241720088', numeric: true },
    dosen: { label: 'Email Kampus / NIP', placeholder: 'cth. nama@polinema.ac.id atau 198010102005012001', numeric: true },
    admin: { label: 'Email', placeholder: 'cth. admin@ruanglombajti.test', numeric: false },
};

// Tombol lihat/sembunyikan kata sandi.
document.querySelectorAll('[data-toggle-password]').forEach((button) => {
    const input = document.getElementById(button.dataset.togglePassword);
    button.addEventListener('click', () => {
        const reveal = input.type === 'password';
        input.type = reveal ? 'text' : 'password';
        button.setAttribute('aria-pressed', String(reveal));
        button.querySelector('[data-eye]').classList.toggle('hidden', reveal);
        button.querySelector('[data-eye-off]').classList.toggle('hidden', !reveal);
    });
});

// Centang hijau saat isian sudah valid.
const roleInputs = document.querySelectorAll('input[name="role"]');
const selectedRole = () => document.querySelector('input[name="role"]:checked')?.value ?? 'mahasiswa';

const validators = {
    login: (v) => ANY_EMAIL.test(v) || (LOGIN_CONFIG[selectedRole()]?.numeric && /^\d{8,18}$/.test(v)),
    email: (v) => (ROLE_CONFIG[selectedRole()] ?? ROLE_CONFIG.mahasiswa).emailPattern.test(v),
};

function refreshChecks() {
    document.querySelectorAll('[data-check-for]').forEach((icon) => {
        const input = document.getElementById(icon.dataset.checkFor);
        const rule = validators[icon.dataset.checkRule];
        icon.classList.toggle('hidden', !(input && rule && rule(input.value.trim())));
    });
}

document.querySelectorAll('[data-check-for]').forEach((icon) => {
    document.getElementById(icon.dataset.checkFor)?.addEventListener('input', refreshChecks);
});

// Pergantian peran pada form registrasi dan login.
function applyRole() {
    const cfg = ROLE_CONFIG[selectedRole()] ?? ROLE_CONFIG.mahasiswa;
    const set = (selector, text) => {
        const el = document.querySelector(selector);
        if (el) el.textContent = text;
    };

    set('label[for="nim_nip"] [data-text]', cfg.idLabel);
    set('#nim_nip-aside', cfg.idAside);
    set('label[for="email"] [data-text]', cfg.emailLabel);
    document.getElementById('nim_nip')?.setAttribute('placeholder', cfg.idPlaceholder);
    document.getElementById('email')?.setAttribute('placeholder', cfg.emailPlaceholder);
    document.getElementById('name')?.setAttribute('placeholder', cfg.namePlaceholder);
    document.querySelectorAll('[data-role-only]').forEach((el) => {
        el.classList.toggle('hidden', !el.dataset.roleOnly.split(',').includes(selectedRole()));
    });

    // Halaman masuk: ganti label dan contoh isian sesuai peran.
    const login = LOGIN_CONFIG[selectedRole()];
    if (login && document.getElementById('login')) {
        set('label[for="login"] [data-text]', login.label);
        document.getElementById('login').setAttribute('placeholder', login.placeholder);
    }
    refreshChecks();
}

roleInputs.forEach((input) => input.addEventListener('change', applyRole));
if (roleInputs.length) applyRole();
else refreshChecks();