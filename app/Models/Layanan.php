<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Layanan extends Model
{
    protected $table = 'layanan';
    protected $guarded = [];
    protected $casts = ['aktif' => 'boolean', 'unggulan' => 'boolean', 'urutan' => 'integer'];

    /** Jumlah maksimum layanan unggulan yang tampil di beranda (sesuai grid 4 kolom). */
    public const MAKS_UNGGULAN = 4;

    /** Hanya layanan yang tampil di katalog publik dan boleh dipesan. Riwayat pesanan lama tetap memakai relasi biasa. */
    public function scopeAktif($q) { return $q->where('aktif', true); }

    /** Layanan yang ditandai admin sebagai unggulan beranda. */
    public function scopeUnggulan($q) { return $q->where('unggulan', true); }

    /** Urutan tampil buatan admin (angka kecil dulu); seri sama diurutkan menurut id agar hasilnya selalu stabil. */
    public function scopeUrut($q) { return $q->orderBy('urutan')->orderBy('id'); }

    /** URL foto: URL penuh, id Unsplash (data contoh), atau berkas di storage/public. */
    public function getFotoUrlAttribute(): ?string
    {
        $f = $this->foto;
        if (!$f) return null;
        if (str_starts_with($f, 'http')) return $f;
        if (str_starts_with($f, 'photo-')) return "https://images.unsplash.com/{$f}?auto=format&fit=crop&w=800&q=70";
        return asset('storage/' . $f);
    }
}
