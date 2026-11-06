<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Klien extends Model
{
    protected $table = 'klien';

    protected $fillable = ['nama', 'no_whatsapp', 'email'];

    public function pemesanan()
    {
        return $this->hasMany(Pemesanan::class);
    }
}
