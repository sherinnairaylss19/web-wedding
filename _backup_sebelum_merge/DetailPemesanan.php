<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPemesanan extends Model
{
    protected $table = 'detail_pemesanan';

    protected $fillable = ['pemesanan_id', 'layanan_id', 'jumlah', 'harga_satuan', 'subtotal'];

    public function pemesanan()
    {
        return $this->belongsTo(Pemesanan::class);
    }

    public function layanan()
    {
        // withTrashed: riwayat tetap terbaca walau layanan sudah dihapus admin
        return $this->belongsTo(Layanan::class)->withTrashed();
    }
}
