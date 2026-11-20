<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Jejak perubahan total tagihan akibat perubahan rincian pesanan (siapa, kapan, berapa, kenapa). */
class RiwayatRincian extends Model
{
    protected $table = 'riwayat_rincian';
    protected $guarded = [];

    public function admin() { return $this->belongsTo(Admin::class); }
}
