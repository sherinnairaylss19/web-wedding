@extends('layouts.admin')
@section('judul', 'Invoice Digital')
@section('content')
@php
    // Isi tagihan dibaca dari snapshot saat invoice terbit (tidak ikut berubah bila pesanan diedit). Pembayaran tetap terbaru.
    $r = $inv->rincian ?? \App\Models\Invoice::potretTagihan($p);
    $dibayar = $p->terbayar();
    $totalInv = (int) $r['total'];
    $sisa = max(0, $totalInv - $dibayar);
    $lunas = $totalInv > 0 && $dibayar >= $totalInv;
    $lama = ! $inv->is($terbaru);
@endphp
<div class="max-w-3xl mx-auto">
    <div class="no-print flex flex-wrap justify-between gap-3 mb-5"><a href="{{ route('admin.pemesanan.show', $p) }}" class="text-sm text-gold-dark hover:text-burgundy self-center">← Detail pemesanan</a><button onclick="window.print()" class="btn btn-p">🖨️ Cetak Invoice</button></div>

    @if ($semua->count() > 1)
    <div class="no-print flex flex-wrap items-center gap-2 mb-4 text-xs"><span class="text-charcoal/60">Versi invoice:</span>
        @foreach ($semua as $v)<a href="{{ route('admin.pemesanan.invoice', [$p, 'revisi' => $v->revisi]) }}" class="px-3 py-1 rounded-full border {{ $v->is($inv) ? 'border-burgundy text-burgundy font-semibold' : 'border-charcoal/20 text-charcoal/60 hover:text-burgundy' }}">{{ $v->revisi === 0 ? 'Awal' : 'Revisi ' . $v->revisi }}</a>@endforeach
    </div>
    @endif

    @if ($usang)
    <div class="no-print mb-5 rounded-xl bg-amber-50 border border-amber-300 text-amber-800 text-sm px-5 py-4">
        <p class="font-semibold">Rincian pesanan sudah berubah sejak invoice ini diterbitkan.</p>
        <p class="text-xs mt-1">Invoice di bawah tetap menampilkan tagihan saat diterbitkan ({{ rupiah($r['total']) }}), sedangkan total pesanan saat ini {{ rupiah($p->total_biaya) }}. Terbitkan revisi agar klien menerima angka terbaru; invoice lama tetap tersimpan.</p>
        <form method="POST" action="{{ route('admin.pemesanan.invoice.revisi', $p) }}" class="mt-3 flex flex-wrap gap-2">@csrf
            <input name="alasan_revisi" maxlength="255" required class="inp flex-1 min-w-[14rem]" placeholder="Alasan revisi, contoh: Penambahan layanan dekorasi">
            <button class="btn btn-p btn-sm">Terbitkan Revisi Invoice</button></form>
    </div>
    @elseif ($lama)
    <div class="no-print mb-5 rounded-xl bg-amber-50 border border-amber-300 text-amber-800 text-sm px-5 py-3">Ini versi lama invoice. <a href="{{ route('admin.pemesanan.invoice', $p) }}" class="underline font-semibold">Lihat versi terbaru ({{ $terbaru->nomor_invoice }})</a></div>
    @endif

    <div class="card print-area p-8 sm:p-12 bg-white">
        <div class="flex flex-wrap justify-between gap-6 pb-6 border-b-2 border-gold">
            <div class="flex items-center gap-3"><span class="w-14 h-14 rounded-full border-2 border-gold flex items-center justify-center bg-burgundy text-gold font-display text-3xl font-bold">I</span>
                <span><span class="block font-serif text-2xl text-burgundy">Ike's Gallery</span><span class="block text-[10px] tracking-[0.28em] uppercase text-gold-dark">Wedding &amp; Catering</span><span class="block text-xs text-charcoal/60 mt-1">{{ config('ikes.phone') }} · {{ config('ikes.email') }}</span></span></div>
            <div class="text-right"><p class="font-serif text-3xl text-burgundy tracking-widest">INVOICE</p><p class="font-mono text-sm mt-1">{{ $inv->nomor_invoice }}</p><p class="text-xs text-charcoal/60">Tanggal: {{ tgl($inv->tanggal_terbit) }}</p>
                @if ($inv->revisi > 0)<p class="text-xs text-charcoal/60">Revisi ke-{{ $inv->revisi }}@if ($inv->alasan) · {{ $inv->alasan }}@endif</p>@endif
                @if ($lama)<p class="text-xs font-semibold text-red-700">Digantikan oleh {{ $terbaru->nomor_invoice }}</p>@endif
                <span class="inline-block mt-2 px-4 py-1 rounded-full text-xs font-semibold border {{ $lunas ? 'border-green-600 text-green-700' : 'border-amber-600 text-amber-700' }}">{{ $lunas ? 'LUNAS' : 'BELUM LUNAS' }}</span></div>
        </div>
        <div class="grid sm:grid-cols-2 gap-6 py-6 text-sm">
            <div><p class="text-xs uppercase tracking-wider text-charcoal/50 mb-1">Ditagihkan Kepada</p><p class="font-semibold text-burgundy">{{ $r['klien']['nama'] }}</p><p>{{ $r['klien']['no_hp'] }}</p><p class="text-charcoal/70">{{ $r['klien']['alamat'] }}</p></div>
            <div><p class="text-xs uppercase tracking-wider text-charcoal/50 mb-1">Detail Acara</p><p>Kode: <span class="font-mono">{{ $p->kode }}</span></p><p>Tanggal: {{ tgl_long($p->tanggal_acara) }}</p><p>Lokasi: {{ $p->lokasi_acara }}</p></div>
        </div>
        <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Layanan</th><th>Jumlah</th><th>Harga</th><th class="text-right">Subtotal</th></tr></thead><tbody>
            @foreach ($r['items'] as $i)<tr><td>{{ $i['nama'] }}</td><td>{{ $i['jumlah'] }}</td><td>{{ rupiah($i['harga_satuan']) }}</td><td class="text-right">{{ rupiah($i['subtotal']) }}</td></tr>@endforeach</tbody></table></div>
        <div class="flex justify-end mt-4"><dl class="w-full sm:w-72 text-sm space-y-2">
            @if ($r['diskon'] > 0)<div class="flex justify-between"><dt class="text-charcoal/60">Subtotal</dt><dd>{{ rupiah($r['subtotal']) }}</dd></div><div class="flex justify-between"><dt class="text-charcoal/60">Diskon</dt><dd>− {{ rupiah($r['diskon']) }}</dd></div>@endif
            <div class="flex justify-between"><dt class="text-charcoal/60">Total</dt><dd>{{ rupiah($totalInv) }}</dd></div>
            @foreach ($p->pembayaran as $x)<div class="flex justify-between text-xs"><dt class="text-charcoal/60">{{ $x->jenis_pembayaran }} ({{ $x->metode_bayar }}) · {{ tgl($x->tanggal_bayar) }}</dt><dd>− {{ rupiah($x->nominal) }}</dd></div>@endforeach
            <div class="flex justify-between pt-3 border-t-2 border-gold"><dt class="font-semibold text-burgundy">Sisa Tagihan</dt><dd class="font-serif text-xl text-burgundy">{{ rupiah($sisa) }}</dd></div></dl></div>
        <div class="flex justify-between items-end mt-14 text-xs text-charcoal/60"><p class="max-w-xs">Terima kasih telah mempercayakan hari istimewa Anda kepada Ike's Gallery.</p>
            <div class="text-center"><p>Hormat kami,</p><div class="h-14"></div><p class="font-semibold text-burgundy border-t border-charcoal/30 pt-1 px-6">Ike's Gallery</p></div></div>
    </div>
</div>
@endsection
