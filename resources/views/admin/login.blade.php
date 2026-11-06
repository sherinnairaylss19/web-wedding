<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login Admin – Ike's Gallery</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<script>
  tailwind.config = { theme: { extend: {
    colors: { burgundy: { DEFAULT:'#4A0E17', dark:'#33090F' }, gold: { DEFAULT:'#D4AF37', dark:'#B8932A' }, ivory:'#FDFBF7' },
    fontFamily: { serif:['"Playfair Display"','serif'], sans:['Poppins','sans-serif'] } } } }
</script>
</head>
<body class="min-h-screen bg-burgundy flex items-center justify-center px-4 font-sans">
<form method="POST" action="{{ url('admin/login') }}" class="w-full max-w-sm bg-ivory rounded-2xl shadow-2xl p-8 space-y-5">
  @csrf
  <div class="text-center">
    <h1 class="font-serif text-2xl text-burgundy">Ike's Gallery</h1>
    <p class="text-xs tracking-[0.3em] uppercase text-gold-dark mt-1">Login Admin</p>
  </div>
  <div>
    <label class="block text-sm mb-1">Email</label>
    <input type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm">
    @error('email')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
  </div>
  <div>
    <label class="block text-sm mb-1">Password</label>
    <input type="password" name="password" required class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm">
  </div>
  <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember"> Ingat saya</label>
  <button class="w-full bg-gold hover:bg-gold-dark text-burgundy-dark font-semibold py-2.5 rounded-full">Masuk</button>
</form>
</body>
</html>
