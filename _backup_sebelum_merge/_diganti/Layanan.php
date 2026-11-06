<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Layanan extends Model
{
    use SoftDeletes;

    protected $table = 'layanan';

    protected $fillable = ['nama', 'kategori', 'deskripsi', 'harga', 'satuan', 'foto'];

    protected $casts = ['harga' => 'integer'];

    public const KATEGORI = ['Wedding', 'Catering'];

    /** URL foto: mendukung URL eksternal (seeder) maupun file hasil upload. */
    public function getFotoUrlAttribute(): ?string
    {
        if (! $this->foto) {
            return null;
        }

        return str_starts_with($this->foto, 'http') ? $this->foto : Storage::disk('public')->url($this->foto);
    }
}
