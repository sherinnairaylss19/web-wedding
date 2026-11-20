<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Pembayaran, Pemesanan, Pengembalian};
use App\Services\KeuanganService;

class DashboardController extends Controller
{
    public function __construct(private KeuanganService $keuangan) {}

    public function index()
    {
        $status = collect(['Menunggu Konfirmasi', 'Dikonfirmasi', 'Selesai', 'Dibatalkan'])
            ->mapWithKeys(fn($s) => [$s => Pemesanan::where('status_pemesanan', $s)->count()]);

        // Satu sumber hitungan dengan halaman Laporan (KeuanganService), jadi angka dashboard dan laporan selalu sama.
        // Pemasukan bersih = pembayaran masuk - pengembalian dana. Laba kas = pemasukan bersih - pengeluaran usaha.
        $lap = $this->keuangan->laporan('bulanan', now());
        $grafik = $lap['grafik'];

        return view('admin.dashboard', [
            'status' => $status,
            'hariIni' => Pembayaran::dihitung()->whereDate('tanggal_bayar', today())->sum('nominal') - Pengembalian::dihitung()->whereDate('tanggal_pengembalian', today())->sum('nominal'),
            'bulanIni' => $lap['bersih'],
            'pengeluaranBulan' => $lap['keluar'],
            'labaBulan' => $lap['laba'],
            'grafik' => $grafik,
            'adaGrafik' => collect($grafik['bersih'])->merge($grafik['keluar'])->contains(fn($v) => $v != 0),
            'rekapTahun' => $this->keuangan->rekapTahun(now()->year),
            'perLayanan' => $lap['perLayanan'],
            'pengeluaranPerKategori' => $lap['pengeluaranPerKategori'],
            'piutang' => $lap['piutang'],
            'jatuhTempo' => Pemesanan::jatuhTempo()->count(),
            'terbaru' => Pemesanan::with('klien', 'detail.layanan')->latest()->take(6)->get(),
        ]);
    }
}
