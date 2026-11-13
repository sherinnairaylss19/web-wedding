<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Foto bukti transaksi (opsional) dan catatan singkat untuk setiap pembayaran. */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('pembayaran', function (Blueprint $t) {
            $t->string('bukti_transaksi')->nullable()->after('tanggal_bayar');
            $t->string('catatan')->nullable()->after('bukti_transaksi');
        });
    }

    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $t) {
            $t->dropColumn(['bukti_transaksi', 'catatan']);
        });
    }
};
