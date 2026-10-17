@extends('layouts.admin')
@section('judul', 'Laporan Keuangan')
@section('content')
@php $mx = max(1, $perHari->max() ?? 0); @endphp
<form method="GET" class="no-print card p-5 mb-6 flex flex-wrap items-end gap-4">
    <div><label class="lbl">Periode</label><select name="mode" id="mode" class="inp" onchange="toggle()"><option value="harian" @selected($mode === 'harian')>Harian</option><option value="bulanan" @selected($mode === 'bulanan')>Bulanan</option></select></div>
    <div id="wd"><label class="lbl">Tanggal</label><input name="tanggal" type="date" value="{{ $tanggal }}" class="inp"></div>
    <div id="wm"><label class="lbl">Bulan</label><input name="bulan" type="month" value="{{ $bulan }}" class="inp"></div>
    <button class="btn btn-o">Tampilkan</button><button type="button" onclick="window.print()" class="btn btn-p ml-auto">🖨️ Cetak Laporan</button>
</form>
<p class="hidden print:block font-serif text-2xl text-burgundy mb-4">Laporan Keuangan {{ ucfirst($mode) }} – {{ $mode === 'harian' ? tgl_long($ref) : $ref->translatedFormat('F Y') }}</p>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach ([['Total Pemasukan', rupiah($total), '💰'], ['Jumlah Transaksi', $list->count(), '🧾'], ['Total DP', rupiah($totalDp), '📥'], ['Total Pelunasan', rupiah($totalLunas), '✅']] as [$a, $b, $i])
        <div class="card p-5"><p class="text-2xl">{{ $i }}</p><p class="font-serif text-xl sm:text-2xl text-burgundy mt-1 break-words">{{ $b }}</p><p class="text-xs text-charcoal/60 mt-1">{{ $a }}</p></div>
    @endforeach
</div>

<div class="card p-6 mb-6"><h2 class="font-serif text-lg text-burgundy mb-4">Grafik Pemasukan per Hari <span class="text-sm text-charcoal/50 font-sans">· {{ $ref->translatedFormat('F Y') }}</span></h2>
    <div class="flex items-end gap-[3px] h-48">
        @for ($d = 1; $d <= $hariDalamBulan; $d++)
            @php $v = $perHari[$d] ?? 0; $on = $mode === 'harian' && $ref->day === $d; @endphp
            <div class="flex-1 h-full flex items-end" title="{{ $d }}: {{ rupiah($v) }}"><div class="w-full rounded-t {{ $on ? 'bg-gold' : 'bg-burgundy' }}" style="height: {{ $v ? max(3, $v / $mx * 100) : 0 }}%"></div></div>
        @endfor
    </div>
    <div class="flex gap-[3px] mt-1 text-[9px] text-charcoal/50">@for ($d = 1; $d <= $hariDalamBulan; $d++)<div class="flex-1 text-center">{{ $d % 5 === 1 || $d === $hariDalamBulan ? $d : '' }}</div>@endfor</div></div>

<div class="card overflow-hidden"><div class="px-6 py-4 border-b border-gold/20"><h2 class="font-serif text-lg text-burgundy">Rincian Pembayaran</h2></div>
    <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Tanggal</th><th>Kode</th><th>Klien</th><th>Jenis</th><th>Metode</th><th class="text-right">Nominal</th></tr></thead><tbody>
        @forelse ($list as $x)<tr><td>{{ tgl($x->tanggal_bayar) }}</td><td class="font-mono text-xs">{{ $x->pemesanan->kode }}</td><td>{{ $x->pemesanan->klien->nama }}</td><td>{{ $x->jenis_pembayaran }}</td><td>{{ $x->metode_bayar }}</td><td class="text-right">{{ rupiah($x->nominal) }}</td></tr>
        @empty<tr><td colspan="6" class="text-center text-charcoal/60 py-10">Data pembayaran tidak tersedia pada periode yang dipilih.</td></tr>@endforelse
        @if ($list->count())<tr><td colspan="5" class="text-right font-semibold text-burgundy">Total</td><td class="text-right font-semibold text-burgundy">{{ rupiah($total) }}</td></tr>@endif
    </tbody></table></div></div>
@endsection
@push('scripts')
<script>
function toggle() { const d = document.getElementById('mode').value === 'harian'; document.getElementById('wd').classList.toggle('hidden', !d); document.getElementById('wm').classList.toggle('hidden', d); } toggle();
</script>
@endpush
