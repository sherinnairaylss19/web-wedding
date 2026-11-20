<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\UniqueConstraintViolationException;

class Klien extends Model
{
    use SoftDeletes; // klien dihapus lunak: riwayat pesanan dan keuangannya tetap utuh

    protected $table = 'klien';
    protected $guarded = [];

    protected static function booted(): void
    {
        // no_hp_normal (62812xxx) selalu mengikuti no_hp, dari jalur mana pun klien disimpan (form publik, admin, seeder).
        // Kolom ini unique, jadi database menolak dua klien dengan nomor yang setara walau format ketikannya berbeda.
        static::saving(function (Klien $k) {
            if ($k->isDirty('no_hp') || $k->no_hp_normal === null) {
                $k->no_hp_normal = normalisasi_hp($k->no_hp);
            }
        });
    }

    public function pemesanan() { return $this->hasMany(Pemesanan::class); }

    /**
     * Klien dengan nomor ini (format apa pun: 0812-xxx = +62 812 xxx), atau buat baru bila belum ada. Satu query
     * ber-indeks, tidak memuat semua klien. Klien yang pernah dihapus lunak dipulihkan. Klien lama tidak diubah
     * datanya; klien baru menyimpan nomor sesuai ketikan.
     */
    public static function cariAtauBuat(string $noHp, string $nama, ?string $alamat = null): self
    {
        $norm = normalisasi_hp($noHp);
        $klien = static::withTrashed()->where('no_hp_normal', $norm)->first();

        if (! $klien) {
            try {
                $klien = static::create(['no_hp' => trim($noHp), 'nama' => $nama, 'alamat' => $alamat]);
            } catch (UniqueConstraintViolationException) {
                // Pemesanan lain dengan nomor yang sama baru saja membuatnya. lockForUpdate membaca data terbaru yang
                // sudah di-commit (select biasa di dalam transaksi bisa tidak melihatnya).
                $klien = static::withTrashed()->where('no_hp_normal', $norm)->lockForUpdate()->firstOrFail();
            }
        }

        if ($klien->trashed()) {
            $klien->restore();
        }

        return $klien;
    }
}
