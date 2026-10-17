@extends('layouts.admin')
@section('judul', 'Dashboard')
@section('content')
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach ($status as $s => $n)
        <a href="{{ route('admin.pemesanan.index', ['status' => $s]) }}" class="card p-5 hover:border-gold transition block"><p class="text-2xl">{{ ['Menunggu Konfirmasi' => '⏳', 'Dikonfirmasi' => '✅', 'Selesai' => '🎉', 'Dibatalkan' => '✖️'][$s] }}</p><p class="font-serif text-3xl text-burgundy mt-1">{{ $n }}</p><p class="text-xs text-charcoal/60 mt-1">{{ $s }}</p></a>
    @endforeach
</div>
<div class="grid sm:grid-cols-2 gap-4 mb-8">
    <div class="rounded-2xl bg-burgundy text-white p-6 border border-gold/40"><p class="text-xs uppercase tracking-widest text-gold">Pemasukan Hari Ini</p><p class="font-serif text-3xl mt-2">{{ rupiah($hariIni) }}</p></div>
    <div class="rounded-2xl bg-burgundy text-white p-6 border border-gold/40"><p class="text-xs uppercase tracking-widest text-gold">Pemasukan Bulan Ini</p><p class="font-serif text-3xl mt-2">{{ rupiah($bulanIni) }}</p></div>
</div>
<div class="card overflow-hidden">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gold/20"><h2 class="font-serif text-lg text-burgundy">Pemesanan Terbaru</h2><a href="{{ route('admin.pemesanan.index') }}" class="text-sm text-gold-dark hover:text-burgundy">Lihat semua →</a></div>
    <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Kode</th><th>Klien</th><th>Layanan</th><th>Tgl Acara</th><th>Status</th><th>Pembayaran</th><th></th></tr></thead><tbody>
        @forelse ($terbaru as $p)
            <tr><td class="font-mono text-xs">{{ $p->kode }}</td><td>{{ $p->klien->nama }}</td><td>{{ $p->detail->pluck('layanan.nama')->implode(', ') }}</td><td>{{ tgl($p->tanggal_acara) }}</td>
                <td><x-badge :t="$p->status_pemesanan" /></td><td><x-badge :t="$p->status_pembayaran" /></td><td class="text-right"><a href="{{ route('admin.pemesanan.show', $p) }}" class="btn btn-o btn-sm">Detail</a></td></tr>
        @empty<tr><td colspan="7" class="text-center text-charcoal/50 py-8">Belum ada pemesanan.</td></tr>@endforelse
    </tbody></table></div>
</div>
@endsection
