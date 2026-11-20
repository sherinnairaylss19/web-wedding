<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Perlakuan dana pesanan yang dibatalkan, dipilih admin saat membatalkan:
 *  - 'hangus'      : DP hangus, tetap dihitung sebagai pemasukan (boleh dikembalikan sebagian)
 *  - 'dikeluarkan' : dana dikembalikan / dikeluarkan, tidak dihitung sebagai pemasukan
 * NULL pada pesanan Dibatalkan (data lama) diperlakukan sebagai 'dikeluarkan'. Pesanan tidak dibatalkan selalu NULL.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('pemesanan', function (Blueprint $t) {
            $t->string('dana_batal', 12)->nullable()->after('status_pembayaran');
        });
    }

    public function down(): void
    {
        Schema::table('pemesanan', function (Blueprint $t) {
            $t->dropColumn('dana_batal');
        });
    }
};
