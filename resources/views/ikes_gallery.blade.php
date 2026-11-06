<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Ike's Gallery – Wedding & Catering</title>
<meta name="description" content="Ike's Gallery – Paket pernikahan dan katering elegan untuk hari istimewa Anda.">

<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Playfair+Display:wght@500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          burgundy: { DEFAULT: '#4A0E17', light: '#6B1A27', dark: '#33090F' },
          gold: { DEFAULT: '#D4AF37', light: '#E6C964', dark: '#B8932A' },
          ivory: '#FDFBF7',
          charcoal: '#222222'
        },
        fontFamily: {
          serif: ['"Playfair Display"', 'serif'],
          display: ['"Cormorant Garamond"', 'serif'],
          sans: ['Poppins', 'sans-serif']
        }
      }
    }
  }
</script>

<style>
  body { background:#FDFBF7; color:#222; font-family:'Poppins',sans-serif; }
  .gold-line { height:2px; width:72px; background:linear-gradient(90deg,transparent,#D4AF37,transparent); }
  .img-fallback { background:linear-gradient(135deg,#4A0E17,#6B1A27 55%,#D4AF37); }
  .tab-active { background:#4A0E17; color:#fff; border-color:#4A0E17; box-shadow:0 6px 18px rgba(74,14,23,.25); }
  .card-lift { transition:transform .35s ease, box-shadow .35s ease; }
  .card-lift:hover { transform:translateY(-6px); box-shadow:0 18px 40px rgba(74,14,23,.14); }
  .card-lift:hover img { transform:scale(1.06); }
  .card-lift img { transition:transform .6s ease; }
  .fade-in { animation:fade .5s ease both; }
  @keyframes fade { from{opacity:0; transform:translateY(10px)} to{opacity:1; transform:none} }
  input:focus, select:focus, textarea:focus { outline:none; border-color:#D4AF37 !important; box-shadow:0 0 0 3px rgba(212,175,55,.2); }
</style>
</head>

<body class="antialiased">

<!-- ========== TOPBAR ========== -->
<div class="bg-burgundy-dark text-white/90 text-xs">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 flex flex-col sm:flex-row items-center justify-between gap-2">
    <div class="flex flex-wrap items-center justify-center gap-x-5 gap-y-1">
      <a href="tel:+6281318713987" class="flex items-center gap-2 hover:text-gold transition">
        <svg class="w-3.5 h-3.5 text-gold" fill="currentColor" viewBox="0 0 24 24"><path d="M6.6 10.8a15.1 15.1 0 006.6 6.6l2.2-2.2a1 1 0 011-.25c1.1.37 2.3.57 3.6.57a1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.45.57 3.57a1 1 0 01-.25 1z"/></svg>
        +62-813-1871-3987
      </a>
      <a href="mailto:ayantiwedding@gmail.com" class="flex items-center gap-2 hover:text-gold transition">
        <svg class="w-3.5 h-3.5 text-gold" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
        ayantiwedding@gmail.com
      </a>
    </div>
    <div class="flex items-center gap-4">
      <a href="#" aria-label="Facebook" class="hover:text-gold transition"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M13.5 22v-8h2.7l.4-3.2h-3.1V8.8c0-.9.3-1.5 1.6-1.5h1.7V4.4c-.3 0-1.3-.1-2.4-.1-2.4 0-4 1.5-4 4.1v2.4H7.7V14h2.7v8h3.1z"/></svg></a>
      <a href="#" aria-label="Instagram" class="hover:text-gold transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor"/></svg></a>
      <a href="#" aria-label="YouTube" class="hover:text-gold transition"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M21.6 7.2a2.5 2.5 0 00-1.8-1.8C18.2 5 12 5 12 5s-6.2 0-7.8.4A2.5 2.5 0 002.4 7.2C2 8.8 2 12 2 12s0 3.2.4 4.8a2.5 2.5 0 001.8 1.8C5.8 19 12 19 12 19s6.2 0 7.8-.4a2.5 2.5 0 001.8-1.8c.4-1.6.4-4.8.4-4.8s0-3.2-.4-4.8zM10 15V9l5.2 3L10 15z"/></svg></a>
    </div>
  </div>
</div>

<!-- ========== NAVBAR ========== -->
<header class="sticky top-0 z-50 bg-white/95 backdrop-blur border-b border-gold/20 shadow-sm">
  <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
    <a href="#beranda" class="flex items-center gap-3">
      <span class="w-11 h-11 rounded-full border-2 border-gold flex items-center justify-center bg-burgundy text-gold font-display text-2xl font-bold">I</span>
      <span class="leading-tight">
        <span class="block font-serif text-xl font-semibold text-burgundy">Ike's Gallery</span>
        <span class="block text-[10px] tracking-[0.28em] uppercase text-gold-dark">Wedding &amp; Catering</span>
      </span>
    </a>

    <ul class="hidden md:flex items-center gap-9 text-sm font-medium">
      <li><a href="#beranda" class="text-charcoal hover:text-burgundy transition relative after:absolute after:left-0 after:-bottom-1 after:h-px after:w-0 after:bg-gold hover:after:w-full after:transition-all">Beranda</a></li>
      <li><a href="#paket" class="text-charcoal hover:text-burgundy transition relative after:absolute after:left-0 after:-bottom-1 after:h-px after:w-0 after:bg-gold hover:after:w-full after:transition-all">Paket</a></li>
      <li><a href="#tentang" class="text-charcoal hover:text-burgundy transition relative after:absolute after:left-0 after:-bottom-1 after:h-px after:w-0 after:bg-gold hover:after:w-full after:transition-all">Tentang Kami</a></li>
      <li><a href="#galeri" class="text-charcoal hover:text-burgundy transition relative after:absolute after:left-0 after:-bottom-1 after:h-px after:w-0 after:bg-gold hover:after:w-full after:transition-all">Galeri</a></li>
    </ul>

    <div class="flex items-center gap-3">
      <a href="/admin/login" class="hidden sm:inline-flex items-center gap-2 bg-gold hover:bg-gold-dark text-burgundy-dark font-semibold text-sm px-5 py-2.5 rounded-full transition shadow-md">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg>
        Login Admin
      </a>
      <button id="menuBtn" class="md:hidden p-2 text-burgundy" aria-label="Menu">
        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
      </button>
    </div>
  </nav>
  <div id="mobileMenu" class="hidden md:hidden border-t border-gold/20 bg-white">
    <div class="px-6 py-4 flex flex-col gap-4 text-sm font-medium">
      <a href="#beranda" class="mobile-link">Beranda</a>
      <a href="#paket" class="mobile-link">Paket</a>
      <a href="#tentang" class="mobile-link">Tentang Kami</a>
      <a href="#galeri" class="mobile-link">Galeri</a>
      <a href="/admin/login" class="bg-gold text-burgundy-dark font-semibold text-center py-2.5 rounded-full">Login Admin</a>
    </div>
  </div>
</header>

<main>

<!-- ========== HERO ========== -->
<section id="beranda" class="relative overflow-hidden bg-burgundy">
  <div class="absolute inset-0">
    <img src="https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=1800&q=70" alt="" class="w-full h-full object-cover opacity-30" onerror="this.remove()">
    <div class="absolute inset-0 bg-gradient-to-b from-burgundy-dark/80 via-burgundy/70 to-burgundy-dark"></div>
  </div>
  <div class="relative max-w-4xl mx-auto px-6 py-24 sm:py-32 text-center text-white">
    <p class="text-gold tracking-[0.4em] text-xs sm:text-sm uppercase mb-5">Ike's Gallery · Wedding &amp; Catering</p>
    <h1 class="font-serif text-4xl sm:text-6xl font-semibold leading-tight">
      PILIHAN SEMPURNA ANDA:
      <span class="block font-display italic text-gold text-6xl sm:text-8xl mt-2">Paket</span>
    </h1>
    <div class="gold-line mx-auto my-7"></div>
    <p class="max-w-2xl mx-auto text-white/80 font-light leading-relaxed">
      Wujudkan hari bahagia dengan rias, dekorasi, dan hidangan terbaik. Pilih paket yang sesuai, lalu konsultasikan langsung dengan kami tanpa perlu mendaftar.
    </p>
    <div class="mt-9 flex flex-col sm:flex-row gap-3 justify-center">
      <a href="#paket" class="bg-gold hover:bg-gold-dark text-burgundy-dark font-semibold px-8 py-3.5 rounded-full transition shadow-lg">Lihat Paket</a>
      <a href="#pesan" class="border border-gold/70 text-white hover:bg-white/10 px-8 py-3.5 rounded-full transition">Konsultasi Gratis</a>
    </div>
  </div>
</section>

<!-- ========== KATALOG PAKET ========== -->
<section id="paket" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
  <div class="text-center mb-10">
    <p class="text-gold-dark tracking-[0.3em] text-xs uppercase mb-3">Katalog Layanan</p>
    <h2 class="font-serif text-3xl sm:text-4xl text-burgundy">Paket Pilihan Kami</h2>
    <div class="gold-line mx-auto mt-5"></div>
  </div>

  <div class="flex flex-wrap justify-center gap-3 mb-12" id="filterTabs">
    <button data-filter="all" class="filter-btn tab-active px-6 py-2.5 rounded-full border border-gold/50 text-sm font-medium transition">Semua Paket</button>
    <button data-filter="wedding" class="filter-btn px-6 py-2.5 rounded-full border border-gold/50 text-sm font-medium text-burgundy bg-white transition hover:border-burgundy">Wedding</button>
    <button data-filter="catering" class="filter-btn px-6 py-2.5 rounded-full border border-gold/50 text-sm font-medium text-burgundy bg-white transition hover:border-burgundy">Catering</button>
  </div>

  <div id="weddingBlock" class="mb-16">
    <div class="flex items-center gap-4 mb-7">
      <h3 class="font-serif text-2xl text-burgundy">Paket Wedding</h3>
      <span class="flex-1 h-px bg-gradient-to-r from-gold/60 to-transparent"></span>
    </div>
    <div id="weddingGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6"></div>
  </div>

  <div id="cateringBlock">
    <div class="flex items-center gap-4 mb-7">
      <h3 class="font-serif text-2xl text-burgundy">Paket Catering</h3>
      <span class="flex-1 h-px bg-gradient-to-r from-gold/60 to-transparent"></span>
    </div>
    <div id="cateringGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6"></div>
  </div>
</section>

<!-- ========== TENTANG KAMI ========== -->
<section id="tentang" class="bg-white border-y border-gold/20">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 grid lg:grid-cols-2 gap-12 items-center">
    <div class="relative">
      <div class="absolute -top-4 -left-4 w-full h-full border-2 border-gold/50 rounded-2xl"></div>
      <div class="img-fallback relative rounded-2xl overflow-hidden aspect-[4/3]">
        <img src="https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&w=1000&q=70" alt="Tentang Ike's Gallery" class="w-full h-full object-cover" onerror="this.remove()">
      </div>
    </div>
    <div>
      <p class="text-gold-dark tracking-[0.3em] text-xs uppercase mb-3">Tentang Kami</p>
      <h2 class="font-serif text-3xl sm:text-4xl text-burgundy leading-snug">Merangkai Momen Berharga dengan Sentuhan Elegan</h2>
      <div class="gold-line my-5" style="margin-left:0;background:linear-gradient(90deg,#D4AF37,transparent)"></div>
      <p class="text-charcoal/80 leading-relaxed mb-4">
        Ike's Gallery hadir sebagai mitra terpercaya untuk pernikahan dan hajatan Anda, mulai dari rias pengantin, dekorasi, hingga katering yang menggugah selera. Setiap detail kami siapkan dengan teliti agar hari istimewa Anda berjalan lancar dan berkesan.
      </p>
      <div class="grid grid-cols-3 gap-4 mt-8">
        <div class="text-center p-4 rounded-xl bg-ivory border border-gold/30"><p class="font-serif text-3xl text-burgundy">500+</p><p class="text-xs text-charcoal/70 mt-1">Acara Sukses</p></div>
        <div class="text-center p-4 rounded-xl bg-ivory border border-gold/30"><p class="font-serif text-3xl text-burgundy">10+</p><p class="text-xs text-charcoal/70 mt-1">Tahun Pengalaman</p></div>
        <div class="text-center p-4 rounded-xl bg-ivory border border-gold/30"><p class="font-serif text-3xl text-burgundy">98%</p><p class="text-xs text-charcoal/70 mt-1">Klien Puas</p></div>
      </div>
    </div>
  </div>
</section>

<!-- ========== GALERI ========== -->
<section id="galeri" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
  <div class="text-center mb-10">
    <p class="text-gold-dark tracking-[0.3em] text-xs uppercase mb-3">Portofolio</p>
    <h2 class="font-serif text-3xl sm:text-4xl text-burgundy">Galeri Momen</h2>
    <div class="gold-line mx-auto mt-5"></div>
  </div>
  <div id="galleryGrid" class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4"></div>
</section>

<!-- ========== TESTIMONI ========== -->
<section id="testimoni" class="bg-burgundy relative overflow-hidden">
  <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-gold/10 blur-3xl"></div>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 relative">
    <div class="text-center mb-12">
      <p class="text-gold tracking-[0.3em] text-xs uppercase mb-3">Kata Mereka</p>
      <h2 class="font-serif text-3xl sm:text-4xl text-white">TESTIMONIALS &amp; REVIEWS</h2>
      <div class="gold-line mx-auto mt-5"></div>
    </div>
    <div id="testiGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"></div>
  </div>
</section>

<!-- ========== FORM PEMESANAN ========== -->
<section id="pesan" class="max-w-3xl mx-auto px-4 sm:px-6 py-20">
  <div class="text-center mb-10">
    <p class="text-gold-dark tracking-[0.3em] text-xs uppercase mb-3">Tanpa Daftar · Tanpa Login</p>
    <h2 class="font-serif text-3xl sm:text-4xl text-burgundy">Pemesanan &amp; Konsultasi</h2>
    <div class="gold-line mx-auto mt-5"></div>
    <p class="text-charcoal/70 mt-5 text-sm">Isi data singkat di bawah. Anda akan mendapat kode pemesanan dan tombol konsultasi WhatsApp.</p>
  </div>

  <div class="bg-white rounded-3xl shadow-xl shadow-burgundy/5 border border-gold/30 overflow-hidden">
    <div class="grid grid-cols-2 bg-ivory border-b border-gold/30">
      <button type="button" data-service="Wedding" class="service-btn tab-active py-4 font-medium text-sm tracking-wide transition">💍 Wedding</button>
      <button type="button" data-service="Catering" class="service-btn py-4 font-medium text-sm tracking-wide text-burgundy transition">🍽️ Catering</button>
    </div>

    <form id="bookingForm" method="POST" action="{{ route('pemesanan.store') }}" class="p-6 sm:p-10 space-y-5" novalidate>
      @csrf
      @if($errors->any())
        <div class="rounded-xl border border-red-300 bg-red-50 text-red-700 text-sm px-4 py-3">
          <p class="font-medium mb-1">Data belum dapat disimpan:</p>
          <ul class="list-disc pl-5">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
        </div>
      @endif
      <div>
        <label class="block text-sm font-medium text-burgundy mb-2" for="nama">Nama Lengkap <span class="text-red-600">*</span></label>
        <input id="nama" name="nama" value="{{ old('nama') }}" type="text" placeholder="Contoh: Ayu Pratiwi" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-ivory/50 text-sm transition" required>
        <p class="err hidden text-xs text-red-600 mt-1">Nama wajib diisi.</p>
      </div>
      <div>
        <label class="block text-sm font-medium text-burgundy mb-2" for="no_whatsapp">Nomor WhatsApp <span class="text-red-600">*</span></label>
        <input id="no_whatsapp" name="no_whatsapp" value="{{ old('no_whatsapp') }}" type="tel" placeholder="08xx-xxxx-xxxx" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-ivory/50 text-sm transition" required>
        <p class="err hidden text-xs text-red-600 mt-1">Masukkan nomor WhatsApp yang valid (contoh: 0812xxxxxxxx).</p>
      </div>
      <div class="grid sm:grid-cols-2 gap-5">
        <div>
          <label class="block text-sm font-medium text-burgundy mb-2" for="paket-select">Pilihan Paket <span class="text-red-600">*</span></label>
          <select id="paket-select" name="layanan_id" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-ivory/50 text-sm transition" required></select>
          <p class="err hidden text-xs text-red-600 mt-1">Silakan pilih paket.</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-burgundy mb-2" for="tanggal">Estimasi Tanggal Acara <span class="text-red-600">*</span></label>
          <input id="tanggal" name="tanggal_acara" value="{{ old('tanggal_acara') }}" type="date" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-ivory/50 text-sm transition" required>
          <p class="err hidden text-xs text-red-600 mt-1">Pilih tanggal acara.</p>
        </div>
      </div>
      <div id="jumlahWrap" class="hidden">
        <label class="block text-sm font-medium text-burgundy mb-2" for="jumlah">Jumlah (<span id="jumlah-unit">porsi</span>)</label>
        <input id="jumlah" name="jumlah" type="number" min="1" value="{{ old('jumlah', 1) }}" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-ivory/50 text-sm transition">
      </div>
      <div>
        <label class="block text-sm font-medium text-burgundy mb-2" for="lokasi_acara">Lokasi Acara <span class="text-charcoal/40 font-normal">(opsional)</span></label>
        <input id="lokasi_acara" name="lokasi_acara" value="{{ old('lokasi_acara') }}" type="text" placeholder="Nama gedung / alamat" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-ivory/50 text-sm transition">
      </div>
      <div>
        <label class="block text-sm font-medium text-burgundy mb-2" for="catatan">Catatan / Pesan Tambahan</label>
        <textarea id="catatan" name="catatan" rows="4" placeholder="Jumlah tamu, tema, atau kebutuhan khusus..." class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-ivory/50 text-sm transition resize-none">{{ old('catatan') }}</textarea>
      </div>

      <button type="submit" class="w-full flex items-center justify-center gap-3 bg-burgundy hover:bg-burgundy-light text-white font-semibold tracking-wider py-4 rounded-xl transition shadow-lg shadow-burgundy/30 border border-gold/40">
        <svg class="w-5 h-5 text-gold" fill="currentColor" viewBox="0 0 24 24"><path d="M17.5 14.4c-.3-.15-1.75-.86-2-.96-.27-.1-.47-.15-.67.15-.2.3-.77.96-.94 1.16-.17.2-.35.22-.65.07a8.2 8.2 0 01-2.4-1.48 9 9 0 01-1.66-2.07c-.17-.3 0-.46.13-.6.13-.14.3-.35.45-.52.15-.18.2-.3.3-.5.1-.2.05-.37-.03-.52-.07-.15-.67-1.6-.9-2.2-.24-.58-.49-.5-.67-.5h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.88 1.22 3.08c.15.2 2.1 3.2 5.08 4.49.7.3 1.26.49 1.69.62.7.23 1.35.2 1.86.12.57-.08 1.75-.72 2-1.41.25-.7.25-1.3.17-1.42-.07-.12-.27-.2-.57-.35zM12 2a10 10 0 00-8.6 15.1L2 22l5-1.3A10 10 0 1012 2z"/></svg>
        KIRIM PEMESANAN
      </button>
      <p class="text-center text-xs text-charcoal/50">Data Anda disimpan dan Anda akan mendapat kode pemesanan. Setelah itu Anda dapat berkonsultasi lewat WhatsApp.</p>
    </form>
  </div>
</section>

</main>

<!-- ========== FOOTER ========== -->
<footer class="bg-burgundy-dark text-white/80">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 grid md:grid-cols-3 gap-10">
    <div>
      <p class="font-serif text-2xl text-gold mb-3">Ike's Gallery</p>
      <p class="text-sm leading-relaxed text-white/70">Wedding &amp; Catering untuk setiap momen berharga Anda.</p>
    </div>
    <div class="text-sm space-y-2">
      <p class="font-medium text-white mb-3 tracking-wide">Kontak</p>
      <p>📞 +62-813-1871-3987</p>
      <p>✉️ ayantiwedding@gmail.com</p>
    </div>
    <div class="text-sm space-y-2">
      <p class="font-medium text-white mb-3 tracking-wide">Navigasi</p>
      <a href="#paket" class="block hover:text-gold transition">Paket</a>
      <a href="#tentang" class="block hover:text-gold transition">Tentang Kami</a>
      <a href="#galeri" class="block hover:text-gold transition">Galeri</a>
      <a href="#pesan" class="block hover:text-gold transition">Konsultasi</a>
    </div>
  </div>
  <div class="border-t border-white/10 py-5 text-center text-xs text-white/50">© <span id="year"></span> Ike's Gallery – Wedding &amp; Catering. All rights reserved.</div>
</footer>

<!-- Tombol WhatsApp melayang -->
<a id="floatWa" href="#pesan" aria-label="Konsultasi" class="fixed bottom-5 right-5 z-40 w-14 h-14 rounded-full bg-burgundy border-2 border-gold text-gold flex items-center justify-center shadow-xl hover:scale-110 transition">
  <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24"><path d="M17.5 14.4c-.3-.15-1.75-.86-2-.96-.27-.1-.47-.15-.67.15-.2.3-.77.96-.94 1.16-.17.2-.35.22-.65.07a8.2 8.2 0 01-2.4-1.48 9 9 0 01-1.66-2.07c-.17-.3 0-.46.13-.6.13-.14.3-.35.45-.52.15-.18.2-.3.3-.5.1-.2.05-.37-.03-.52-.07-.15-.67-1.6-.9-2.2-.24-.58-.49-.5-.67-.5h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.88 1.22 3.08c.15.2 2.1 3.2 5.08 4.49.7.3 1.26.49 1.69.62.7.23 1.35.2 1.86.12.57-.08 1.75-.72 2-1.41.25-.7.25-1.3.17-1.42-.07-.12-.27-.2-.57-.35zM12 2a10 10 0 00-8.6 15.1L2 22l5-1.3A10 10 0 1012 2z"/></svg>
</a>

<script>
/* ================= DATA KATALOG (dari tabel `layanan`) ================= */
const PACKAGES = @json($paket);

const GALLERY = ['photo-1519741497674-611481863552','photo-1465495976277-4387d4b0b4c6','photo-1511795409834-ef04bbd61622','photo-1464366400600-7168b8af9bc3','photo-1555244162-803834f70033','photo-1414235077428-338989a2e8c0','photo-1504674900247-0877df9cc836','photo-1555939594-58d7cb561ad1'];

const TESTIMONIALS = [
  { name:'Ayu & Rizky', date:'12 Agustus 2026', rating:5, text:'Rias dan dekorasinya cantik sekali, persis seperti yang kami impikan. Tim sangat ramah dan profesional dari awal sampai acara selesai.' },
  { name:'Siti Nurhaliza', date:'28 Juli 2026', rating:5, text:'Katering untuk acara lamaran anak kami enak semua, tamu banyak yang tambah. Penyajian rapi dan tepat waktu. Terima kasih Ike\'s Gallery!' },
  { name:'Dewi & Fajar', date:'3 Juni 2026', rating:5, text:'Paket Wedding Lengkap benar-benar membantu, semua urusan beres dalam satu tempat. Kami tinggal menikmati hari bahagia.' },
  { name:'Bapak Hendra', date:'19 Mei 2026', rating:5, text:'Prasmanan Gold untuk 400 tamu berjalan lancar. Variasi menu banyak dan rasanya konsisten. Sangat direkomendasikan.' },
  { name:'Rina Marlina', date:'21 April 2026', rating:4, text:'Dekorasi akad intimate-nya elegan dan harganya masuk akal. Komunikasinya cepat lewat WhatsApp, jadi mudah koordinasi.' },
  { name:'Putri & Bagas', date:'8 Maret 2026', rating:5, text:'Make-up tahan lama dan natural. Keluarga besar kami semua puas dengan hasilnya. Pasti akan merekomendasikan ke teman.' },
];

/* ================= HELPERS ================= */
const rupiah = n => new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',maximumFractionDigits:0}).format(n);
const unsplash = (id,w=800) => `https://images.unsplash.com/${id}?auto=format&fit=crop&w=${w}&q=70`;
const initials = n => n.split(/[\s&]+/).filter(Boolean).slice(0,2).map(s=>s[0]).join('').toUpperCase();
document.getElementById('year').textContent = new Date().getFullYear();

/* ================= NAVBAR MOBILE ================= */
const menuBtn = document.getElementById('menuBtn'), mobileMenu = document.getElementById('mobileMenu');
menuBtn.addEventListener('click',()=>mobileMenu.classList.toggle('hidden'));
document.querySelectorAll('.mobile-link').forEach(a=>{
  a.classList.add('text-charcoal','hover:text-burgundy');
  a.addEventListener('click',()=>mobileMenu.classList.add('hidden'));
});

/* ================= RENDER PAKET ================= */
function packageCard(p){
  return `
  <article class="card-lift bg-white rounded-2xl overflow-hidden border border-gold/20 shadow-md flex flex-col fade-in">
    <div class="img-fallback relative aspect-[4/3] overflow-hidden">
      ${p.img ? `<img src="${p.img}" alt="${p.name}" loading="lazy" class="w-full h-full object-cover" onerror="this.remove()">` : ''}
      ${p.badge ? `<span class="absolute top-3 left-3 bg-gold text-burgundy-dark text-[11px] font-semibold px-3 py-1 rounded-full shadow">${p.badge}</span>`:''}
    </div>
    <div class="p-5 flex flex-col flex-1">
      <h4 class="font-serif text-lg text-burgundy leading-snug">${p.name}</h4>
      <p class="text-sm text-charcoal/70 mt-2 leading-relaxed flex-1">${p.desc}</p>
      <div class="mt-4 pt-4 border-t border-gold/30 flex items-end justify-between gap-2">
        <div>
          <p class="text-[11px] uppercase tracking-wider text-charcoal/50">${p.type==='wedding'?'Mulai dari':'Harga'}</p>
          <p class="font-semibold text-burgundy">${rupiah(p.price)}<span class="text-xs font-normal text-charcoal/60"> ${p.unit||''}</span></p>
        </div>
        <button data-pick="${p.id}" class="pick-btn text-xs font-medium bg-burgundy hover:bg-burgundy-light text-white px-4 py-2 rounded-full transition">Pesan</button>
      </div>
    </div>
  </article>`;
}
function renderPackages(){
  document.getElementById('weddingGrid').innerHTML = PACKAGES.filter(p=>p.type==='wedding').map(packageCard).join('');
  document.getElementById('cateringGrid').innerHTML = PACKAGES.filter(p=>p.type==='catering').map(packageCard).join('');
}
renderPackages();

/* Filter */
document.querySelectorAll('.filter-btn').forEach(btn=>{
  btn.addEventListener('click',()=>{
    document.querySelectorAll('.filter-btn').forEach(b=>{
      b.classList.remove('tab-active'); b.classList.add('bg-white','text-burgundy');
    });
    btn.classList.add('tab-active'); btn.classList.remove('bg-white','text-burgundy');
    const f = btn.dataset.filter;
    document.getElementById('weddingBlock').classList.toggle('hidden', f==='catering');
    document.getElementById('cateringBlock').classList.toggle('hidden', f==='wedding');
  });
});

/* ================= GALERI ================= */
document.getElementById('galleryGrid').innerHTML = GALLERY.map((id,i)=>`
  <div class="img-fallback ${i%5===0?'row-span-2':''} rounded-xl overflow-hidden aspect-square ${i%5===0?'md:aspect-auto':''} group">
    <img src="${unsplash(id,600)}" alt="Galeri ${i+1}" loading="lazy" class="w-full h-full object-cover group-hover:scale-110 transition duration-700" onerror="this.remove()">
  </div>`).join('');

/* ================= TESTIMONI ================= */
const star = on => `<svg class="w-4 h-4 ${on?'text-gold':'text-gray-300'}" fill="currentColor" viewBox="0 0 20 20"><path d="M10 1.5l2.6 5.5 6 .8-4.4 4.2 1.1 6L10 15l-5.3 3 1.1-6L1.4 7.8l6-.8L10 1.5z"/></svg>`;
document.getElementById('testiGrid').innerHTML = TESTIMONIALS.map(t=>`
  <figure class="bg-white rounded-2xl p-6 shadow-lg border-t-4 border-gold flex flex-col">
    <svg class="w-8 h-8 text-gold/40 mb-3" fill="currentColor" viewBox="0 0 24 24"><path d="M9.5 6C6.5 7 4 10 4 14v4h6v-6H7c0-2 1-3.5 3-4.3L9.5 6zm10 0c-3 1-5.5 4-5.5 8v4h6v-6h-3c0-2 1-3.5 3-4.3L19.5 6z"/></svg>
    <blockquote class="text-sm text-charcoal/80 leading-relaxed flex-1">${t.text}</blockquote>
    <figcaption class="mt-5 pt-5 border-t border-gray-100 flex items-center gap-3">
      <span class="w-12 h-12 rounded-full bg-burgundy text-gold border-2 border-gold flex items-center justify-center font-serif font-semibold shrink-0">${initials(t.name)}</span>
      <span class="min-w-0">
        <span class="block font-medium text-burgundy text-sm truncate">${t.name}</span>
        <span class="flex items-center gap-0.5 mt-0.5">${[1,2,3,4,5].map(i=>star(i<=t.rating)).join('')}</span>
        <span class="block text-[11px] text-charcoal/50 mt-0.5">Acara: ${t.date}</span>
      </span>
    </figcaption>
  </figure>`).join('');

/* ================= FORM ================= */
let service = 'Wedding';
const paketSelect = document.getElementById('paket-select');

function fillPackages(type, selectedId){
  const list = PACKAGES.filter(p=>p.type===type.toLowerCase());
  paketSelect.innerHTML = `<option value="">— Pilih paket ${type} —</option>` +
    list.map(p=>`<option value="${p.id}" ${String(p.id)===String(selectedId)?'selected':''}>${p.name} (${rupiah(p.price)}${p.unit?' '+p.unit:''})</option>`).join('');
}
function setService(s, selectedId){
  service = s;
  document.querySelectorAll('.service-btn').forEach(b=>{
    const on = b.dataset.service===s;
    b.classList.toggle('tab-active', on);
    b.classList.toggle('text-burgundy', !on);
  });
  fillPackages(s, selectedId);
}
setService('Wedding');
document.querySelectorAll('.service-btn').forEach(b=>b.addEventListener('click',()=>setService(b.dataset.service)));

/* tombol "Pesan" di kartu → isi form otomatis */
document.addEventListener('click',e=>{
  const btn = e.target.closest('.pick-btn'); if(!btn) return;
  const p = PACKAGES.find(x=>String(x.id)===btn.dataset.pick);
  setService(p.type==='wedding'?'Wedding':'Catering', p.id);
  document.getElementById('pesan').scrollIntoView({behavior:'smooth'});
});

/* tanggal minimal = hari ini */
document.getElementById('tanggal').min = new Date().toISOString().split('T')[0];

/* validasi */
function setErr(el, show){
  el.classList.toggle('border-red-500', show);
  const msg = el.parentElement.querySelector('.err'); if(msg) msg.classList.toggle('hidden', !show);
}
const isPhone = v => /^(\+62|62|0)8[0-9\s\-]{8,14}$/.test(v.trim());

document.getElementById('bookingForm').addEventListener('submit', e=>{
  const nama = document.getElementById('nama'), wa = document.getElementById('no_whatsapp'), tanggal = document.getElementById('tanggal');
  const v1 = nama.value.trim().length>=3;  setErr(nama,!v1);
  const v2 = isPhone(wa.value);            setErr(wa,!v2);
  const v3 = !!paketSelect.value;          setErr(paketSelect,!v3);
  const v4 = !!tanggal.value;              setErr(tanggal,!v4);
  if(!(v1&&v2&&v3&&v4)) e.preventDefault(); // valid? Tidak -> tetap di form
  // valid? Ya -> form dikirim ke server (POST /pemesanan) dan disimpan ke database
});

/* jumlah porsi hanya relevan untuk Catering */
function toggleJumlah(){
  const p = PACKAGES.find(x=>String(x.id)===paketSelect.value);
  const wrap = document.getElementById('jumlahWrap');
  wrap.classList.toggle('hidden', !(p && p.type==='catering'));
  if(p){ document.getElementById('jumlah-unit').textContent = p.unit ? p.unit.replace('/','').trim() : 'porsi'; }
}
paketSelect.addEventListener('change', toggleJumlah);
document.addEventListener('click', e=>{ if(e.target.closest('.pick-btn')) setTimeout(toggleJumlah,0); });
document.querySelectorAll('.service-btn').forEach(b=>b.addEventListener('click',()=>setTimeout(toggleJumlah,0)));

/* isi ulang pilihan layanan jika server menolak data (validasi gagal) */
const OLD_LAYANAN = @json(old('layanan_id'));
if(OLD_LAYANAN){
  const p = PACKAGES.find(x=>String(x.id)===String(OLD_LAYANAN));
  if(p){ setService(p.type==='wedding'?'Wedding':'Catering', p.id); toggleJumlah(); document.getElementById('pesan').scrollIntoView(); }
}
</script>
</body>
</html>
