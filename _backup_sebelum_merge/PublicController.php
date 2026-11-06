<?php

namespace App\Http\Controllers;

use App\Models\Layanan;

class PublicController extends Controller
{
    // UC-01: "Menampilkan katalog layanan" (data dari tabel layanan)
    public function index()
    {
        $paket = Layanan::orderBy('kategori')->orderBy('harga')->get()->map(fn (Layanan $l) => [
            'id'    => $l->id,
            'type'  => strtolower($l->kategori),
            'name'  => $l->nama,
            'price' => $l->harga,
            'unit'  => $l->satuan,
            'desc'  => $l->deskripsi,
            'img'   => $l->foto_url,
        ])->values();

        return view('ikes_gallery', compact('paket'));
    }
}
