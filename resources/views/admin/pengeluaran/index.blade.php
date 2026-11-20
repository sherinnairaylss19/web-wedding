@extends('layouts.admin')
@section('judul', 'Pengeluaran')
@section('content')
@php
    $kategori = \App\Models\Pengeluaran::KATEGORI;
    $editId = session('edit_id');
    $bukaTambah = $errors->any() && ! $editId;
@endphp
<form method="GET" class="no-print card p-5 mb-6 flex flex-wrap items-end gap-4">
    <div><label class="lbl">Bulan</label><input name="bulan" type="month" value="{{ $bulan }}" class="inp" onchange="this.form.submit()"></div>
    <div><label class="lbl">Kategori</label><select name="kategori" class="inp" onchange="this.form.submit()"><option value="">Semua kategori</option>@foreach ($kategori as $k)<option @selected(request('kategori') === $k)>{{ $k }}</option>@endforeach</select></div>
    <button class="btn btn-o">Tampilkan</button>
    <button type="button" onclick="openModal('mTambah')" class="btn btn-p ml-auto">＋ Catat Pengeluaran</button>
</form>

<div class="grid sm:grid-cols-3 gap-4 mb-6">
    <div class="rounded-2xl bg-burgundy text-white p-6 border border-gold/40 sm:col-span-1"><p class="text-xs uppercase tracking-widest text-gold">Total {{ $label }}</p><p class="font-serif text-3xl mt-2">{{ rupiah($total) }}</p><p class="text-xs text-white/70 mt-1">{{ $daftar->count() }} catatan</p></div>
    <div class="card p-5 sm:col-span-2"><p class="text-xs text-charcoal/60 mb-3">Per kategori</p>
        @forelse ($perKategori as $nama => $nilai)
            <div class="flex justify-between text-sm py-1.5 border-b border-gray-100 last:border-0"><span>{{ $nama }}</span><span class="font-medium">{{ rupiah($nilai) }}</span></div>
        @empty
            <p class="text-sm text-charcoal/50">Belum ada pengeluaran pada bulan ini.</p>
        @endforelse
    </div>
</div>

<div class="card overflow-hidden"><div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Tanggal</th><th>Kategori</th><th>Keterangan</th><th>Dicatat Oleh</th><th class="text-right">Nominal</th><th></th></tr></thead><tbody>
    @forelse ($daftar as $x)
    <tr>
        <td>{{ tgl($x->tanggal) }}</td><td>{{ $x->kategori }}</td><td class="max-w-xs text-charcoal/80">{{ $x->keterangan }}</td><td class="text-xs text-charcoal/60">{{ $x->admin->nama ?? '-' }}</td>
        <td class="text-right">{{ rupiah($x->nominal) }}</td>
        <td class="whitespace-nowrap text-right">
            <button type="button" class="btn btn-o btn-sm" data-edit="{{ $x->id }}" data-action="{{ route('admin.pengeluaran.update', $x) }}"
                data-tanggal="{{ $x->tanggal->toDateString() }}" data-kategori="{{ $x->kategori }}" data-keterangan="{{ $x->keterangan }}" data-nominal="{{ $x->nominal }}">Edit</button>
            <form method="POST" action="{{ route('admin.pengeluaran.destroy', $x) }}" class="inline" onsubmit="return confirm('Hapus pengeluaran {{ rupiah($x->nominal) }} ini dari laporan?')">@csrf @method('DELETE')
                <button class="btn btn-o btn-sm !text-red-700 !border-red-300">Hapus</button></form>
        </td>
    </tr>
    @empty<tr><td colspan="6" class="text-center text-charcoal/50 py-10">Belum ada pengeluaran pada periode ini.</td></tr>@endforelse
    @if ($daftar->isNotEmpty())<tr><td colspan="4" class="text-right font-semibold text-burgundy">Total</td><td class="text-right font-semibold text-burgundy">{{ rupiah($total) }}</td><td></td></tr>@endif
</tbody></table></div></div>

<div id="mTambah" class="modal {{ $bukaTambah ? 'open' : '' }}"><div class="bg-white rounded-2xl w-full max-w-md p-6 sm:p-8 relative my-8">
    <button type="button" data-close class="absolute top-4 right-5 text-2xl text-charcoal/40 hover:text-burgundy" aria-label="Tutup">×</button>
    <h3 class="font-serif text-2xl text-burgundy mb-5">Catat Pengeluaran</h3>
    <form method="POST" action="{{ route('admin.pengeluaran.store') }}" class="space-y-4">@csrf
        <div><label class="lbl">Tanggal</label><input name="tanggal" type="date" max="{{ today()->toDateString() }}" value="{{ $editId ? today()->toDateString() : old('tanggal', today()->toDateString()) }}" class="inp" required></div>
        <div><label class="lbl">Kategori</label><select name="kategori" class="inp" required>@foreach ($kategori as $k)<option @selected(! $editId && old('kategori') === $k)>{{ $k }}</option>@endforeach</select></div>
        <div><label class="lbl">Keterangan</label><input name="keterangan" maxlength="255" value="{{ $editId ? '' : old('keterangan') }}" class="inp" placeholder="Contoh: Belanja bahan prasmanan 150 porsi" required></div>
        <div><label class="lbl">Nominal (Rp)</label><input name="nominal" type="number" min="1" value="{{ $editId ? '' : old('nominal') }}" class="inp" required></div>
        <button class="btn btn-p w-full py-3">Simpan Pengeluaran</button>
    </form></div></div>

<div id="mEdit" class="modal"><div class="bg-white rounded-2xl w-full max-w-md p-6 sm:p-8 relative my-8">
    <button type="button" data-close class="absolute top-4 right-5 text-2xl text-charcoal/40 hover:text-burgundy" aria-label="Tutup">×</button>
    <h3 class="font-serif text-2xl text-burgundy mb-5">Edit Pengeluaran</h3>
    <form id="fEdit" method="POST" action="" class="space-y-4">@csrf @method('PUT')
        <div><label class="lbl">Tanggal</label><input name="tanggal" type="date" max="{{ today()->toDateString() }}" class="inp" required></div>
        <div><label class="lbl">Kategori</label><select name="kategori" class="inp" required>@foreach ($kategori as $k)<option>{{ $k }}</option>@endforeach</select></div>
        <div><label class="lbl">Keterangan</label><input name="keterangan" maxlength="255" class="inp" required></div>
        <div><label class="lbl">Nominal (Rp)</label><input name="nominal" type="number" min="1" class="inp" required></div>
        <button class="btn btn-p w-full py-3">Simpan Perubahan</button>
    </form></div></div>
@endsection
@push('scripts')
<script>
(() => {
    const f = document.getElementById('fEdit');
    document.querySelectorAll('[data-edit]').forEach(b => b.addEventListener('click', () => {
        const d = b.dataset, el = f.elements;
        f.action = d.action;
        el.tanggal.value = d.tanggal; el.kategori.value = d.kategori; el.keterangan.value = d.keterangan; el.nominal.value = d.nominal;
        openModal('mEdit');
    }));
    @if ($editId)
    // Validasi gagal saat edit: buka lagi modal yang sama, lalu kembalikan isian terakhir pengguna.
    const lama = @json(old());
    const tombol = document.querySelector('[data-edit="{{ $editId }}"]');
    if (tombol) {
        tombol.click();
        ['tanggal', 'kategori', 'keterangan', 'nominal'].forEach(k => { if (lama[k] !== undefined && lama[k] !== null) f.elements[k].value = lama[k]; });
    }
    @endif
})();
</script>
@endpush
