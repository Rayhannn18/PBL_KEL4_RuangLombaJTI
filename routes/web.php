<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BimbinganController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LombaController;
use App\Http\Controllers\TimController;
use Illuminate\Support\Facades\Route;

// Dashboard Analitik Prestasi (publik)
Route::get('/', [DashboardController::class, 'index'])->name('dashboard.analitik');
Route::get('/analitik', [DashboardController::class, 'index'])->name('dashboard.analitik.alias');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/home', [DashboardController::class, 'index'])->name('home');

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

// Modul Katalog & Eksplorasi Lomba Mahasiswa
Route::get('/lomba', [LombaController::class, 'index'])->name('lomba.index');
Route::get('/lomba/{id}', [LombaController::class, 'show'])->name('lomba.show')->whereNumber('id');
Route::get('/lomba/{id}/pedoman', [LombaController::class, 'downloadPedoman'])->name('lomba.pedoman.download')->whereNumber('id');

// Autentikasi
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1');

    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Modul Tim Mahasiswa (Papan Lowongan Tim, Form Ketua Tim, & Pengajuan Anggota Tim)
    Route::prefix('tim')->name('tim.')->group(function () {
        Route::get('/', [TimController::class, 'index'])->name('index');
        Route::get('/lowongan', [TimController::class, 'lowongan'])->name('lowongan');
        Route::get('/buat', [TimController::class, 'create'])->name('create');
        Route::post('/buat', [TimController::class, 'store'])->name('store');
        Route::get('/{id}/gabung', [TimController::class, 'formGabung'])->name('gabung.form');
        Route::post('/{id}/gabung', [TimController::class, 'gabung'])->name('gabung');
        Route::post('/anggota/{id}/respon', [TimController::class, 'responAnggota'])->name('anggota.respon');
        Route::get('/cv/{type}/{id}', [TimController::class, 'downloadCv'])->name('cv');
    });

    // Modul Bimbingan Tim Lomba & Dosen Pembimbing
    Route::get('/bimbingan', [BimbinganController::class, 'index'])->name('bimbingan.index');
    Route::post('/bimbingan/ajukan', [BimbinganController::class, 'ajukan'])->name('bimbingan.ajukan');
    Route::post('/bimbingan/logbook', [BimbinganController::class, 'storeLogbook'])->name('bimbingan.logbook.store');
    Route::post('/bimbingan/logbook/{id}/validasi', [BimbinganController::class, 'validasiLogbook'])->name('bimbingan.logbook.validasi');
    Route::post('/bimbingan/pengajuan/{id}/respon', [BimbinganController::class, 'responPengajuan'])->name('bimbingan.pengajuan.respon');
    Route::post('/bimbingan/daftar-tim-cepat', [BimbinganController::class, 'gabungLombaCepat'])->name('bimbingan.daftar_tim');
});
