@extends('layouts.admin')
@section('judul', 'Laporan Keuangan')
@section('content')
@php
    $dataHarian = collect(range(1, $hariDalamBulan))->map(fn ($d) => (float) ($perHari[$d] ?? 0))->values();
    $hariAktif = $mode === 'harian' ? $ref->day : null;
@endphp
<form method="GET" class="no-print card p-5 mb-6 flex flex-wrap items-end gap-4">
    <div><label class="lbl">Periode</label><select name="mode" id="mode" class="inp" onchange="toggle()"><option value="harian" @selected($mode === 'harian')>Harian</option><option value="bulanan" @selected($mode === 'bulanan')>Bulanan</option></select></div>
    <div id="wd"><label class="lbl">Tanggal</label><input name="tanggal" type="date" value="{{ $tanggal }}" class="inp"></div>
    <div id="wm"><label class="lbl">Bulan</label><input name="bulan" type="month" value="{{ $bulan }}" class="inp"></div>
    <button class="btn btn-o">Tampilkan</button><button type="button" onclick="window.print()" class="btn btn-p ml-auto">🖨️ Cetak Laporan</button>
</form>
<p class="hidden print:block font-serif text-2xl text-burgundy mb-4">Laporan Keuangan {{ ucfirst($mode) }} – {{ $mode === 'harian' ? tgl_long($ref) : $ref->translatedFormat('F Y') }}</p>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach ([['Total Pemasukan', rupiah($total)], ['Jumlah Transaksi', $list->count()], ['Total DP', rupiah($totalDp)], ['Total Pelunasan', rupiah($totalLunas)]] as [$a, $b])
    <div class="card p-5">
        <p class="font-serif text-xl sm:text-2xl text-burgundy break-words">{{ $b }}</p>
        <p class="text-xs text-charcoal/60 mt-1">{{ $a }}</p>
    </div>
    @endforeach
</div>

<div class="card p-6 mb-6">
    <div class="flex flex-wrap items-baseline justify-between gap-2 mb-4">
        <h2 class="font-serif text-lg text-burgundy">Grafik Pemasukan per Hari <span class="text-sm text-charcoal/50 font-sans">· {{ $ref->translatedFormat('F Y') }}</span></h2>
        <p class="text-xs text-charcoal/60">Total bulan ini: <span class="font-semibold text-burgundy">{{ rupiah($dataHarian->sum()) }}</span></p>
    </div>
    @if ($dataHarian->sum() > 0)
        <div class="relative h-64"><canvas id="grafikHarian" aria-label="Grafik pemasukan per hari" role="img"></canvas></div>
        @if ($hariAktif)<p class="text-xs text-charcoal/50 mt-3"><span class="inline-block w-2.5 h-2.5 rounded-sm bg-gold align-middle mr-1"></span>Tanggal yang sedang dipilih</p>@endif
    @else
        <p class="text-center text-charcoal/60 py-16">Belum ada pemasukan pada bulan ini.</p>
    @endif
</div>

<div class="card overflow-hidden"><div class="px-6 py-4 border-b border-gold/20"><h2 class="font-serif text-lg text-burgundy">Rincian Pembayaran</h2></div>
    <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Tanggal</th><th>Kode</th><th>Klien</th><th>Jenis</th><th>Metode</th><th>Catatan</th><th>Bukti</th><th class="text-right">Nominal</th></tr></thead><tbody>
        @forelse ($list as $x)<tr><td>{{ tgl($x->tanggal_bayar) }}</td><td class="font-mono text-xs"><a href="{{ route('admin.pemesanan.show', $x->pemesanan) }}" class="text-burgundy hover:text-gold-dark">{{ $x->pemesanan->kode }}</a></td><td>{{ $x->pemesanan->klien->nama }}</td><td>{{ $x->jenis_pembayaran }}</td><td>{{ $x->metode_bayar }}</td>
            <td class="text-xs text-charcoal/70 max-w-[12rem]">{{ $x->catatan ?: '-' }}</td>
            <td>@if ($x->bukti_url)<a href="{{ $x->bukti_url }}" target="_blank" rel="noopener" title="Lihat bukti transaksi"><img src="{{ $x->bukti_url }}" alt="Bukti transaksi {{ $x->pemesanan->kode }}" class="w-10 h-10 object-cover rounded border border-gold/40"></a>@else<span class="text-xs text-amber-700">Belum ada</span>@endif</td>
            <td class="text-right">{{ rupiah($x->nominal) }}</td></tr>
        @empty<tr><td colspan="8" class="text-center text-charcoal/60 py-10">Data pembayaran tidak tersedia pada periode yang dipilih.</td></tr>@endforelse
        @if ($list->count())<tr><td colspan="7" class="text-right font-semibold text-burgundy">Total</td><td class="text-right font-semibold text-burgundy">{{ rupiah($total) }}</td></tr>@endif
    </tbody></table></div></div>
@endsection
@push('scripts')
<script>
function toggle() { const d = document.getElementById('mode').value === 'harian'; document.getElementById('wd').classList.toggle('hidden', !d); document.getElementById('wm').classList.toggle('hidden', d); } toggle();
</script>
@if ($dataHarian->sum() > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(() => {
    const nilai = @json($dataHarian);
    const bulan = @json($ref->translatedFormat('F Y'));
    const aktif = @json($hariAktif);
    const rp = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');
    const singkat = n => n >= 1e6 ? 'Rp ' + (n / 1e6).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' jt' : n >= 1e3 ? 'Rp ' + (n / 1e3).toLocaleString('id-ID', { maximumFractionDigits: 0 }) + ' rb' : 'Rp ' + n;
    new Chart(document.getElementById('grafikHarian'), {
        type: 'bar',
        data: {
            labels: nilai.map((_, i) => i + 1),
            datasets: [{
                label: 'Pemasukan',
                data: nilai,
                backgroundColor: nilai.map((_, i) => (i + 1) === aktif ? '#D4AF37' : '#4A0E17'),
                hoverBackgroundColor: '#B8932A',
                borderRadius: 4,
                maxBarThickness: 22,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#33090F', titleColor: '#D4AF37', bodyColor: '#fff', padding: 10,
                    callbacks: { title: i => i[0].label + ' ' + bulan, label: c => rp(c.parsed.y) },
                },
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#6b7280', font: { size: 10 } } },
                y: { beginAtZero: true, grid: { color: 'rgba(212,175,55,.2)' }, ticks: { color: '#6b7280', font: { size: 10 }, callback: singkat } },
            },
        },
    });
})();
</script>
@endif
@endpush
