<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Login Admin – Ike's Gallery</title><x-tw /></head>
<body class="antialiased">
<main class="min-h-screen relative overflow-hidden bg-burgundy flex items-center justify-center px-4 py-12">
    <div class="absolute inset-0 bg-gradient-to-b from-burgundy-dark/80 via-burgundy to-burgundy-dark"></div>
    <div class="relative w-full max-w-md">
        <a href="{{ route('beranda') }}" class="flex flex-col items-center text-center mb-8">
            <span class="w-16 h-16 rounded-full border-2 border-gold flex items-center justify-center bg-burgundy-dark text-gold font-display text-4xl font-bold">I</span>
            <span class="font-serif text-2xl text-white mt-3">Ike's Gallery</span><span class="text-[10px] tracking-[0.3em] uppercase text-gold">Wedding &amp; Catering</span>
        </a>
        <div class="bg-white rounded-3xl shadow-2xl border border-gold/40 p-8 sm:p-10">
            <h1 class="font-serif text-2xl text-burgundy text-center">Login Admin</h1><div class="gold-line mx-auto my-4"></div>
            <p class="text-center text-xs text-charcoal/60 mb-6">Halaman ini khusus untuk owner / admin Ike's Gallery.</p>
            <form method="POST" action="{{ route('admin.login.attempt') }}" class="space-y-5">
                @csrf
                <div><label class="lbl" for="email">Email</label><input id="email" name="email" type="email" value="{{ old('email') }}" class="inp" autocomplete="username" required autofocus></div>
                <div><label class="lbl" for="password">Password</label><input id="password" name="password" type="password" class="inp" autocomplete="current-password" required></div>
                @if ($errors->any())<p class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-4 py-2.5">{{ $errors->first() }}</p>@endif
                <button class="btn btn-p w-full py-3.5 tracking-wider border border-gold/40">MASUK</button>
            </form>
        </div>
        <a href="{{ route('beranda') }}" class="block text-center text-sm text-white/70 hover:text-gold mt-6 transition">← Kembali ke website</a>
    </div>
</main>
</body>
</html>
