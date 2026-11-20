<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Biaya operasional usaha (bahan baku, upah, sewa, dll.). Soft delete: catatan keuangan tidak hilang permanen. */
class Pengeluaran extends Model
{
    use SoftDeletes;

    protected $table = 'pengeluaran';
    protected $guarded = [];
    protected $casts = ['tanggal' => 'date'];

    public const KATEGORI = ['Bahan Baku', 'Upah & Gaji', 'Sewa & Transport', 'Dekorasi & Perlengkapan', 'Operasional', 'Lainnya'];

    public function admin() { return $this->belongsTo(Admin::class); }
}
