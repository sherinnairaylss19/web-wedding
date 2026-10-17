@extends('layouts.public')
@section('title', "Ringkasan Pemesanan – Ike's Gallery")
@section('eyebrow', 'Pemesanan Tersimpan')
@section('judul', 'Ringkasan Pemesanan')
@section('sub', 'Simpan kode pemesanan Anda, lalu lanjutkan konsultasi bersama tim kami melalui WhatsApp.')
@section('content')
@php $row = fn($a, $b) => '<div class="flex justify-between gap-6 py-2.5 border-b border-gray-100 text-sm"><dt class="text-charcoal/60">' . $a . '</dt><dd class="text-right font-medium">' . $b . '</dd></div>'; @endphp
<section class="max-w-3xl mx-auto px-4 sm:px-6 py-16">
    <div class="card overflow-hidden">
        <div class="bg-burgundy text-white text-center px-6 py-8">
            <span class="inline-flex w-14 h-14 rounded-full border-2 border-gold items-center justify-center text-gold text-2xl mb-3">✓</span>
            <p class="font-serif text-2xl">Pemesanan Berhasil Disimpan</p>
            <p class="text-white/70 text-sm mt-1">Terima kasih, {{ $p->klien->nama }}. Tim kami akan segera menghubungi Anda.</p>
        </div>
        <div class="p-6 sm:p-10">
            <div class="text-center mb-8">
                <p class="text-xs uppercase tracking-[0.3em] text-gold-dark mb-2">Kode Pemesanan</p>
                <button type="button" id="copy" class="font-mono text-2xl sm:text-3xl font-semibold text-burgundy border-2 border-dashed border-gold rounded-xl px-6 py-3 hover:bg-gold/10 transition">{{ $p->kode }}</button>
                <p class="text-xs text-charcoal/50 mt-2" id="hint">Klik kode untuk menyalin. Kode dipakai untuk mengirim testimoni setelah acara.</p>
            </div>
            <dl>
                {!! $row('Status Pemesanan', e($p->status_pemesanan)) !!}
                {!! $row('Nama Pemesan', e($p->klien->nama)) !!}
                {!! $row('Kontak', e($p->klien->no_hp)) !!}
                {!! $row('Layanan', $p->detail->map(fn($d) => e($d->layanan->nama) . ' × ' . $d->jumlah)->implode('<br>')) !!}
                {!! $row('Tanggal Acara', e(tgl_long($p->tanggal_acara))) !!}
                {!! $row('Lokasi Acara', e($p->lokasi_acara)) !!}
                {!! $row('Catatan', e($p->catatan ?: '-')) !!}
                <div class="flex justify-between items-center pt-4"><dt class="font-medium text-burgundy">Estimasi Total</dt><dd class="font-serif text-2xl text-burgundy">{{ rupiah($p->total_biaya) }}</dd></div>
            </dl>
            <div class="flex flex-col sm:flex-row gap-3 mt-8">
                <a href="{{ $p->waUrl() }}" target="_blank" rel="noopener" class="btn btn-p flex-1 py-3.5 border border-gold/40">💬 Konsultasi via WhatsApp</a>
                <a href="{{ route('beranda') }}" class="btn btn-o flex-1 py-3.5">Kembali ke Beranda</a>
            </div>
        </div>
    </div>
</section>
@endsection
@push('scripts')
<script>document.getElementById('copy').onclick = () => { navigator.clipboard && navigator.clipboard.writeText('{{ $p->kode }}'); document.getElementById('hint').textContent = 'Kode disalin ✓'; };</script>
@endpush
