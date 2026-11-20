@extends('layouts.public')
@section('title', "Daftar – Ike's Gallery")
@section('content')
<section class="max-w-md mx-auto px-4 sm:px-6 py-16">
    <div class="card p-6 sm:p-8">
        <div class="text-center mb-6">
            <h1 class="font-serif text-2xl text-burgundy">Buat Akun</h1>
            <p class="text-xs text-charcoal/60 mt-1">Daftar untuk dapat mengirim testimoni.</p>
        </div>

        <form method="POST" action="{{ route('pelanggan.register.submit') }}" class="space-y-4">
            @csrf
            <div>
                <label class="lbl" for="nama">Nama Lengkap</label>
                <input id="nama" name="nama" value="{{ old('nama') }}" required autofocus class="inp">
                @error('nama')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="lbl" for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required class="inp">
                @error('email')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="lbl" for="password">Password</label>
                <input id="password" type="password" name="password" required class="inp" placeholder="Minimal 8 karakter">
                @error('password')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="lbl" for="password_confirmation">Ulangi Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required class="inp">
            </div>
            <button class="btn btn-p w-full py-3">Daftar</button>
        </form>

        <p class="text-center text-sm text-charcoal/70 mt-5">Sudah punya akun?
            <a href="{{ route('pelanggan.login') }}" class="text-burgundy font-medium hover:underline">Login</a>
        </p>
    </div>
</section>
@endsection