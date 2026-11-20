@extends('layouts.public')
@section('title', "Gallery – Ike's Gallery")
@section('eyebrow', 'Portofolio')
@section('judul', 'Galeri Momen')
@section('sub', "Dokumentasi hasil karya Ike's Gallery sebagai gambaran kualitas kami.")
@section('content')
@php $kategori = $foto->pluck('kategori')->unique()->values(); @endphp
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    @if ($foto->isEmpty())
        <div class="text-center max-w-xl mx-auto py-10">
            <p class="font-serif text-2xl text-burgundy mb-3">Galeri sedang kami siapkan</p>
            <p class="text-charcoal/70 leading-relaxed mb-7">Dokumentasi hasil karya kami akan segera hadir di sini. Sementara itu, lihat paket yang tersedia atau konsultasikan kebutuhan acara Anda langsung dengan kami.</p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('katalog') }}" class="btn btn-p">Lihat Katalog</a>
                <a href="{{ route('pemesanan.create') }}" class="btn btn-o">Konsultasi Sekarang</a>
            </div>
        </div>
    @else
        @if ($kategori->count() > 1)
        <div id="tabs" class="flex flex-wrap justify-center gap-3 mb-12">
            <button data-v="all" class="tab tab-active">Semua</button>
            @foreach ($kategori as $k)<button data-v="{{ $k }}" class="tab">{{ $k }}</button>@endforeach
        </div>
        @endif
        <div id="grid" class="columns-2 md:columns-3 lg:columns-4 gap-3 sm:gap-4">
            @foreach ($foto as $f)
                <button type="button" data-kat="{{ $f->kategori }}" data-src="{{ $f->foto_url }}" data-cap="{{ $f->judul }}" class="group relative block w-full mb-3 sm:mb-4 break-inside-avoid img-fallback rounded-xl overflow-hidden">
                    <img src="{{ $f->foto_url }}" alt="{{ $f->judul ?: "Karya Ike's Gallery ({$f->kategori})" }}" loading="lazy" class="w-full h-auto object-cover group-hover:scale-105 transition duration-700" onerror="this.remove()">
                    @if ($f->judul)<span class="absolute inset-x-0 bottom-0 p-3 text-left text-xs text-white bg-gradient-to-t from-burgundy-dark/90 to-transparent opacity-0 group-hover:opacity-100 transition">{{ $f->judul }}</span>@endif
                </button>
            @endforeach
        </div>
    @endif
</section>
@if ($foto->isNotEmpty())
<div id="lb" class="modal" style="background: rgba(0,0,0,.88)"><button data-close class="absolute top-5 right-6 text-4xl text-white/80 hover:text-gold" aria-label="Tutup">×</button>
    <figure class="max-w-4xl w-full text-center"><img id="lbImg" class="max-h-[80vh] mx-auto rounded-xl" alt=""><figcaption id="lbCap" class="text-white/80 text-sm mt-4"></figcaption></figure></div>
@endif
@endsection
@if ($foto->isNotEmpty())
@push('scripts')
<script>
const tabs = document.getElementById('tabs');
if (tabs) tabs.onclick = e => { const b = e.target.closest('[data-v]'); if (!b) return; document.querySelectorAll('#tabs .tab').forEach(x => x.classList.toggle('tab-active', x === b)); document.querySelectorAll('#grid > button').forEach(g => g.classList.toggle('hidden', b.dataset.v !== 'all' && g.dataset.kat !== b.dataset.v)); };
document.getElementById('grid').onclick = e => { const b = e.target.closest('[data-src]'); if (!b) return; lbImg.src = b.dataset.src; lbCap.textContent = b.dataset.cap; lb.classList.add('open'); };
document.addEventListener('click', e => { const m = e.target.closest('.modal'); if (m && (e.target === m || e.target.closest('[data-close]'))) m.classList.remove('open'); });
</script>
@endpush
@endif
