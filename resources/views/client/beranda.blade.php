@extends('layouts.public')
@section('content')
<section class="relative overflow-hidden bg-burgundy">
    <div class="absolute inset-0">
        <img src="https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=1800&q=70" alt="" class="w-full h-full object-cover opacity-30" onerror="this.remove()">
        <div class="absolute inset-0 bg-gradient-to-b from-burgundy-dark/80 via-burgundy/70 to-burgundy-dark"></div>
    </div>
    <div class="relative max-w-4xl mx-auto px-6 py-24 sm:py-32 text-center text-white">
        <p class="text-gold tracking-[0.4em] text-xs sm:text-sm uppercase mb-5">Ike's Gallery · Wedding &amp; Catering</p>
        <h1 class="font-serif text-4xl sm:text-6xl font-semibold leading-tight">PILIHAN SEMPURNA ANDA:<span class="block font-display italic text-gold text-6xl sm:text-8xl mt-2">Paket</span></h1>
        <div class="gold-line mx-auto my-7"></div>
        <p class="max-w-2xl mx-auto text-white/80 font-light leading-relaxed">Wujudkan hari bahagia dengan rias, dekorasi, dan hidangan terbaik. Pilih paket yang sesuai, lalu konsultasikan langsung dengan kami tanpa perlu mendaftar.</p>
        <div class="mt-9 flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('katalog') }}" class="bg-gold hover:bg-gold-dark text-burgundy-dark font-semibold px-8 py-3.5 rounded-full transition shadow-lg">Lihat Katalog</a>
            <a href="{{ route('pemesanan.create') }}" class="border border-gold/70 text-white hover:bg-white/10 px-8 py-3.5 rounded-full transition">Konsultasi WhatsApp</a>
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
    <div class="text-center mb-10"><p class="text-gold-dark tracking-[0.3em] text-xs uppercase mb-3">Ringkasan Layanan</p><h2 class="font-serif text-3xl sm:text-4xl text-burgundy">Paket Pilihan Kami</h2><div class="gold-line mx-auto mt-5"></div></div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">@foreach ($unggulan as $p)<x-package-card :p="$p" />@endforeach</div>
    <div class="text-center mt-10"><a href="{{ route('katalog') }}" class="btn btn-o">Lihat Semua Paket →</a></div>
</section>

@if ($testimoni->count())
<section class="bg-burgundy relative overflow-hidden"><div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
    <div class="text-center mb-12"><p class="text-gold tracking-[0.3em] text-xs uppercase mb-3">Kata Mereka</p><h2 class="font-serif text-3xl sm:text-4xl text-white">TESTIMONIALS &amp; REVIEWS</h2><div class="gold-line mx-auto mt-5"></div></div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">@foreach ($testimoni as $t)<x-testi-card :t="$t" />@endforeach</div>
    <div class="text-center mt-10"><a href="{{ route('testimoni') }}" class="btn btn-g">Baca Semua Testimoni</a></div>
</div></section>
@endif
@endsection
