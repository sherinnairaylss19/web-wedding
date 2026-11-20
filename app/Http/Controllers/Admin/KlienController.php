<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Klien;
use Illuminate\Http\Request;

/** UC-08: lihat dan perbarui data klien. */
class KlienController extends Controller
{
    public function index(Request $request)
    {
        $q = Klien::withCount('pemesanan')->orderBy('nama');
        if ($cari = trim((string) $request->query('q'))) $q->where('nama', 'like', "%$cari%");
        return view('admin.klien.index', ['daftar' => $q->paginate(20)->withQueryString()]);
    }

    public function update(Request $request, Klien $klien)
    {
        $d = $request->validate([
            'nama' => 'required|string|min:3|max:100',
            'no_hp' => ['required', 'string', 'max:30', function ($attr, $value, $gagal) use ($klien) {
                $norm = normalisasi_hp($value);
                if (strlen($norm) < 9) {
                    $gagal('Masukkan nomor WhatsApp yang valid.');
                } elseif (Klien::withTrashed()->where('no_hp_normal', $norm)->where('id', '!=', $klien->id)->exists()) {
                    // Dibandingkan dalam bentuk ternormalkan: 0812-xxx dan +62 812 xxx dianggap nomor yang sama.
                    $gagal('Nomor WhatsApp ini sudah dipakai klien lain.');
                }
            }],
            'alamat' => 'nullable|string|max:255',
        ]);
        $klien->update($d);
        return back()->with('ok', 'Data klien diperbarui.');
    }
}
