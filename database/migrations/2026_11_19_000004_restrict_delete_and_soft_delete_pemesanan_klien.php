<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lindungi data keuangan dari penghapusan berantai.
 *
 * Sebelumnya klien -> pemesanan -> (detail, pembayaran, invoice, testimoni, riwayat_*) memakai cascadeOnDelete,
 * sehingga menghapus satu klien/pesanan ikut menghapus semua pembayaran dan jejak revisinya (sedangkan
 * pengembalian sudah restrict). Sekarang:
 *  - semua FK di bawah memakai restrictOnDelete: induk yang masih punya data turunan tidak bisa dihapus permanen;
 *  - klien dan pemesanan memakai soft delete: "hapus" hanya menandai deleted_at, riwayat keuangan tetap utuh.
 */
return new class extends Migration {
    /** [tabel anak, kolom FK, tabel induk] */
    private const FK = [
        ['pemesanan', 'klien_id', 'klien'],
        ['detail_pemesanan', 'pemesanan_id', 'pemesanan'],
        ['pembayaran', 'pemesanan_id', 'pemesanan'],
        ['riwayat_reschedule', 'pemesanan_id', 'pemesanan'],
        ['invoice', 'pemesanan_id', 'pemesanan'],
        ['testimoni', 'pemesanan_id', 'pemesanan'],
        ['riwayat_rincian', 'pemesanan_id', 'pemesanan'],
    ];

    public function up(): void
    {
        $this->ubahFk(restrict: true);

        Schema::table('klien', function (Blueprint $t) {
            $t->softDeletes();
        });
        Schema::table('pemesanan', function (Blueprint $t) {
            $t->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('pemesanan', function (Blueprint $t) {
            $t->dropSoftDeletes();
        });
        Schema::table('klien', function (Blueprint $t) {
            $t->dropSoftDeletes();
        });

        $this->ubahFk(restrict: false);
    }

    /** Lepas FK lama lalu pasang lagi (drop dan add dipisah agar aman juga di SQLite). */
    private function ubahFk(bool $restrict): void
    {
        foreach (self::FK as [$tabel, $kolom, $induk]) {
            Schema::table($tabel, function (Blueprint $t) use ($kolom) {
                $t->dropForeign([$kolom]);
            });
            Schema::table($tabel, function (Blueprint $t) use ($kolom, $induk, $restrict) {
                $fk = $t->foreign($kolom)->references('id')->on($induk);
                $restrict ? $fk->restrictOnDelete() : $fk->cascadeOnDelete();
            });
        }
    }
};
