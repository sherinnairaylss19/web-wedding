<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pemesanan extends Model
{
    protected $table = 'pemesanan';

    protected $fillable = [
        'kode_pemesanan', 'klien_id', 'tanggal_acara', 'lokasi_acara', 'catatan',
        'total_biaya', 'status_pemesanan', 'status_pembayaran',
    ];

    protected $casts = ['tanggal_acara' => 'date', 'total_biaya' => 'integer'];

    public const MENUNGGU = 'Menunggu Konfirmasi';

    public function klien()
    {
        return $this->belongsTo(Klien::class);
    }

    public function detail()
    {
        return $this->hasMany(DetailPemesanan::class);
    }

    /** Ringkasan memakai kode pemesanan (bukan id berurutan) di URL. */
    public function getRouteKeyName(): string
    {
        return 'kode_pemesanan';
    }

    /** Contoh: IKE-20261017-7K3QXM (tanpa karakter membingungkan 0/O/1/I). */
    public static function buatKode(): string
    {
        $abjad = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        do {
            $acak = '';
            for ($i = 0; $i < 6; $i++) {
                $acak .= $abjad[random_int(0, strlen($abjad) - 1)];
            }
            $kode = 'IKE-'.now()->format('Ymd').'-'.$acak;
        } while (static::where('kode_pemesanan', $kode)->exists());

        return $kode;
    }
}
