<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BimbinganController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - RuangLombaJTI
|--------------------------------------------------------------------------
*/

// Beranda langsung mengarah ke Dashboard Analitik Prestasi
Route::get('/', [DashboardController::class, 'index'])->name('dashboard.analitik');
Route::get('/analitik', [DashboardController::class, 'index'])->name('dashboard.analitik.alias');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/home', [DashboardController::class, 'index'])->name('home');

// Modul Bimbingan Tim Lomba & Dosen Pembimbing
Route::get('/bimbingan', [BimbinganController::class, 'index'])->name('bimbingan.index');
Route::post('/bimbingan/ajukan', [BimbinganController::class, 'ajukan'])->name('bimbingan.ajukan');
Route::post('/bimbingan/logbook', [BimbinganController::class, 'storeLogbook'])->name('bimbingan.logbook.store');
Route::post('/bimbingan/logbook/{id}/validasi', [BimbinganController::class, 'validasiLogbook'])->name('bimbingan.logbook.validasi');
Route::post('/bimbingan/pengajuan/{id}/respon', [BimbinganController::class, 'responPengajuan'])->name('bimbingan.pengajuan.respon');
Route::post('/bimbingan/daftar-tim-cepat', [BimbinganController::class, 'gabungLombaCepat'])->name('bimbingan.daftar_tim');

// Panel Administrator RuangLombaJTI (Berdasarkan Activity Diagram PBL)
Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');

    // 1. Verifikasi & Kelola Data Lomba
    Route::post('/lomba', [AdminController::class, 'storeLomba'])->name('lomba.store');
    Route::put('/lomba/{id}', [AdminController::class, 'updateLomba'])->name('lomba.update');
    Route::post('/lomba/{id}/verifikasi', [AdminController::class, 'verifikasiLomba'])->name('lomba.verifikasi');
    Route::delete('/lomba/{id}', [AdminController::class, 'destroyLomba'])->name('lomba.destroy');

    // 2. Menyetujui / Menolak Ketua Tim
    Route::post('/tim/{id}/verifikasi', [AdminController::class, 'verifikasiTim'])->name('tim.verifikasi');
    Route::delete('/tim/{id}', [AdminController::class, 'destroyTim'])->name('tim.destroy');

    // 3. Kelola Data Dosen
    Route::post('/dosen', [AdminController::class, 'storeDosen'])->name('dosen.store');
    Route::put('/dosen/{nidn}', [AdminController::class, 'updateDosen'])->name('dosen.update');
    Route::delete('/dosen/{nidn}', [AdminController::class, 'destroyDosen'])->name('dosen.destroy');

    // 4. Kelola Data Mahasiswa
    Route::post('/mahasiswa', [AdminController::class, 'storeMahasiswa'])->name('mahasiswa.store');
    Route::put('/mahasiswa/{nim}', [AdminController::class, 'updateMahasiswa'])->name('mahasiswa.update');
    Route::delete('/mahasiswa/{nim}', [AdminController::class, 'destroyMahasiswa'])->name('mahasiswa.destroy');
});

// Redirect rute legacy ke Dashboard Analitik agar tidak 404
Route::redirect('/lomba', '/');
Route::redirect('/lomba/{any}', '/')->where('any', '.*');
Route::redirect('/tim', '/');
Route::redirect('/tim/{any}', '/')->where('any', '.*');

// Modul Autentikasi Multi-Aktor (Mahasiswa, Dosen, Admin)
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
