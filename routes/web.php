<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\PemesananController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\TestimoniController;
use App\Http\Controllers\PelangganAuthController;
use App\Http\Middleware\PelangganAuth;
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

Route::get('/cek-pesanan', [PemesananController::class, 'cek'])->name('pemesanan.cek');
Route::post('/cek-pesanan', [PemesananController::class, 'cekProses'])->middleware('throttle:5,1')->name('pemesanan.cek.proses');
Route::get('/cek-pesanan/{pemesanan}', [PemesananController::class, 'status'])->middleware('signed')->name('pemesanan.status');

Route::get('/testimoni', [TestimoniController::class, 'index'])->name('testimoni');
Route::post('/testimoni', [TestimoniController::class, 'store'])->middleware([PelangganAuth::class, 'throttle:10,1'])->name('testimoni.store');

/* ---------- Login & daftar user (untuk kirim testimoni) ---------- */
Route::get('/masuk', [PelangganAuthController::class, 'showLogin'])->name('pelanggan.login');
Route::post('/masuk', [PelangganAuthController::class, 'login'])->middleware('throttle:10,1')->name('pelanggan.login.submit');
Route::get('/daftar', [PelangganAuthController::class, 'showRegister'])->name('pelanggan.register');
Route::post('/daftar', [PelangganAuthController::class, 'register'])->middleware('throttle:10,1')->name('pelanggan.register.submit');
Route::post('/keluar', [PelangganAuthController::class, 'logout'])->name('pelanggan.logout');

// Tombol "Kirim Testimoni" mengarah ke sini: belum login -> ke /masuk, sudah login -> kembali ke /testimoni dengan form terbuka
Route::get('/testimoni/kirim', fn () => redirect()->route('testimoni')->with('buka_form', true))
    ->middleware(PelangganAuth::class)
    ->name('testimoni.kirim');

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
        Route::post('pemesanan/{pemesanan}/pengembalian', [Admin\PemesananController::class, 'pengembalian'])->name('pemesanan.pengembalian');
        Route::put('pemesanan/{pemesanan}/rincian', [Admin\PemesananController::class, 'ubahRincian'])->name('pemesanan.rincian');
        Route::post('pemesanan/{pemesanan}/reschedule', [Admin\PemesananController::class, 'reschedule'])->name('pemesanan.reschedule');
        Route::get('pemesanan/{pemesanan}/invoice', [Admin\PemesananController::class, 'invoice'])->name('pemesanan.invoice');
        Route::post('pemesanan/{pemesanan}/invoice/revisi', [Admin\PemesananController::class, 'revisiInvoice'])->name('pemesanan.invoice.revisi');

        Route::get('laporan', [Admin\LaporanController::class, 'index'])->name('laporan');
        Route::get('laporan/ekspor', [Admin\LaporanController::class, 'ekspor'])->name('laporan.ekspor');
        Route::get('laporan/pdf', [Admin\LaporanController::class, 'pdf'])->name('laporan.pdf');
        Route::resource('pengeluaran', Admin\PengeluaranController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::patch('galeri/{galeri}/aktif', [Admin\GaleriController::class, 'aktif'])->name('galeri.aktif');
        Route::resource('galeri', Admin\GaleriController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::patch('layanan/{layanan}/aktif', [Admin\LayananController::class, 'aktif'])->name('layanan.aktif');
        Route::resource('layanan', Admin\LayananController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('klien', Admin\KlienController::class)->only(['index', 'update']);
        Route::get('testimoni', [Admin\TestimoniController::class, 'index'])->name('testimoni.index');
        Route::patch('testimoni/{testimoni}', [Admin\TestimoniController::class, 'update'])->name('testimoni.update');
    });
});
