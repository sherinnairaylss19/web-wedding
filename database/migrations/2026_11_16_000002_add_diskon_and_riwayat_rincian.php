<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Admin dapat mengubah rincian pesanan (tambah/hapus layanan, ubah jumlah & harga, beri diskon).
 * Aturan: total_biaya = jumlah subtotal detail_pemesanan - diskon. Setiap perubahan dicatat di riwayat_rincian.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('pemesanan', function (Blueprint $t) {
            $t->unsignedBigInteger('diskon')->default(0)->after('total_biaya');
        });

        Schema::create('riwayat_rincian', function (Blueprint $t) {
            $t->id();
            $t->foreignId('pemesanan_id')->constrained('pemesanan')->cascadeOnDelete();
            $t->foreignId('admin_id')->constrained('admin');
            $t->unsignedBigInteger('total_lama');
            $t->unsignedBigInteger('total_baru');
            $t->unsignedBigInteger('diskon')->default(0);
            $t->string('alasan');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_rincian');
        Schema::table('pemesanan', function (Blueprint $t) {
            $t->dropColumn('diskon');
        });
    }
};
