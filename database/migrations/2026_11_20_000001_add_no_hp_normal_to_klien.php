<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Keunikan klien berdasarkan nomor HP yang sudah dinormalkan.
 *
 * klien.no_hp unique pada string mentah, jadi 0812-xxx dan +62 812 xxx dianggap berbeda, dan pencocokan dilakukan
 * di PHP dengan memuat semua klien ke memori. Sekarang no_hp_normal (62812xxx) disimpan, unique, dan terindeks;
 * pencocokan menjadi satu query WHERE, dan database sendiri yang menolak klien kembar.
 *
 * Bila data lama sudah berisi klien yang nomornya setara, migration berhenti dengan daftar klien tersebut (tanpa
 * menggabungkan otomatis, karena penggabungan memindahkan riwayat pesanan dan harus diputuskan manusia). Setelah
 * dirapikan, jalankan ulang migrate: langkah di sini aman diulang.
 */
return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('klien', 'no_hp_normal')) {
            Schema::table('klien', function (Blueprint $t) {
                $t->string('no_hp_normal', 20)->nullable()->after('no_hp');
            });
        }

        // DB::table tidak terkena soft delete: klien yang sudah dihapus lunak ikut diperiksa (nomornya tetap unique).
        $kelompok = [];
        foreach (DB::table('klien')->select('id', 'nama', 'no_hp')->orderBy('id')->get() as $k) {
            $n = $this->normalisasi($k->no_hp);
            DB::table('klien')->where('id', $k->id)->update(['no_hp_normal' => $n]);
            $kelompok[$n][] = "#{$k->id} {$k->nama} ({$k->no_hp})";
        }

        $kembar = array_filter($kelompok, fn ($g) => count($g) > 1);
        if ($kembar) {
            $baris = [];
            foreach ($kembar as $n => $g) {
                $baris[] = "  {$n}: " . implode('  |  ', $g);
            }
            throw new RuntimeException(
                "Ada klien dengan nomor HP yang sama setelah dinormalkan. Gabungkan atau ubah salah satunya (menu Data Klien), lalu jalankan migrate lagi:\n" . implode("\n", $baris)
            );
        }

        Schema::table('klien', function (Blueprint $t) {
            $t->unique('no_hp_normal');
        });
    }

    public function down(): void
    {
        Schema::table('klien', function (Blueprint $t) {
            $t->dropUnique(['no_hp_normal']);
        });
        Schema::table('klien', function (Blueprint $t) {
            $t->dropColumn('no_hp_normal');
        });
    }

    /** Salinan beku dari normalisasi_hp() agar migration tidak berubah perilakunya bila helper diubah kelak. */
    private function normalisasi(?string $n): string
    {
        $d = preg_replace('/\D+/', '', (string) $n);
        if (str_starts_with($d, '0')) return '62' . substr($d, 1);
        if (str_starts_with($d, '8')) return '62' . $d;
        return $d;
    }
};
