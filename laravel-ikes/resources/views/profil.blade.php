@extends('layouts.public')
@section('title', "Profil Usaha – Ike's Gallery")
@section('eyebrow', 'Tentang Kami')
@section('judul', 'Profil Usaha')
@section('sub', "Mengenal Ike's Gallery, mitra terpercaya untuk pernikahan dan hajatan Anda.")
@section('content')
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 grid lg:grid-cols-2 gap-12 items-center">
    <div class="relative"><div class="absolute -top-4 -left-4 w-full h-full border-2 border-gold/50 rounded-2xl"></div>
        <div class="img-fallback relative rounded-2xl overflow-hidden aspect-[4/3]"><img src="https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&w=1000&q=70" alt="Tentang Ike's Gallery" class="w-full h-full object-cover" onerror="this.remove()"></div></div>
    <div>
        <p class="text-gold-dark tracking-[0.3em] text-xs uppercase mb-3">Siapa Kami</p>
        <h2 class="font-serif text-3xl sm:text-4xl text-burgundy leading-snug">Merangkai Momen Berharga dengan Sentuhan Elegan</h2>
        <div class="gold-line my-5" style="background: linear-gradient(90deg, #D4AF37, transparent)"></div>
        <p class="text-charcoal/80 leading-relaxed">Ike's Gallery hadir sebagai mitra terpercaya untuk pernikahan dan hajatan Anda, mulai dari rias pengantin, dekorasi, hingga katering yang menggugah selera. Setiap detail kami siapkan dengan teliti agar hari istimewa Anda berjalan lancar dan berkesan.</p>
    </div>
</section>
<section class="bg-white border-y border-gold/20"><div class="max-w-5xl mx-auto px-4 sm:px-6 py-20">
    <div class="text-center mb-12"><p class="text-gold-dark tracking-[0.3em] text-xs uppercase mb-3">Layanan Kami</p><h2 class="font-serif text-3xl text-burgundy">Dua Layanan, Satu Tempat</h2><div class="gold-line mx-auto mt-5"></div></div>
    <div class="grid md:grid-cols-2 gap-8">
        <div class="card p-8"><p class="text-3xl mb-3">💍</p><h3 class="font-serif text-xl text-burgundy mb-3">Wedding</h3><ul class="text-sm text-charcoal/75 space-y-2"><li>✦ Rias pengantin &amp; busana akad/resepsi</li><li>✦ Dekorasi pelaminan dan gedung</li><li>✦ MC, entertainment, dan dokumentasi</li></ul><a href="{{ route('katalog') }}" class="btn btn-o btn-sm mt-6">Lihat Paket Wedding</a></div>
        <div class="card p-8"><p class="text-3xl mb-3">🍽️</p><h3 class="font-serif text-xl text-burgundy mb-3">Catering</h3><ul class="text-sm text-charcoal/75 space-y-2"><li>✦ Nasi box premium dan snack box</li><li>✦ Prasmanan reguler dan gold</li><li>✦ Tumpeng untuk syukuran dan arisan</li></ul><a href="{{ route('katalog') }}" class="btn btn-o btn-sm mt-6">Lihat Paket Catering</a></div>
    </div></div></section>
<section class="max-w-5xl mx-auto px-4 sm:px-6 py-20">
    <div class="text-center mb-12"><p class="text-gold-dark tracking-[0.3em] text-xs uppercase mb-3">Hubungi Kami</p><h2 class="font-serif text-3xl text-burgundy">Kontak &amp; Lokasi</h2><div class="gold-line mx-auto mt-5"></div></div>
    <div class="grid sm:grid-cols-2 gap-6">
        <a href="https://wa.me/{{ config('ikes.wa_number') }}" target="_blank" rel="noopener" class="card p-6 flex items-center gap-4 hover:border-gold transition"><span class="w-12 h-12 rounded-full bg-burgundy text-gold flex items-center justify-center shrink-0">💬</span><span><span class="block text-xs text-charcoal/50 uppercase tracking-wider">WhatsApp</span><span class="font-medium text-burgundy">{{ config('ikes.phone') }}</span></span></a>
        <a href="mailto:{{ config('ikes.email') }}" class="card p-6 flex items-center gap-4 hover:border-gold transition"><span class="w-12 h-12 rounded-full bg-burgundy text-gold flex items-center justify-center shrink-0">✉️</span><span><span class="block text-xs text-charcoal/50 uppercase tracking-wider">Email</span><span class="font-medium text-burgundy break-all">{{ config('ikes.email') }}</span></span></a>
        <div class="card p-6 flex items-center gap-4 sm:col-span-2"><span class="w-12 h-12 rounded-full bg-burgundy text-gold flex items-center justify-center shrink-0">📍</span><span class="flex-1"><span class="block text-xs text-charcoal/50 uppercase tracking-wider">Lokasi Usaha</span><span class="font-medium text-burgundy">{{ config('ikes.address') }}</span></span>
            <a href="https://www.google.com/maps/search/{{ urlencode("Ike's Gallery Wedding & Catering") }}" target="_blank" rel="noopener" class="btn btn-o btn-sm">Buka Peta</a></div>
    </div>
</section>
@endsection
