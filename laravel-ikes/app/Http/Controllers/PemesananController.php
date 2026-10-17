<?php

namespace App\Http\Controllers;

use App\Models\{Klien, Layanan, Pemesanan};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class PemesananController extends Controller
{
    /** UC-02: form pemesanan tanpa login. */
    public function create(Request $request)
    {
        return view('pemesanan', [
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

        // URL bertanda tangan: ringkasan (berisi data pribadi) tidak bisa dibuka dengan menebak kode.
        return redirect(URL::signedRoute('pemesanan.ringkasan', ['pemesanan' => $p->kode]));
    }

    /** UC-03: ringkasan + tombol konsultasi WhatsApp. */
    public function ringkasan(Pemesanan $pemesanan)
    {
        return view('ringkasan', ['p' => $pemesanan->load('klien', 'detail.layanan')]);
    }
}
