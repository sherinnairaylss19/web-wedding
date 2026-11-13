<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\PemesananController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\TestimoniController;
use Illuminate\Support\Facades\Route;

/* ---------- Publik (calon klien, tanpa login) ---------- */
Route::get('/', [PublicController::class, 'beranda'])->name('beranda');
Route::redirect('/ikes_gallery', '/'); // link lama tetap berfungsi
Route::get('/katalog', [PublicController::class, 'katalog'])->name('katalog');
Route::get('/gallery', [PublicController::class, 'gallery'])->name('gallery');
Route::get('/profil', [PublicController::class, 'profil'])->name('profil');

Route::get('/pemesanan', [PemesananController::class, 'create'])->name('pemesanan.create');
Route::post('/pemesanan', [PemesananController::class, 'store'])->middleware('throttle:10,1')->name('pemesanan.store');
Route::get('/pemesanan/{pemesanan}', [PemesananController::class, 'ringkasan'])->name('pemesanan.ringkasan')->middleware('signed');

Route::get('/testimoni', [TestimoniController::class, 'index'])->name('testimoni');
Route::post('/testimoni', [TestimoniController::class, 'store'])->middleware('throttle:10,1')->name('testimoni.store');

/* ---------- Admin (wajib login) ---------- */
Route::prefix('admin')->name('admin.')->group(function () {
    // Belum login -> halaman login. Sudah login -> otomatis ke dashboard (redirectUsersTo di bootstrap/app.php)
    Route::middleware('guest')->group(function () {
        Route::get('login', [Admin\AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [Admin\AuthController::class, 'login'])->name('login.attempt')->middleware('throttle:10,1');
    });

    // Sudah login -> panel admin. Belum login -> dilempar ke admin.login (redirectGuestsTo)
    Route::middleware('auth')->group(function () {
        Route::post('logout', [Admin\AuthController::class, 'logout'])->name('logout');
        Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

        Route::get('pemesanan', [Admin\PemesananController::class, 'index'])->name('pemesanan.index');
        Route::get('pemesanan/{pemesanan}', [Admin\PemesananController::class, 'show'])->name('pemesanan.show');
        Route::patch('pemesanan/{pemesanan}/status', [Admin\PemesananController::class, 'status'])->name('pemesanan.status');
        Route::post('pemesanan/{pemesanan}/pembayaran', [Admin\PemesananController::class, 'bayar'])->name('pemesanan.bayar');
        Route::put('pemesanan/{pemesanan}/pembayaran/{pembayaran}', [Admin\PemesananController::class, 'ubahPembayaran'])->name('pemesanan.pembayaran.update');
        Route::delete('pemesanan/{pemesanan}/pembayaran/{pembayaran}', [Admin\PemesananController::class, 'hapusPembayaran'])->name('pemesanan.pembayaran.destroy');
        Route::get('pembayaran/{pembayaran}/bukti', [Admin\PemesananController::class, 'bukti'])->name('pembayaran.bukti');
        Route::post('pemesanan/{pemesanan}/reschedule', [Admin\PemesananController::class, 'reschedule'])->name('pemesanan.reschedule');
        Route::get('pemesanan/{pemesanan}/invoice', [Admin\PemesananController::class, 'invoice'])->name('pemesanan.invoice');

        Route::get('laporan', [Admin\LaporanController::class, 'index'])->name('laporan');
        Route::resource('layanan', Admin\LayananController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('klien', Admin\KlienController::class)->only(['index', 'update']);
        Route::get('testimoni', [Admin\TestimoniController::class, 'index'])->name('testimoni.index');
        Route::patch('testimoni/{testimoni}', [Admin\TestimoniController::class, 'update'])->name('testimoni.update');
    });
});
