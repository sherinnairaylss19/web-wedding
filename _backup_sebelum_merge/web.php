<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\KlienController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\LayananController;
use App\Http\Controllers\Admin\PemesananController as AdminPemesananController;
use App\Http\Controllers\Admin\TestimoniController as AdminTestimoniController;
use App\Http\Controllers\PemesananController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

// Halaman Utama Website
Route::get('/', function () {
    return view('welcome');
});

/* ---------- 1. AREA PUBLIK (Tanpa Login) ---------- */
Route::get('/ikes_gallery', [PublicController::class, 'index'])->name('beranda');
Route::post('/pemesanan', [PemesananController::class, 'store'])->middleware('throttle:10,1')->name('pemesanan.store');
Route::get('/pemesanan/{pemesanan}', [PemesananController::class, 'show'])->middleware('throttle:30,1')->name('pemesanan.show');


/* ---------- 2. AREA ADMIN ---------- */
Route::prefix('admin')->group(function () {
    
    // A. Guest (Belum login, diarahkan ke halaman login admin)
    // Menghubungkan ke views/admin/login.blade.php[cite: 2]
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    });

    // B. Authenticated (Sudah login, masuk ke panel admin)
    Route::middleware('auth')->name('admin.')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        
        // Halaman Dashboard Admin
        // Menghubungkan ke views/admin/dashboard.blade.php[cite: 2]
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        
        // Halaman Laporan Admin
        // Menghubungkan ke views/admin/laporan.blade.php[cite: 2]
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan');

        // Resource CRUD Manajemen Admin
        // Menghubungkan ke folder views/admin/klien, layanan, pemesanan, testimoni[cite: 1, 2]
        Route::resource('klien', KlienController::class);
        Route::resource('layanan', LayananController::class);
        Route::resource('pemesanan', AdminPemesananController::class);
        Route::resource('testimoni', AdminTestimoniController::class);
    });
});