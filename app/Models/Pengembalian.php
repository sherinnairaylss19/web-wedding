<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Dana yang dikembalikan ke klien setelah pesanan dibatalkan. */
class Pengembalian extends Model
{
    protected $table = 'pengembalian';
    protected $guarded = [];
    protected $casts = ['tanggal_pengembalian' => 'date'];

    public function pemesanan() { return $this->belongsTo(Pemesanan::class)->withTrashed(); }

    /** Hanya pengembalian yang ikut dihitung di laporan (pesanan batal dengan dana dikeluarkan tidak dihitung, karena pembayarannya juga tidak). */
    public function scopeDihitung($q) { return $q->whereHas('pemesanan', fn ($p) => $p->danaDihitung()); }
    public function admin() { return $this->belongsTo(Admin::class); }
}
