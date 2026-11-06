<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'Admin') – Ike's Gallery</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<script>
  tailwind.config = { theme: { extend: {
    colors: { burgundy: { DEFAULT:'#4A0E17', light:'#6B1A27', dark:'#33090F' }, gold: { DEFAULT:'#D4AF37', dark:'#B8932A' }, ivory:'#FDFBF7' },
    fontFamily: { serif:['"Playfair Display"','serif'], sans:['Poppins','sans-serif'] } } } }
</script>
</head>
<body class="bg-ivory font-sans text-gray-800 min-h-screen">
<header class="bg-burgundy text-white">
  <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
    <a href="{{ route('admin.dashboard') }}" class="font-serif text-xl text-gold">Ike's Gallery <span class="text-white/70 text-sm font-sans">Admin</span></a>
    <nav class="flex items-center gap-5 text-sm">
      <a href="{{ route('admin.layanan.index') }}" class="hover:text-gold">Layanan</a>
      <form method="POST" action="{{ route('admin.logout') }}">@csrf
        <button class="bg-gold text-burgundy-dark font-semibold px-4 py-1.5 rounded-full hover:bg-gold-dark">Keluar</button>
      </form>
    </nav>
  </div>
</header>
<main class="max-w-6xl mx-auto px-4 py-8">
  @if(session('sukses'))
    <div class="mb-5 rounded-lg border border-green-300 bg-green-50 text-green-800 px-4 py-3 text-sm">{{ session('sukses') }}</div>
  @endif
  @yield('content')
</main>
</body>
</html>
