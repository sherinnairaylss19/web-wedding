<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPemesanan extends Model
{
    protected $table = 'detail_pemesanan';
    public $timestamps = false;
    protected $guarded = [];

    public function layanan() { return $this->belongsTo(Layanan::class); }
}
