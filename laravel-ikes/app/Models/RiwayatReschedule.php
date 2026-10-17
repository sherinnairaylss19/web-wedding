<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatReschedule extends Model
{
    protected $table = 'riwayat_reschedule';
    protected $guarded = [];
    protected $casts = ['tanggal_lama' => 'date', 'tanggal_baru' => 'date'];

    public function admin() { return $this->belongsTo(Admin::class); }
}
