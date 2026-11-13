<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Pembayaran, Pemesanan};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PemesananController extends Controller
{
    /** UC-09: daftar + pencarian + filter status. */
    public function index(Request $request)
    {
        $q = Pemesanan::with('klien', 'detail.layanan')->latest();
        if ($s = $request->query('status')) $q->where('status_pemesanan', $s);
        if (in_array($bp = $request->query('pembayaran'), ['Belum Bayar', 'DP', 'Lunas'], true)) $q->where('status_pembayaran', $bp);
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
            'bukti_transaksi' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'catatan' => 'nullable|string|max:255',
        ], $this->pesanBukti());
        $sisa = $pemesanan->sisa();
        if ($sisa <= 0) return back()->withErrors(['nominal' => 'Pemesanan ini sudah lunas.']);
        if ($d['nominal'] > $sisa) return back()->withInput()->withErrors(['nominal' => 'Nominal melebihi sisa tagihan (' . rupiah($sisa) . ').']);

        $d['bukti_transaksi'] = $request->hasFile('bukti_transaksi') ? $request->file('bukti_transaksi')->store('bukti-pembayaran', 'local') : null;

        DB::transaction(function () use ($pemesanan, $d) {
            $pemesanan->pembayaran()->create($d);
            $pemesanan->refreshPembayaran();
        });
        return back()->with('ok', 'Pembayaran dicatat. Status: ' . $pemesanan->status_pembayaran . '.');
    }

    /** Koreksi pembayaran (jenis, metode, nominal, tanggal, bukti, catatan); status pembayaran dihitung ulang. */
    public function ubahPembayaran(Request $request, Pemesanan $pemesanan, Pembayaran $pembayaran)
    {
        abort_unless((int) $pembayaran->pemesanan_id === (int) $pemesanan->id, 404);
        $d = $request->validate([
            'jenis_pembayaran' => ['required', Rule::in(['DP', 'Pelunasan'])],
            'metode_bayar' => ['required', Rule::in(['Tunai', 'Transfer'])],
            'nominal' => 'required|integer|min:1',
            'tanggal_bayar' => 'required|date',
            'bukti_transaksi' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'catatan' => 'nullable|string|max:255',
            'hapus_bukti' => 'nullable|boolean',
        ], $this->pesanBukti());

        // Batas nominal = sisa tagihan jika pembayaran ini diabaikan.
        $maks = max(0, (int) $pemesanan->total_biaya - ($pemesanan->terbayar() - (int) $pembayaran->nominal));
        if ((int) $d['nominal'] > $maks) {
            return back()->withInput()->with('edit_id', $pembayaran->id)
                ->withErrors(['nominal' => 'Nominal melebihi sisa tagihan (' . rupiah($maks) . ').']);
        }

        $bukti = $pembayaran->bukti_transaksi;
        if ($request->hasFile('bukti_transaksi')) {
            $baru = $request->file('bukti_transaksi')->store('bukti-pembayaran', 'local');
            $this->hapusFile($bukti);
            $bukti = $baru;
        } elseif ($request->boolean('hapus_bukti') && $bukti) {
            $this->hapusFile($bukti);
            $bukti = null;
        }

        DB::transaction(function () use ($pemesanan, $pembayaran, $d, $bukti) {
            $pembayaran->update([
                'jenis_pembayaran' => $d['jenis_pembayaran'], 'metode_bayar' => $d['metode_bayar'],
                'nominal' => $d['nominal'], 'tanggal_bayar' => $d['tanggal_bayar'],
                'catatan' => $d['catatan'] ?? null, 'bukti_transaksi' => $bukti,
            ]);
            $pemesanan->refreshPembayaran();
        });
        return back()->with('ok', 'Pembayaran diperbarui. Status: ' . $pemesanan->status_pembayaran . '.');
    }

    /** Hapus catatan pembayaran yang salah input (beserta fotonya); status dihitung ulang. */
    public function hapusPembayaran(Pemesanan $pemesanan, Pembayaran $pembayaran)
    {
        abort_unless((int) $pembayaran->pemesanan_id === (int) $pemesanan->id, 404);
        $this->hapusFile($pembayaran->bukti_transaksi);
        DB::transaction(function () use ($pemesanan, $pembayaran) {
            $pembayaran->delete();
            $pemesanan->refreshPembayaran();
        });
        return back()->with('ok', 'Pembayaran dihapus. Status: ' . $pemesanan->status_pembayaran . '.');
    }

    /** Tampilkan foto bukti (hanya admin login; file disimpan privat, tidak punya URL publik). */
    public function bukti(Pembayaran $pembayaran)
    {
        $path = $pembayaran->bukti_transaksi;
        abort_unless($path, 404);
        // Disk 'public' hanya sebagai cadangan untuk file lama sebelum bukti dipindah ke penyimpanan privat.
        $disk = Storage::disk('local')->exists($path) ? 'local' : (Storage::disk('public')->exists($path) ? 'public' : null);
        abort_unless($disk, 404);
        return Storage::disk($disk)->response($path, null, [
            'Cache-Control' => 'private, max-age=0, must-revalidate',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /** Hapus file bukti dari disk privat (dan disk publik untuk file lama). */
    private function hapusFile(?string $path): void
    {
        if (! $path) return;
        Storage::disk('local')->delete($path);
        Storage::disk('public')->delete($path);
    }

    private function pesanBukti(): array
    {
        return [
            'bukti_transaksi.image' => 'Bukti transaksi harus berupa gambar.',
            'bukti_transaksi.mimes' => 'Format bukti harus JPG, PNG, atau WEBP.',
            'bukti_transaksi.max' => 'Ukuran bukti maksimal 4 MB.',
            'bukti_transaksi.uploaded' => 'Bukti gagal diunggah. Pastikan ukurannya maksimal 4 MB.',
        ];
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
