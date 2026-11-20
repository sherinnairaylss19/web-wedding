<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Jejak audit satu pembayaran: dicatat, diubah, atau dihapus (siapa, kapan, nilai lama/baru, kenapa). */
class RiwayatPembayaran extends Model
{
    protected $table = 'riwayat_pembayaran';
    protected $guarded = [];
    protected $casts = ['data_lama' => 'array', 'data_baru' => 'array'];

    public const DICATAT = 'Dicatat';
    public const DIUBAH = 'Diubah';
    public const DIHAPUS = 'Dihapus';

    public function admin() { return $this->belongsTo(Admin::class); }
    public function pemesanan() { return $this->belongsTo(Pemesanan::class)->withTrashed(); }
    public function pembayaran() { return $this->belongsTo(Pembayaran::class)->withTrashed(); }
}
