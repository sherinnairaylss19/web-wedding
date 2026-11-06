@extends('layouts.admin')
@section('judul', 'Kelola Data Klien')
@section('content')
<form method="GET" class="mb-5 flex gap-3"><input name="q" value="{{ request('q') }}" type="search" class="inp sm:max-w-sm" placeholder="Cari nama klien..."><button class="btn btn-p">Cari</button></form>
<div class="card overflow-hidden"><div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Nama</th><th>No. HP</th><th>Alamat</th><th>Pemesanan</th><th></th></tr></thead><tbody>
    @forelse ($daftar as $k)
        <tr><td class="font-medium text-burgundy">{{ $k->nama }}</td><td>{{ $k->no_hp }}</td><td class="text-charcoal/70">{{ $k->alamat ?: '-' }}</td>
            <td><a href="{{ route('admin.pemesanan.index', ['q' => $k->nama]) }}" class="badge b-blue">{{ $k->pemesanan_count }} pemesanan</a></td>
            <td class="text-right"><button type="button" class="btn btn-o btn-sm js-edit" data-k="{{ json_encode($k->only(['id', 'nama', 'no_hp', 'alamat'])) }}">Ubah</button></td></tr>
    @empty<tr><td colspan="5" class="text-center text-charcoal/60 py-10">Klien tidak ditemukan.</td></tr>@endforelse
</tbody></table></div></div>
<div class="mt-4">{{ $daftar->links() }}</div>
<p class="text-xs text-charcoal/50 mt-3">Data klien tersimpan otomatis dari formulir pemesanan. Admin dapat memperbaruinya jika ada kesalahan pencatatan.</p>

<div id="m" class="modal"><div class="bg-white rounded-2xl w-full max-w-md p-6 sm:p-8 relative my-8">
    <button type="button" data-close class="absolute top-4 right-5 text-2xl text-charcoal/40 hover:text-burgundy">×</button>
    <h3 class="font-serif text-2xl text-burgundy mb-5">Ubah Data Klien</h3>
    <form id="f" method="POST" class="space-y-4">@csrf @method('PUT')
        <div><label class="lbl">Nama Lengkap</label><input name="nama" id="nama" class="inp" required></div>
        <div><label class="lbl">No. HP</label><input name="no_hp" id="no_hp" class="inp" required></div>
        <div><label class="lbl">Alamat</label><textarea name="alamat" id="alamat" rows="3" class="inp resize-none"></textarea></div>
        <button class="btn btn-p w-full py-3">Simpan Perubahan</button></form></div></div>
@endsection
@push('scripts')
<script>
document.querySelectorAll('.js-edit').forEach(b => b.onclick = () => {
    const k = JSON.parse(b.dataset.k); f.action = '{{ url('admin/klien') }}/' + k.id;
    ['nama', 'no_hp', 'alamat'].forEach(x => document.getElementById(x).value = k[x] ?? ''); openModal('m');
});
</script>
@endpush
