<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', "Ike's Gallery – Wedding & Catering")</title>
    <x-tw />
</head>
@php
    $nav = [['beranda', 'Beranda'], ['katalog', 'Katalog'], ['gallery', 'Gallery'], ['testimoni', 'Testimoni'], ['profil', 'Profil Usaha'], ['pemesanan.create', 'Pesan']];
@endphp
<body class="antialiased">
<div class="bg-burgundy-dark text-white/90 text-xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 flex flex-col sm:flex-row items-center justify-between gap-2">
        <div class="flex flex-wrap items-center justify-center gap-x-5 gap-y-1">
            <a href="tel:{{ preg_replace('/[^+\d]/', '', config('ikes.phone')) }}" class="hover:text-gold transition">📞 {{ config('ikes.phone') }}</a>
            <a href="mailto:{{ config('ikes.email') }}" class="hover:text-gold transition">✉️ {{ config('ikes.email') }}</a>
        </div>
    </div>
</div>

<header class="sticky top-0 z-50 bg-white/95 backdrop-blur border-b border-gold/20 shadow-sm">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
        <a href="{{ route('beranda') }}" class="flex items-center gap-3">
            <span class="w-11 h-11 rounded-full border-2 border-gold flex items-center justify-center bg-burgundy text-gold font-display text-2xl font-bold">I</span>
            <span class="leading-tight"><span class="block font-serif text-xl font-semibold text-burgundy">Ike's Gallery</span><span class="block text-[10px] tracking-[0.28em] uppercase text-gold-dark">Wedding &amp; Catering</span></span>
        </a>
        <ul class="hidden md:flex items-center gap-6 lg:gap-8 text-sm font-medium">
            @foreach ($nav as [$r, $t])
                <li><a href="{{ route($r) }}" class="{{ request()->routeIs($r . '*') ? 'text-burgundy font-semibold after:w-full' : 'text-charcoal hover:text-burgundy after:w-0' }} transition relative after:absolute after:left-0 after:-bottom-1 after:h-px after:bg-gold hover:after:w-full after:transition-all">{{ $t }}</a></li>
            @endforeach
        </ul>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.login') }}" class="hidden sm:inline-flex items-center gap-2 bg-gold hover:bg-gold-dark text-burgundy-dark font-semibold text-sm px-5 py-2.5 rounded-full transition shadow-md">Login Admin</a>
            <button id="menuBtn" class="md:hidden p-2 text-burgundy text-2xl" aria-label="Menu">☰</button>
        </div>
    </nav>
    <div id="mobileMenu" class="hidden md:hidden border-t border-gold/20 bg-white">
        <div class="px-6 py-4 flex flex-col gap-4 text-sm font-medium">
            @foreach ($nav as [$r, $t])<a href="{{ route($r) }}" class="{{ request()->routeIs($r . '*') ? 'text-burgundy font-semibold' : 'text-charcoal' }}">{{ $t }}</a>@endforeach
            <a href="{{ route('admin.login') }}" class="bg-gold text-burgundy-dark font-semibold text-center py-2.5 rounded-full">Login Admin</a>
        </div>
    </div>
</header>

@hasSection('judul')
<section class="relative bg-burgundy overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-b from-burgundy-dark/80 via-burgundy to-burgundy-dark"></div>
    <div class="relative max-w-4xl mx-auto px-6 py-14 sm:py-20 text-center text-white">
        <p class="text-gold tracking-[0.4em] text-xs uppercase mb-4">@yield('eyebrow')</p>
        <h1 class="font-serif text-3xl sm:text-5xl font-semibold leading-tight">@yield('judul')</h1>
        <div class="gold-line mx-auto my-5"></div>
        <p class="max-w-2xl mx-auto text-white/80 font-light text-sm leading-relaxed">@yield('sub')</p>
    </div>
</section>
@endif

<main>
    @if (session('ok'))<div class="max-w-3xl mx-auto mt-6 px-4"><div class="rounded-xl bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3">{{ session('ok') }}</div></div>@endif
    @yield('content')
</main>

<footer class="bg-burgundy-dark text-white/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 grid md:grid-cols-3 gap-10">
        <div><p class="font-serif text-2xl text-gold mb-3">Ike's Gallery</p><p class="text-sm leading-relaxed text-white/70">Wedding &amp; Catering untuk setiap momen berharga Anda.</p></div>
        <div class="text-sm space-y-2"><p class="font-medium text-white mb-3 tracking-wide">Kontak</p><p>📞 {{ config('ikes.phone') }}</p><p>✉️ {{ config('ikes.email') }}</p></div>
        <div class="text-sm space-y-2"><p class="font-medium text-white mb-3 tracking-wide">Navigasi</p>
            @foreach (array_slice($nav, 1) as [$r, $t])<a href="{{ route($r) }}" class="block hover:text-gold transition">{{ $t }}</a>@endforeach</div>
    </div>
    <div class="border-t border-white/10 py-5 text-center text-xs text-white/50">© {{ date('Y') }} Ike's Gallery – Wedding &amp; Catering. All rights reserved.</div>
</footer>
<a href="{{ route('pemesanan.create') }}" aria-label="Pesan" class="fixed bottom-5 right-5 z-40 w-14 h-14 rounded-full bg-burgundy border-2 border-gold text-gold flex items-center justify-center shadow-xl hover:scale-110 transition text-2xl">💬</a>
<script>document.getElementById('menuBtn').onclick = () => document.getElementById('mobileMenu').classList.toggle('hidden');</script>
@stack('scripts')
</body>
</html>
