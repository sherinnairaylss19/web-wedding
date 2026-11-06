<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimoni extends Model
{
    protected $table = 'testimoni';
    protected $guarded = [];

    public function pemesanan() { return $this->belongsTo(Pemesanan::class); }
    public function scopeTampil($q) { return $q->where('status_tampil', 'Tampil'); }

    /** URL foto bukti (disimpan di disk "public"); null jika klien tidak melampirkan foto. */
    public function getFotoUrlAttribute(): ?string
    {
        // asset() mengikuti alamat yang sedang dibuka di browser, jadi tidak bergantung pada APP_URL
        return $this->foto ? asset('storage/'.$this->foto) : null;
    }
}
