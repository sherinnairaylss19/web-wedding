<?php

namespace App\Http\Controllers;

use App\Models\{Klien, Layanan, Pemesanan};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class PemesananController extends Controller
{
    /** UC-02: form pemesanan tanpa login. */
    public function create(Request $request)
    {
        return view('client.pemesanan', [
            'layanan' => Layanan::orderBy('kategori', 'desc')->orderBy('harga')->get(),
            'dipilih' => $request->integer('layanan') ?: null,
        ]);
    }

    public function store(Request $request)
    {
        $d = $request->validate([
            'nama' => 'required|string|min:3|max:100',
            'no_hp' => ['required', 'regex:/^\+?[0-9\s\-]{9,16}$/'],
            'alamat' => 'nullable|string|max:255',
            'layanan_id' => 'required|exists:layanan,id',
            'jumlah' => 'required|integer|min:1|max:100000',
            'tanggal_acara' => 'required|date|after_or_equal:today',
            'lokasi_acara' => 'required|string|min:3|max:255',
            'catatan' => 'nullable|string|max:1000',
        ], [
            'no_hp.regex' => 'Masukkan nomor WhatsApp yang valid.',
            'tanggal_acara.after_or_equal' => 'Tanggal acara tidak boleh di masa lalu.',
        ]);

        $p = DB::transaction(function () use ($d) {
            $klien = Klien::firstOrCreate(['no_hp' => trim($d['no_hp'])], ['nama' => $d['nama'], 'alamat' => $d['alamat'] ?? null]);
            $layanan = Layanan::findOrFail($d['layanan_id']);
            $subtotal = $layanan->harga * $d['jumlah'];
            $urut = Pemesanan::whereYear('created_at', now()->year)->lockForUpdate()->count() + 1;

            $p = Pemesanan::create([
                'kode' => sprintf('IKG-%d-%03d', now()->year, $urut),
                'klien_id' => $klien->id,
                'tanggal_acara' => $d['tanggal_acara'],
                'lokasi_acara' => $d['lokasi_acara'],
                'catatan' => $d['catatan'] ?? null,
                'total_biaya' => $subtotal,
            ]);
            $p->detail()->create(['layanan_id' => $layanan->id, 'jumlah' => $d['jumlah'], 'harga_satuan' => $layanan->harga, 'subtotal' => $subtotal]);
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

    /** UC-03: ringkasan + tombol konsultasi WhatsApp. */
    public function ringkasan(Pemesanan $pemesanan)
    {
        return view('client.ringkasan', ['p' => $pemesanan->load('klien', 'detail.layanan')]);
    }
}
