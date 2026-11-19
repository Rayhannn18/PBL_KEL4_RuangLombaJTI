/**
 * Test Suite: Registrasi Akun Mahasiswa
 * Modul: Registrasi (Berdasarkan Dokumen Pengujian Software Testing PBL)
 * Test Cases: TC_REG_001 s/d TC_REG_010
 */

describe('Modul Registrasi - RuangLombaJTI', () => {
  beforeEach(() => {
    cy.visit('/register');
  });

  it('TC_REG_001 - Registrasi mahasiswa dengan seluruh data valid', () => {
    const uniqueId = Date.now().toString().slice(-6);
    const validNIM = '264107' + uniqueId;
    const validEmail = `mhs${uniqueId}@student.polinema.ac.id`;

    cy.get('input[name="nim_nip"]').type(validNIM);
    cy.get('input[name="name"]').type('Mahasiswa Cypress Test');
    cy.get('input[name="email"]').type(validEmail);
    cy.get('select[name="prodi"]').select('D-IV Sistem Informasi Bisnis');
    cy.get('select[name="angkatan"]').select('2024');
    cy.get('input[name="password"]').type('CypressPass123!');
    cy.get('input[name="password_confirmation"]').type('CypressPass123!');
    cy.get('input[name="terms"]').check();

    cy.get('button[type="submit"]').contains('Daftarkan Akun Mahasiswa').click();

    // Sistem mengarahkan pengguna ke halaman utama/dashboard dengan sesi login aktif
    cy.url().should('not.include', '/register');
    cy.contains('Dashboard Analitik').should('be.visible');
    cy.contains('Mahasiswa Cypress Test').should('be.visible');
  });

  it('TC_REG_002 - Registrasi akun mahasiswa dengan NIM kosong', () => {
    cy.get('input[name="name"]').type('Mahasiswa Test');
    cy.get('input[name="email"]').type('mhs.test@student.polinema.ac.id');
    cy.get('select[name="prodi"]').select('D-IV Sistem Informasi Bisnis');
    cy.get('select[name="angkatan"]').select('2024');
    cy.get('input[name="password"]').type('CypressPass123!');
    cy.get('input[name="password_confirmation"]').type('CypressPass123!');
    cy.get('input[name="terms"]').check();

    cy.get('button[type="submit"]').click();

    // Validasi browser (HTML5 required) aktif dan URL tetap di register
    cy.get('input[name="nim_nip"]:invalid').should('exist');
    cy.url().should('include', '/register');
  });

  it('TC_REG_003 - Memeriksa registrasi dengan nama lengkap kosong', () => {
    cy.get('input[name="nim_nip"]').type('264107060099');
    cy.get('input[name="email"]').type('mhs.test@student.polinema.ac.id');
    cy.get('select[name="prodi"]').select('D-IV Sistem Informasi Bisnis');
    cy.get('select[name="angkatan"]').select('2024');
    cy.get('input[name="password"]').type('CypressPass123!');
    cy.get('input[name="password_confirmation"]').type('CypressPass123!');
    cy.get('input[name="terms"]').check();

    cy.get('button[type="submit"]').click();

    cy.get('input[name="name"]:invalid').should('exist');
    cy.url().should('include', '/register');
  });

  it('TC_REG_004 - Memeriksa registrasi dengan email kosong', () => {
    cy.get('input[name="nim_nip"]').type('264107060099');
    cy.get('input[name="name"]').type('Mahasiswa Test');
    cy.get('select[name="prodi"]').select('D-IV Sistem Informasi Bisnis');
    cy.get('select[name="angkatan"]').select('2024');
    cy.get('input[name="password"]').type('CypressPass123!');
    cy.get('input[name="password_confirmation"]').type('CypressPass123!');
    cy.get('input[name="terms"]').check();

    cy.get('button[type="submit"]').click();

    cy.get('input[name="email"]:invalid').should('exist');
    cy.url().should('include', '/register');
  });

  it('TC_REG_005 - Memeriksa registrasi dengan format email tidak valid', () => {
    cy.get('input[name="nim_nip"]').type('264107060099');
    cy.get('input[name="name"]').type('Mahasiswa Test');
    cy.get('input[name="email"]').type('sastrazaky23gmail.com'); // tanpa @
    cy.get('select[name="prodi"]').select('D-IV Sistem Informasi Bisnis');
    cy.get('select[name="angkatan"]').select('2024');
    cy.get('input[name="password"]').type('CypressPass123!');
    cy.get('input[name="password_confirmation"]').type('CypressPass123!');
    cy.get('input[name="terms"]').check();

    cy.get('button[type="submit"]').click();

    cy.get('input[name="email"]:invalid').should('exist');
    cy.url().should('include', '/register');
  });

  it('TC_REG_006 - Memeriksa registrasi tanpa memilih program studi', () => {
    cy.get('input[name="nim_nip"]').type('264107060099');
    cy.get('input[name="name"]').type('Mahasiswa Test');
    cy.get('input[name="email"]').type('test.prodi@student.polinema.ac.id');
    // Tidak memilih program studi
    cy.get('select[name="angkatan"]').select('2024');
    cy.get('input[name="password"]').type('CypressPass123!');
    cy.get('input[name="password_confirmation"]').type('CypressPass123!');
    cy.get('input[name="terms"]').check();

    cy.get('button[type="submit"]').click();

    cy.get('select[name="prodi"]:invalid').should('exist');
    cy.url().should('include', '/register');
  });

  it('TC_REG_007 - Memeriksa registrasi dengan password kurang dari 6 karakter', () => {
    cy.get('input[name="nim_nip"]').type('264107060099');
    cy.get('input[name="name"]').type('Mahasiswa Test');
    cy.get('input[name="email"]').type('test.pwd@student.polinema.ac.id');
    cy.get('select[name="prodi"]').select('D-IV Sistem Informasi Bisnis');
    cy.get('select[name="angkatan"]').select('2024');
    cy.get('input[name="password"]').type('12345'); // Kurang dari 6 karakter
    cy.get('input[name="password_confirmation"]').type('12345');
    cy.get('input[name="terms"]').check();

    cy.get('button[type="submit"]').click();

    // Sistem menolak registrasi dan menampilkan validasi error
    cy.get('.alert-danger').should('be.visible');
    cy.url().should('include', '/register');
  });

  it('TC_REG_008 - Memeriksa konfirmasi password yang tidak sesuai', () => {
    cy.get('input[name="nim_nip"]').type('264107060099');
    cy.get('input[name="name"]').type('Mahasiswa Test');
    cy.get('input[name="email"]').type('test.mismatch@student.polinema.ac.id');
    cy.get('select[name="prodi"]').select('D-IV Sistem Informasi Bisnis');
    cy.get('select[name="angkatan"]').select('2024');
    cy.get('input[name="password"]').type('CypressPass123!');
    cy.get('input[name="password_confirmation"]').type('CypressPass999!'); // Tidak cocok
    cy.get('input[name="terms"]').check();

    cy.get('button[type="submit"]').click();

    cy.get('.alert-danger').should('be.visible');
    cy.url().should('include', '/register');
  });

  it('TC_REG_009 - Memeriksa registrasi menggunakan email yang sudah terdaftar', () => {
    const uniqueId = Date.now().toString().slice(-6);
    cy.get('input[name="nim_nip"]').type('264107' + uniqueId);
    cy.get('input[name="name"]').type('Mahasiswa Duplikat Email');
    cy.get('input[name="email"]').type('sastra.maheva@student.polinema.ac.id'); // Email sudah ada
    cy.get('select[name="prodi"]').select('D-IV Sistem Informasi Bisnis');
    cy.get('select[name="angkatan"]').select('2024');
    cy.get('input[name="password"]').type('CypressPass123!');
    cy.get('input[name="password_confirmation"]').type('CypressPass123!');
    cy.get('input[name="terms"]').check();

    cy.get('button[type="submit"]').click();

    // Sistem menolak pendaftaran
    cy.get('.alert-danger').should('be.visible');
    cy.url().should('include', '/register');
  });

  it('TC_REG_010 - Memeriksa registrasi menggunakan NIM yang sudah terdaftar', () => {
    const uniqueId = Date.now().toString().slice(-6);
    cy.get('input[name="nim_nip"]').type('244107060116'); // NIM sudah ada
    cy.get('input[name="name"]').type('Mahasiswa Duplikat NIM');
    cy.get('input[name="email"]').type(`unik${uniqueId}@student.polinema.ac.id`);
    cy.get('select[name="prodi"]').select('D-IV Sistem Informasi Bisnis');
    cy.get('select[name="angkatan"]').select('2024');
    cy.get('input[name="password"]').type('CypressPass123!');
    cy.get('input[name="password_confirmation"]').type('CypressPass123!');
    cy.get('input[name="terms"]').check();

    cy.get('button[type="submit"]').click();

    // Sistem menolak pendaftaran
    cy.get('.alert-danger').should('be.visible');
    cy.url().should('include', '/register');
  });
});
