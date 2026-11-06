<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Layanan extends Model
{
    protected $table = 'layanan';
    protected $guarded = [];

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
