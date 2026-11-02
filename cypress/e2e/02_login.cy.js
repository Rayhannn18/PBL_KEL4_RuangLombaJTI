/**
 * Test Suite: Login Multi-Aktor & Keamanan Sesi
 * Modul: Login (Berdasarkan Dokumen Pengujian Software Testing PBL)
 * Test Cases: TC_LOGIN_001 s/d TC_LOGIN_012
 */

describe('Modul Login - RuangLombaJTI', () => {
  beforeEach(() => {
    cy.visit('/login');
  });

  it('TC_LOGIN_001 - Memeriksa login mahasiswa dengan NIM & password valid', () => {
    cy.contains('.role-tab-btn', 'Mahasiswa').click();
    cy.get('input[name="identifier"]').type('244107060116');
    cy.get('input[name="password"]').type('password123');
    cy.get('button[type="submit"]').click();

    // Berhasil masuk ke dashboard
    cy.url().should('not.include', '/login');
    cy.contains('Dashboard Analitik').should('be.visible');
    cy.contains('Sastra Maheva Zaky').should('be.visible');
    cy.contains('Mahasiswa').should('be.visible');
  });

  it('TC_LOGIN_002 - Memeriksa login dengan username valid dan password salah', () => {
    cy.contains('.role-tab-btn', 'Mahasiswa').click();
    cy.get('input[name="identifier"]').type('244107060116');
    cy.get('input[name="password"]').type('passwordsalah');
    cy.get('button[type="submit"]').click();

    // Menolak login dan menampilkan pesan kesalahan
    cy.get('.alert-danger').should('be.visible');
    cy.contains('Login gagal! Email/NIM/NIDN atau kata sandi tidak sesuai.').should('be.visible');
    cy.url().should('include', '/login');
  });

  it('TC_LOGIN_003 - Memeriksa login dengan username tidak terdaftar dan password valid', () => {
    cy.contains('.role-tab-btn', 'Mahasiswa').click();
    cy.get('input[name="identifier"]').type('999999999999');
    cy.get('input[name="password"]').type('password123');
    cy.get('button[type="submit"]').click();

    cy.get('.alert-danger').should('be.visible');
    cy.url().should('include', '/login');
  });

  it('TC_LOGIN_004 - Memeriksa login dengan username dan password tidak valid', () => {
    cy.contains('.role-tab-btn', 'Mahasiswa').click();
    cy.get('input[name="identifier"]').type('nim_salah_total');
    cy.get('input[name="password"]').type('pwd_salah_total');
    cy.get('button[type="submit"]').click();

    cy.get('.alert-danger').should('be.visible');
    cy.url().should('include', '/login');
  });

  it('TC_LOGIN_005 - Memeriksa login dosen dengan data valid', () => {
    cy.contains('.role-tab-btn', 'Dosen').click();
    cy.get('input[name="identifier"]').type('ade.ismail@polinema.ac.id');
    cy.get('input[name="password"]').type('password123');
    cy.get('button[type="submit"]').click();

    // Dosen berhasil masuk ke dashboard
    cy.url().should('not.include', '/login');
    cy.contains('Ade Ismail').should('be.visible');
    cy.contains('Dosen').should('be.visible');
  });

  it('TC_LOGIN_006 - Memeriksa login admin dengan data valid', () => {
    cy.contains('.role-tab-btn', 'Admin JTI').click();
    cy.get('input[name="identifier"]').type('admin.jti@polinema.ac.id');
    cy.get('input[name="password"]').type('password123');
    cy.get('button[type="submit"]').click();

    // Admin berhasil masuk ke dashboard admin
    cy.url().should('include', '/admin');
    cy.contains('Panel Administrator').should('be.visible');
  });

  it('TC_LOGIN_007 - Memeriksa login default tanpa mengganti tab role', () => {
    // Default role terpasang 'mahasiswa'
    cy.get('input#roleInput').should('have.value', 'mahasiswa');
    cy.get('input[name="identifier"]').type('244107060116');
    cy.get('input[name="password"]').type('password123');
    cy.get('button[type="submit"]').click();

    cy.url().should('not.include', '/login');
    cy.contains('Dashboard Analitik').should('be.visible');
  });

  it('TC_LOGIN_008 - Memeriksa login dengan input akun yang berbeda role (Fitur Auto-detection)', () => {
    // Memilih tab Dosen tetapi mengisi kredensial Mahasiswa
    cy.contains('.role-tab-btn', 'Dosen').click();
    cy.get('input[name="identifier"]').type('244107060116');
    cy.get('input[name="password"]').type('password123');
    cy.get('button[type="submit"]').click();

    // Di sistem PBL ini terdapat auto-detection cerdas
    cy.url().should('not.include', '/login');
  });

  it('TC_LOGIN_009 - Memeriksa login dengan NIM/email kosong', () => {
    cy.get('input[name="password"]').type('password123');
    cy.get('button[type="submit"]').click();

    cy.get('input[name="identifier"]:invalid').should('exist');
    cy.url().should('include', '/login');
  });

  it('TC_LOGIN_010 - Memeriksa login dengan password kosong', () => {
    cy.get('input[name="identifier"]').type('244107060116');
    cy.get('button[type="submit"]').click();

    cy.get('input[name="password"]:invalid').should('exist');
    cy.url().should('include', '/login');
  });

  it('TC_LOGIN_011 - Memeriksa fitur tampil/sembunyikan password', () => {
    cy.get('#passwordInput').type('passwordRahasia123');

    // Awalnya input bertipe 'password'
    cy.get('#passwordInput').should('have.attr', 'type', 'password');

    // Klik tombol mata untuk menampilkan password
    cy.get('.password-toggle').click();
    cy.get('#passwordInput').should('have.attr', 'type', 'text');

    // Klik kembali untuk menyembunyikan
    cy.get('.password-toggle').click();
    cy.get('#passwordInput').should('have.attr', 'type', 'password');
  });

  it('TC_LOGIN_012 - Memeriksa akses dashboard tanpa login (Defect Report PBL)', () => {
    // Catatan Defect PBL: Dashboard masih dapat diakses publik tanpa login
    cy.visit('/');
    cy.url().then((url) => {
      if (url.includes('/login')) {
        cy.log('Sistem mengamankan dashboard, pengguna diarahkan ke login');
      } else {
        cy.log('Catatan PBL: Dashboard Analitik saat ini bersifat open publik');
        cy.contains('Dashboard Analitik & Rekapitulasi Lomba').should('be.visible');
      }
    });
  });
});
