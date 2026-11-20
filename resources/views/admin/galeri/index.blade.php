@extends('layouts.admin')
@section('judul', 'Kelola Galeri')
@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <p class="text-sm text-charcoal/60 max-w-2xl">Foto karya Ike's Gallery yang tampil di halaman Gallery publik. Gunakan dokumentasi asli (rias, dekorasi, hidangan); angka urutan kecil tampil lebih dulu.</p>
    <button type="button" id="add" class="btn btn-p">＋ Unggah Foto</button>
</div>

@if ($daftar->isEmpty())
    <div class="card p-10 text-center text-charcoal/60">Belum ada foto. Halaman Gallery publik akan menampilkan pesan "Galeri sedang kami siapkan" sampai ada foto yang aktif.</div>
@else
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
    @foreach ($daftar as $g)
    <div class="card overflow-hidden {{ $g->aktif ? '' : 'opacity-60' }}">
        <div class="aspect-[4/3] img-fallback"><img src="{{ $g->foto_url }}" alt="" loading="lazy" class="w-full h-full object-cover" onerror="this.remove()"></div>
        <div class="p-3 space-y-2">
            <p class="text-sm font-medium text-burgundy truncate" title="{{ $g->judul }}">{{ $g->judul ?: 'Tanpa keterangan' }}</p>
            <div class="flex flex-wrap gap-1">
                <span class="badge {{ $g->kategori === 'Wedding' ? 'b-blue' : 'b-green' }}">{{ $g->kategori }}</span>
                <span class="badge b-gray">Urutan {{ $g->urutan }}</span>
                @unless ($g->aktif)<span class="badge b-amber">Disembunyikan</span>@endunless
            </div>
            <div class="flex flex-wrap gap-1.5 pt-1">
                <button type="button" class="btn btn-o btn-sm js-edit" data-g="{{ json_encode($g->only(['id', 'judul', 'kategori', 'urutan'])) }}">Ubah</button>
                <form method="POST" action="{{ route('admin.galeri.aktif', $g) }}">@csrf @method('PATCH')<button class="btn btn-o btn-sm">{{ $g->aktif ? 'Sembunyikan' : 'Tampilkan' }}</button></form>
                <form method="POST" action="{{ route('admin.galeri.destroy', $g) }}" onsubmit="return confirm('Hapus foto ini dari galeri? Berkas fotonya ikut terhapus.')">@csrf @method('DELETE')<button class="btn btn-r btn-sm">Hapus</button></form>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

<div id="mTambah" class="modal"><div class="bg-white rounded-2xl w-full max-w-md p-6 sm:p-8 relative my-8">
    <button type="button" data-close class="absolute top-4 right-5 text-2xl text-charcoal/40 hover:text-burgundy" aria-label="Tutup">×</button>
    <h3 class="font-serif text-2xl text-burgundy mb-1">Unggah Foto</h3>
    <p class="text-xs text-charcoal/60 mb-5">Bisa memilih beberapa foto sekaligus (maks. 20, tiap foto maks. 4 MB, format JPG/PNG/WEBP). Keterangan dan kategori berlaku untuk semua foto yang dipilih; keterangan bisa diubah per foto sesudahnya.</p>
    <form method="POST" action="{{ route('admin.galeri.store') }}" enctype="multipart/form-data" class="space-y-4">@csrf
        <div><label class="lbl">Foto</label><input name="foto[]" type="file" accept="image/jpeg,image/png,image/webp" multiple required class="inp"></div>
        <div class="grid grid-cols-2 gap-4">
            <div><label class="lbl">Kategori</label><select name="kategori" class="inp"><option>Wedding</option><option>Catering</option></select></div>
            <div><label class="lbl">Urutan</label><input name="urutan" type="number" min="0" max="65535" value="0" class="inp"></div></div>
        <div><label class="lbl">Keterangan <span class="text-charcoal/40 font-normal">(opsional)</span></label><input name="judul" maxlength="100" class="inp" placeholder="Contoh: Dekorasi pelaminan Bu Sari"></div>
        <button class="btn btn-p w-full py-3">Unggah</button>
    </form></div></div>

<div id="mEdit" class="modal"><div class="bg-white rounded-2xl w-full max-w-md p-6 sm:p-8 relative my-8">
    <button type="button" data-close class="absolute top-4 right-5 text-2xl text-charcoal/40 hover:text-burgundy" aria-label="Tutup">×</button>
    <h3 class="font-serif text-2xl text-burgundy mb-5">Ubah Foto</h3>
    <form id="fEdit" method="POST" action="" enctype="multipart/form-data" class="space-y-4">@csrf @method('PUT')
        <div class="grid grid-cols-2 gap-4">
            <div><label class="lbl">Kategori</label><select name="kategori" class="inp"><option>Wedding</option><option>Catering</option></select></div>
            <div><label class="lbl">Urutan</label><input name="urutan" type="number" min="0" max="65535" class="inp"></div></div>
        <div><label class="lbl">Keterangan <span class="text-charcoal/40 font-normal">(opsional)</span></label><input name="judul" maxlength="100" class="inp"></div>
        <div><label class="lbl">Ganti Foto <span class="text-charcoal/40 font-normal">(opsional, maks. 4 MB)</span></label><input name="foto" type="file" accept="image/jpeg,image/png,image/webp" class="inp"></div>
        <button class="btn btn-p w-full py-3">Simpan Perubahan</button>
    </form></div></div>
@endsection
@push('scripts')
<script>
(() => {
    const f = document.getElementById('fEdit'), base = '{{ url('admin/galeri') }}';
    document.getElementById('add').onclick = () => openModal('mTambah');
    document.querySelectorAll('.js-edit').forEach(b => b.addEventListener('click', () => {
        const g = JSON.parse(b.dataset.g), el = f.elements;
        f.reset();
        f.action = base + '/' + g.id;
        el.kategori.value = g.kategori; el.urutan.value = g.urutan ?? 0; el.judul.value = g.judul ?? '';
        openModal('mEdit');
    }));
})();
</script>
@endpush
