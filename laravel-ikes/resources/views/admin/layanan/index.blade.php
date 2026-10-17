@extends('layouts.admin')
@section('judul', 'Kelola Katalog Layanan')
@section('content')
<div class="flex justify-end mb-5"><button type="button" id="add" class="btn btn-p">＋ Tambah Layanan</button></div>
<div class="card overflow-hidden"><div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Foto</th><th>Nama Layanan</th><th>Kategori</th><th>Harga</th><th>Deskripsi</th><th></th></tr></thead><tbody>
    @foreach ($daftar as $l)
        <tr><td><div class="w-14 h-14 rounded-lg img-fallback overflow-hidden">@if ($l->foto_url)<img src="{{ $l->foto_url }}" class="w-full h-full object-cover" onerror="this.remove()">@endif</div></td>
            <td class="font-medium text-burgundy">{{ $l->nama }} @if ($l->label)<span class="badge b-amber ml-1">{{ $l->label }}</span>@endif</td>
            <td><span class="badge {{ $l->kategori === 'Wedding' ? 'b-blue' : 'b-green' }}">{{ $l->kategori }}</span></td>
            <td class="whitespace-nowrap">{{ rupiah($l->harga) }} <span class="text-xs text-charcoal/50">{{ $l->satuan }}</span></td>
            <td class="max-w-xs text-charcoal/70 text-xs">{{ $l->deskripsi }}</td>
            <td class="text-right whitespace-nowrap">
                <button type="button" class="btn btn-o btn-sm js-edit" data-l="{{ json_encode($l->only(['id', 'nama', 'kategori', 'deskripsi', 'harga', 'satuan', 'label'])) }}">Ubah</button>
                <form method="POST" action="{{ route('admin.layanan.destroy', $l) }}" class="inline" onsubmit="return confirm('Hapus layanan ini dari katalog?')">@csrf @method('DELETE')<button class="btn btn-r btn-sm">Hapus</button></form></td></tr>
    @endforeach
</tbody></table></div></div>

<div id="m" class="modal"><div class="bg-white rounded-2xl w-full max-w-lg p-6 sm:p-8 relative my-8">
    <button type="button" data-close class="absolute top-4 right-5 text-2xl text-charcoal/40 hover:text-burgundy">×</button>
    <h3 id="mt" class="font-serif text-2xl text-burgundy mb-5">Tambah Layanan</h3>
    <form id="f" method="POST" action="{{ route('admin.layanan.store') }}" enctype="multipart/form-data" class="space-y-4">@csrf <span id="spoof"></span>
        <div><label class="lbl">Nama Layanan</label><input name="nama" id="nama" class="inp" required></div>
        <div class="grid grid-cols-2 gap-4">
            <div><label class="lbl">Kategori</label><select name="kategori" id="kategori" class="inp"><option>Wedding</option><option>Catering</option></select></div>
            <div><label class="lbl">Satuan Harga</label><input name="satuan" id="satuan" class="inp" placeholder="/ porsi (kosongkan jika paket)"></div></div>
        <div><label class="lbl">Harga (Rp)</label><input name="harga" id="harga" type="number" min="1" class="inp" required></div>
        <div><label class="lbl">Deskripsi</label><textarea name="deskripsi" id="deskripsi" rows="3" class="inp resize-none" required></textarea></div>
        <div><label class="lbl">Label (opsional)</label><input name="label" id="label" class="inp" placeholder="Contoh: Terlaris"></div>
        <div><label class="lbl">Foto Layanan (maks. 2 MB)</label><input name="foto" type="file" accept="image/*" class="text-xs"></div>
        <button class="btn btn-p w-full py-3">Simpan</button>
    </form></div></div>
@endsection
@push('scripts')
<script>
const f = document.getElementById('f'), base = '{{ url('admin/layanan') }}', store = '{{ route('admin.layanan.store') }}';
function open_(l) {
    f.reset(); document.getElementById('mt').textContent = l ? 'Ubah Layanan' : 'Tambah Layanan';
    f.action = l ? base + '/' + l.id : store; document.getElementById('spoof').innerHTML = l ? '<input type="hidden" name="_method" value="PUT">' : '';
    ['nama', 'kategori', 'satuan', 'harga', 'deskripsi', 'label'].forEach(k => document.getElementById(k).value = l ? (l[k] ?? '') : '');
    openModal('m');
}
document.getElementById('add').onclick = () => open_(null);
document.querySelectorAll('.js-edit').forEach(b => b.onclick = () => open_(JSON.parse(b.dataset.l)));
</script>
@endpush
