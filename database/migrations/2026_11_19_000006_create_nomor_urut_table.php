<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Penghitung nomor urut kode pemesanan (IKG-TAHUN-NNN) per tahun.
 *
 * Sebelumnya nomor urut = jumlah pesanan tahun itu + 1. Cara itu bisa menghasilkan kode kembar bila ada pesanan
 * yang terhapus atau dua pemesanan masuk bersamaan (error unique pada kolom kode). Sekarang nomor diambil dari
 * tabel ini dengan kunci baris, lihat Pemesanan::buatKode().
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('nomor_urut', function (Blueprint $t) {
            $t->unsignedSmallInteger('tahun')->primary();
            $t->unsignedInteger('terakhir')->default(0);   // nomor yang sudah terpakai terakhir kali
        });

        // Isi awal dari data yang ada: nomor tertinggi per tahun, termasuk pesanan yang sudah dihapus lunak
        // (DB::table tidak terkena soft delete). Dipakai nomor tertinggi, bukan jumlah, agar tidak bentrok dengan kode lama.
        $maks = [];
        foreach (DB::table('pemesanan')->pluck('kode') as $kode) {
            if (preg_match('/^IKG-(\d{4})-(\d+)$/', $kode, $m)) {
                $maks[(int) $m[1]] = max($maks[(int) $m[1]] ?? 0, (int) $m[2]);
            }
        }
        foreach ($maks as $tahun => $terakhir) {
            DB::table('nomor_urut')->insert(['tahun' => $tahun, 'terakhir' => $terakhir]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('nomor_urut');
    }
};
