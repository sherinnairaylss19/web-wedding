<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Layanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/** UC-07: tambah, ubah, hapus layanan. */
class LayananController extends Controller
{
    private function rules(): array
    {
        return [
            'nama' => 'required|string|max:150',
            'kategori' => ['required', Rule::in(['Wedding', 'Catering'])],
            'deskripsi' => 'required|string|max:1000',
            'harga' => 'required|integer|min:1',
            'satuan' => 'nullable|string|max:30',
            'label' => 'nullable|string|max:40',
            'unggulan' => 'nullable|boolean',
            'urutan' => 'nullable|integer|min:0|max:65535',
            'foto' => 'nullable|image|max:2048',
        ];
    }

    /** Checkbox yang tidak dicentang tidak terkirim sama sekali, jadi nilainya dibaca dari boolean(); urutan kosong = 0. */
    private function normalisasi(Request $request, array $d): array
    {
        $d['satuan'] = $d['satuan'] ?? '';
        $d['unggulan'] = $request->boolean('unggulan');
        $d['urutan'] = (int) ($d['urutan'] ?? 0);
        return $d;
    }

    public function index()
    {
        return view('admin.layanan.index', ['daftar' => Layanan::orderBy('kategori', 'desc')->orderBy('nama')->get()]);
    }

    public function store(Request $request)
    {
        $d = $this->normalisasi($request, $request->validate($this->rules()));
        if ($request->hasFile('foto')) $d['foto'] = $request->file('foto')->store('layanan', 'public');
        Layanan::create($d);
        return back()->with('ok', 'Layanan ditambahkan.');
    }

    public function update(Request $request, Layanan $layanan)
    {
        $d = $this->normalisasi($request, $request->validate($this->rules()));
        if ($request->hasFile('foto')) {
            if ($layanan->foto && !str_starts_with($layanan->foto, 'photo-') && !str_starts_with($layanan->foto, 'http')) Storage::disk('public')->delete($layanan->foto);
            $d['foto'] = $request->file('foto')->store('layanan', 'public');
        } else {
            unset($d['foto']);
        }
        $layanan->update($d);
        return back()->with('ok', 'Layanan diperbarui.');
    }

    /** Tampilkan / sembunyikan layanan di katalog publik tanpa menghapus riwayat pesanan. */
    public function aktif(Layanan $layanan)
    {
        $layanan->update(['aktif' => ! $layanan->aktif]);
        return back()->with('ok', $layanan->aktif
            ? "Layanan \"{$layanan->nama}\" diaktifkan dan tampil di katalog."
            : "Layanan \"{$layanan->nama}\" dinonaktifkan dan disembunyikan dari katalog.");
    }

    public function destroy(Layanan $layanan)
    {
        if (\App\Models\DetailPemesanan::where('layanan_id', $layanan->id)->exists()) {
            return back()->withErrors(['hapus' => 'Layanan sudah dipakai pada pemesanan sehingga tidak dapat dihapus. Gunakan tombol Nonaktifkan agar tidak tampil di katalog.']);
        }
        $layanan->delete();
        return back()->with('ok', 'Layanan dihapus.');
    }
}
