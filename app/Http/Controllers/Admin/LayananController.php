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
            'foto' => 'nullable|image|max:2048',
        ];
    }

    public function index()
    {
        return view('admin.layanan.index', ['daftar' => Layanan::orderBy('kategori', 'desc')->orderBy('nama')->get()]);
    }

    public function store(Request $request)
    {
        $d = $request->validate($this->rules());
        $d['satuan'] = $d['satuan'] ?? '';
        if ($request->hasFile('foto')) $d['foto'] = $request->file('foto')->store('layanan', 'public');
        Layanan::create($d);
        return back()->with('ok', 'Layanan ditambahkan.');
    }

    public function update(Request $request, Layanan $layanan)
    {
        $d = $request->validate($this->rules());
        $d['satuan'] = $d['satuan'] ?? '';
        if ($request->hasFile('foto')) {
            if ($layanan->foto && !str_starts_with($layanan->foto, 'photo-') && !str_starts_with($layanan->foto, 'http')) Storage::disk('public')->delete($layanan->foto);
            $d['foto'] = $request->file('foto')->store('layanan', 'public');
        } else {
            unset($d['foto']);
        }
        $layanan->update($d);
        return back()->with('ok', 'Layanan diperbarui.');
    }

    public function destroy(Layanan $layanan)
    {
        if ($layanan->id && \App\Models\DetailPemesanan::where('layanan_id', $layanan->id)->exists()) {
            return back()->withErrors(['hapus' => 'Layanan sudah dipakai pada pemesanan sehingga tidak dapat dihapus.']);
        }
        $layanan->delete();
        return back()->with('ok', 'Layanan dihapus.');
    }
}
