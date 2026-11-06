<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** 9 tabel sesuai ERD (Gambar 4.21, Tabel 4.2 dan 4.3). */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('admin', function (Blueprint $t) {
            $t->id();
            $t->string('nama', 100);
            $t->string('email')->unique();
            $t->string('password');
            $t->rememberToken();
            $t->timestamps();
        });

        Schema::create('layanan', function (Blueprint $t) {
            $t->id();
            $t->string('nama', 150);
            $t->enum('kategori', ['Wedding', 'Catering']);
            $t->text('deskripsi');
            $t->unsignedBigInteger('harga');
            $t->string('satuan', 30)->default('');   // contoh: "/ porsi"
            $t->string('label', 40)->nullable();     // contoh: "Terlaris"
            $t->string('foto')->nullable();
            $t->timestamps();
        });

        Schema::create('klien', function (Blueprint $t) {
            $t->id();
            $t->string('nama', 100);
            $t->string('no_hp', 30)->unique();
            $t->string('alamat')->nullable();
            $t->timestamps();
        });

        Schema::create('pemesanan', function (Blueprint $t) {
            $t->id();
            $t->string('kode', 20)->unique();
            $t->foreignId('klien_id')->constrained('klien')->cascadeOnDelete();
            $t->date('tanggal_acara');
            $t->string('lokasi_acara');
            $t->text('catatan')->nullable();
            $t->unsignedBigInteger('total_biaya')->default(0);
            $t->enum('status_pemesanan', ['Menunggu Konfirmasi', 'Dikonfirmasi', 'Selesai', 'Dibatalkan'])->default('Menunggu Konfirmasi');
            $t->enum('status_pembayaran', ['Belum Bayar', 'DP', 'Lunas'])->default('Belum Bayar');
            $t->timestamps();
        });

        Schema::create('detail_pemesanan', function (Blueprint $t) {
            $t->id();
            $t->foreignId('pemesanan_id')->constrained('pemesanan')->cascadeOnDelete();
            $t->foreignId('layanan_id')->constrained('layanan');
            $t->unsignedInteger('jumlah');
            $t->unsignedBigInteger('harga_satuan');
            $t->unsignedBigInteger('subtotal');
        });

        Schema::create('pembayaran', function (Blueprint $t) {
            $t->id();
            $t->foreignId('pemesanan_id')->constrained('pemesanan')->cascadeOnDelete();
            $t->enum('jenis_pembayaran', ['DP', 'Pelunasan']);
            $t->enum('metode_bayar', ['Tunai', 'Transfer']);
            $t->unsignedBigInteger('nominal');
            $t->date('tanggal_bayar')->index();
            $t->timestamps();
        });

        Schema::create('riwayat_reschedule', function (Blueprint $t) {
            $t->id();
            $t->foreignId('pemesanan_id')->constrained('pemesanan')->cascadeOnDelete();
            $t->foreignId('admin_id')->constrained('admin');
            $t->date('tanggal_lama');
            $t->date('tanggal_baru');
            $t->string('alasan');
            $t->timestamps();
        });

        Schema::create('invoice', function (Blueprint $t) {
            $t->id();
            $t->foreignId('pemesanan_id')->unique()->constrained('pemesanan')->cascadeOnDelete();
            $t->string('nomor_invoice', 30)->unique();
            $t->date('tanggal_terbit');
            $t->timestamps();
        });

        Schema::create('testimoni', function (Blueprint $t) {
            $t->id();
            $t->foreignId('pemesanan_id')->unique()->constrained('pemesanan')->cascadeOnDelete();
            $t->unsignedTinyInteger('rating');
            $t->text('isi');
            $t->enum('status_tampil', ['Menunggu', 'Tampil', 'Disembunyikan'])->default('Menunggu');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['testimoni', 'invoice', 'riwayat_reschedule', 'pembayaran', 'detail_pemesanan', 'pemesanan', 'klien', 'layanan', 'admin'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
