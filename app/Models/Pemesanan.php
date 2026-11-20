<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Pemesanan extends Model
{
    use SoftDeletes; // pesanan dihapus lunak: pembayaran dan riwayatnya tetap utuh untuk laporan

    protected $table = 'pemesanan';
    protected $guarded = [];
    protected $casts = ['tanggal_acara' => 'date'];

    public function getRouteKeyName(): string { return 'kode'; }

    /**
     * Kode pemesanan berikutnya (IKG-TAHUN-NNN) yang dijamin tidak kembar, juga untuk pemesanan bersamaan dan
     * walau ada pesanan yang pernah dihapus. Nomor diambil dari tabel nomor_urut dengan kunci baris
     * (SELECT ... FOR UPDATE): pemanggil kedua menunggu sampai transaksi pemanggil pertama selesai.
     *
     * WAJIB dipanggil di dalam DB::transaction yang juga menyimpan pesanannya, agar kunci bertahan sampai pesanan
     * tersimpan. Bila transaksi gagal, kenaikan nomor ikut dibatalkan sehingga tidak ada nomor yang bolong.
     */
    public static function buatKode(?int $tahun = null): string
    {
        $tahun ??= now()->year;

        // Tahun baru: baris dibuat otomatis mulai dari 0. insertOrIgnore aman bila dua proses membuatnya bersamaan.
        DB::table('nomor_urut')->insertOrIgnore(['tahun' => $tahun, 'terakhir' => 0]);
        $urut = (int) DB::table('nomor_urut')->where('tahun', $tahun)->lockForUpdate()->value('terakhir') + 1;
        DB::table('nomor_urut')->where('tahun', $tahun)->update(['terakhir' => $urut]);

        return sprintf('IKG-%d-%03d', $tahun, $urut);
    }

    public function klien()      { return $this->belongsTo(Klien::class)->withTrashed(); }
    public function detail()     { return $this->hasMany(DetailPemesanan::class); }
    public function pembayaran() { return $this->hasMany(Pembayaran::class)->orderBy('tanggal_bayar'); }
    public function reschedule() { return $this->hasMany(RiwayatReschedule::class)->latest(); }
    public function riwayatRincian() { return $this->hasMany(RiwayatRincian::class)->latest(); }
    public function riwayatPembayaran() { return $this->hasMany(RiwayatPembayaran::class)->latest(); }
    public function pengembalian() { return $this->hasMany(Pengembalian::class)->latest('tanggal_pengembalian'); }
    public function invoices()   { return $this->hasMany(Invoice::class)->orderBy('revisi'); }
    /** Invoice terbaru (revisi tertinggi). */
    public function invoice()    { return $this->hasOne(Invoice::class)->latestOfMany('revisi'); }
    public function testimoni()  { return $this->hasOne(Testimoni::class); }

    public function terbayar(): int { return (int) $this->pembayaran()->sum('nominal'); }
    public function sisa(): int     { return max(0, $this->total_biaya - $this->terbayar()); }

    /** Total dana yang sudah dikembalikan ke klien (pesanan dibatalkan). */
    public function dikembalikan(): int { return (int) $this->pengembalian()->sum('nominal'); }

    /** Dana masuk yang belum dikembalikan. */
    public function saldoRefund(): int { return max(0, $this->terbayar() - $this->dikembalikan()); }

    public const DANA_HANGUS = 'hangus';           // DP hangus: tetap pemasukan
    public const DANA_DIKELUARKAN = 'dikeluarkan'; // dikembalikan/dikeluarkan: bukan pemasukan

    /** Pesanan dibatalkan yang dananya dikeluarkan dari pemasukan (NULL pada data lama dianggap dikeluarkan). */
    public function dikeluarkanDariPemasukan(): bool
    {
        return $this->status_pemesanan === 'Dibatalkan' && $this->dana_batal !== self::DANA_HANGUS;
    }

    /** Pesanan yang pembayaran/pengembaliannya ikut dihitung sebagai pemasukan: tidak dibatalkan, atau dibatalkan dengan DP hangus. */
    public function scopeDanaDihitung($q)
    {
        return $q->where(fn ($w) => $w->where('status_pemesanan', '!=', 'Dibatalkan')->orWhere('dana_batal', self::DANA_HANGUS));
    }

    /** Kebalikan scopeDanaDihitung: pesanan Dibatalkan yang dananya dikeluarkan dari pemasukan. */
    public function scopeDanaDikeluarkan($q)
    {
        return $q->where('status_pemesanan', 'Dibatalkan')->where(fn ($w) => $w->whereNull('dana_batal')->orWhere('dana_batal', '!=', self::DANA_HANGUS));
    }

    /** Nilai DP menurut kebijakan (config ikes.dp_persen), dibulatkan ke atas. */
    public function dpTarget(): int { return (int) ceil($this->total_biaya * config('ikes.dp_persen') / 100); }

    /** Batas pelunasan = N hari sebelum acara; ikut bergeser otomatis bila tanggal acara di-reschedule. */
    public function batasPelunasan(): \Carbon\CarbonInterface
    {
        return $this->tanggal_acara->copy()->subDays((int) config('ikes.pelunasan_hari'));
    }

    /** Pesanan aktif yang belum lunas dan sudah mencapai batas pelunasan. */
    public function sudahJatuhTempo(): bool
    {
        return in_array($this->status_pemesanan, ['Menunggu Konfirmasi', 'Dikonfirmasi'], true)
            && $this->status_pembayaran !== 'Lunas'
            && today()->gte($this->batasPelunasan());
    }

    /** Sama dengan sudahJatuhTempo(), tetapi sebagai filter query (untuk daftar dan dashboard). */
    public function scopeJatuhTempo($q)
    {
        return $q->whereIn('status_pemesanan', ['Menunggu Konfirmasi', 'Dikonfirmasi'])
            ->where('status_pembayaran', '!=', 'Lunas')
            ->whereDate('tanggal_acara', '<=', today()->addDays((int) config('ikes.pelunasan_hari')));
    }

    /**
     * Hitung ulang status pembayaran dari tabel pembayaran.
     * Bila total terbayar mencapai DP kebijakan, pesanan yang masih "Menunggu Konfirmasi" otomatis menjadi
     * "Dikonfirmasi". Status hanya dinaikkan, tidak pernah diturunkan otomatis.
     */
    public function refreshPembayaran(): void
    {
        $b = $this->terbayar();
        $this->status_pembayaran = ($this->total_biaya > 0 && $b >= $this->total_biaya) ? 'Lunas' : ($b > 0 ? 'DP' : 'Belum Bayar');
        if ($this->status_pemesanan === 'Menunggu Konfirmasi' && $b > 0 && $b >= $this->dpTarget()) {
            $this->status_pemesanan = 'Dikonfirmasi';
        }
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
