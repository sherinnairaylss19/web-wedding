<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') – Ike's Gallery</title>
    <x-tw />
</head>
@php
    $menu = [['admin.dashboard', 'admin.dashboard', '📊', 'Dashboard'], ['admin.pemesanan.index', 'admin.pemesanan.*', '📋', 'Pemesanan'], ['admin.laporan', 'admin.laporan', '💰', 'Laporan Keuangan'],
             ['admin.layanan.index', 'admin.layanan.*', '🛎️', 'Katalog Layanan'], ['admin.klien.index', 'admin.klien.*', '👥', 'Data Klien'], ['admin.testimoni.index', 'admin.testimoni.*', '⭐', 'Testimoni']];
@endphp
<body class="antialiased">
<div class="flex min-h-screen">
    <aside id="sb" class="no-print fixed lg:sticky top-0 inset-y-0 left-0 z-50 lg:h-screen w-64 -translate-x-full lg:translate-x-0 transition bg-burgundy-dark text-white flex flex-col shrink-0">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-6 h-20 border-b border-white/10">
            <span class="w-10 h-10 rounded-full border-2 border-gold flex items-center justify-center bg-burgundy text-gold font-display text-xl font-bold">I</span>
            <span class="leading-tight"><span class="block font-serif text-lg">Ike's Gallery</span><span class="block text-[10px] tracking-[0.25em] uppercase text-gold">Panel Admin</span></span>
        </a>
        <nav class="flex-1 p-4 space-y-1 text-sm overflow-y-auto">
            @foreach ($menu as [$r, $pat, $i, $t])
                <a href="{{ route($r) }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition {{ request()->routeIs($pat) ? 'bg-gold text-burgundy-dark font-semibold' : 'text-white/80 hover:bg-white/10' }}"><span>{{ $i }}</span>{{ $t }}</a>
            @endforeach
        </nav>
        <div class="p-4 border-t border-white/10 text-sm space-y-1">
            <a href="{{ route('beranda') }}" target="_blank" class="block px-4 py-2 rounded-lg text-white/80 hover:bg-white/10">🌐 Lihat Website</a>
            <form method="POST" action="{{ route('admin.logout') }}">@csrf<button class="w-full text-left px-4 py-2 rounded-lg text-white/80 hover:bg-white/10">🚪 Keluar</button></form>
        </div>
    </aside>
    <div class="flex-1 min-w-0 flex flex-col">
        <header class="no-print sticky top-0 z-40 h-20 bg-white/95 backdrop-blur border-b border-gold/20 flex items-center justify-between px-4 sm:px-8">
            <div class="flex items-center gap-3">
                <button onclick="document.getElementById('sb').classList.toggle('-translate-x-full')" class="lg:hidden p-2 text-burgundy text-xl" aria-label="Menu">☰</button>
                <h1 class="font-serif text-xl sm:text-2xl text-burgundy">@yield('judul')</h1>
            </div>
            <div class="flex items-center gap-3 text-sm"><span class="hidden sm:block text-charcoal/60">{{ auth()->user()->nama }}</span>
                <span class="w-10 h-10 rounded-full bg-burgundy text-gold border-2 border-gold flex items-center justify-center font-display font-bold">{{ Str::upper(Str::substr(auth()->user()->nama, 0, 1)) }}</span></div>
        </header>
        <div id="slot" class="p-4 sm:p-8">
            @if (session('ok'))<div class="no-print mb-5 rounded-xl bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3">{{ session('ok') }}</div>@endif
            @if ($errors->any())<div class="no-print mb-5 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3"><ul class="list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
            @yield('content')
        </div>
    </div>
</div>
<script>
document.addEventListener('click', e => { const m = e.target.closest('.modal'); if (m && (e.target === m || e.target.closest('[data-close]'))) m.classList.remove('open'); });
const openModal = id => document.getElementById(id).classList.add('open');
</script>
@stack('scripts')
</body>
</html>
