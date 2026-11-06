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

        {{-- WhatsApp --}}
        <a href="https://wa.me/{{ config('ikes.wa_number') }}" target="_blank" rel="noopener" aria-label="Chat WhatsApp Ike's Gallery" class="card p-6 flex items-center gap-4 border border-gold/30 hover:border-gold hover:shadow-md transition">
            <span class="w-12 h-12 rounded-full bg-burgundy text-gold flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
            </span>
            <span><span class="block text-xs text-charcoal/50 uppercase tracking-wider">WhatsApp</span><span class="font-medium text-burgundy">{{ config('ikes.phone') }}</span></span>
        </a>

        {{-- Email --}}
        <a href="mailto:{{ config('ikes.email') }}" aria-label="Kirim email ke Ike's Gallery" class="card p-6 flex items-center gap-4 border border-gold/30 hover:border-gold hover:shadow-md transition">
            <span class="w-12 h-12 rounded-full bg-burgundy text-gold flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            </span>
            <span class="min-w-0"><span class="block text-xs text-charcoal/50 uppercase tracking-wider">Email</span><span class="font-medium text-burgundy break-all">{{ config('ikes.email') }}</span></span>
        </a>

        {{-- Lokasi --}}
        <div class="card p-6 flex flex-col sm:flex-row sm:items-center gap-4 sm:col-span-2 border border-gold/30">
            <div class="flex items-center gap-4 flex-1">
                <span class="w-12 h-12 rounded-full bg-burgundy text-gold flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                </span>
                <span><span class="block text-xs text-charcoal/50 uppercase tracking-wider">Lokasi Usaha</span><span class="font-medium text-burgundy">{{ config('ikes.address') }}</span></span>
            </div>
            <a href="https://www.google.com/maps/search/{{ urlencode("Ike's Gallery Wedding & Catering") }}" target="_blank" rel="noopener" class="btn btn-o btn-sm self-start sm:self-auto">Buka Peta</a>
        </div>

        {{-- Media Sosial --}}
        <div class="card p-6 sm:col-span-2 border border-gold/30">
            <span class="block text-xs text-charcoal/50 uppercase tracking-wider mb-4 text-center sm:text-left">Ikuti Kami</span>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <a href="{{ config('ikes.instagram') }}" target="_blank" rel="noopener" aria-label="Instagram Ike's Gallery" class="flex items-center gap-3 rounded-xl border border-gold/30 p-3 hover:border-gold hover:shadow-md transition">
                    <span class="w-12 h-12 rounded-full bg-burgundy text-gold flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor"/></svg>
                    </span>
                    <span><span class="block text-xs text-charcoal/50 uppercase tracking-wider">Instagram</span><span class="font-medium text-burgundy">@ikesgallery</span></span>
                </a>
                <a href="{{ config('ikes.facebook') }}" target="_blank" rel="noopener" aria-label="Facebook Ike's Gallery" class="flex items-center gap-3 rounded-xl border border-gold/30 p-3 hover:border-gold hover:shadow-md transition">
                    <span class="w-12 h-12 rounded-full bg-burgundy text-gold flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                    </span>
                    <span><span class="block text-xs text-charcoal/50 uppercase tracking-wider">Facebook</span><span class="font-medium text-burgundy">Ike's Gallery</span></span>
                </a>
                <a href="{{ config('ikes.tiktok') }}" target="_blank" rel="noopener" aria-label="TikTok Ike's Gallery" class="flex items-center gap-3 rounded-xl border border-gold/30 p-3 hover:border-gold hover:shadow-md transition">
                    <span class="w-12 h-12 rounded-full bg-burgundy text-gold flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                    </span>
                    <span><span class="block text-xs text-charcoal/50 uppercase tracking-wider">TikTok</span><span class="font-medium text-burgundy">@ikesgallery</span></span>
                </a>
            </div>
        </div>

    </div>
</section>
@endsection
