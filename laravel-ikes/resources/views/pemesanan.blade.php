@extends('layouts.public')
@section('title', "Form Pemesanan – Ike's Gallery")
@section('eyebrow', 'Tanpa Daftar · Tanpa Login')
@section('judul', 'Pemesanan & Konsultasi')
@section('sub', 'Isi data pemesan dan detail acara. Setelah tersimpan, Anda bisa langsung berkonsultasi lewat WhatsApp.')
@section('content')
@php $field = fn($n) => $errors->has($n) ? 'border-red-500' : ''; @endphp
<section class="max-w-6xl mx-auto px-4 sm:px-6 py-16 grid lg:grid-cols-3 gap-8 items-start">
    <form method="POST" action="{{ route('pemesanan.store') }}" class="lg:col-span-2 card p-6 sm:p-10 space-y-6">
        @csrf
        <div><h2 class="font-serif text-xl text-burgundy">Data Pemesan</h2><div class="h-px bg-gradient-to-r from-gold/60 to-transparent mt-2"></div></div>
        <div class="grid sm:grid-cols-2 gap-5">
            <div><label class="lbl" for="nama">Nama Lengkap <span class="text-red-600">*</span></label><input id="nama" name="nama" value="{{ old('nama') }}" class="inp {{ $field('nama') }}" placeholder="Contoh: Ayu Pratiwi">@error('nama')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror</div>
            <div><label class="lbl" for="no_hp">Nomor WhatsApp <span class="text-red-600">*</span></label><input id="no_hp" name="no_hp" value="{{ old('no_hp') }}" class="inp {{ $field('no_hp') }}" placeholder="08xx-xxxx-xxxx">@error('no_hp')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror</div>
        </div>
        <div><label class="lbl" for="alamat">Alamat</label><textarea id="alamat" name="alamat" rows="2" class="inp resize-none">{{ old('alamat') }}</textarea></div>

        <div class="pt-2"><h2 class="font-serif text-xl text-burgundy">Detail Acara</h2><div class="h-px bg-gradient-to-r from-gold/60 to-transparent mt-2"></div></div>
        <div class="grid sm:grid-cols-3 gap-5">
            <div class="sm:col-span-2"><label class="lbl" for="layanan">Layanan <span class="text-red-600">*</span></label>
                <select id="layanan" name="layanan_id" class="inp {{ $field('layanan_id') }}">
                    <option value="">— Pilih layanan —</option>
                    @foreach ($layanan->groupBy('kategori') as $kat => $items)
                        <optgroup label="{{ $kat }}">@foreach ($items as $l)
                            <option value="{{ $l->id }}" data-harga="{{ $l->harga }}" data-kat="{{ $l->kategori }}" data-nama="{{ $l->nama }}" data-desk="{{ $l->deskripsi }}" data-foto="{{ $l->foto_url }}" data-satuan="{{ $l->satuan }}" @selected(old('layanan_id', $dipilih) == $l->id)>{{ $l->nama }} ({{ rupiah($l->harga) }} {{ $l->satuan }})</option>
                        @endforeach</optgroup>
                    @endforeach
                </select>@error('layanan_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror</div>
            <div><label class="lbl" for="jumlah" id="jumlahLbl">Jumlah</label><input id="jumlah" name="jumlah" type="number" min="1" value="{{ old('jumlah', 1) }}" class="inp {{ $field('jumlah') }}">@error('jumlah')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror</div>
        </div>
        <div class="grid sm:grid-cols-2 gap-5">
            <div><label class="lbl" for="tanggal_acara">Tanggal Acara <span class="text-red-600">*</span></label><input id="tanggal_acara" name="tanggal_acara" type="date" min="{{ today()->toDateString() }}" value="{{ old('tanggal_acara') }}" class="inp {{ $field('tanggal_acara') }}">@error('tanggal_acara')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror</div>
            <div><label class="lbl" for="lokasi_acara">Lokasi Acara <span class="text-red-600">*</span></label><input id="lokasi_acara" name="lokasi_acara" value="{{ old('lokasi_acara') }}" class="inp {{ $field('lokasi_acara') }}" placeholder="Gedung / alamat acara">@error('lokasi_acara')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror</div>
        </div>
        <div><label class="lbl" for="catatan">Catatan Tambahan</label><textarea id="catatan" name="catatan" rows="3" class="inp resize-none" placeholder="Jumlah tamu, tema, atau kebutuhan khusus...">{{ old('catatan') }}</textarea></div>
        <button class="btn btn-p w-full py-4 tracking-wider border border-gold/40 shadow-lg shadow-burgundy/30">SIMPAN PEMESANAN</button>
    </form>

    <aside class="card p-6 lg:sticky lg:top-28"><p class="text-gold-dark tracking-[0.3em] text-xs uppercase mb-4">Layanan Dipilih</p><div id="panel"></div></aside>
</section>
@endsection
@push('scripts')
<script>
const sel = document.getElementById('layanan'), jml = document.getElementById('jumlah'), rp = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');
function panel() {
    const o = sel.selectedOptions[0], n = Math.max(1, parseInt(jml.value, 10) || 1), el = document.getElementById('panel');
    if (!o || !o.value) { el.innerHTML = '<p class="text-sm text-charcoal/60">Belum ada layanan dipilih. Pilih layanan pada formulir untuk melihat estimasi biaya.</p>'; return; }
    document.getElementById('jumlahLbl').textContent = o.dataset.kat === 'Catering' ? 'Jumlah Porsi / Box' : 'Jumlah Paket';
    const h = +o.dataset.harga, t = document.createElement('div');
    t.textContent = ''; const esc = s => { t.textContent = s; return t.innerHTML; };
    el.innerHTML = '<div class="img-fallback rounded-xl overflow-hidden aspect-[4/3] mb-4">' + (o.dataset.foto ? '<img src="' + esc(o.dataset.foto) + '" class="w-full h-full object-cover" onerror="this.remove()">' : '') + '</div>'
        + '<h3 class="font-serif text-lg text-burgundy">' + esc(o.dataset.nama) + '</h3><p class="text-xs text-charcoal/60 mt-1">' + esc(o.dataset.desk) + '</p>'
        + '<dl class="mt-4 pt-4 border-t border-gold/30 text-sm space-y-2"><div class="flex justify-between"><dt class="text-charcoal/60">Harga satuan</dt><dd>' + rp(h) + ' ' + esc(o.dataset.satuan) + '</dd></div>'
        + '<div class="flex justify-between"><dt class="text-charcoal/60">Jumlah</dt><dd>' + n + '</dd></div>'
        + '<div class="flex justify-between pt-3 border-t border-gold/30"><dt class="font-medium text-burgundy">Estimasi Total</dt><dd class="font-serif text-xl text-burgundy">' + rp(h * n) + '</dd></div></dl>';
}
sel.onchange = () => { const o = sel.selectedOptions[0]; if (o && o.value) jml.value = o.dataset.kat === 'Catering' ? 100 : 1; panel(); };
jml.oninput = panel; panel();
</script>
@endpush
