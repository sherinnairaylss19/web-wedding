<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';
    protected $guarded = [];
    protected $casts = ['tanggal_bayar' => 'date'];

    public function pemesanan() { return $this->belongsTo(Pemesanan::class); }
}
