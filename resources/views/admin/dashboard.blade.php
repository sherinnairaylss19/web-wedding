@extends('layouts.admin')
@section('judul', 'Dashboard')
@section('content')
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach ($status as $s => $n)
    <a href="{{ route('admin.pemesanan.index', ['status' => $s]) }}" class="card p-5 hover:border-gold transition block">
        <p class="font-serif text-3xl text-burgundy">{{ $n }}</p>
        <p class="text-xs text-charcoal/60 mt-1">{{ $s }}</p>
    </a>
    @endforeach
</div>
<div class="grid sm:grid-cols-2 gap-4 mb-8">
    <div class="rounded-2xl bg-burgundy text-white p-6 border border-gold/40"><p class="text-xs uppercase tracking-widest text-gold">Pemasukan Hari Ini</p><p class="font-serif text-3xl mt-2">{{ rupiah($hariIni) }}</p></div>
    <div class="rounded-2xl bg-burgundy text-white p-6 border border-gold/40"><p class="text-xs uppercase tracking-widest text-gold">Pemasukan Bulan Ini</p><p class="font-serif text-3xl mt-2">{{ rupiah($bulanIni) }}</p></div>
</div>
<div class="card p-6 mb-8">
    <div class="flex flex-wrap items-baseline justify-between gap-2 mb-4">
        <h2 class="font-serif text-lg text-burgundy">Pemasukan per Hari <span class="text-sm text-charcoal/50 font-sans">· {{ now()->translatedFormat('F Y') }}</span></h2>
        <a href="{{ route('admin.laporan') }}" class="text-sm text-gold-dark hover:text-burgundy">Laporan lengkap →</a>
    </div>
    @if ($grafik->sum() > 0)
        <div class="relative h-60"><canvas id="grafikDashboard" role="img" aria-label="Grafik pemasukan per hari bulan ini"></canvas></div>
        <p class="text-xs text-charcoal/50 mt-3"><span class="inline-block w-2.5 h-2.5 rounded-sm bg-gold align-middle mr-1"></span>Hari ini</p>
    @else
        <p class="text-center text-charcoal/60 py-14">Belum ada pemasukan pada bulan ini.</p>
    @endif
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
@if ($grafik->sum() > 0)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(() => {
    const nilai = @json($grafik);
    const bulan = @json(now()->translatedFormat('F Y'));
    const hariIni = {{ now()->day }};
    const rp = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');
    const singkat = n => n >= 1e6 ? 'Rp ' + (n / 1e6).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' jt' : n >= 1e3 ? 'Rp ' + (n / 1e3).toLocaleString('id-ID', { maximumFractionDigits: 0 }) + ' rb' : 'Rp ' + n;
    new Chart(document.getElementById('grafikDashboard'), {
        type: 'bar',
        data: { labels: nilai.map((_, i) => i + 1), datasets: [{ label: 'Pemasukan', data: nilai, backgroundColor: nilai.map((_, i) => (i + 1) === hariIni ? '#D4AF37' : '#4A0E17'), hoverBackgroundColor: '#B8932A', borderRadius: 4, maxBarThickness: 22 }] },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { backgroundColor: '#33090F', titleColor: '#D4AF37', bodyColor: '#fff', padding: 10, callbacks: { title: i => i[0].label + ' ' + bulan, label: c => rp(c.parsed.y) } } },
            scales: { x: { grid: { display: false }, ticks: { color: '#6b7280', font: { size: 10 } } }, y: { beginAtZero: true, grid: { color: 'rgba(212,175,55,.2)' }, ticks: { color: '#6b7280', font: { size: 10 }, callback: singkat } } },
        },
    });
})();
</script>
@endpush
@endif
