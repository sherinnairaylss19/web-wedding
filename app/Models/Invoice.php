<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Invoice yang diterbitkan ke klien. Isi tagihannya (klien, item layanan, subtotal, diskon, total) disimpan sebagai
 * snapshot di kolom "rincian" saat terbit, jadi tidak berubah walau pesanan diedit kemudian. Bila tagihan berubah,
 * terbitkan revisi baru (revisi 1, 2, ...); invoice lama tetap utuh.
 */
class Invoice extends Model
{
    protected $table = 'invoice';
    protected $guarded = [];
    protected $casts = ['tanggal_terbit' => 'date', 'rincian' => 'array'];

    public function pemesanan() { return $this->belongsTo(Pemesanan::class)->withTrashed(); }

    /** Nomor invoice: INV-2026-007 untuk invoice awal, INV-2026-007-R1 untuk revisi pertama, dst. */
    public static function nomorUntuk(string $kode, int $revisi): string
    {
        return 'INV-' . substr($kode, 4) . ($revisi > 0 ? "-R{$revisi}" : '');
    }

    /** Snapshot tagihan pesanan saat ini. Pemanggil sebaiknya memuat ulang relasi klien dan detail.layanan lebih dulu. */
    public static function potretTagihan(Pemesanan $p): array
    {
        $p->loadMissing('klien', 'detail.layanan');
        $items = $p->detail->map(fn ($d) => [
            'layanan_id' => (int) $d->layanan_id, 'nama' => $d->layanan?->nama ?? 'Layanan dihapus',
            'jumlah' => (int) $d->jumlah, 'harga_satuan' => (int) $d->harga_satuan, 'subtotal' => (int) $d->subtotal,
        ])->values()->all();

        return [
            'klien' => ['nama' => $p->klien->nama, 'no_hp' => $p->klien->no_hp, 'alamat' => $p->klien->alamat],
            'items' => $items,
            'subtotal' => array_sum(array_column($items, 'subtotal')),
            'diskon' => (int) $p->diskon,
            'total' => (int) $p->total_biaya,
        ];
    }

    /**
     * Apakah tagihan pesanan sekarang berbeda dari yang tertulis di invoice ini? Yang dibandingkan hanya syarat
     * tagihannya (layanan, jumlah, harga, subtotal, diskon, total). Mengganti nama layanan atau data kontak klien
     * tidak dianggap perubahan tagihan.
     */
    public function berbedaDari(array $skrg): bool
    {
        return self::inti($this->rincian ?? []) !== self::inti($skrg);
    }

    private static function inti(array $r): array
    {
        $items = array_map(
            fn ($i) => [(int) $i['layanan_id'], (int) $i['jumlah'], (int) $i['harga_satuan'], (int) $i['subtotal']],
            $r['items'] ?? []
        );
        usort($items, fn ($a, $b) => $a <=> $b);   // urutan baris tidak boleh memengaruhi hasil perbandingan

        return [$items, (int) ($r['subtotal'] ?? 0), (int) ($r['diskon'] ?? 0), (int) ($r['total'] ?? 0)];
    }
}
