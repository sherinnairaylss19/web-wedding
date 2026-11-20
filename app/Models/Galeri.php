<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Satu foto karya Ike's Gallery di halaman Gallery publik. */
class Galeri extends Model
{
    protected $table = 'galeri';
    protected $guarded = [];
    protected $casts = ['aktif' => 'boolean', 'urutan' => 'integer'];

    /** Hanya foto yang tampil di Gallery publik. */
    public function scopeAktif($q) { return $q->where('aktif', true); }

    /** Urutan buatan admin (angka kecil dulu); seri sama: foto terbaru dulu, agar hasilnya selalu stabil. */
    public function scopeUrut($q) { return $q->orderBy('urutan')->orderByDesc('id'); }

    /** URL foto yang diunggah ke storage/public. */
    public function getFotoUrlAttribute(): string
    {
        return asset('storage/' . $this->foto);
    }
}
