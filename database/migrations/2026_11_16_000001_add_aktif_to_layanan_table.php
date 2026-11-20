<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Layanan nonaktif disembunyikan dari katalog dan tidak bisa dipesan, tetapi riwayat pesanan lama tetap utuh. */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('layanan', function (Blueprint $t) {
            $t->boolean('aktif')->default(true)->index()->after('foto');
        });
    }

    public function down(): void
    {
        Schema::table('layanan', function (Blueprint $t) {
            $t->dropIndex(['aktif']);
            $t->dropColumn('aktif');
        });
    }
};
