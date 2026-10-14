<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BimbinganController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Dashboard Analitik Prestasi (publik)
Route::get('/', [DashboardController::class, 'index'])->name('dashboard.analitik');
Route::get('/analitik', [DashboardController::class, 'index'])->name('dashboard.analitik.alias');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/home', [DashboardController::class, 'index'])->name('home');

// Redirect rute fitur yang dihapus ke Dashboard Analitik agar tidak 404
Route::redirect('/lomba', '/');
Route::redirect('/lomba/{any}', '/')->where('any', '.*');
Route::redirect('/tim', '/');
Route::redirect('/tim/{any}', '/')->where('any', '.*');
Route::redirect('/admin', '/');
Route::redirect('/admin/{any}', '/')->where('any', '.*');

// Autentikasi
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1');

    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Modul Bimbingan Tim Lomba & Dosen Pembimbing
    Route::get('/bimbingan', [BimbinganController::class, 'index'])->name('bimbingan.index');
    Route::post('/bimbingan/ajukan', [BimbinganController::class, 'ajukan'])->name('bimbingan.ajukan');
    Route::post('/bimbingan/logbook', [BimbinganController::class, 'storeLogbook'])->name('bimbingan.logbook.store');
    Route::post('/bimbingan/logbook/{id}/validasi', [BimbinganController::class, 'validasiLogbook'])->name('bimbingan.logbook.validasi');
    Route::post('/bimbingan/pengajuan/{id}/respon', [BimbinganController::class, 'responPengajuan'])->name('bimbingan.pengajuan.respon');
    Route::post('/bimbingan/daftar-tim-cepat', [BimbinganController::class, 'gabungLombaCepat'])->name('bimbingan.daftar_tim');
});