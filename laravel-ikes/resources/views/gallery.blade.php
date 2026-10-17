@extends('layouts.public')
@section('title', "Gallery – Ike's Gallery")
@section('eyebrow', 'Portofolio')
@section('judul', 'Galeri Momen')
@section('sub', "Dokumentasi hasil layanan wedding dan catering Ike's Gallery sebagai gambaran kualitas kami.")
@section('content')
@php $ratio = ['aspect-square', 'aspect-[3/4]', 'aspect-[4/3]', 'aspect-[3/4]']; @endphp
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div id="tabs" class="flex flex-wrap justify-center gap-3 mb-12">
        @foreach (['all' => 'Semua', 'Wedding' => 'Wedding', 'Catering' => 'Catering'] as $v => $l)<button data-v="{{ $v }}" class="tab {{ $loop->first ? 'tab-active' : '' }}">{{ $l }}</button>@endforeach
    </div>
    <div id="grid" class="columns-2 md:columns-3 lg:columns-4 gap-3 sm:gap-4">
        @foreach ($foto as $i => [$id, $kat, $cap])
            <button type="button" data-kat="{{ $kat }}" data-src="https://images.unsplash.com/{{ $id }}?auto=format&fit=crop&w=1400&q=75" data-cap="{{ $cap }}" class="group relative block w-full mb-3 sm:mb-4 break-inside-avoid img-fallback rounded-xl overflow-hidden {{ $ratio[$i % 4] }}">
                <img src="https://images.unsplash.com/{{ $id }}?auto=format&fit=crop&w=600&q=70" alt="{{ $cap }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-110 transition duration-700" onerror="this.remove()">
                <span class="absolute inset-x-0 bottom-0 p-3 text-left text-xs text-white bg-gradient-to-t from-burgundy-dark/90 to-transparent opacity-0 group-hover:opacity-100 transition">{{ $cap }}</span>
            </button>
        @endforeach
    </div>
</section>
<div id="lb" class="modal" style="background: rgba(0,0,0,.88)"><button data-close class="absolute top-5 right-6 text-4xl text-white/80 hover:text-gold" aria-label="Tutup">×</button>
    <figure class="max-w-4xl w-full text-center"><img id="lbImg" class="max-h-[80vh] mx-auto rounded-xl" alt=""><figcaption id="lbCap" class="text-white/80 text-sm mt-4"></figcaption></figure></div>
@endsection
@push('scripts')
<script>
document.getElementById('tabs').onclick = e => { const b = e.target.closest('[data-v]'); if (!b) return; document.querySelectorAll('#tabs .tab').forEach(x => x.classList.toggle('tab-active', x === b)); document.querySelectorAll('#grid > button').forEach(g => g.classList.toggle('hidden', b.dataset.v !== 'all' && g.dataset.kat !== b.dataset.v)); };
document.getElementById('grid').onclick = e => { const b = e.target.closest('[data-src]'); if (!b) return; lbImg.src = b.dataset.src; lbCap.textContent = b.dataset.cap; lb.classList.add('open'); };
document.addEventListener('click', e => { const m = e.target.closest('.modal'); if (m && (e.target === m || e.target.closest('[data-close]'))) m.classList.remove('open'); });
</script>
@endpush
