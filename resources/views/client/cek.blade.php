@extends('layouts.public')
@section('title', "Cek Pesanan – Ike's Gallery")
@section('eyebrow', 'Lacak Pesanan')
@section('judul', 'Cek Status Pemesanan')
@section('sub', 'Masukkan kode pemesanan dan nomor WhatsApp yang Anda daftarkan untuk melihat perkembangan pesanan Anda.')
@section('content')
<section class="max-w-xl mx-auto px-4 sm:px-6 py-16">
    <form method="POST" action="{{ route('pemesanan.cek.proses') }}" class="card p-6 sm:p-10 space-y-5">
        @csrf
        @error('kode')<div class="rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3" role="alert">{{ $message }}</div>@enderror
        <div>
            <label class="lbl" for="kode">Kode Pemesanan <span class="text-red-600">*</span></label>
            <input id="kode" name="kode" value="{{ old('kode') }}" class="inp font-mono uppercase" placeholder="IKG-2026-001" autocomplete="off" required>
        </div>
        <div>
            <label class="lbl" for="no_hp">Nomor WhatsApp <span class="text-red-600">*</span></label>
            <input id="no_hp" name="no_hp" type="tel" value="{{ old('no_hp') }}" class="inp {{ $errors->has('no_hp') ? 'border-red-500' : '' }}" placeholder="08xx-xxxx-xxxx" required>
            @error('no_hp')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            <p class="text-xs text-charcoal/50 mt-1">Gunakan nomor yang sama dengan saat memesan. Format 08…, +62…, atau 62… sama-sama diterima.</p>
        </div>
        <button class="btn btn-p w-full py-3.5 border border-gold/40">CEK STATUS</button>
        <p class="text-xs text-center text-charcoal/60">Lupa kode pemesanan? <a href="https://wa.me/{{ config('ikes.wa_number') }}" target="_blank" rel="noopener" class="text-burgundy underline">Hubungi kami via WhatsApp</a>.</p>
    </form>
</section>
@endsection
