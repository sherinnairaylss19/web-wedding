<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table = 'invoice';
    protected $guarded = [];
    protected $casts = ['tanggal_terbit' => 'date'];

    public function pemesanan() { return $this->belongsTo(Pemesanan::class); }
}
