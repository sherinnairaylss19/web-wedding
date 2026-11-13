<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Pembayaran, Pemesanan};

class DashboardController extends Controller
{
    public function index()
    {
        $status = collect(['Menunggu Konfirmasi', 'Dikonfirmasi', 'Selesai', 'Dibatalkan'])
            ->mapWithKeys(fn($s) => [$s => Pemesanan::where('status_pemesanan', $s)->count()]);

        $perHari = Pembayaran::whereYear('tanggal_bayar', now()->year)->whereMonth('tanggal_bayar', now()->month)
            ->get()->groupBy(fn($p) => $p->tanggal_bayar->day)->map->sum('nominal');
        $grafik = collect(range(1, now()->daysInMonth))->map(fn($d) => (float) ($perHari[$d] ?? 0))->values();

        return view('admin.dashboard', [
            'grafik' => $grafik,
            'status' => $status,
            'hariIni' => Pembayaran::whereDate('tanggal_bayar', today())->sum('nominal'),
            'bulanIni' => Pembayaran::whereYear('tanggal_bayar', now()->year)->whereMonth('tanggal_bayar', now()->month)->sum('nominal'),
            'terbaru' => Pemesanan::with('klien', 'detail.layanan')->latest()->take(6)->get(),
        ]);
    }
}
