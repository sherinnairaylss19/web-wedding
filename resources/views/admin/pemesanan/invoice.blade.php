@extends('layouts.admin')
@section('judul', 'Invoice Digital')
@section('content')
@php $dibayar = $p->terbayar(); $sisa = $p->sisa(); $lunas = $p->status_pembayaran === 'Lunas'; @endphp
<div class="max-w-3xl mx-auto">
    <div class="no-print flex flex-wrap justify-between gap-3 mb-5"><a href="{{ route('admin.pemesanan.show', $p) }}" class="text-sm text-gold-dark hover:text-burgundy self-center">← Detail pemesanan</a><button onclick="window.print()" class="btn btn-p">🖨️ Cetak Invoice</button></div>
    <div class="card print-area p-8 sm:p-12 bg-white">
        <div class="flex flex-wrap justify-between gap-6 pb-6 border-b-2 border-gold">
            <div class="flex items-center gap-3"><span class="w-14 h-14 rounded-full border-2 border-gold flex items-center justify-center bg-burgundy text-gold font-display text-3xl font-bold">I</span>
                <span><span class="block font-serif text-2xl text-burgundy">Ike's Gallery</span><span class="block text-[10px] tracking-[0.28em] uppercase text-gold-dark">Wedding &amp; Catering</span><span class="block text-xs text-charcoal/60 mt-1">{{ config('ikes.phone') }} · {{ config('ikes.email') }}</span></span></div>
            <div class="text-right"><p class="font-serif text-3xl text-burgundy tracking-widest">INVOICE</p><p class="font-mono text-sm mt-1">{{ $inv->nomor_invoice }}</p><p class="text-xs text-charcoal/60">Tanggal: {{ tgl($inv->tanggal_terbit) }}</p>
                <span class="inline-block mt-2 px-4 py-1 rounded-full text-xs font-semibold border {{ $lunas ? 'border-green-600 text-green-700' : 'border-amber-600 text-amber-700' }}">{{ $lunas ? 'LUNAS' : 'BELUM LUNAS' }}</span></div>
        </div>
        <div class="grid sm:grid-cols-2 gap-6 py-6 text-sm">
            <div><p class="text-xs uppercase tracking-wider text-charcoal/50 mb-1">Ditagihkan Kepada</p><p class="font-semibold text-burgundy">{{ $p->klien->nama }}</p><p>{{ $p->klien->no_hp }}</p><p class="text-charcoal/70">{{ $p->klien->alamat }}</p></div>
            <div><p class="text-xs uppercase tracking-wider text-charcoal/50 mb-1">Detail Acara</p><p>Kode: <span class="font-mono">{{ $p->kode }}</span></p><p>Tanggal: {{ tgl_long($p->tanggal_acara) }}</p><p>Lokasi: {{ $p->lokasi_acara }}</p></div>
        </div>
        <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Layanan</th><th>Jumlah</th><th>Harga</th><th class="text-right">Subtotal</th></tr></thead><tbody>
            @foreach ($p->detail as $d)<tr><td>{{ $d->layanan->nama }}</td><td>{{ $d->jumlah }}</td><td>{{ rupiah($d->harga_satuan) }}</td><td class="text-right">{{ rupiah($d->subtotal) }}</td></tr>@endforeach</tbody></table></div>
        <div class="flex justify-end mt-4"><dl class="w-full sm:w-72 text-sm space-y-2">
            <div class="flex justify-between"><dt class="text-charcoal/60">Total</dt><dd>{{ rupiah($p->total_biaya) }}</dd></div>
            @foreach ($p->pembayaran as $x)<div class="flex justify-between text-xs"><dt class="text-charcoal/60">{{ $x->jenis_pembayaran }} ({{ $x->metode_bayar }}) · {{ tgl($x->tanggal_bayar) }}</dt><dd>− {{ rupiah($x->nominal) }}</dd></div>@endforeach
            <div class="flex justify-between pt-3 border-t-2 border-gold"><dt class="font-semibold text-burgundy">Sisa Tagihan</dt><dd class="font-serif text-xl text-burgundy">{{ rupiah($sisa) }}</dd></div></dl></div>
        <div class="flex justify-between items-end mt-14 text-xs text-charcoal/60"><p class="max-w-xs">Terima kasih telah mempercayakan hari istimewa Anda kepada Ike's Gallery.</p>
            <div class="text-center"><p>Hormat kami,</p><div class="h-14"></div><p class="font-semibold text-burgundy border-t border-charcoal/30 pt-1 px-6">Ike's Gallery</p></div></div>
    </div>
</div>
@endsection
