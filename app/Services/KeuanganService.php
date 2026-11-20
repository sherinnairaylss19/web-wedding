<?php

namespace App\Services;

use App\Models\{Pembayaran, Pemesanan, Pengembalian, Pengeluaran};
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Perhitungan laporan keuangan (basis kas).
 *
 *  pemasukan bersih = pembayaran masuk - pengembalian dana (periode yang sama)
 *  laba kas         = pemasukan bersih - pengeluaran
 *  piutang          = sisa tagihan pesanan yang tidak dibatalkan (posisi per hari ini)
 *
 * Pesanan Dibatalkan mengikuti pilihan admin saat membatalkan (Pemesanan::dana_batal):
 *  - 'hangus'      : pembayarannya tetap pemasukan, pengembalian sebagian mengurangi (danaHangus ditampilkan sebagai info)
 *  - 'dikeluarkan' : pembayaran DAN pengembaliannya sama sekali tidak dihitung (danaDikeluarkan ditampilkan sebagai info).
 *                    Data lama tanpa pilihan (NULL) diperlakukan sebagai 'dikeluarkan'.
 * Pemilahan ini lewat scope Pembayaran::dihitung() dan Pengembalian::dihitung().
 */
class KeuanganService
{
    public const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    /** @return array{0: Carbon, 1: Carbon} awal & akhir periode */
    public function rentang(string $mode, CarbonInterface $ref): array
    {
        $r = Carbon::instance($ref)->startOfDay();
        return match ($mode) {
            'tahunan' => [$r->copy()->startOfYear(), $r->copy()->endOfYear()->startOfDay()],
            'bulanan' => [$r->copy()->startOfMonth(), $r->copy()->endOfMonth()->startOfDay()],
            default => [$r, $r->copy()],
        };
    }

    public function label(string $mode, CarbonInterface $ref): string
    {
        return match ($mode) {
            'tahunan' => (string) $ref->year,
            'bulanan' => $ref->translatedFormat('F Y'),
            default => $ref->translatedFormat('l, j F Y'),
        };
    }

    public function laporan(string $mode, CarbonInterface $ref): array
    {
        [$dari, $sampai] = $this->rentang($mode, $ref);

        // whereDate: kolom bertipe date tetapi Eloquent bisa menyimpannya sebagai "Y-m-d 00:00:00".
        $pembayaran = Pembayaran::dihitung()->with('pemesanan.klien', 'pemesanan.detail.layanan')
            ->whereDate('tanggal_bayar', '>=', $dari->toDateString())->whereDate('tanggal_bayar', '<=', $sampai->toDateString())
            ->orderBy('tanggal_bayar')->orderBy('id')->get();
        $pengembalian = Pengembalian::dihitung()->with('pemesanan.klien', 'pemesanan.detail.layanan')
            ->whereDate('tanggal_pengembalian', '>=', $dari->toDateString())->whereDate('tanggal_pengembalian', '<=', $sampai->toDateString())
            ->orderBy('tanggal_pengembalian')->orderBy('id')->get();
        $pengeluaran = Pengeluaran::with('admin')
            ->whereDate('tanggal', '>=', $dari->toDateString())->whereDate('tanggal', '<=', $sampai->toDateString())
            ->orderBy('tanggal')->orderBy('id')->get();

        $masuk = (int) $pembayaran->sum('nominal');
        $refund = (int) $pengembalian->sum('nominal');
        $keluar = (int) $pengeluaran->sum('nominal');

        return [
            'mode' => $mode, 'ref' => $ref, 'label' => $this->label($mode, $ref),
            'pembayaran' => $pembayaran, 'pengembalian' => $pengembalian, 'pengeluaran' => $pengeluaran,
            'masuk' => $masuk, 'refund' => $refund, 'bersih' => $masuk - $refund,
            'keluar' => $keluar, 'laba' => $masuk - $refund - $keluar,
            'totalDp' => (int) $pembayaran->where('jenis_pembayaran', 'DP')->sum('nominal'),
            'totalLunas' => (int) $pembayaran->where('jenis_pembayaran', 'Pelunasan')->sum('nominal'),
            'pengeluaranPerKategori' => $pengeluaran->groupBy('kategori')->map->sum('nominal')->sortDesc(),
            'grafik' => $this->grafik($mode, $ref, $pembayaran, $pengembalian, $pengeluaran),
            'perLayanan' => $this->perLayanan($pembayaran, $pengembalian),
            'piutang' => $this->piutang(),
            // Info: bagian pemasukan periode ini yang berasal dari pesanan batal (DP hangus), dan pembayaran pesanan batal yang dikeluarkan.
            'danaHangus' => (int) $pembayaran->filter(fn ($x) => $x->pemesanan?->status_pemesanan === 'Dibatalkan')->sum('nominal')
                - (int) $pengembalian->filter(fn ($x) => $x->pemesanan?->status_pemesanan === 'Dibatalkan')->sum('nominal'),
            'danaDikeluarkan' => $this->danaDikeluarkan($dari, $sampai),
        ];
    }

    /**
     * Rekap per bulan untuk satu tahun penuh (dipakai dashboard). Hanya memuat kolom yang dibutuhkan agar ringan.
     *
     * @return array{baris: array<int, array<string, mixed>>, total: array<string, int>}
     */
    public function rekapTahun(int $tahun): array
    {
        $ref = Carbon::create($tahun, 1, 1)->startOfDay();
        $awal = $ref->toDateString();
        $akhir = $ref->copy()->endOfYear()->toDateString();

        $bayar = Pembayaran::dihitung()->whereDate('tanggal_bayar', '>=', $awal)->whereDate('tanggal_bayar', '<=', $akhir)->get(['tanggal_bayar', 'nominal']);
        $refund = Pengembalian::dihitung()->whereDate('tanggal_pengembalian', '>=', $awal)->whereDate('tanggal_pengembalian', '<=', $akhir)->get(['tanggal_pengembalian', 'nominal']);
        $keluar = Pengeluaran::whereDate('tanggal', '>=', $awal)->whereDate('tanggal', '<=', $akhir)->get(['tanggal', 'nominal']);

        return $this->rekap('tahunan', $ref, $this->grafik('tahunan', $ref, $bayar, $refund, $keluar));
    }

    /**
     * Susun deret grafik menjadi baris rekap (label, masuk, refund, bersih, keluar, laba) beserta totalnya.
     * Mode tahunan: satu baris per bulan. Mode bulanan: satu baris per hari. (Mode harian tidak punya rekap.)
     *
     * @param  array  $g  hasil grafik()
     * @return array{baris: array<int, array<string, mixed>>, total: array<string, int>}
     */
    public function rekap(string $mode, CarbonInterface $ref, array $g): array
    {
        $baris = [];
        $total = ['masuk' => 0, 'refund' => 0, 'bersih' => 0, 'keluar' => 0, 'laba' => 0];
        foreach ($g['label'] as $i => $label) {
            $b = [
                'label' => $mode === 'tahunan' ? $label . ' ' . $ref->year : tgl(Carbon::create($ref->year, $ref->month, (int) $label)),
                'masuk' => (int) $g['masuk'][$i], 'refund' => (int) $g['refund'][$i], 'bersih' => (int) $g['bersih'][$i],
                'keluar' => (int) $g['keluar'][$i], 'laba' => (int) $g['laba'][$i],
            ];
            foreach ($total as $k => $_) {
                $total[$k] += $b[$k];
            }
            $baris[] = $b;
        }
        return ['baris' => $baris, 'total' => $total];
    }

    /** Deret per hari (harian/bulanan: satu bulan penuh) atau per bulan (tahunan). Semua berupa list berindeks 0. */
    private function grafik(string $mode, CarbonInterface $ref, Collection $bayar, Collection $refund, Collection $keluar): array
    {
        $tahunan = $mode === 'tahunan';
        $n = $tahunan ? 12 : $ref->daysInMonth;

        // Mode harian hanya memuat data satu hari, sedangkan grafiknya tetap sebulan penuh: ambil ulang data sebulan.
        if ($mode === 'harian') {
            $mulai = Carbon::instance($ref)->startOfMonth()->toDateString();
            $akhir = Carbon::instance($ref)->endOfMonth()->toDateString();
            $bayar = Pembayaran::dihitung()->whereDate('tanggal_bayar', '>=', $mulai)->whereDate('tanggal_bayar', '<=', $akhir)->get();
            $refund = Pengembalian::dihitung()->whereDate('tanggal_pengembalian', '>=', $mulai)->whereDate('tanggal_pengembalian', '<=', $akhir)->get();
            $keluar = Pengeluaran::whereDate('tanggal', '>=', $mulai)->whereDate('tanggal', '<=', $akhir)->get();
        }
        $kelompok = fn (Collection $c, string $kolom) => $c->groupBy(fn ($x) => $tahunan ? $x->{$kolom}->month : $x->{$kolom}->day)->map->sum('nominal');
        $m = $kelompok($bayar, 'tanggal_bayar');
        $r = $kelompok($refund, 'tanggal_pengembalian');
        $k = $kelompok($keluar, 'tanggal');

        $g = ['label' => [], 'masuk' => [], 'refund' => [], 'bersih' => [], 'keluar' => [], 'laba' => []];
        for ($i = 1; $i <= $n; $i++) {
            $mi = (int) ($m[$i] ?? 0);
            $ri = (int) ($r[$i] ?? 0);
            $ki = (int) ($k[$i] ?? 0);
            $g['label'][] = $tahunan ? self::BULAN[$i - 1] : $i;
            $g['masuk'][] = $mi;
            $g['refund'][] = $ri;
            $g['bersih'][] = $mi - $ri;
            $g['keluar'][] = $ki;
            $g['laba'][] = $mi - $ri - $ki;
        }
        return $g;
    }

    /**
     * Pemasukan bersih per layanan. Pembayaran tercatat per pesanan, jadi nilainya dibagi ke tiap layanan
     * dalam pesanan sebanding dengan subtotal (sisa pembulatan masuk ke layanan dengan subtotal terbesar).
     * Pengembalian dana mengurangi dengan cara yang sama.
     *
     * @return array{layanan: Collection, kategori: Collection}
     */
    public function perLayanan(Collection $pembayaran, Collection $pengembalian): array
    {
        $baris = [];
        $tambah = function (Pemesanan $p, int $nominal, int $tanda) use (&$baris) {
            foreach ($this->alokasi($nominal, $p->detail) as $layananId => $bagian) {
                $d = $p->detail->firstWhere('layanan_id', $layananId);
                $nama = $d?->layanan?->nama ?? 'Layanan dihapus';
                $kat = $d?->layanan?->kategori ?? 'Lainnya';
                $key = $kat . '|' . $nama;
                $baris[$key] ??= ['nama' => $nama, 'kategori' => $kat, 'nominal' => 0];
                $baris[$key]['nominal'] += $tanda * $bagian;
            }
        };
        foreach ($pembayaran as $x) { if ($x->pemesanan) $tambah($x->pemesanan, (int) $x->nominal, 1); }
        foreach ($pengembalian as $x) { if ($x->pemesanan) $tambah($x->pemesanan, (int) $x->nominal, -1); }

        $layanan = collect($baris)->values()->sortBy([['kategori', 'asc'], ['nominal', 'desc']])->values();
        $kategori = $layanan->groupBy('kategori')->map->sum('nominal');
        return ['layanan' => $layanan, 'kategori' => $kategori];
    }

    /** Bagi $nominal ke baris detail sebanding subtotal. @return array<int,int> layanan_id => bagian (jumlahnya tepat $nominal) */
    private function alokasi(int $nominal, Collection $detail): array
    {
        $per = $detail->groupBy('layanan_id')->map->sum('subtotal');
        $tot = (int) $per->sum();
        if ($per->isEmpty()) return [];
        if ($tot <= 0) { // semua harga 0: bagi rata
            $bagi = intdiv($nominal, $per->count());
            $hasil = $per->map(fn () => $bagi)->all();
            $hasil[array_key_first($hasil)] += $nominal - $bagi * $per->count();
            return $hasil;
        }
        // floor pada float: menghindari overflow integer untuk nominal sangat besar; selisih pembulatan ditutup di bawah.
        $hasil = $per->map(fn ($s) => (int) floor($nominal * (int) $s / $tot))->all();
        $sisa = $nominal - array_sum($hasil);
        $terbesar = $per->sortDesc()->keys()->first();
        $hasil[$terbesar] += $sisa;
        return $hasil;
    }

    /**
     * Piutang = sisa tagihan pesanan yang tidak dibatalkan (termasuk yang sudah Selesai tetapi belum lunas).
     * Jatuh tempo mengikuti aturan Pemesanan::sudahJatuhTempo().
     *
     * @return array{total: int, jatuhTempo: int, baris: Collection}
     */
    public function piutang(): array
    {
        $terbayar = Pembayaran::selectRaw('pemesanan_id, SUM(nominal) as s')->groupBy('pemesanan_id')->pluck('s', 'pemesanan_id');
        $baris = Pemesanan::with('klien')->where('status_pemesanan', '!=', 'Dibatalkan')->get()
            ->map(function (Pemesanan $p) use ($terbayar) {
                $b = (int) ($terbayar[$p->id] ?? 0);
                return ['pemesanan' => $p, 'terbayar' => $b, 'sisa' => max(0, (int) $p->total_biaya - $b), 'jatuh_tempo' => $p->sudahJatuhTempo()];
            })
            ->filter(fn ($r) => $r['sisa'] > 0)
            ->sortBy([['jatuh_tempo', 'desc'], fn ($a, $b) => $a['pemesanan']->tanggal_acara <=> $b['pemesanan']->tanggal_acara])
            ->values();

        return [
            'total' => (int) $baris->sum('sisa'),
            'jatuhTempo' => (int) $baris->where('jatuh_tempo', true)->sum('sisa'),
            'baris' => $baris,
        ];
    }

    /** Info: pembayaran periode ini dari pesanan Dibatalkan yang dananya dikeluarkan (tidak dihitung sebagai pemasukan). */
    private function danaDikeluarkan(CarbonInterface $dari, CarbonInterface $sampai): int
    {
        return (int) Pembayaran::whereDate('tanggal_bayar', '>=', $dari->toDateString())->whereDate('tanggal_bayar', '<=', $sampai->toDateString())
            ->whereHas('pemesanan', fn ($p) => $p->danaDikeluarkan())->sum('nominal');
    }

    public function totalPengeluaran(CarbonInterface $dari, CarbonInterface $sampai): int
    {
        return (int) Pengeluaran::whereDate('tanggal', '>=', $dari->toDateString())->whereDate('tanggal', '<=', $sampai->toDateString())->sum('nominal');
    }
}
