<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimoni extends Model
{
    protected $table = 'testimoni';
    protected $guarded = [];

    public function pemesanan() { return $this->belongsTo(Pemesanan::class); }
    public function scopeTampil($q) { return $q->where('status_tampil', 'Tampil'); }
}
