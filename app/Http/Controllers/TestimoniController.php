<?php

namespace App\Http\Controllers;

use App\Models\{Pemesanan, Testimoni};
use Illuminate\Http\Request;

class TestimoniController extends Controller
{
    /** UC-04: hanya testimoni berstatus Tampil. */
    public function index()
    {
        $tampil = Testimoni::tampil()->with('pemesanan.klien')->latest()->get();
        return view('client.testimoni', [
            'tampil' => $tampil,
            'rata' => round($tampil->avg('rating') ?? 0, 1),
            'sebaran' => collect([5, 4, 3, 2, 1])->mapWithKeys(fn($r) => [$r => $tampil->where('rating', $r)->count()]),
        ]);
    }

    /** UC-05: kirim testimoni memakai kode pemesanan; tampil setelah disetujui admin. */
    public function store(Request $request)
    {
        $d = $request->validate([
            'kode' => 'required|string|exists:pemesanan,kode',
            'rating' => 'required|integer|between:1,5',
            'isi' => 'required|string|min:10|max:1000',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], ['kode.exists' => 'Kode pemesanan tidak ditemukan.', 'isi.min' => 'Testimoni minimal 10 karakter.', 'foto.image' => 'File harus berupa gambar.', 'foto.mimes' => 'Format foto harus JPG, PNG, atau WEBP.', 'foto.max' => 'Ukuran foto maksimal 2 MB.', 'foto.uploaded' => 'Foto gagal diunggah. Pastikan ukurannya maksimal 2 MB.']);

        $p = Pemesanan::where('kode', strtoupper(trim($d['kode'])))->firstOrFail();
        if ($p->testimoni()->exists()) {
            return back()->withInput()->withErrors(['kode' => 'Testimoni untuk kode pemesanan ini sudah pernah dikirim.']);
        }
        $foto = $request->hasFile('foto') ? $request->file('foto')->store('testimoni', 'public') : null;
        $p->testimoni()->create(['rating' => $d['rating'], 'isi' => $d['isi'], 'foto' => $foto, 'status_tampil' => 'Menunggu']);

        return redirect()->route('testimoni')->with('ok', 'Terima kasih! Testimoni Anda akan ditampilkan setelah ditinjau admin.');
    }
}
