<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Jejak audit pembayaran.
 *
 * Sebelumnya pembayaran bisa diubah dan dihapus permanen tanpa jejak, dan tidak tercatat siapa yang menginputnya.
 * Sekarang:
 *  - pembayaran.admin_id : siapa yang mencatat (NULL untuk data lama sebelum kolom ini ada);
 *  - pembayaran.deleted_at : "hapus" hanya soft delete, nominalnya tidak lagi dihitung tetapi barisnya tetap ada;
 *  - riwayat_pembayaran : setiap pencatatan, koreksi, dan penghapusan disimpan lengkap dengan nilai lama, nilai baru,
 *    alasan, admin, dan waktunya. Semua FK restrict agar riwayat tidak ikut terhapus.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('pembayaran', function (Blueprint $t) {
            $t->foreignId('admin_id')->nullable()->after('pemesanan_id')->constrained('admin')->restrictOnDelete();
            $t->softDeletes();
        });

        Schema::create('riwayat_pembayaran', function (Blueprint $t) {
            $t->id();
            $t->foreignId('pembayaran_id')->constrained('pembayaran')->restrictOnDelete();
            $t->foreignId('pemesanan_id')->constrained('pemesanan')->restrictOnDelete();
            $t->foreignId('admin_id')->constrained('admin')->restrictOnDelete();
            $t->string('aksi', 10)->index();          // Dicatat | Diubah | Dihapus
            $t->json('data_lama')->nullable();        // kondisi sebelum perubahan (NULL saat Dicatat)
            $t->json('data_baru')->nullable();        // kondisi sesudah perubahan (NULL saat Dihapus)
            $t->string('alasan')->nullable();         // wajib diisi di aplikasi untuk Diubah dan Dihapus
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_pembayaran');

        Schema::table('pembayaran', function (Blueprint $t) {
            $t->dropForeign(['admin_id']);
            $t->dropColumn('admin_id');
            $t->dropSoftDeletes();
        });
    }
};
