<?php

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

// Redirect rute fitur yang dihapus ke Dashboard Analitik agar tidak 404
Route::redirect('/lomba', '/');
Route::redirect('/lomba/{any}', '/')->where('any', '.*');
Route::redirect('/tim', '/');
Route::redirect('/tim/{any}', '/')->where('any', '.*');
Route::redirect('/admin', '/');
Route::redirect('/admin/{any}', '/')->where('any', '.*');

// Modul Autentikasi Multi-Aktor (Mahasiswa, Dosen, Admin)
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
Route::get('/quick-login/{role}', [AuthController::class, 'quickLogin'])->name('auth.quick');
