@extends('layouts.admin')
@section('judul', 'Detail Pemesanan')
@section('content')
@php
    $k = $p->klien; $sisa = $p->sisa(); $bayar = $p->terbayar();
    $info = fn($a, $b) => '<div><dt class="text-xs text-charcoal/50 uppercase tracking-wider">' . $a . '</dt><dd class="font-medium mt-0.5">' . $b . '</dd></div>';
@endphp
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div><a href="{{ route('admin.pemesanan.index') }}" class="text-sm text-gold-dark hover:text-burgundy">← Daftar pemesanan</a>
        <p class="font-mono text-2xl text-burgundy mt-1">{{ $p->kode }} <span class="align-middle ml-2"><x-badge :t="$p->status_pemesanan" /> <x-badge :t="$p->status_pembayaran" /></span></p></div>
    <div class="flex flex-wrap gap-2">
        <button type="button" onclick="openModal('mPay')" class="btn btn-p btn-sm">💳 Catat Pembayaran</button>
        <button type="button" onclick="openModal('mRes')" class="btn btn-o btn-sm">📅 Ubah Tanggal</button>
        <a href="{{ route('admin.pemesanan.invoice', $p) }}" class="btn btn-o btn-sm">🧾 Invoice</a>
        <a target="_blank" rel="noopener" href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/\D/', '', $k->no_hp)) }}" class="btn btn-o btn-sm">💬 WhatsApp Klien</a>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6 items-start">
<div class="lg:col-span-2 space-y-6">
    <div class="card overflow-hidden"><div class="px-6 py-4 border-b border-gold/20"><h2 class="font-serif text-lg text-burgundy">Informasi Klien &amp; Acara</h2></div>
        <dl class="p-6 grid sm:grid-cols-2 gap-5 text-sm">
            {!! $info('Nama', e($k->nama)) !!}{!! $info('Kontak', e($k->no_hp)) !!}{!! $info('Alamat', e($k->alamat ?: '-')) !!}
            {!! $info('Tanggal Acara', e(tgl_long($p->tanggal_acara))) !!}{!! $info('Lokasi Acara', e($p->lokasi_acara)) !!}{!! $info('Dipesan Pada', e(tgl($p->created_at))) !!}
            <div class="sm:col-span-2">{!! $info('Catatan', e($p->catatan ?: '-')) !!}</div>
        </dl></div>

    <div class="card overflow-hidden"><div class="px-6 py-4 border-b border-gold/20"><h2 class="font-serif text-lg text-burgundy">Rincian Layanan</h2></div>
        <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Layanan</th><th>Jumlah</th><th>Harga Satuan</th><th class="text-right">Subtotal</th></tr></thead><tbody>
            @foreach ($p->detail as $d)<tr><td>{{ $d->layanan->nama }}</td><td>{{ $d->jumlah }}</td><td>{{ rupiah($d->harga_satuan) }}</td><td class="text-right">{{ rupiah($d->subtotal) }}</td></tr>@endforeach
        </tbody></table></div></div>

    <div class="card overflow-hidden"><div class="px-6 py-4 border-b border-gold/20"><h2 class="font-serif text-lg text-burgundy">Riwayat Pembayaran</h2></div>
        @if ($p->pembayaran->isEmpty())<p class="p-6 text-sm text-charcoal/50">Belum ada pembayaran.</p>@else
        <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Tanggal</th><th>Jenis</th><th>Metode</th><th class="text-right">Nominal</th></tr></thead><tbody>
            @foreach ($p->pembayaran as $x)<tr><td>{{ tgl($x->tanggal_bayar) }}</td><td>{{ $x->jenis_pembayaran }}</td><td>{{ $x->metode_bayar }}</td><td class="text-right">{{ rupiah($x->nominal) }}</td></tr>@endforeach
        </tbody></table></div>@endif</div>

    <div class="card overflow-hidden"><div class="px-6 py-4 border-b border-gold/20"><h2 class="font-serif text-lg text-burgundy">Riwayat Perubahan Tanggal</h2></div>
        @if ($p->reschedule->isEmpty())<p class="p-6 text-sm text-charcoal/50">Tanggal acara belum pernah diubah.</p>@else
        <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Diubah</th><th>Tanggal Lama</th><th>Tanggal Baru</th><th>Alasan</th><th>Oleh</th></tr></thead><tbody>
            @foreach ($p->reschedule as $x)<tr><td>{{ tgl($x->created_at) }}</td><td>{{ tgl($x->tanggal_lama) }}</td><td>{{ tgl($x->tanggal_baru) }}</td><td>{{ $x->alasan }}</td><td>{{ $x->admin->nama }}</td></tr>@endforeach
        </tbody></table></div>@endif</div>
</div>

<div class="space-y-6">
    <div class="card overflow-hidden"><div class="px-6 py-4 border-b border-gold/20"><h2 class="font-serif text-lg text-burgundy">Status Pemesanan</h2></div>
        <form method="POST" action="{{ route('admin.pemesanan.status', $p) }}" class="p-6 space-y-3">@csrf @method('PATCH')
            <select name="status_pemesanan" class="inp">@foreach (['Menunggu Konfirmasi', 'Dikonfirmasi', 'Selesai', 'Dibatalkan'] as $s)<option @selected($s === $p->status_pemesanan)>{{ $s }}</option>@endforeach</select>
            <button class="btn btn-p w-full">Perbarui Status</button></form></div>
    <div class="card overflow-hidden"><div class="px-6 py-4 border-b border-gold/20"><h2 class="font-serif text-lg text-burgundy">Ringkasan Tagihan</h2></div>
        <dl class="p-6 text-sm space-y-3">
            <div class="flex justify-between"><dt class="text-charcoal/60">Total Biaya</dt><dd>{{ rupiah($p->total_biaya) }}</dd></div>
            <div class="flex justify-between"><dt class="text-charcoal/60">Sudah Dibayar</dt><dd class="text-green-700">{{ rupiah($bayar) }}</dd></div>
            <div class="flex justify-between pt-3 border-t border-gold/30"><dt class="font-medium text-burgundy">Sisa Tagihan</dt><dd class="font-serif text-xl text-burgundy">{{ rupiah($sisa) }}</dd></div></dl></div>
</div>
</div>

<div id="mPay" class="modal {{ $errors->has('nominal') || $errors->has('jenis_pembayaran') ? 'open' : '' }}"><div class="bg-white rounded-2xl w-full max-w-md p-6 sm:p-8 relative my-8">
    <button type="button" data-close class="absolute top-4 right-5 text-2xl text-charcoal/40 hover:text-burgundy">×</button>
    <h3 class="font-serif text-2xl text-burgundy mb-5">Catat Pembayaran</h3>
    <form method="POST" action="{{ route('admin.pemesanan.bayar', $p) }}" class="space-y-4">@csrf
        <div class="grid grid-cols-2 gap-4">
            <div><label class="lbl">Jenis</label><select name="jenis_pembayaran" id="jenis" class="inp"><option @selected($bayar == 0)>DP</option><option @selected($bayar > 0)>Pelunasan</option></select></div>
            <div><label class="lbl">Metode</label><select name="metode_bayar" class="inp"><option>Tunai</option><option>Transfer</option></select></div></div>
        <div><label class="lbl">Nominal (Rp)</label><input name="nominal" id="nominal" type="number" min="1" max="{{ $sisa }}" value="{{ old('nominal', $bayar > 0 ? $sisa : (int) round($p->total_biaya * 0.5)) }}" class="inp"></div>
        <div><label class="lbl">Tanggal Bayar</label><input name="tanggal_bayar" type="date" value="{{ old('tanggal_bayar', today()->toDateString()) }}" class="inp"></div>
        <div class="rounded-xl bg-ivory border border-gold/30 p-4 text-sm space-y-1">
            <div class="flex justify-between"><span class="text-charcoal/60">Total biaya</span><span>{{ rupiah($p->total_biaya) }}</span></div>
            <div class="flex justify-between"><span class="text-charcoal/60">Sudah dibayar</span><span>{{ rupiah($bayar) }}</span></div>
            <div class="flex justify-between font-semibold text-burgundy pt-1 border-t border-gold/30"><span>Sisa tagihan</span><span>{{ rupiah($sisa) }}</span></div></div>
        <button class="btn btn-p w-full py-3">Simpan Pembayaran</button>
    </form></div></div>

<div id="mRes" class="modal {{ $errors->has('tanggal_baru') || $errors->has('alasan') ? 'open' : '' }}"><div class="bg-white rounded-2xl w-full max-w-md p-6 sm:p-8 relative my-8">
    <button type="button" data-close class="absolute top-4 right-5 text-2xl text-charcoal/40 hover:text-burgundy">×</button>
    <h3 class="font-serif text-2xl text-burgundy mb-1">Ubah Tanggal Acara</h3><p class="text-xs text-charcoal/60 mb-5">Tanggal saat ini: {{ tgl_long($p->tanggal_acara) }}</p>
    <form method="POST" action="{{ route('admin.pemesanan.reschedule', $p) }}" class="space-y-4">@csrf
        <div><label class="lbl">Tanggal Baru</label><input name="tanggal_baru" type="date" min="{{ today()->toDateString() }}" value="{{ old('tanggal_baru') }}" class="inp"></div>
        <div><label class="lbl">Alasan Perubahan</label><textarea name="alasan" rows="3" class="inp resize-none" placeholder="Contoh: Permintaan klien">{{ old('alasan') }}</textarea></div>
        <button class="btn btn-p w-full py-3">Simpan Perubahan</button>
    </form></div></div>
@endsection
@push('scripts')
<script>
const jenis = document.getElementById('jenis'), nom = document.getElementById('nominal');
jenis.onchange = () => { nom.value = jenis.value === 'DP' ? Math.min({{ $sisa }}, {{ (int) round($p->total_biaya * 0.5) }}) : {{ $sisa }}; };
</script>
@endpush
