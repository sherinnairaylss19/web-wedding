<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Invoice yang membeku + revisi.
 *
 * Sebelumnya tabel invoice hanya menyimpan nomor dan tanggal terbit, sedangkan isinya (nama layanan, rincian, diskon,
 * total, data klien) dibaca ulang dari pesanan setiap kali dibuka, sehingga invoice yang sudah diterima klien ikut
 * berubah bila rincian atau harga diedit kemudian. Sekarang:
 *  - rincian : snapshot tagihan (klien, item layanan, subtotal, diskon, total) saat invoice diterbitkan;
 *  - revisi  : 0 = invoice awal, 1, 2, ... = revisi bila tagihan berubah; satu pesanan boleh punya banyak revisi;
 *  - alasan  : alasan penerbitan revisi.
 */
return new class extends Migration {
    public function up(): void
    {
        // Pasang unique gabungan lebih dulu: FK pemesanan_id tetap punya indeks pendukung saat unique lama dilepas (MySQL).
        Schema::table('invoice', function (Blueprint $t) {
            $t->unsignedSmallInteger('revisi')->default(0)->after('pemesanan_id');
            $t->json('rincian')->nullable()->after('tanggal_terbit');
            $t->string('alasan')->nullable()->after('rincian');
            $t->unique(['pemesanan_id', 'revisi']);
        });
        Schema::table('invoice', function (Blueprint $t) {
            $t->dropUnique(['pemesanan_id']);
        });

        // Invoice yang sudah terbit sebelum fitur ini tidak punya snapshot asli. Dibekukan dari kondisi pesanan SAAT INI,
        // mulai sekarang isinya tidak berubah lagi. (Bila rincian sempat diedit setelah invoice terbit, tidak ada cara
        // memulihkan angka aslinya.)
        foreach (DB::table('invoice')->get() as $inv) {
            $p = DB::table('pemesanan')->find($inv->pemesanan_id);
            $k = DB::table('klien')->find($p->klien_id);
            $items = DB::table('detail_pemesanan as d')
                ->join('layanan as l', 'l.id', '=', 'd.layanan_id')
                ->where('d.pemesanan_id', $p->id)->orderBy('d.id')
                ->get(['d.layanan_id', 'l.nama', 'd.jumlah', 'd.harga_satuan', 'd.subtotal'])
                ->map(fn ($r) => [
                    'layanan_id' => (int) $r->layanan_id, 'nama' => $r->nama, 'jumlah' => (int) $r->jumlah,
                    'harga_satuan' => (int) $r->harga_satuan, 'subtotal' => (int) $r->subtotal,
                ])->all();

            DB::table('invoice')->where('id', $inv->id)->update(['rincian' => json_encode([
                'klien' => ['nama' => $k->nama, 'no_hp' => $k->no_hp, 'alamat' => $k->alamat],
                'items' => $items,
                'subtotal' => array_sum(array_column($items, 'subtotal')),
                'diskon' => (int) $p->diskon,
                'total' => (int) $p->total_biaya,
            ])]);
        }
    }

    public function down(): void
    {
        // Satu pesanan hanya boleh satu invoice lagi, jadi revisi (> 0) dibuang.
        DB::table('invoice')->where('revisi', '>', 0)->delete();

        Schema::table('invoice', function (Blueprint $t) {
            $t->unique(['pemesanan_id']);
        });
        Schema::table('invoice', function (Blueprint $t) {
            $t->dropUnique(['pemesanan_id', 'revisi']);
        });
        Schema::table('invoice', function (Blueprint $t) {
            $t->dropColumn(['revisi', 'rincian', 'alasan']);
        });
    }
};
