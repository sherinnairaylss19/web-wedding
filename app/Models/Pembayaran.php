<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';
    protected $guarded = [];
    protected $casts = ['tanggal_bayar' => 'date'];

    public function pemesanan() { return $this->belongsTo(Pemesanan::class); }

    /** URL foto bukti transaksi lewat route admin (null bila belum diunggah). Hanya bisa dibuka setelah login. */
    public function getBuktiUrlAttribute(): ?string
    {
        return $this->bukti_transaksi ? route('admin.pembayaran.bukti', $this) : null;
    }
}
