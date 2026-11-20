<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Pengembalian dana (refund) untuk pesanan yang dibatalkan. Dicatat terpisah agar riwayat pembayaran asli tetap utuh. */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('pengembalian', function (Blueprint $t) {
            $t->id();
            // restrict: catatan keuangan tidak ikut terhapus bila pesanan dihapus.
            $t->foreignId('pemesanan_id')->constrained('pemesanan')->restrictOnDelete();
            $t->foreignId('admin_id')->constrained('admin');
            $t->unsignedBigInteger('nominal');
            $t->string('metode', 20);
            $t->date('tanggal_pengembalian')->index();
            $t->string('catatan')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengembalian');
    }
};
