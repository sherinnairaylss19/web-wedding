@extends('layouts.public')
@section('title', "Login – Ike's Gallery")
@section('content')
<section class="max-w-md mx-auto px-4 sm:px-6 py-16">
    <div class="card p-6 sm:p-8">
        <div class="text-center mb-6">
            <h1 class="font-serif text-2xl text-burgundy">Login</h1>
            <p class="text-xs text-charcoal/60 mt-1">Masuk untuk mengirim testimoni.</p>
        </div>

        @if (session('info'))
            <div class="rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs px-4 py-3 mb-4">{{ session('info') }}</div>
        @endif

        <form method="POST" action="{{ route('pelanggan.login.submit') }}" class="space-y-4">
            @csrf
            <div>
                <label class="lbl" for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="inp">
                @error('email')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="lbl" for="password">Password</label>
                <input id="password" type="password" name="password" required class="inp">
                @error('password')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember"> Ingat saya</label>
            <button class="btn btn-p w-full py-3">Masuk</button>
        </form>

        <p class="text-center text-sm text-charcoal/70 mt-5">Belum punya akun?
            <a href="{{ route('pelanggan.register') }}" class="text-burgundy font-medium hover:underline">Daftar</a>
        </p>
    </div>
</section>
@endsection