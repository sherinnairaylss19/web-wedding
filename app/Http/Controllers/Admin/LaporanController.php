<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\KeuanganService;
use App\Support\PdfWriter;
use App\Support\XlsxWriter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Laporan keuangan harian, bulanan, dan tahunan (basis kas) beserta piutang (sisa tagihan).
 * Semua perhitungan ada di KeuanganService; controller hanya membaca periode dan menyajikan hasilnya
 * (halaman web, ekspor Excel, dan unduhan PDF).
 */
class LaporanController extends Controller
{
    public function __construct(private KeuanganService $keuangan) {}

    public function index(Request $request)
    {
        [$mode, $ref, $tanggal, $bulan, $tahun] = $this->periode($request);

        return view('admin.laporan', $this->keuangan->laporan($mode, $ref) + [
            'tanggal' => $tanggal, 'bulan' => $bulan, 'tahun' => $tahun,
        ]);
    }

    /** Ekspor Excel: ringkasan, rekap (bulanan/tahunan), pembayaran, pengembalian, pengeluaran, piutang, dan pemasukan per layanan. */
    public function ekspor(Request $request)
    {
        [$mode, $ref] = $this->periode($request);
        $d = $this->keuangan->laporan($mode, $ref);
        $pt = $d['piutang'];

        $head = fn (array $kolom) => array_map(fn ($k) => XlsxWriter::head($k), $kolom);

        $ringkasan = [
            [XlsxWriter::title('Laporan Keuangan Ike\'s Gallery – ' . $d['label'])],
            [],
            $head(['Keterangan', 'Nominal']),
            ['Pembayaran masuk', XlsxWriter::money($d['masuk'])],
            ['   di antaranya DP', XlsxWriter::money($d['totalDp'])],
            ['   di antaranya Pelunasan', XlsxWriter::money($d['totalLunas'])],
            ['Pengembalian dana', XlsxWriter::money($d['refund'])],
            [XlsxWriter::bold('Pemasukan bersih'), XlsxWriter::moneyBold($d['bersih'])],
            ['Pengeluaran', XlsxWriter::money($d['keluar'])],
            [XlsxWriter::bold('Laba kas'), XlsxWriter::moneyBold($d['laba'])],
            [],
            [XlsxWriter::bold('Posisi per ' . tgl(today()))],
            ['Piutang (sisa tagihan pesanan aktif)', XlsxWriter::money($pt['total'])],
            ['   di antaranya jatuh tempo', XlsxWriter::money($pt['jatuhTempo'])],
            [],
            [XlsxWriter::bold('Pesanan dibatalkan (periode ini)')],
            ['DP hangus, termasuk dalam pemasukan', XlsxWriter::money($d['danaHangus'])],
            ['Pembayaran dikeluarkan, tidak dihitung sebagai pemasukan', XlsxWriter::money($d['danaDikeluarkan'])],
            [],
            [XlsxWriter::bold('Pemasukan bersih per kategori layanan')],
        ];
        foreach ($d['perLayanan']['kategori'] as $kat => $n) {
            $ringkasan[] = ['   ' . $kat, XlsxWriter::money((int) $n)];
        }
        $ringkasan[] = [];
        $ringkasan[] = [XlsxWriter::bold('Pengeluaran per kategori')];
        foreach ($d['pengeluaranPerKategori'] as $kat => $n) {
            $ringkasan[] = ['   ' . $kat, XlsxWriter::money((int) $n)];
        }

        // Rekap per bulan (tahunan) atau per hari (bulanan); mode harian tidak punya rekap.
        $rekap = null;
        if ($mode !== 'harian') {
            $rk = $this->keuangan->rekap($mode, $ref, $d['grafik']);
            $rekap = [$head([$mode === 'tahunan' ? 'Bulan' : 'Tanggal', 'Pembayaran Masuk', 'Pengembalian', 'Pemasukan Bersih', 'Pengeluaran', 'Laba Kas'])];
            foreach ($rk['baris'] as $b) {
                $rekap[] = [$b['label'], XlsxWriter::money($b['masuk']), XlsxWriter::money($b['refund']), XlsxWriter::money($b['bersih']), XlsxWriter::money($b['keluar']), XlsxWriter::money($b['laba'])];
            }
            $t = $rk['total'];
            $rekap[] = [XlsxWriter::bold('Total'), XlsxWriter::moneyBold($t['masuk']), XlsxWriter::moneyBold($t['refund']), XlsxWriter::moneyBold($t['bersih']), XlsxWriter::moneyBold($t['keluar']), XlsxWriter::moneyBold($t['laba'])];
        }

        $pembayaran = [$head(['Tanggal', 'Kode', 'Klien', 'Jenis', 'Metode', 'Catatan', 'Nominal'])];
        foreach ($d['pembayaran'] as $x) {
            $pembayaran[] = [tgl($x->tanggal_bayar), $x->pemesanan->kode, $x->pemesanan->klien->nama, $x->jenis_pembayaran, $x->metode_bayar, (string) $x->catatan, XlsxWriter::money((int) $x->nominal)];
        }
        $pembayaran[] = [XlsxWriter::bold('Total'), '', '', '', '', '', XlsxWriter::moneyBold($d['masuk'])];

        $pengembalian = [$head(['Tanggal', 'Kode', 'Klien', 'Metode', 'Catatan', 'Nominal'])];
        foreach ($d['pengembalian'] as $x) {
            $pengembalian[] = [tgl($x->tanggal_pengembalian), $x->pemesanan->kode, $x->pemesanan->klien->nama, $x->metode, (string) $x->catatan, XlsxWriter::money((int) $x->nominal)];
        }
        $pengembalian[] = [XlsxWriter::bold('Total'), '', '', '', '', XlsxWriter::moneyBold($d['refund'])];

        $pengeluaran = [$head(['Tanggal', 'Kategori', 'Keterangan', 'Dicatat oleh', 'Nominal'])];
        foreach ($d['pengeluaran'] as $x) {
            $pengeluaran[] = [tgl($x->tanggal), $x->kategori, $x->keterangan, (string) $x->admin?->nama, XlsxWriter::money((int) $x->nominal)];
        }
        $pengeluaran[] = [XlsxWriter::bold('Total'), '', '', '', XlsxWriter::moneyBold($d['keluar'])];

        $piutang = [$head(['Kode', 'Klien', 'No. HP', 'Tgl Acara', 'Batas Pelunasan', 'Status Pesanan', 'Total Biaya', 'Dibayar', 'Sisa', 'Jatuh Tempo'])];
        foreach ($pt['baris'] as $row) {
            $p = $row['pemesanan'];
            $piutang[] = [
                $p->kode, $p->klien->nama, (string) $p->klien->no_hp, tgl($p->tanggal_acara), tgl($p->batasPelunasan()), $p->status_pemesanan,
                XlsxWriter::money((int) $p->total_biaya), XlsxWriter::money($row['terbayar']), XlsxWriter::money($row['sisa']), $row['jatuh_tempo'] ? 'Ya' : '',
            ];
        }
        $piutang[] = [XlsxWriter::bold('Total'), '', '', '', '', '', '', '', XlsxWriter::moneyBold($pt['total']), ''];

        $layanan = [$head(['Kategori', 'Layanan', 'Pemasukan Bersih'])];
        foreach ($d['perLayanan']['layanan'] as $row) {
            $layanan[] = [$row['kategori'], $row['nama'], XlsxWriter::money((int) $row['nominal'])];
        }

        $xlsx = (new XlsxWriter())->sheet('Ringkasan', $ringkasan, [42, 20]);
        if ($rekap) {
            $xlsx->sheet($mode === 'tahunan' ? 'Rekap Bulanan' : 'Rekap Harian', $rekap, [18, 20, 20, 20, 20, 20], true);
        }
        $xlsx->sheet('Pembayaran', $pembayaran, [14, 22, 24, 12, 12, 30, 18], true)
            ->sheet('Pengembalian', $pengembalian, [14, 22, 24, 12, 30, 18], true)
            ->sheet('Pengeluaran', $pengeluaran, [14, 24, 40, 20, 18], true)
            ->sheet('Piutang', $piutang, [22, 24, 18, 14, 16, 20, 18, 18, 18, 12], true)
            ->sheet('Per Layanan', $layanan, [14, 40, 20], true);

        $path = tempnam(sys_get_temp_dir(), 'ikes');
        try {
            $xlsx->save($path);
        } catch (RuntimeException $e) {
            @unlink($path);
            return back()->withErrors(['ekspor' => $e->getMessage()]);
        }

        $nama = 'laporan-keuangan-' . $mode . '-' . Str::slug($d['label']) . '.xlsx';
        return response()->download($path, $nama, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /** Unduh PDF: isi sama dengan halaman laporan (ringkasan, rekap, per layanan/kategori, piutang, dan rincian transaksi). */
    public function pdf(Request $request)
    {
        [$mode, $ref] = $this->periode($request);
        $d = $this->keuangan->laporan($mode, $ref);
        $pt = $d['piutang'];
        $rp = fn ($n) => ($n < 0 ? '- ' : '') . rupiah(abs($n));

        $pdf = new PdfWriter("Ike's Gallery · Laporan Keuangan · dicetak " . tgl(today()));
        $pdf->title("Laporan Keuangan Ike's Gallery", ucfirst($mode) . ' · ' . $d['label']);

        $pdf->heading('Ringkasan');
        $pdf->table(['Keterangan', 'Nominal'], [
            ['Pembayaran masuk', $rp($d['masuk'])],
            ['   di antaranya DP', $rp($d['totalDp'])],
            ['   di antaranya Pelunasan', $rp($d['totalLunas'])],
            ['Pengembalian dana', $rp($d['refund'])],
            ['Pemasukan bersih', $rp($d['bersih'])],
            ['Pengeluaran', $rp($d['keluar'])],
            ['Laba kas', $rp($d['laba'])],
        ], [3, 2], ['L', 'R'], [4, 6], 0.5);
        $pdf->paragraph('Basis kas: pembayaran diakui saat diterima, bukan saat acara berlangsung. Laba kas = pembayaran masuk − pengembalian − pengeluaran.');

        $pdf->heading('Posisi per ' . tgl(today()));
        $pdf->table(['Keterangan', 'Nominal'], [
            ['Piutang (sisa tagihan pesanan aktif)', $rp($pt['total'])],
            ['   di antaranya jatuh tempo', $rp($pt['jatuhTempo'])],
        ], [3, 2], ['L', 'R'], [], 0.5);

        $pdf->heading('Pesanan Dibatalkan (periode ini)');
        $pdf->table(['Keterangan', 'Nominal'], [
            ['DP hangus, termasuk dalam pemasukan', $rp($d['danaHangus'])],
            ['Pembayaran dikeluarkan, tidak dihitung sebagai pemasukan', $rp($d['danaDikeluarkan'])],
        ], [3, 2], ['L', 'R'], [], 0.5);

        if ($mode !== 'harian') {
            $rk = $this->keuangan->rekap($mode, $ref, $d['grafik']);
            $baris = $mode === 'tahunan'
                ? $rk['baris']
                : array_values(array_filter($rk['baris'], fn ($b) => $b['masuk'] || $b['refund'] || $b['keluar'])); // bulanan: lewati hari tanpa transaksi
            $rows = array_map(fn ($b) => [$b['label'], $rp($b['masuk']), $rp($b['refund']), $rp($b['bersih']), $rp($b['keluar']), $rp($b['laba'])], $baris);
            $t = $rk['total'];
            $rows[] = ['Total', $rp($t['masuk']), $rp($t['refund']), $rp($t['bersih']), $rp($t['keluar']), $rp($t['laba'])];
            $pdf->heading($mode === 'tahunan' ? 'Rekap per Bulan' : 'Rekap per Hari');
            $pdf->table([$mode === 'tahunan' ? 'Bulan' : 'Tanggal', 'Pembayaran Masuk', 'Pengembalian', 'Pemasukan Bersih', 'Pengeluaran', 'Laba Kas'],
                $rows, [2, 2, 2, 2, 2, 2], ['L', 'R', 'R', 'R', 'R', 'R'], [count($rows) - 1]);
        }

        $rows = [];
        $tebal = [];
        foreach ($d['perLayanan']['kategori'] as $kat => $n) {
            $tebal[] = count($rows);
            $rows[] = [$kat, $rp($n)];
            foreach ($d['perLayanan']['layanan']->where('kategori', $kat) as $r) {
                $rows[] = ['   ' . $r['nama'], $rp($r['nominal'])];
            }
        }
        $pdf->heading('Pemasukan Bersih per Layanan');
        $pdf->table(['Layanan', 'Pemasukan Bersih'], $rows, [3, 2], ['L', 'R'], $tebal, 0.5);
        $pdf->paragraph('Pembayaran dicatat per pesanan, sehingga untuk pesanan berisi beberapa layanan nilainya dibagi sebanding dengan subtotal tiap layanan.');

        $rows = [];
        foreach ($d['pengeluaranPerKategori'] as $kat => $n) {
            $rows[] = [$kat, $rp($n)];
        }
        if ($rows) {
            $rows[] = ['Total', $rp($d['keluar'])];
        }
        $pdf->heading('Pengeluaran per Kategori');
        $pdf->table(['Kategori', 'Total'], $rows, [3, 2], ['L', 'R'], $rows ? [count($rows) - 1] : [], 0.5);

        $rows = [];
        foreach ($pt['baris']->take(25) as $row) {
            $p = $row['pemesanan'];
            $rows[] = [$p->kode, $p->klien->nama, tgl($p->tanggal_acara), tgl($p->batasPelunasan()), $rp($p->total_biaya), $rp($row['terbayar']), $rp($row['sisa']), $row['jatuh_tempo'] ? 'Jatuh tempo' : ''];
        }
        $pdf->heading('Piutang (Sisa Tagihan per Hari Ini) · Total ' . $rp($pt['total']));
        $pdf->table(['Kode', 'Klien', 'Tgl Acara', 'Batas Pelunasan', 'Total', 'Dibayar', 'Sisa', ''], $rows, [3, 4, 2, 2, 2, 2, 2, 2], ['L', 'L', 'L', 'L', 'R', 'R', 'R', 'L']);
        if ($pt['baris']->count() > 25) {
            $pdf->paragraph('Menampilkan 25 dari ' . $pt['baris']->count() . ' pesanan. Daftar lengkap ada di ekspor Excel (sheet Piutang).');
        }

        $rows = [];
        foreach ($d['pembayaran'] as $x) {
            $rows[] = [tgl($x->tanggal_bayar), $x->pemesanan->kode, $x->pemesanan->klien->nama, $x->jenis_pembayaran, $x->metode_bayar, (string) $x->catatan, $rp($x->nominal)];
        }
        if ($rows) {
            $rows[] = ['Total', '', '', '', '', '', $rp($d['masuk'])];
        }
        $pdf->heading('Rincian Pembayaran');
        $pdf->table(['Tanggal', 'Kode', 'Klien', 'Jenis', 'Metode', 'Catatan', 'Nominal'], $rows, [2, 3, 4, 2, 2, 4, 2], ['L', 'L', 'L', 'L', 'L', 'L', 'R'], $rows ? [count($rows) - 1] : []);

        if ($d['pengembalian']->isNotEmpty()) {
            $rows = [];
            foreach ($d['pengembalian'] as $x) {
                $rows[] = [tgl($x->tanggal_pengembalian), $x->pemesanan->kode, $x->pemesanan->klien->nama, $x->metode, (string) $x->catatan, $rp($x->nominal)];
            }
            $rows[] = ['Total', '', '', '', '', $rp($d['refund'])];
            $pdf->heading('Pengembalian Dana');
            $pdf->table(['Tanggal', 'Kode', 'Klien', 'Metode', 'Catatan', 'Nominal'], $rows, [2, 3, 4, 2, 4, 2], ['L', 'L', 'L', 'L', 'L', 'R'], [count($rows) - 1]);
        }

        if ($d['pengeluaran']->isNotEmpty()) {
            $rows = [];
            foreach ($d['pengeluaran'] as $x) {
                $rows[] = [tgl($x->tanggal), $x->kategori, $x->keterangan, (string) $x->admin?->nama, $rp($x->nominal)];
            }
            $rows[] = ['Total', '', '', '', $rp($d['keluar'])];
            $pdf->heading('Rincian Pengeluaran');
            $pdf->table(['Tanggal', 'Kategori', 'Keterangan', 'Dicatat oleh', 'Nominal'], $rows, [2, 3, 6, 2, 2], ['L', 'L', 'L', 'L', 'R'], [count($rows) - 1]);
        }

        $nama = 'laporan-keuangan-' . $mode . '-' . Str::slug($d['label']) . '.pdf';
        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $nama . '"',
        ]);
    }

    /**
     * Baca periode dari query string. Parameter yang tidak valid ditolak (422/redirect) alih-alih membuat Carbon::parse error 500.
     *
     * @return array{0: string, 1: Carbon, 2: string, 3: string, 4: int} mode, tanggal acuan, lalu isian form (tanggal, bulan, tahun)
     */
    private function periode(Request $request): array
    {
        $request->validate([
            'mode' => 'nullable|in:harian,bulanan,tahunan',
            'tanggal' => 'nullable|date',
            'bulan' => 'nullable|date_format:Y-m',
            'tahun' => 'nullable|integer|min:2000|max:2100',
        ]);

        $mode = $request->query('mode') ?: 'harian';
        $tanggal = $request->query('tanggal') ?: today()->toDateString();
        $bulan = $request->query('bulan') ?: now()->format('Y-m');
        $tahun = (int) ($request->query('tahun') ?: now()->year);

        $ref = match ($mode) {
            'tahunan' => Carbon::create($tahun, 1, 1)->startOfDay(),
            'bulanan' => Carbon::parse($bulan . '-01')->startOfDay(),
            default => Carbon::parse($tanggal)->startOfDay(),
        };

        return [$mode, $ref, $tanggal, $bulan, $tahun];
    }
}
