<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Layanan unggulan di beranda ditentukan eksplisit oleh admin:
 *  - unggulan : tampil di beranda (bila layanan aktif)
 *  - urutan   : urutan tampil, angka kecil lebih dulu (seri sama diurutkan menurut id)
 * Sebelumnya beranda memakai orderByDesc('label') (urutan abjad teks label), sehingga hasilnya tidak bisa diatur.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('layanan', function (Blueprint $t) {
            $t->boolean('unggulan')->default(false)->index()->after('aktif');
            $t->unsignedSmallInteger('urutan')->default(0)->after('unggulan');
        });

        // Pertahankan tampilan beranda saat ini: 4 layanan aktif yang sebelumnya terpilih diberi tanda unggulan
        // dengan urutan 1..4. Admin bisa mengubahnya dari Katalog Layanan.
        $ids = DB::table('layanan')->where('aktif', true)->orderByDesc('label')->orderBy('id')->limit(4)->pluck('id');
        foreach ($ids as $i => $id) {
            DB::table('layanan')->where('id', $id)->update(['unggulan' => true, 'urutan' => $i + 1]);
        }
    }

    public function down(): void
    {
        Schema::table('layanan', function (Blueprint $t) {
            $t->dropIndex(['unggulan']);
            $t->dropColumn(['unggulan', 'urutan']);
        });
    }
};
