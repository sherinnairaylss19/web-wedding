# Ike's Gallery – Kerangka Laravel (Backend + Blade)

Konversi prototipe HTML (`ikes-gallery/`) menjadi aplikasi Laravel 11 + MySQL sesuai proposal:
9 tabel ERD, 14 use case (UC-01 s.d. UC-14), tampilan identik dengan prototipe.

> **Status:** kode ditulis berdasarkan rancangan proposal tetapi **belum dijalankan** (tidak ada PHP di lingkungan penyusunan).
> Jalankan langkah di bawah dan laporkan error jika ada. Ini juga bahan yang baik untuk Black Box Testing di Bab V.

## Instalasi

```bash
composer create-project laravel/laravel ikes-gallery
cd ikes-gallery
```

1. **Salin** seluruh isi folder ini ke dalam proyek (timpa jika ditanya): `app/`, `config/ikes.php`, `database/`, `public/css/`, `resources/views/`, `routes/web.php`.
2. **Autoload helper** – di `composer.json`, bagian `autoload`, tambahkan `files`:
   ```json
   "autoload": { "psr-4": { "App\\": "app/", ... }, "files": ["app/helpers.php"] }
   ```
   lalu `composer dump-autoload`.
3. **Guard admin** – di `config/auth.php`, ubah provider `users`:
   ```php
   'providers' => ['users' => ['driver' => 'eloquent', 'model' => App\Models\Admin::class]],
   ```
4. **Redirect login** – di `bootstrap/app.php`:
   ```php
   ->withMiddleware(function (Middleware $middleware) {
       $middleware->redirectGuestsTo(fn () => route('admin.login'));
       $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
   })
   ```
5. **`.env`**
   ```
   APP_LOCALE=id
   APP_TIMEZONE=Asia/Jakarta        # atau set 'timezone' di config/app.php
   DB_CONNECTION=mysql
   DB_DATABASE=ikes_gallery
   IKES_WA=628976617711
   IKES_PHONE="+62-897-6617-711"
   IKES_EMAIL=ikesunaniwedding@gmail.com
   IKES_ADDRESS="Alamat usaha"
   ```
6. **Migrasi & data awal**
   ```bash
   php artisan migrate --seed
   php artisan storage:link     # agar foto layanan hasil upload tampil
   php artisan serve
   ```
7. Login admin: `admin@ikesgallery.id` / `admin123` → **ganti segera** sebelum produksi.

## Pemetaan ke proposal

| Use case | Route | Controller |
|---|---|---|
| UC-01 Katalog / Beranda / Gallery / Profil | `/`, `/katalog`, `/gallery`, `/profil` | `PublicController` |
| UC-02 Pemesanan tanpa login | `POST /pemesanan` | `PemesananController@store` |
| UC-03 Konsultasi WhatsApp | `/pemesanan/{kode}` (signed) | `PemesananController@ringkasan` + `Pemesanan::waUrl()` |
| UC-04/05 Testimoni | `/testimoni` | `TestimoniController` |
| UC-06 Login | `/admin/login` | `Admin\AuthController` |
| UC-07 Katalog admin | `/admin/layanan` | `Admin\LayananController` |
| UC-08 Data klien | `/admin/klien` | `Admin\KlienController` |
| UC-09 s.d. UC-12 | `/admin/pemesanan/...` | `Admin\PemesananController` |
| UC-13 Testimoni admin | `/admin/testimoni` | `Admin\TestimoniController` |
| UC-14 Laporan | `/admin/laporan` | `Admin\LaporanController` |

## Perbedaan dengan prototipe HTML
- Beranda berisi ringkasan layanan + cuplikan testimoni (sesuai mockup 4.4.10), bukan one-page panjang.
- Pemesanan wajib memilih satu layanan (opsi "belum yakin" dihilangkan karena `detail_pemesanan` membutuhkan `layanan_id`).
- Testimoni dibatasi satu per pemesanan (`unique pemesanan_id`) dan validasi kode dilakukan di server.
- Halaman ringkasan memakai *signed URL* agar data klien tidak bisa dibuka dengan menebak kode.
- Laporan keuangan dihitung dari tabel `pembayaran`, tanpa tabel khusus (sesuai Subbab 4.3).

## Catatan sebelum produksi
- Tailwind masih via CDN (sama dengan prototipe). Untuk produksi, pasang Tailwind lewat Vite.
- Tambahkan backup database dan batasi upload foto (sudah divalidasi: gambar ≤ 2 MB).
