<?php

namespace App\Http\Controllers;

use App\Models\{Klien, Layanan, Pemesanan};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PemesananController extends Controller
{
    /** UC-02: form pemesanan tanpa login. */
    public function create(Request $request)
    {
        return view('client.pemesanan', [
            'layanan' => Layanan::aktif()->orderBy('kategori', 'desc')->orderBy('harga')->get(),
            'dipilih' => $request->integer('layanan') ?: null,
        ]);
    }

    public function store(Request $request)
    {
        $d = $request->validate([
            'nama' => 'required|string|min:3|max:100',
            'no_hp' => ['required', 'regex:/^\+?[0-9\s\-]{9,16}$/'],
            'alamat' => 'nullable|string|max:255',
            'items' => 'required|array|min:1|max:10',
            'items.*.layanan_id' => ['required', Rule::exists('layanan', 'id')->where('aktif', true)],
            'items.*.jumlah' => 'required|integer|min:1|max:100000',
            'tanggal_acara' => 'required|date|after_or_equal:today',
            'lokasi_acara' => 'required|string|min:3|max:255',
            'catatan' => 'nullable|string|max:1000',
        ], [
            'items.required' => 'Pilih minimal satu layanan.',
            'items.max' => 'Maksimal 10 jenis layanan dalam satu pemesanan.',
            'items.*.layanan_id.required' => 'Silakan pilih layanan pada setiap baris, atau hapus baris yang kosong.',
            'items.*.layanan_id.exists' => 'Layanan yang dipilih sudah tidak tersedia.',
            'items.*.jumlah.required' => 'Jumlah wajib diisi pada setiap layanan.',
            'items.*.jumlah.min' => 'Jumlah minimal 1.',
            'no_hp.regex' => 'Masukkan nomor WhatsApp yang valid.',
            'tanggal_acara.after_or_equal' => 'Tanggal acara tidak boleh di masa lalu.',
        ]);

        $p = DB::transaction(function () use ($d) {
            // Klien dicocokkan lewat kolom no_hp_normal (0812-xxx = +62 812 xxx; unique dan terindeks): satu query, dan
            // nomor yang sama dengan format berbeda tidak menjadi dua klien. Klien yang dihapus lunak dipulihkan.
            $klien = Klien::cariAtauBuat($d['no_hp'], $d['nama'], $d['alamat'] ?? null);
            // Baris dengan layanan yang sama digabung. Paket Wedding selalu berjumlah 1 (ditegakkan di server),
            // sedangkan Catering dijumlahkan per porsi/box.
            $layanan = Layanan::aktif()->whereIn('id', collect($d['items'])->pluck('layanan_id'))->get()->keyBy('id');
            $baris = collect($d['items'])->groupBy('layanan_id')->map(function ($rows, $id) use ($layanan) {
                $l = $layanan->get((int) $id);
                if (! $l) {
                    throw ValidationException::withMessages(['items' => 'Salah satu layanan yang dipilih sudah tidak tersedia.']);
                }
                return ['layanan' => $l, 'jumlah' => $l->kategori === 'Wedding' ? 1 : min(100000, (int) $rows->sum('jumlah'))];
            });
            $subtotal = (int) $baris->sum(fn ($b) => $b['layanan']->harga * $b['jumlah']);
            // Kode diambil dari penghitung per tahun (kunci baris di DB), bukan dari count(): aman untuk pemesanan
            // bersamaan dan untuk pesanan yang pernah dihapus. Dipanggil di dalam transaksi ini.
            $p = Pemesanan::create([
                'kode' => Pemesanan::buatKode(),
                'klien_id' => $klien->id,
                'tanggal_acara' => $d['tanggal_acara'],
                'lokasi_acara' => $d['lokasi_acara'],
                'catatan' => $d['catatan'] ?? null,
                'total_biaya' => $subtotal,
            ]);
            foreach ($baris as $b) {
                $p->detail()->create([
                    'layanan_id' => $b['layanan']->id, 'jumlah' => $b['jumlah'],
                    'harga_satuan' => $b['layanan']->harga, 'subtotal' => $b['layanan']->harga * $b['jumlah'],
                ]);
            }
            return $p;
        });

        $this->notifikasiOwner($p);

        // URL bertanda tangan: ringkasan (berisi data pribadi) tidak bisa dibuka dengan menebak kode.
        // Flash 'baru' membuat halaman ringkasan langsung membuka WhatsApp owner (hanya sekali).
        return redirect(URL::signedRoute('pemesanan.ringkasan', ['pemesanan' => $p->kode]))->with('baru', true);
    }

    /**
     * Kirim notifikasi WA otomatis ke owner lewat Fonnte (opsional).
     * Aktif hanya bila FONNTE_TOKEN diisi; kegagalan tidak boleh menggagalkan pemesanan.
     */
    private function notifikasiOwner(Pemesanan $p): void
    {
        $token = config('ikes.fonnte_token');
        if (! $token) {
            return;
        }

        try {
            $p->loadMissing('klien', 'detail.layanan');
            $layanan = $p->detail->map(fn ($d) => $d->layanan->nama . ' x' . $d->jumlah)->implode(', ');
            $pesan = "*Pemesanan Baru - Ike's Gallery*\n\n"
                . "Kode: {$p->kode}\nNama: {$p->klien->nama}\nWA: {$p->klien->no_hp}\n"
                . "Layanan: {$layanan}\nTanggal Acara: " . tgl_long($p->tanggal_acara) . "\n"
                . "Lokasi: {$p->lokasi_acara}\nEstimasi: " . rupiah($p->total_biaya) . "\n"
                . 'Catatan: ' . ($p->catatan ?: '-');

            Http::timeout(5)
                ->withHeaders(['Authorization' => $token])
                ->asForm()
                ->post('https://api.fonnte.com/send', [
                    'target' => config('ikes.wa_number'),
                    'message' => $pesan,
                    'countryCode' => '62',
                ]);
        } catch (\Throwable $e) {
            Log::warning('Notifikasi WA owner gagal: ' . $e->getMessage());
        }
    }

    /** Form "Cek Pesanan": klien melacak status memakai kode pemesanan + nomor WhatsApp yang didaftarkan. */
    public function cek()
    {
        return view('client.cek');
    }

    public function cekProses(Request $request)
    {
        $d = $request->validate([
            'kode' => 'required|string|max:30',
            'no_hp' => ['required', 'regex:/^\+?[0-9\s\-]{9,16}$/'],
        ], ['no_hp.regex' => 'Masukkan nomor WhatsApp yang valid.']);

        $p = Pemesanan::with('klien')->where('kode', strtoupper(trim($d['kode'])))->first();

        // Satu pesan generik untuk kode salah maupun nomor salah, agar kode pesanan tidak bisa ditebak lewat form ini.
        if (! $p || normalisasi_hp($p->klien->no_hp) !== normalisasi_hp($d['no_hp'])) {
            return back()->withInput($request->only('kode', 'no_hp'))
                ->withErrors(['kode' => 'Kode pemesanan atau nomor WhatsApp tidak cocok. Periksa kembali data Anda.']);
        }

        // Tautan bertanda tangan berlaku 30 menit: cukup untuk melihat status, tidak bisa dibagikan permanen.
        return redirect(URL::temporarySignedRoute('pemesanan.status', now()->addMinutes(30), ['pemesanan' => $p->kode]));
    }

    public function status(Pemesanan $pemesanan)
    {
        return view('client.status', ['p' => $pemesanan->load('klien', 'detail.layanan', 'pembayaran', 'reschedule', 'testimoni', 'pengembalian')]);
    }

    /** UC-03: ringkasan + tombol konsultasi WhatsApp. */
    public function ringkasan(Pemesanan $pemesanan)
    {
        return view('client.ringkasan', ['p' => $pemesanan->load('klien', 'detail.layanan')]);
    }
}
