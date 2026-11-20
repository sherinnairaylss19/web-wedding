<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengeluaran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/** Pencatatan pengeluaran usaha; dasar perhitungan laba kas di laporan keuangan. */
class PengeluaranController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'bulan' => 'nullable|date_format:Y-m',
            'kategori' => ['nullable', Rule::in(Pengeluaran::KATEGORI)],
        ]);

        $bulan = Carbon::parse(($request->query('bulan') ?: now()->format('Y-m')) . '-01');
        $q = Pengeluaran::with('admin')
            ->whereDate('tanggal', '>=', $bulan->copy()->startOfMonth()->toDateString())
            ->whereDate('tanggal', '<=', $bulan->copy()->endOfMonth()->toDateString())
            ->orderByDesc('tanggal')->orderByDesc('id');
        if ($k = $request->query('kategori')) {
            $q->where('kategori', $k);
        }
        $daftar = $q->get();

        return view('admin.pengeluaran.index', [
            'daftar' => $daftar, 'bulan' => $bulan->format('Y-m'), 'label' => $bulan->translatedFormat('F Y'),
            'total' => (int) $daftar->sum('nominal'),
            'perKategori' => $daftar->groupBy('kategori')->map->sum('nominal')->sortDesc(),
        ]);
    }

    public function store(Request $request)
    {
        $d = $request->validate($this->aturan(), $this->pesan());
        Pengeluaran::create($d + ['admin_id' => $request->user()->id]);
        return back()->with('ok', 'Pengeluaran dicatat: ' . rupiah($d['nominal']) . ' (' . $d['kategori'] . ').');
    }

    public function update(Request $request, Pengeluaran $pengeluaran)
    {
        $v = Validator::make($request->all(), $this->aturan(), $this->pesan());
        if ($v->fails()) {
            // Buka kembali modal edit yang benar beserta isian terakhir.
            return back()->withInput()->withErrors($v)->with('edit_id', $pengeluaran->id);
        }
        $pengeluaran->update($v->validated());
        return back()->with('ok', 'Pengeluaran diperbarui.');
    }

    /** Soft delete: baris hilang dari laporan tetapi tetap tersimpan di database untuk penelusuran. */
    public function destroy(Pengeluaran $pengeluaran)
    {
        $pengeluaran->delete();
        return back()->with('ok', 'Pengeluaran dihapus dari laporan.');
    }

    private function aturan(): array
    {
        return [
            'tanggal' => 'required|date|before_or_equal:today',
            'kategori' => ['required', Rule::in(Pengeluaran::KATEGORI)],
            'keterangan' => 'required|string|min:3|max:255',
            'nominal' => 'required|integer|min:1|max:100000000000',
        ];
    }

    private function pesan(): array
    {
        return [
            'tanggal.before_or_equal' => 'Tanggal pengeluaran tidak boleh di masa depan.',
            'kategori.in' => 'Pilih salah satu kategori yang tersedia.',
            'keterangan.required' => 'Keterangan pengeluaran wajib diisi.',
            'keterangan.min' => 'Keterangan minimal 3 karakter.',
            'nominal.min' => 'Nominal harus lebih dari 0.',
        ];
    }
}
