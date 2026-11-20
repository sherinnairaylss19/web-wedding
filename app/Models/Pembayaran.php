<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pembayaran extends Model
{
    use SoftDeletes; // pembayaran yang "dihapus" tidak lagi dihitung, tetapi barisnya tetap ada untuk audit

    protected $table = 'pembayaran';
    protected $guarded = [];
    protected $casts = ['tanggal_bayar' => 'date'];

    /** withTrashed: pembayaran pesanan yang dihapus lunak tetap terbaca dan ikut laporan. */
    public function pemesanan() { return $this->belongsTo(Pemesanan::class)->withTrashed(); }

    public function admin() { return $this->belongsTo(Admin::class); }
    public function riwayat() { return $this->hasMany(RiwayatPembayaran::class)->latest(); }

    /** Hanya pembayaran yang ikut dihitung sebagai pemasukan (bukan dari pesanan batal yang dananya dikeluarkan). */
    public function scopeDihitung($q) { return $q->whereHas('pemesanan', fn ($p) => $p->danaDihitung()); }

    /** URL foto bukti transaksi lewat route admin (null bila belum diunggah). Hanya bisa dibuka setelah login. */
    public function getBuktiUrlAttribute(): ?string
    {
        return $this->bukti_transaksi ? route('admin.pembayaran.bukti', $this) : null;
    }
}
