@extends('layouts.admin')
@section('judul', 'Daftar Pemesanan')
@section('content')
<form method="GET" class="flex flex-col md:flex-row gap-3 mb-5">
    <input name="q" value="{{ request('q') }}" type="search" class="inp md:max-w-sm" placeholder="Cari kode atau nama klien...">
    <select name="status" class="inp md:max-w-xs" onchange="this.form.submit()"><option value="">Semua Status</option>@foreach (['Menunggu Konfirmasi', 'Dikonfirmasi', 'Selesai', 'Dibatalkan'] as $s)<option @selected(request('status') === $s)>{{ $s }}</option>@endforeach</select>
    <button class="btn btn-p">Cari</button>
</form>
<div class="card overflow-hidden"><div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Kode</th><th>Klien</th><th>Layanan</th><th>Tgl Acara</th><th>Total</th><th>Status</th><th>Pembayaran</th><th></th></tr></thead><tbody>
    @forelse ($daftar as $p)
        <tr><td class="font-mono text-xs">{{ $p->kode }}</td><td>{{ $p->klien->nama }}</td><td>{{ $p->detail->map(fn($d) => $d->layanan->nama . ' ×' . $d->jumlah)->implode(', ') }}</td><td>{{ tgl($p->tanggal_acara) }}</td><td>{{ rupiah($p->total_biaya) }}</td>
            <td><x-badge :t="$p->status_pemesanan" /></td><td><x-badge :t="$p->status_pembayaran" /></td>
            <td class="text-right whitespace-nowrap"><a href="{{ route('admin.pemesanan.show', $p) }}" class="btn btn-o btn-sm">Detail</a> <a href="{{ route('admin.pemesanan.invoice', $p) }}" class="btn btn-o btn-sm">Invoice</a></td></tr>
    @empty<tr><td colspan="8" class="text-center text-charcoal/50 py-10">Tidak ada pemesanan yang cocok.</td></tr>@endforelse
</tbody></table></div></div>
<div class="mt-4">{{ $daftar->links() }}</div>
@endsection
