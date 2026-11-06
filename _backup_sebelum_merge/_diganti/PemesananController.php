<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePemesananRequest;
use App\Models\DetailPemesanan;
use App\Models\Klien;
use App\Models\Layanan;
use App\Models\Pemesanan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class PemesananController extends Controller
{
    // UC-02: "Data valid?" Ya -> "Menyimpan data pemesanan (status: Menunggu Konfirmasi)"
    // (validasi gagal ditangani StorePemesananRequest: kembali ke form + pesan error)
    public function store(StorePemesananRequest $request): RedirectResponse
    {
        $pemesanan = DB::transaction(function () use ($request) {
            $layanan = Layanan::findOrFail($request->integer('layanan_id'));

            // Wedding = 1 paket; Catering = jumlah porsi/box
            $jumlah = $layanan->kategori === 'Catering' ? max(1, $request->integer('jumlah', 1)) : 1;
            $subtotal = $layanan->harga * $jumlah;

            // Klien yang sama (nomor WA sama) tidak dibuat ganda
            $klien = Klien::updateOrCreate(
                ['no_whatsapp' => $request->input('no_whatsapp')],
                ['nama' => $request->input('nama')]
            );

            $pemesanan = Pemesanan::create([
                'kode_pemesanan'    => Pemesanan::buatKode(),
                'klien_id'          => $klien->id,
                'tanggal_acara'     => $request->input('tanggal_acara'),
                'lokasi_acara'      => $request->input('lokasi_acara'),
                'catatan'           => $request->input('catatan'),
                'total_biaya'       => $subtotal,
                'status_pemesanan'  => Pemesanan::MENUNGGU,
                'status_pembayaran' => 'Belum Bayar',
            ]);

            DetailPemesanan::create([
                'pemesanan_id' => $pemesanan->id,
                'layanan_id'   => $layanan->id,
                'jumlah'       => $jumlah,
                'harga_satuan' => $layanan->harga, // snapshot harga
                'subtotal'     => $subtotal,
            ]);

            return $pemesanan;
        });

        return redirect()->route('pemesanan.show', $pemesanan);
    }

    // "Menampilkan ringkasan dan kode pemesanan" (+ tombol WhatsApp untuk UC-03)
    public function show(Pemesanan $pemesanan)
    {
        $pemesanan->load(['klien', 'detail.layanan']);

        $item = $pemesanan->detail->first();
        $pesan = "Halo Ike's Gallery 👋\n"
            ."Saya sudah mengisi pemesanan di website.\n\n"
            ."*Kode Pemesanan*: {$pemesanan->kode_pemesanan}\n"
            ."*Nama*: {$pemesanan->klien->nama}\n"
            .'*Layanan*: '.($item?->layanan->nama ?? '-').($item && $item->jumlah > 1 ? " ({$item->jumlah})" : '')."\n"
            .'*Tanggal Acara*: '.$pemesanan->tanggal_acara->translatedFormat('d F Y')."\n\n"
            .'Mohon informasinya. Terima kasih 🙏';

        $waUrl = 'https://wa.me/'.config('ikes.wa_number').'?text='.rawurlencode($pesan);

        return view('pemesanan.ringkasan', compact('pemesanan', 'waUrl'));
    }
}
