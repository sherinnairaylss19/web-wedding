@extends('layouts.public')
@section('title', "Status Pesanan {$p->kode} – Ike's Gallery")
@section('eyebrow', 'Status Pesanan')
@section('judul', 'Perkembangan Pesanan Anda')
@section('sub', 'Halaman ini hanya bisa dibuka sementara demi keamanan data Anda. Untuk melihatnya lagi, gunakan menu Cek Pesanan.')
@section('content')
@php
    $bayar = (int) $p->pembayaran->sum('nominal');
    $sisa = max(0, $p->total_biaya - $bayar);
    $kembali = (int) $p->pengembalian->sum('nominal');
    $row = fn($a, $b) => '<div class="flex justify-between gap-6 py-2.5 border-b border-gray-100 text-sm"><dt class="text-charcoal/60">' . $a . '</dt><dd class="text-right font-medium">' . $b . '</dd></div>';
@endphp
<section class="max-w-3xl mx-auto px-4 sm:px-6 py-16 space-y-6">
    <div class="card overflow-hidden">
        <div class="bg-burgundy text-white text-center px-6 py-8">
            <p class="text-xs uppercase tracking-[0.3em] text-gold mb-2">Kode Pemesanan</p>
            <p class="font-mono text-2xl sm:text-3xl font-semibold">{{ $p->kode }}</p>
            <div class="mt-4 flex flex-wrap justify-center gap-2"><x-badge :t="$p->status_pemesanan" /> <x-badge :t="$p->status_pembayaran" /></div>
        </div>
        <div class="p-6 sm:p-10">
            <dl>
                {!! $row('Nama Pemesan', e($p->klien->nama)) !!}
                {!! $row('Layanan', $p->detail->map(fn($d) => e($d->layanan->nama) . ' × ' . $d->jumlah)->implode('<br>')) !!}
                {!! $row('Tanggal Acara', e(tgl_long($p->tanggal_acara))) !!}
                {!! $row('Lokasi Acara', e($p->lokasi_acara)) !!}
                @if ($p->diskon > 0){!! $row('Diskon', '− ' . e(rupiah($p->diskon))) !!}@endif
                {!! $row('Total Biaya', e(rupiah($p->total_biaya))) !!}
                {!! $row('Sudah Dibayar', '<span class="text-green-700">' . e(rupiah($bayar)) . '</span>') !!}
                <div class="flex justify-between items-center pt-4"><dt class="font-medium text-burgundy">Sisa Tagihan</dt><dd class="font-serif text-2xl text-burgundy">{{ rupiah($sisa) }}</dd></div>
            </dl>
            @if ($p->status_pemesanan === 'Dibatalkan')
                @if ($bayar > 0)<p class="mt-5 rounded-xl bg-ivory border border-gold/30 p-4 text-xs text-charcoal/70">Pesanan ini dibatalkan. Dana yang sudah dikembalikan kepada Anda: <strong class="text-burgundy">{{ rupiah($kembali) }}</strong>. Untuk pertanyaan seputar pengembalian dana, silakan hubungi kami lewat WhatsApp.</p>@endif
            @elseif ($sisa > 0)
                <div class="mt-5 rounded-xl bg-ivory border border-gold/30 p-4 text-xs space-y-1.5">
                    <div class="flex justify-between gap-4"><span class="text-charcoal/60">DP ({{ config('ikes.dp_persen') }}%)</span><span>{{ rupiah($p->dpTarget()) }}@if ($bayar > 0 && $bayar >= $p->dpTarget()) <span class="text-green-700">✓ terpenuhi</span>@endif</span></div>
                    <div class="flex justify-between gap-4"><span class="text-charcoal/60">Batas pelunasan</span><span class="{{ $p->sudahJatuhTempo() ? 'text-red-700 font-semibold' : '' }}">{{ tgl($p->batasPelunasan()) }}@if ($p->sudahJatuhTempo()) · sudah jatuh tempo @endif</span></div>
                </div>
            @endif
        </div>
    </div>

    @if ($p->pembayaran->isNotEmpty())
    <div class="card overflow-hidden">
        <div class="px-6 py-4 border-b border-gold/20"><h2 class="font-serif text-lg text-burgundy">Riwayat Pembayaran</h2></div>
        <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Tanggal</th><th>Jenis</th><th>Metode</th><th class="text-right">Nominal</th></tr></thead><tbody>
            @foreach ($p->pembayaran as $x)<tr><td>{{ tgl($x->tanggal_bayar) }}</td><td>{{ $x->jenis_pembayaran }}</td><td>{{ $x->metode_bayar }}</td><td class="text-right">{{ rupiah($x->nominal) }}</td></tr>@endforeach
        </tbody></table></div>
    </div>
    @endif

    @if ($p->reschedule->isNotEmpty())
    <div class="card overflow-hidden">
        <div class="px-6 py-4 border-b border-gold/20"><h2 class="font-serif text-lg text-burgundy">Perubahan Tanggal Acara</h2></div>
        <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Diubah</th><th>Tanggal Lama</th><th>Tanggal Baru</th><th>Alasan</th></tr></thead><tbody>
            @foreach ($p->reschedule as $x)<tr><td>{{ tgl($x->created_at) }}</td><td>{{ tgl($x->tanggal_lama) }}</td><td>{{ tgl($x->tanggal_baru) }}</td><td>{{ $x->alasan }}</td></tr>@endforeach
        </tbody></table></div>
    </div>
    @endif

    <div class="flex flex-col sm:flex-row gap-3">
        <a href="https://wa.me/{{ config('ikes.wa_number') }}?text={{ rawurlencode("Halo Ike's Gallery, saya ingin menanyakan pesanan dengan kode {$p->kode}.") }}" target="_blank" rel="noopener" class="btn btn-p flex-1 py-3.5 border border-gold/40">💬 Tanya via WhatsApp</a>
        @if ($p->status_pemesanan === 'Selesai' && ! $p->testimoni)
            <a href="{{ route('testimoni', ['kode' => $p->kode]) }}" class="btn btn-o flex-1 py-3.5">✍️ Kirim Testimoni</a>
        @endif
        <a href="{{ route('beranda') }}" class="btn btn-o flex-1 py-3.5">Kembali ke Beranda</a>
    </div>
</section>
@endsection
