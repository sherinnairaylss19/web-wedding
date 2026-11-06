@extends('layouts.public')
@section('title', "Katalog Layanan – Ike's Gallery")
@section('eyebrow', 'Katalog Layanan')
@section('judul', 'Paket Pilihan Kami')
@section('sub', 'Pilih paket wedding atau catering yang sesuai, lalu pesan langsung tanpa perlu mendaftar.')
@section('content')
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="flex flex-col md:flex-row gap-4 md:items-center md:justify-between mb-10">
        <div id="tabs" class="flex flex-wrap gap-3">
            @foreach (['all' => 'Semua Paket', 'Wedding' => 'Wedding', 'Catering' => 'Catering'] as $v => $l)
                <button data-v="{{ $v }}" class="tab {{ $loop->first ? 'tab-active' : '' }}">{{ $l }}</button>
            @endforeach
        </div>
        <input id="q" type="search" placeholder="Cari paket..." class="inp md:max-w-xs">
    </div>
    <div id="grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach ($layanan as $p)<div data-kat="{{ $p->kategori }}" data-txt="{{ Str::lower($p->nama . ' ' . $p->deskripsi) }}"><x-package-card :p="$p" /></div>@endforeach
    </div>
    <p id="empty" class="hidden text-center text-charcoal/60 py-16">Paket yang Anda cari tidak ditemukan.</p>
</section>
@endsection
@push('scripts')
<script>
let f = 'all', q = '';
function filter() {
    let n = 0;
    document.querySelectorAll('#grid > div').forEach(d => { const ok = (f === 'all' || d.dataset.kat === f) && d.dataset.txt.includes(q); d.classList.toggle('hidden', !ok); n += ok; });
    document.getElementById('empty').classList.toggle('hidden', n > 0);
}
document.getElementById('tabs').onclick = e => { const b = e.target.closest('[data-v]'); if (!b) return; f = b.dataset.v; document.querySelectorAll('#tabs .tab').forEach(x => x.classList.toggle('tab-active', x === b)); filter(); };
document.getElementById('q').oninput = e => { q = e.target.value.trim().toLowerCase(); filter(); };
</script>
@endpush
