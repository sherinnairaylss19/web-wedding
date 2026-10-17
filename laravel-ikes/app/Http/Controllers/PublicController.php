<?php

namespace App\Http\Controllers;

use App\Models\{Layanan, Testimoni};

class PublicController extends Controller
{
    public function beranda()
    {
        return view('beranda', [
            'unggulan' => Layanan::orderByDesc('label')->orderBy('id')->take(4)->get(),
            'testimoni' => Testimoni::tampil()->with('pemesanan.klien')->latest()->take(3)->get(),
        ]);
    }

    public function katalog()
    {
        return view('katalog', ['layanan' => Layanan::orderBy('kategori', 'desc')->orderBy('harga')->get()]);
    }

    public function gallery()
    {
        // Gallery bersifat statis (tidak memerlukan tabel), sesuai Subbab 4.4.11.
        $foto = [
            ['photo-1465495976277-4387d4b0b4c6', 'Wedding', 'Dekorasi Pelaminan'], ['photo-1519741497674-611481863552', 'Wedding', 'Pelaminan Elegan'],
            ['photo-1511795409834-ef04bbd61622', 'Wedding', 'Akad Intimate'], ['photo-1464366400600-7168b8af9bc3', 'Wedding', 'Resepsi Gedung'],
            ['photo-1555244162-803834f70033', 'Catering', 'Prasmanan Reguler'], ['photo-1414235077428-338989a2e8c0', 'Catering', 'Prasmanan Gold'],
            ['photo-1504674900247-0877df9cc836', 'Catering', 'Nasi Box Premium'], ['photo-1555939594-58d7cb561ad1', 'Catering', 'Tumpeng & Snack Box'],
        ];
        return view('gallery', compact('foto'));
    }

    public function profil()
    {
        return view('profil');
    }
}
