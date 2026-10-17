@extends('layouts.public')
@section('title', "Testimoni & Rating – Ike's Gallery")
@section('eyebrow', 'Kata Mereka')
@section('judul', 'Testimonials & Reviews')
@section('sub', 'Pengalaman klien terdahulu sebagai bahan pertimbangan Anda sebelum memesan.')
@section('content')
<section class="max-w-5xl mx-auto px-4 sm:px-6 py-16">
    <div class="card p-6 sm:p-10 grid md:grid-cols-[auto,1fr,auto] gap-8 items-center">
        <div class="text-center"><p class="font-serif text-6xl text-burgundy leading-none">{{ number_format($rata, 1) }}</p><div class="flex justify-center mt-2"><x-stars :n="$rata" /></div><p class="text-xs text-charcoal/60 mt-1">{{ $tampil->count() }} ulasan</p></div>
        <div class="space-y-1.5">
            @foreach ($sebaran as $r => $c)
                <div class="flex items-center gap-3 text-xs"><span class="w-4 text-charcoal/60">{{ $r }}★</span>
                    <div class="flex-1 h-2 rounded-full bg-gray-100 overflow-hidden"><div class="h-full bg-gold" style="width: {{ $tampil->count() ? $c / $tampil->count() * 100 : 0 }}%"></div></div><span class="w-5 text-right text-charcoal/50">{{ $c }}</span></div>
            @endforeach
        </div>
        <button type="button" onclick="openModal('fm')" class="btn btn-p py-3.5 border border-gold/40">✍️ Kirim Testimoni</button>
    </div>
</section>

<section class="bg-burgundy relative overflow-hidden"><div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    @if ($tampil->isEmpty())<p class="text-white/70 text-center">Belum ada testimoni.</p>@endif
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">@foreach ($tampil as $t)<x-testi-card :t="$t" />@endforeach</div>
</div></section>

<div id="fm" class="modal {{ $errors->any() ? 'open' : '' }}"><div class="bg-white rounded-2xl w-full max-w-lg p-6 sm:p-8 relative my-8">
    <button type="button" data-close class="absolute top-4 right-5 text-2xl text-charcoal/40 hover:text-burgundy" aria-label="Tutup">×</button>
    <h3 class="font-serif text-2xl text-burgundy">Kirim Testimoni &amp; Rating</h3>
    <p class="text-xs text-charcoal/60 mt-1 mb-5">Testimoni akan tampil di halaman publik setelah disetujui admin.</p>
    <form method="POST" action="{{ route('testimoni.store') }}" class="space-y-4">
        @csrf
        <div><label class="lbl" for="kode">Kode Pemesanan <span class="text-red-600">*</span></label><input id="kode" name="kode" value="{{ old('kode') }}" class="inp font-mono" placeholder="IKG-2026-001">@error('kode')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror</div>
        <div><label class="lbl">Rating <span class="text-red-600">*</span></label>
            <input type="hidden" name="rating" id="rating" value="{{ old('rating') }}">
            <div id="pick" class="flex gap-1">@for ($i = 1; $i <= 5; $i++)<button type="button" data-r="{{ $i }}" class="p-0.5"><svg class="w-8 h-8 text-gray-300" fill="currentColor" viewBox="0 0 20 20"><path d="M10 1.5l2.6 5.5 6 .8-4.4 4.2 1.1 6L10 15l-5.3 3 1.1-6L1.4 7.8l6-.8L10 1.5z"/></svg></button>@endfor</div>
            @error('rating')<p class="text-xs text-red-600 mt-1">Pilih rating bintang.</p>@enderror</div>
        <div><label class="lbl" for="isi">Isi Testimoni <span class="text-red-600">*</span></label><textarea id="isi" name="isi" rows="4" class="inp resize-none" placeholder="Ceritakan pengalaman Anda...">{{ old('isi') }}</textarea>@error('isi')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror</div>
        <button class="btn btn-p w-full py-3.5">Kirim Testimoni</button>
    </form>
</div></div>
@endsection
@push('scripts')
<script>
document.addEventListener('click', e => { const m = e.target.closest('.modal'); if (m && (e.target === m || e.target.closest('[data-close]'))) m.classList.remove('open'); });
const openModal = id => document.getElementById(id).classList.add('open');
const inp = document.getElementById('rating'), stars = document.querySelectorAll('#pick svg');
const paint = () => stars.forEach((s, i) => { s.classList.toggle('text-gold', i < +inp.value); s.classList.toggle('text-gray-300', i >= +inp.value); });
document.getElementById('pick').onclick = e => { const b = e.target.closest('[data-r]'); if (b) { inp.value = b.dataset.r; paint(); } }; paint();
</script>
@endpush
