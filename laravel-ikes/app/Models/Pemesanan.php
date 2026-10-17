<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pemesanan extends Model
{
    protected $table = 'pemesanan';
    protected $guarded = [];
    protected $casts = ['tanggal_acara' => 'date'];

    public function getRouteKeyName(): string { return 'kode'; }

    public function klien()      { return $this->belongsTo(Klien::class); }
    public function detail()     { return $this->hasMany(DetailPemesanan::class); }
    public function pembayaran() { return $this->hasMany(Pembayaran::class)->orderBy('tanggal_bayar'); }
    public function reschedule() { return $this->hasMany(RiwayatReschedule::class)->latest(); }
    public function invoice()    { return $this->hasOne(Invoice::class); }
    public function testimoni()  { return $this->hasOne(Testimoni::class); }

    public function terbayar(): int { return (int) $this->pembayaran()->sum('nominal'); }
    public function sisa(): int     { return max(0, $this->total_biaya - $this->terbayar()); }

    /** Hitung ulang status pembayaran dari tabel pembayaran. */
    public function refreshPembayaran(): void
    {
        $b = $this->terbayar();
        $this->status_pembayaran = ($this->total_biaya > 0 && $b >= $this->total_biaya) ? 'Lunas' : ($b > 0 ? 'DP' : 'Belum Bayar');
        $this->save();
    }

    /** Tautan click-to-chat WhatsApp owner dengan ringkasan pemesanan (UC-03). */
    public function waUrl(): string
    {
        $this->loadMissing('klien', 'detail.layanan');
        $layanan = $this->detail->map(fn($d) => $d->layanan->nama . ' x' . $d->jumlah)->implode(', ');
        $msg = "Halo Ike's Gallery 👋\nSaya sudah mengisi form pemesanan dan ingin berkonsultasi.\n\n"
            . "*Kode Pemesanan*: {$this->kode}\n*Nama*: {$this->klien->nama}\n*Layanan*: {$layanan}\n"
            . '*Tanggal Acara*: ' . tgl_long($this->tanggal_acara) . "\n*Lokasi*: {$this->lokasi_acara}\n"
            . '*Estimasi Biaya*: ' . rupiah($this->total_biaya) . "\n*Catatan*: " . ($this->catatan ?: '-')
            . "\n\nMohon informasinya. Terima kasih 🙏";
        return 'https://wa.me/' . config('ikes.wa_number') . '?text=' . rawurlencode($msg);
    }
}
