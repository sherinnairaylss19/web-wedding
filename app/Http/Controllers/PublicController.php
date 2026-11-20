<?php

namespace App\Http\Controllers;

use App\Models\{Galeri, Layanan, Testimoni};

class PublicController extends Controller
{
    public function beranda()
    {
        return view('client.beranda', [
            'unggulan' => Layanan::aktif()->unggulan()->urut()->take(Layanan::MAKS_UNGGULAN)->get(),
            'testimoni' => Testimoni::tampil()->with('pemesanan.klien')->latest()->take(3)->get(),
        ]);
    }

    public function katalog()
    {
        return view('client.katalog', ['layanan' => Layanan::aktif()->orderBy('kategori', 'desc')->orderBy('harga')->get()]);
    }

    public function gallery()
    {
        // Foto karya Ike's Gallery yang diunggah admin (Admin\GaleriController). Tidak ada foto stok sebagai cadangan.
        return view('client.gallery', ['foto' => Galeri::aktif()->urut()->get()]);
    }

    public function profil()
    {
        return view('client.profil');
    }
}
