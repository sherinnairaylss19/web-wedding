<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pemesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PemesananController extends Controller
{
    /** UC-09: daftar + pencarian + filter status. */
    public function index(Request $request)
    {
        $q = Pemesanan::with('klien', 'detail.layanan')->latest();
        if ($s = $request->query('status')) $q->where('status_pemesanan', $s);
        if ($cari = trim((string) $request->query('q'))) {
            $q->where(fn($w) => $w->where('kode', 'like', "%$cari%")->orWhereHas('klien', fn($k) => $k->where('nama', 'like', "%$cari%")));
        }
        return view('admin.pemesanan.index', ['daftar' => $q->paginate(15)->withQueryString()]);
    }

    public function show(Pemesanan $pemesanan)
    {
        return view('admin.pemesanan.show', ['p' => $pemesanan->load('klien', 'detail.layanan', 'pembayaran', 'reschedule.admin')]);
    }

    public function status(Request $request, Pemesanan $pemesanan)
    {
        $d = $request->validate(['status_pemesanan' => ['required', Rule::in(['Menunggu Konfirmasi', 'Dikonfirmasi', 'Selesai', 'Dibatalkan'])]]);
        $pemesanan->update($d);
        return back()->with('ok', 'Status pemesanan diperbarui.');
    }

    /** UC-10: catat DP / pelunasan, lalu hitung ulang status pembayaran. */
    public function bayar(Request $request, Pemesanan $pemesanan)
    {
        $d = $request->validate([
            'jenis_pembayaran' => ['required', Rule::in(['DP', 'Pelunasan'])],
            'metode_bayar' => ['required', Rule::in(['Tunai', 'Transfer'])],
            'nominal' => 'required|integer|min:1',
            'tanggal_bayar' => 'required|date',
        ]);
        $sisa = $pemesanan->sisa();
        if ($sisa <= 0) return back()->withErrors(['nominal' => 'Pemesanan ini sudah lunas.']);
        if ($d['nominal'] > $sisa) return back()->withInput()->withErrors(['nominal' => 'Nominal melebihi sisa tagihan (' . rupiah($sisa) . ').']);

        DB::transaction(function () use ($pemesanan, $d) {
            $pemesanan->pembayaran()->create($d);
            $pemesanan->refreshPembayaran();
        });
        return back()->with('ok', 'Pembayaran dicatat. Status: ' . $pemesanan->status_pembayaran . '.');
    }

    /** UC-11: ubah tanggal acara dan simpan riwayatnya. */
    public function reschedule(Request $request, Pemesanan $pemesanan)
    {
        $d = $request->validate([
            'tanggal_baru' => ['required', 'date', 'after_or_equal:today', Rule::notIn([$pemesanan->tanggal_acara->toDateString()])],
            'alasan' => 'required|string|max:255',
        ], ['tanggal_baru.not_in' => 'Tanggal baru harus berbeda dari tanggal saat ini.']);

        DB::transaction(function () use ($pemesanan, $d, $request) {
            $pemesanan->reschedule()->create([
                'admin_id' => $request->user()->id, 'tanggal_lama' => $pemesanan->tanggal_acara,
                'tanggal_baru' => $d['tanggal_baru'], 'alasan' => $d['alasan'],
            ]);
            $pemesanan->update(['tanggal_acara' => $d['tanggal_baru']]);
        });
        return back()->with('ok', 'Tanggal acara diubah dan riwayat tersimpan.');
    }

    /** UC-12: invoice digital (dibuat sekali, nomor tetap). */
    public function invoice(Pemesanan $pemesanan)
    {
        $inv = $pemesanan->invoice()->firstOrCreate([], [
            'nomor_invoice' => 'INV-' . substr($pemesanan->kode, 4), 'tanggal_terbit' => today(),
        ]);
        return view('admin.pemesanan.invoice', ['p' => $pemesanan->load('klien', 'detail.layanan', 'pembayaran'), 'inv' => $inv]);
    }
}
