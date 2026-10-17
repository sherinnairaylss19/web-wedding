<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use Carbon\Carbon;
use Illuminate\Http\Request;

/** UC-14: laporan dihitung langsung dari tabel pembayaran (tanpa tabel laporan). */
class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $mode = $request->query('mode') === 'bulanan' ? 'bulanan' : 'harian';
        $tanggal = $request->query('tanggal', today()->toDateString());
        $bulan = $request->query('bulan', now()->format('Y-m'));
        $ref = $mode === 'harian' ? Carbon::parse($tanggal) : Carbon::parse($bulan . '-01');

        $q = Pembayaran::with('pemesanan.klien')->orderBy('tanggal_bayar');
        $mode === 'harian' ? $q->whereDate('tanggal_bayar', $ref) : $q->whereYear('tanggal_bayar', $ref->year)->whereMonth('tanggal_bayar', $ref->month);
        $list = $q->get();

        $perHari = Pembayaran::whereYear('tanggal_bayar', $ref->year)->whereMonth('tanggal_bayar', $ref->month)
            ->get()->groupBy(fn($p) => $p->tanggal_bayar->day)->map->sum('nominal');

        return view('admin.laporan', [
            'mode' => $mode, 'tanggal' => $ref->toDateString(), 'bulan' => $ref->format('Y-m'), 'ref' => $ref, 'list' => $list,
            'total' => $list->sum('nominal'),
            'totalDp' => $list->where('jenis_pembayaran', 'DP')->sum('nominal'),
            'totalLunas' => $list->where('jenis_pembayaran', 'Pelunasan')->sum('nominal'),
            'perHari' => $perHari, 'hariDalamBulan' => $ref->daysInMonth,
        ]);
    }
}
