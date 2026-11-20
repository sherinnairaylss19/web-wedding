<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Galeri;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/** Kelola foto karya yang tampil di halaman Gallery publik: unggah (bisa banyak sekaligus), ubah, sembunyikan, hapus. */
class GaleriController extends Controller
{
    public function index()
    {
        return view('admin.galeri.index', ['daftar' => Galeri::urut()->get()]);
    }

    public function store(Request $request)
    {
        $d = $request->validate([
            'foto' => 'required|array|min:1|max:20',
            'foto.*' => 'image|mimes:jpg,jpeg,png,webp|max:4096',
            'judul' => 'nullable|string|max:100',
            'kategori' => ['required', Rule::in(['Wedding', 'Catering'])],
            'urutan' => 'nullable|integer|min:0|max:65535',
        ], [
            'foto.required' => 'Pilih minimal satu foto.',
            'foto.max' => 'Maksimal 20 foto sekali unggah.',
            'foto.*.image' => 'Setiap berkas harus berupa gambar.',
            'foto.*.mimes' => 'Format foto harus JPG, PNG, atau WEBP.',
            'foto.*.max' => 'Ukuran tiap foto maksimal 4 MB.',
            'foto.*.uploaded' => 'Foto gagal diunggah. Pastikan ukuran tiap foto maksimal 4 MB.',
        ]);

        foreach ($request->file('foto') as $file) {
            Galeri::create([
                'judul' => $d['judul'] ?? null, 'kategori' => $d['kategori'], 'urutan' => (int) ($d['urutan'] ?? 0),
                'foto' => $file->store('galeri', 'public'),
            ]);
        }
        return back()->with('ok', count($request->file('foto')) . ' foto ditambahkan ke galeri.');
    }

    public function update(Request $request, Galeri $galeri)
    {
        $d = $request->validate([
            'judul' => 'nullable|string|max:100',
            'kategori' => ['required', Rule::in(['Wedding', 'Catering'])],
            'urutan' => 'nullable|integer|min:0|max:65535',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ], [
            'foto.image' => 'Berkas harus berupa gambar.',
            'foto.mimes' => 'Format foto harus JPG, PNG, atau WEBP.',
            'foto.max' => 'Ukuran foto maksimal 4 MB.',
            'foto.uploaded' => 'Foto gagal diunggah. Pastikan ukurannya maksimal 4 MB.',
        ]);

        $data = ['judul' => $d['judul'] ?? null, 'kategori' => $d['kategori'], 'urutan' => (int) ($d['urutan'] ?? 0)];
        if ($request->hasFile('foto')) {
            $lama = $galeri->foto;
            $data['foto'] = $request->file('foto')->store('galeri', 'public');
            $galeri->update($data);
            Storage::disk('public')->delete($lama);
        } else {
            $galeri->update($data);
        }
        return back()->with('ok', 'Foto galeri diperbarui.');
    }

    /** Tampilkan / sembunyikan foto di Gallery publik tanpa menghapus berkasnya. */
    public function aktif(Galeri $galeri)
    {
        $galeri->update(['aktif' => ! $galeri->aktif]);
        return back()->with('ok', $galeri->aktif ? 'Foto ditampilkan di Gallery.' : 'Foto disembunyikan dari Gallery.');
    }

    public function destroy(Galeri $galeri)
    {
        Storage::disk('public')->delete($galeri->foto);
        $galeri->delete();
        return back()->with('ok', 'Foto dihapus dari galeri.');
    }
}
