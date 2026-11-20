<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Invoice, Layanan, Pembayaran, Pemesanan, RiwayatPembayaran};
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PemesananController extends Controller
{
    /** UC-09: daftar + pencarian + filter status. */
    public function index(Request $request)
    {
        $q = Pemesanan::with('klien', 'detail.layanan')->latest();
        if ($s = $request->query('status')) $q->where('status_pemesanan', $s);
        if (in_array($bp = $request->query('pembayaran'), ['Belum Bayar', 'DP', 'Lunas'], true)) $q->where('status_pembayaran', $bp);
        if ($request->query('tagihan') === 'terlambat') $q->jatuhTempo();
        if ($cari = trim((string) $request->query('q'))) {
            $q->where(fn($w) => $w->where('kode', 'like', "%$cari%")->orWhereHas('klien', fn($k) => $k->where('nama', 'like', "%$cari%")));
        }
        return view('admin.pemesanan.index', ['daftar' => $q->paginate(15)->withQueryString()]);
    }

    public function show(Pemesanan $pemesanan)
    {
        return view('admin.pemesanan.show', [
            'p' => $pemesanan->load('klien', 'detail.layanan', 'pembayaran.admin', 'riwayatPembayaran.admin', 'reschedule.admin', 'riwayatRincian.admin', 'pengembalian.admin'),
            'layananAktif' => Layanan::aktif()->orderBy('kategori', 'desc')->orderBy('nama')->get(),
        ]);
    }

    public function status(Request $request, Pemesanan $pemesanan)
    {
        $d = $request->validate([
            'status_pemesanan' => ['required', Rule::in(['Menunggu Konfirmasi', 'Dikonfirmasi', 'Selesai', 'Dibatalkan'])],
            'dana_batal' => ['nullable', Rule::in([Pemesanan::DANA_HANGUS, Pemesanan::DANA_DIKELUARKAN])],
        ]);
        $batal = $d['status_pemesanan'] === 'Dibatalkan';
        $terbayar = $pemesanan->terbayar();

        // Pesanan batal yang sudah menerima pembayaran: admin wajib memilih apakah dana hangus (tetap pemasukan) atau dikeluarkan.
        if ($batal && $terbayar > 0 && empty($d['dana_batal'])) {
            return back()->withErrors(['dana_batal' => 'Pilih perlakuan dana ' . rupiah($terbayar) . ' untuk pesanan yang dibatalkan: DP hangus atau dikembalikan/dikeluarkan dari pemasukan.']);
        }

        $pemesanan->update([
            'status_pemesanan' => $d['status_pemesanan'],
            // Hanya bermakna untuk pesanan Dibatalkan; status lain mengembalikannya ke NULL (pembayaran dihitung seperti biasa).
            'dana_batal' => $batal ? ($d['dana_batal'] ?? Pemesanan::DANA_DIKELUARKAN) : null,
        ]);

        $pesan = 'Status pemesanan diperbarui.';
        if ($batal && $terbayar > 0) {
            $pesan .= $pemesanan->dana_batal === Pemesanan::DANA_HANGUS
                ? ' Dana ' . rupiah($terbayar) . ' tetap dicatat sebagai pemasukan (DP hangus).'
                : ' Dana ' . rupiah($terbayar) . ' dikeluarkan dari pemasukan.';
        }
        return back()->with('ok', $pesan);
    }

    /** UC-10: catat DP / pelunasan, lalu hitung ulang status pembayaran. */
    public function bayar(Request $request, Pemesanan $pemesanan)
    {
        if ($pemesanan->status_pemesanan === 'Dibatalkan') {
            return back()->withErrors(['pembayaran' => 'Pesanan ini sudah dibatalkan sehingga tidak dapat menerima pembayaran baru.']);
        }
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

        $d['admin_id'] = $request->user()->id;

        DB::transaction(function () use ($request, $pemesanan, $d) {
            $baru = $pemesanan->pembayaran()->create($d);
            $pemesanan->refreshPembayaran();
            $this->catat($request, $baru, RiwayatPembayaran::DICATAT, null, $this->potret($baru), null);
        });
        return back()->with('ok', 'Pembayaran dicatat. Status: ' . $pemesanan->status_pembayaran . '.'
            . ($pemesanan->wasChanged('status_pemesanan') ? ' Pesanan otomatis dikonfirmasi karena DP sudah terpenuhi.' : ''));
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
            'alasan_pembayaran' => 'required|string|max:255',
        ], $this->pesanBukti() + ['alasan_pembayaran.required' => 'Alasan koreksi pembayaran wajib diisi.']);

        // Batas nominal = sisa tagihan jika pembayaran ini diabaikan.
        $maks = max(0, (int) $pemesanan->total_biaya - ($pemesanan->terbayar() - (int) $pembayaran->nominal));
        if ((int) $d['nominal'] > $maks) {
            return back()->withInput()->with('edit_id', $pembayaran->id)
                ->withErrors(['nominal' => 'Nominal melebihi sisa tagihan (' . rupiah($maks) . ').']);
        }

        // Total pembayaran tidak boleh turun di bawah dana yang sudah dikembalikan ke klien.
        $terbayarBaru = $pemesanan->terbayar() - (int) $pembayaran->nominal + (int) $d['nominal'];
        if ($terbayarBaru < $pemesanan->dikembalikan()) {
            return back()->withInput()->with('edit_id', $pembayaran->id)
                ->withErrors(['nominal' => 'Total pembayaran tidak boleh lebih kecil dari dana yang sudah dikembalikan (' . rupiah($pemesanan->dikembalikan()) . ').']);
        }

        // File bukti lama sengaja TIDAK dihapus dari disk: path-nya tersimpan di riwayat_pembayaran sebagai jejak audit.
        $bukti = $pembayaran->bukti_transaksi;
        if ($request->hasFile('bukti_transaksi')) {
            $bukti = $request->file('bukti_transaksi')->store('bukti-pembayaran', 'local');
        } elseif ($request->boolean('hapus_bukti') && $bukti) {
            $bukti = null;
        }

        DB::transaction(function () use ($request, $pemesanan, $pembayaran, $d, $bukti) {
            $lama = $this->potret($pembayaran);
            $pembayaran->update([
                'jenis_pembayaran' => $d['jenis_pembayaran'], 'metode_bayar' => $d['metode_bayar'],
                'nominal' => $d['nominal'], 'tanggal_bayar' => $d['tanggal_bayar'],
                'catatan' => $d['catatan'] ?? null, 'bukti_transaksi' => $bukti,
            ]);
            $pemesanan->refreshPembayaran();
            $this->catat($request, $pembayaran, RiwayatPembayaran::DIUBAH, $lama, $this->potret($pembayaran->refresh()), $d['alasan_pembayaran']);
        });
        return back()->with('ok', 'Pembayaran diperbarui. Status: ' . $pemesanan->status_pembayaran . '.');
    }

    /** Hapus lunak catatan pembayaran yang salah input (alasan wajib, tercatat di riwayat_pembayaran); status dihitung ulang. */
    public function hapusPembayaran(Request $request, Pemesanan $pemesanan, Pembayaran $pembayaran)
    {
        abort_unless((int) $pembayaran->pemesanan_id === (int) $pemesanan->id, 404);
        $d = $request->validate(['alasan_hapus' => 'required|string|max:255'], ['alasan_hapus.required' => 'Alasan penghapusan pembayaran wajib diisi.']);
        if ($pemesanan->terbayar() - (int) $pembayaran->nominal < $pemesanan->dikembalikan()) {
            return back()->withErrors(['pembayaran' => 'Pembayaran ini tidak dapat dihapus karena dananya sudah (sebagian) dikembalikan ke klien.']);
        }
        // Soft delete: barisnya dan file buktinya dipertahankan sebagai jejak audit; nominalnya tidak lagi dihitung.
        DB::transaction(function () use ($request, $pemesanan, $pembayaran, $d) {
            $lama = $this->potret($pembayaran);
            $pembayaran->delete();
            $pemesanan->refreshPembayaran();
            $this->catat($request, $pembayaran, RiwayatPembayaran::DIHAPUS, $lama, null, $d['alasan_hapus']);
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

    /** Potret kolom pembayaran yang relevan untuk disimpan di riwayat_pembayaran. */
    private function potret(Pembayaran $b): array
    {
        return [
            'jenis_pembayaran' => $b->jenis_pembayaran, 'metode_bayar' => $b->metode_bayar,
            'nominal' => (int) $b->nominal, 'tanggal_bayar' => $b->tanggal_bayar->toDateString(),
            'bukti_transaksi' => $b->bukti_transaksi, 'catatan' => $b->catatan,
        ];
    }

    /** Tulis satu baris jejak audit pembayaran (dipanggil di dalam transaksi yang sama dengan perubahannya). */
    private function catat(Request $request, Pembayaran $b, string $aksi, ?array $lama, ?array $baru, ?string $alasan): void
    {
        RiwayatPembayaran::create([
            'pembayaran_id' => $b->id, 'pemesanan_id' => $b->pemesanan_id, 'admin_id' => $request->user()->id,
            'aksi' => $aksi, 'data_lama' => $lama, 'data_baru' => $baru, 'alasan' => $alasan,
        ]);
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

    /**
     * Ubah rincian pesanan: jumlah & harga tiap layanan, hapus layanan, tambah layanan, dan diskon.
     * Total baru = jumlah subtotal - diskon. Total tidak boleh lebih kecil dari yang sudah dibayar;
     * status pembayaran dihitung ulang dan perubahan dicatat di riwayat_rincian.
     */
    public function ubahRincian(Request $request, Pemesanan $pemesanan)
    {
        if (in_array($pemesanan->status_pemesanan, ['Selesai', 'Dibatalkan'], true)) {
            return back()->withErrors(['rincian' => 'Rincian pesanan berstatus ' . $pemesanan->status_pemesanan . ' tidak dapat diubah. Ubah status pemesanan lebih dulu bila perlu koreksi.']);
        }

        $maks = 100000000000;
        $d = $request->validate([
            'item' => 'required|array|min:1',
            'item.*.jumlah' => 'required|integer|min:1|max:100000',
            'item.*.harga_satuan' => "required|integer|min:0|max:$maks",
            'item.*.hapus' => 'nullable|boolean',
            'baru.layanan_id' => ['nullable', Rule::exists('layanan', 'id')->where('aktif', true)],
            'baru.jumlah' => 'nullable|integer|min:1|max:100000',
            'baru.harga_satuan' => "nullable|integer|min:0|max:$maks",
            'diskon' => "nullable|integer|min:0|max:$maks",
            'alasan_rincian' => 'required|string|max:255',
        ], [
            'alasan_rincian.required' => 'Alasan perubahan rincian wajib diisi.',
            'baru.layanan_id.exists' => 'Layanan yang dipilih untuk ditambahkan tidak tersedia.',
        ]);

        DB::transaction(function () use ($request, $pemesanan, $d) {
            // Kunci baris pemesanan agar perubahan rincian dan pencatatan pembayaran tidak saling menimpa.
            $p = Pemesanan::whereKey($pemesanan->id)->lockForUpdate()->firstOrFail();
            $detail = $p->detail()->get()->keyBy('id');
            $totalLama = (int) $p->total_biaya;

            foreach (array_keys($d['item']) as $id) {
                abort_unless($detail->has((int) $id), 404);
            }

            // Baris yang tidak ikut terkirim (mis. ditambah admin lain saat form terbuka) dibiarkan apa adanya.
            foreach ($detail as $id => $row) {
                $in = $d['item'][$id] ?? null;
                if (! $in) continue;
                if (! empty($in['hapus'])) { $row->delete(); continue; }
                $row->update([
                    'jumlah' => (int) $in['jumlah'], 'harga_satuan' => (int) $in['harga_satuan'],
                    'subtotal' => (int) $in['jumlah'] * (int) $in['harga_satuan'],
                ]);
            }

            $baru = $d['baru'] ?? [];
            if (! empty($baru['layanan_id'])) {
                $layanan = Layanan::findOrFail($baru['layanan_id']);
                $jumlah = (int) ($baru['jumlah'] ?? 1);
                $harga = isset($baru['harga_satuan']) && $baru['harga_satuan'] !== '' ? (int) $baru['harga_satuan'] : (int) $layanan->harga;
                $p->detail()->create(['layanan_id' => $layanan->id, 'jumlah' => $jumlah, 'harga_satuan' => $harga, 'subtotal' => $jumlah * $harga]);
            }

            if (! $p->detail()->exists()) {
                throw ValidationException::withMessages(['rincian' => 'Pesanan harus memiliki minimal satu layanan.']);
            }

            $subtotal = (int) $p->detail()->sum('subtotal');
            $diskon = (int) ($d['diskon'] ?? 0);
            if ($diskon > $subtotal) {
                throw ValidationException::withMessages(['diskon' => 'Diskon (' . rupiah($diskon) . ') tidak boleh melebihi subtotal (' . rupiah($subtotal) . ').']);
            }

            $total = $subtotal - $diskon;
            $terbayar = $p->terbayar();
            if ($total < $terbayar) {
                throw ValidationException::withMessages(['rincian' => 'Total baru (' . rupiah($total) . ') lebih kecil dari pembayaran yang sudah tercatat (' . rupiah($terbayar) . '). Koreksi atau hapus pembayaran terlebih dahulu.']);
            }

            $p->update(['diskon' => $diskon, 'total_biaya' => $total]);
            $p->refreshPembayaran();
            $p->riwayatRincian()->create([
                'admin_id' => $request->user()->id, 'total_lama' => $totalLama, 'total_baru' => $total,
                'diskon' => $diskon, 'alasan' => $d['alasan_rincian'],
            ]);
        });

        return back()->with('ok', 'Rincian pesanan diperbarui dan riwayat tersimpan.');
    }

    /**
     * Catat pengembalian dana untuk pesanan yang dibatalkan. Nominal dibatasi sebesar dana masuk yang
     * belum dikembalikan. Pengembalian tidak menghapus riwayat pembayaran; laporan menghitung pemasukan bersih.
     */
    public function pengembalian(Request $request, Pemesanan $pemesanan)
    {
        if ($pemesanan->status_pemesanan !== 'Dibatalkan') {
            return back()->withErrors(['pengembalian' => 'Pengembalian dana hanya dapat dicatat untuk pesanan yang berstatus Dibatalkan.']);
        }
        if ($pemesanan->dana_batal !== Pemesanan::DANA_HANGUS) {
            return back()->withErrors(['pengembalian' => 'Dana pesanan ini sudah dikeluarkan dari pemasukan, jadi tidak perlu dicatat sebagai pengembalian. Ubah perlakuan dana menjadi "DP hangus" bila sebagian dana tetap menjadi pemasukan.']);
        }

        $d = $request->validate([
            'nominal_refund' => 'required|integer|min:1',
            'metode_refund' => ['required', Rule::in(['Tunai', 'Transfer'])],
            'tanggal_refund' => 'required|date|before_or_equal:today',
            'catatan_refund' => 'nullable|string|max:255',
        ], ['tanggal_refund.before_or_equal' => 'Tanggal pengembalian tidak boleh di masa depan.']);

        DB::transaction(function () use ($request, $pemesanan, $d) {
            $p = Pemesanan::whereKey($pemesanan->id)->lockForUpdate()->firstOrFail();
            $saldo = $p->saldoRefund();
            if ((int) $d['nominal_refund'] > $saldo) {
                throw ValidationException::withMessages(['nominal_refund' => 'Nominal melebihi dana yang dapat dikembalikan (' . rupiah($saldo) . ').']);
            }
            $p->pengembalian()->create([
                'admin_id' => $request->user()->id, 'nominal' => $d['nominal_refund'], 'metode' => $d['metode_refund'],
                'tanggal_pengembalian' => $d['tanggal_refund'], 'catatan' => $d['catatan_refund'] ?? null,
            ]);
        });

        return back()->with('ok', 'Pengembalian dana dicatat.');
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

    /**
     * UC-12: invoice digital. Isi tagihan (klien, rincian layanan, diskon, total) dibekukan saat invoice diterbitkan,
     * jadi mengubah rincian, harga, atau nama layanan kemudian tidak mengubah invoice yang sudah diterima klien.
     * Perubahan tagihan ditangani lewat revisi (revisiInvoice). Daftar pembayaran tetap dibaca terbaru.
     */
    public function invoice(Request $request, Pemesanan $pemesanan)
    {
        $pemesanan->load('klien', 'detail.layanan', 'pembayaran');

        $semua = $pemesanan->invoices()->get();
        if ($semua->isEmpty()) {
            try {
                Invoice::create([
                    'pemesanan_id' => $pemesanan->id, 'revisi' => 0, 'nomor_invoice' => Invoice::nomorUntuk($pemesanan->kode, 0),
                    'tanggal_terbit' => today(), 'rincian' => Invoice::potretTagihan($pemesanan),
                ]);
            } catch (UniqueConstraintViolationException) {
                // Admin lain membuka invoice yang sama pada saat bersamaan: pakai yang sudah terbit.
            }
            $semua = $pemesanan->invoices()->get();
        }

        $terbaru = $semua->last();
        $inv = $request->query('revisi') !== null
            ? ($semua->firstWhere('revisi', (int) $request->query('revisi')) ?? abort(404))
            : $terbaru;

        return view('admin.pemesanan.invoice', [
            'p' => $pemesanan, 'inv' => $inv, 'semua' => $semua, 'terbaru' => $terbaru,
            // Invoice terbaru sudah tidak sama dengan tagihan pesanan saat ini: tawarkan penerbitan revisi.
            'usang' => $inv->is($terbaru) && $inv->berbedaDari(Invoice::potretTagihan($pemesanan)),
        ]);
    }

    /** Terbitkan revisi invoice (nomor -R1, -R2, ...) setelah tagihan pesanan berubah. Invoice lama tetap tersimpan. */
    public function revisiInvoice(Request $request, Pemesanan $pemesanan)
    {
        $d = $request->validate(['alasan_revisi' => 'required|string|max:255'], ['alasan_revisi.required' => 'Alasan revisi invoice wajib diisi.']);

        DB::transaction(function () use ($pemesanan, $d) {
            // Kunci baris pesanan agar dua admin yang menekan tombol bersamaan tidak menerbitkan revisi ganda.
            $p = Pemesanan::whereKey($pemesanan->id)->lockForUpdate()->firstOrFail()->load('klien', 'detail.layanan');
            $terbaru = Invoice::where('pemesanan_id', $p->id)->orderByDesc('revisi')->first();
            abort_unless($terbaru, 404);

            $skrg = Invoice::potretTagihan($p);
            if (! $terbaru->berbedaDari($skrg)) {
                throw ValidationException::withMessages(['invoice' => 'Tagihan tidak berubah sejak invoice terakhir diterbitkan, jadi revisi tidak diperlukan.']);
            }

            $revisi = $terbaru->revisi + 1;
            Invoice::create([
                'pemesanan_id' => $p->id, 'revisi' => $revisi, 'nomor_invoice' => Invoice::nomorUntuk($p->kode, $revisi),
                'tanggal_terbit' => today(), 'rincian' => $skrg, 'alasan' => $d['alasan_revisi'],
            ]);
        });

        return redirect()->route('admin.pemesanan.invoice', $pemesanan)->with('ok', 'Revisi invoice diterbitkan.');
    }
}
