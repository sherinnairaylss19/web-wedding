<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimoni;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** UC-13: setujui / sembunyikan testimoni. */
class TestimoniController extends Controller
{
    public function index(Request $request)
    {
        $s = $request->query('status');
        $q = Testimoni::with('pemesanan.klien')->latest();
        if (in_array($s, ['Menunggu', 'Tampil', 'Disembunyikan'])) $q->where('status_tampil', $s);
        return view('admin.testimoni.index', [
            'daftar' => $q->get(), 'aktif' => $s ?: 'Semua',
            'hitung' => Testimoni::selectRaw('status_tampil, count(*) n')->groupBy('status_tampil')->pluck('n', 'status_tampil'),
        ]);
    }

    public function update(Request $request, Testimoni $testimoni)
    {
        $d = $request->validate(['status_tampil' => ['required', Rule::in(['Menunggu', 'Tampil', 'Disembunyikan'])]]);
        $testimoni->update($d);
        return back()->with('ok', 'Status testimoni diperbarui.');
    }
}
