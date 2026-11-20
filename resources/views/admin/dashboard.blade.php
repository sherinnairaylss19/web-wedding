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
@if ($jatuhTempo > 0)
<a href="{{ route('admin.pemesanan.index', ['tagihan' => 'terlambat']) }}" class="block rounded-2xl border border-red-200 bg-red-50 text-red-800 px-5 py-4 mb-6 text-sm hover:bg-red-100 transition">
    ⏰ <strong>{{ $jatuhTempo }}</strong> pesanan sudah melewati batas pelunasan ({{ config('ikes.pelunasan_hari') }} hari sebelum acara) dan belum lunas. <span class="underline">Lihat daftar →</span>
</a>
@endif
<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="rounded-2xl bg-burgundy text-white p-6 border border-gold/40"><p class="text-xs uppercase tracking-widest text-gold">Pemasukan Bersih Hari Ini</p><p class="font-serif text-2xl xl:text-3xl mt-2 break-words">{{ rupiah($hariIni) }}</p></div>
    <div class="rounded-2xl bg-burgundy text-white p-6 border border-gold/40"><p class="text-xs uppercase tracking-widest text-gold">Pemasukan Bersih Bulan Ini</p><p class="font-serif text-2xl xl:text-3xl mt-2 break-words">{{ rupiah($bulanIni) }}</p></div>
    <div class="rounded-2xl bg-burgundy text-white p-6 border border-gold/40"><p class="text-xs uppercase tracking-widest text-gold">Laba Kas Bulan Ini</p><p class="font-serif text-2xl xl:text-3xl mt-2 break-words {{ $labaBulan < 0 ? 'text-red-300' : '' }}">{{ $labaBulan < 0 ? '−' : '' }}{{ rupiah(abs($labaBulan)) }}</p><p class="text-xs text-white/70 mt-1">Pengeluaran {{ rupiah($pengeluaranBulan) }}</p></div>
    <a href="{{ route('admin.laporan') }}#piutang" class="card p-6 border-gold hover:bg-ivory transition block"><p class="text-xs uppercase tracking-widest text-gold-dark">Piutang (Sisa Tagihan)</p><p class="font-serif text-2xl xl:text-3xl text-burgundy mt-2 break-words">{{ rupiah($piutang['total']) }}</p><p class="text-xs mt-1 {{ $piutang['jatuhTempo'] > 0 ? 'text-red-700 font-semibold' : 'text-charcoal/60' }}">{{ $piutang['jatuhTempo'] > 0 ? rupiah($piutang['jatuhTempo']) . ' sudah jatuh tempo' : $piutang['baris']->count() . ' pesanan belum lunas' }}</p></a>
</div>
<div class="card p-6 mb-8">
    <div class="flex flex-wrap items-baseline justify-between gap-2 mb-4">
        <h2 class="font-serif text-lg text-burgundy">Pemasukan &amp; Pengeluaran per Hari <span class="text-sm text-charcoal/50 font-sans">· {{ now()->translatedFormat('F Y') }}</span></h2>
        <a href="{{ route('admin.laporan') }}" class="text-sm text-gold-dark hover:text-burgundy">Laporan lengkap →</a>
    </div>
    @if ($adaGrafik)
        <div class="relative h-60"><canvas id="grafikDashboard" role="img" aria-label="Grafik pemasukan bersih dan pengeluaran per hari bulan ini"></canvas></div>
        <p class="text-xs text-charcoal/50 mt-3">Batang pemasukan yang lebih terang menandai hari ini.</p>
    @else
        <p class="text-center text-charcoal/60 py-14">Belum ada pemasukan atau pengeluaran pada bulan ini.</p>
    @endif
</div>
@php $qTahun = ['mode' => 'tahunan', 'tahun' => now()->year]; $tt = $rekapTahun['total']; @endphp
<div class="card overflow-hidden mb-8">
    <div class="flex flex-wrap items-center justify-between gap-2 px-6 py-4 border-b border-gold/20">
        <h2 class="font-serif text-lg text-burgundy">Rekap Tahun {{ now()->year }}</h2>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.laporan', $qTahun) }}" class="btn btn-o btn-sm">Laporan tahunan</a>
            <a href="{{ route('admin.laporan.ekspor', $qTahun) }}" class="btn btn-o btn-sm">📥 Excel</a>
            <a href="{{ route('admin.laporan.pdf', $qTahun) }}" class="btn btn-o btn-sm">📄 PDF</a>
        </div>
    </div>
    <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Bulan</th><th class="text-right">Pembayaran Masuk</th><th class="text-right">Pengembalian</th><th class="text-right">Pemasukan Bersih</th><th class="text-right">Pengeluaran</th><th class="text-right">Laba Kas</th></tr></thead><tbody>
        @foreach ($rekapTahun['baris'] as $b)
        <tr><td>{{ $b['label'] }}</td><td class="text-right">{{ rupiah($b['masuk']) }}</td><td class="text-right">{{ rupiah($b['refund']) }}</td><td class="text-right">{{ rupiah($b['bersih']) }}</td><td class="text-right">{{ rupiah($b['keluar']) }}</td><td class="text-right font-medium {{ $b['laba'] < 0 ? 'text-red-700' : 'text-burgundy' }}">{{ $b['laba'] < 0 ? '−' : '' }}{{ rupiah(abs($b['laba'])) }}</td></tr>
        @endforeach
        <tr><td class="font-semibold text-burgundy">Total {{ now()->year }}</td><td class="text-right font-semibold">{{ rupiah($tt['masuk']) }}</td><td class="text-right font-semibold">{{ rupiah($tt['refund']) }}</td><td class="text-right font-semibold">{{ rupiah($tt['bersih']) }}</td><td class="text-right font-semibold">{{ rupiah($tt['keluar']) }}</td><td class="text-right font-semibold {{ $tt['laba'] < 0 ? 'text-red-700' : 'text-burgundy' }}">{{ $tt['laba'] < 0 ? '−' : '' }}{{ rupiah(abs($tt['laba'])) }}</td></tr>
    </tbody></table></div>
</div>
<div class="grid lg:grid-cols-2 gap-6 mb-8">
    <div class="card overflow-hidden">
        <div class="flex flex-wrap items-baseline justify-between gap-2 px-6 py-4 border-b border-gold/20">
            <h2 class="font-serif text-lg text-burgundy">Pemasukan per Layanan <span class="text-sm text-charcoal/50 font-sans">· bulan ini</span></h2>
            <a href="{{ route('admin.laporan', ['mode' => 'bulanan']) }}" class="text-sm text-gold-dark hover:text-burgundy">Detail →</a>
        </div>
        <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Layanan</th><th class="text-right">Pemasukan Bersih</th></tr></thead><tbody>
            @forelse ($perLayanan['kategori'] as $namaKat => $nilaiKat)
                <tr class="bg-ivory"><td class="font-semibold text-burgundy">{{ $namaKat }}</td><td class="text-right font-semibold text-burgundy">{{ rupiah($nilaiKat) }}</td></tr>
                @foreach ($perLayanan['layanan']->where('kategori', $namaKat) as $row)<tr><td class="pl-8">{{ $row['nama'] }}</td><td class="text-right">{{ rupiah($row['nominal']) }}</td></tr>@endforeach
            @empty
                <tr><td colspan="2" class="text-center text-charcoal/50 py-8">Belum ada pemasukan pada bulan ini.</td></tr>
            @endforelse
        </tbody></table></div>
    </div>
    <div class="card overflow-hidden">
        <div class="flex flex-wrap items-baseline justify-between gap-2 px-6 py-4 border-b border-gold/20">
            <h2 class="font-serif text-lg text-burgundy">Pengeluaran per Kategori <span class="text-sm text-charcoal/50 font-sans">· bulan ini</span></h2>
            <a href="{{ route('admin.pengeluaran.index') }}" class="text-sm text-gold-dark hover:text-burgundy">Catat pengeluaran →</a>
        </div>
        <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Kategori</th><th class="text-right">Total</th></tr></thead><tbody>
            @forelse ($pengeluaranPerKategori as $nama => $nilai)<tr><td>{{ $nama }}</td><td class="text-right">{{ rupiah($nilai) }}</td></tr>
            @empty<tr><td colspan="2" class="text-center text-charcoal/50 py-8">Belum ada pengeluaran pada bulan ini.</td></tr>@endforelse
            @if ($pengeluaranPerKategori->isNotEmpty())<tr><td class="font-semibold text-burgundy">Total</td><td class="text-right font-semibold text-burgundy">{{ rupiah($pengeluaranBulan) }}</td></tr>@endif
        </tbody></table></div>
    </div>
</div>
<div class="card overflow-hidden mb-8">
    <div class="flex flex-wrap items-baseline justify-between gap-2 px-6 py-4 border-b border-gold/20">
        <h2 class="font-serif text-lg text-burgundy">Piutang <span class="text-sm text-charcoal/50 font-sans">· {{ $piutang['baris']->count() }} pesanan belum lunas</span></h2>
        <a href="{{ route('admin.laporan') }}#piutang" class="text-sm text-gold-dark hover:text-burgundy">Rincian lengkap →</a>
    </div>
    <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Kode</th><th>Klien</th><th>Batas Pelunasan</th><th class="text-right">Total</th><th class="text-right">Sisa</th><th></th></tr></thead><tbody>
        @forelse ($piutang['baris']->take(5) as $row)
            @php
                $p = $row['pemesanan'];
                $pesanWa = "Halo Kak {$p->klien->nama}, kami dari Ike's Gallery. Mengingatkan sisa pembayaran pesanan {$p->kode} sebesar " . rupiah($row['sisa']) . ' (batas pelunasan ' . tgl($p->batasPelunasan()) . '). Terima kasih 🙏';
            @endphp
            <tr><td class="font-mono text-xs"><a href="{{ route('admin.pemesanan.show', $p) }}" class="text-burgundy hover:text-gold-dark">{{ $p->kode }}</a></td><td>{{ $p->klien->nama }}</td>
                <td>{{ tgl($p->batasPelunasan()) }}@if ($row['jatuh_tempo']) <span class="inline-block text-[10px] font-semibold text-red-700 border border-red-300 rounded-full px-2 py-0.5 ml-1">Jatuh tempo</span>@endif</td>
                <td class="text-right">{{ rupiah($p->total_biaya) }}</td><td class="text-right font-medium text-burgundy">{{ rupiah($row['sisa']) }}</td>
                <td class="text-right">@if ($p->klien->no_hp)<a href="https://wa.me/{{ normalisasi_hp($p->klien->no_hp) }}?text={{ rawurlencode($pesanWa) }}" target="_blank" rel="noopener" class="btn btn-o btn-sm">Tagih via WA</a>@endif</td></tr>
        @empty<tr><td colspan="6" class="text-center text-charcoal/50 py-8">Tidak ada piutang. Semua pesanan aktif sudah lunas.</td></tr>@endforelse
    </tbody></table></div>
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
@if ($adaGrafik)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(() => {
    const G = @json($grafik);
    const bulan = @json(now()->translatedFormat('F Y'));
    const hariIni = {{ now()->day }};
    const rp = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');
    const singkat = n => { const a = Math.abs(n), s = n < 0 ? '−' : ''; return a >= 1e6 ? s + 'Rp ' + (a / 1e6).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' jt' : a >= 1e3 ? s + 'Rp ' + (a / 1e3).toLocaleString('id-ID', { maximumFractionDigits: 0 }) + ' rb' : s + 'Rp ' + a; };
    new Chart(document.getElementById('grafikDashboard'), {
        type: 'bar',
        data: {
            labels: G.label,
            datasets: [
                { label: 'Pemasukan bersih', data: G.bersih, backgroundColor: G.label.map(l => l === hariIni ? '#9B1B30' : '#4A0E17'), hoverBackgroundColor: '#6B1424', borderRadius: 4, maxBarThickness: 22 },
                { label: 'Pengeluaran', data: G.keluar, backgroundColor: '#D4AF37', hoverBackgroundColor: '#B8932A', borderRadius: 4, maxBarThickness: 22 },
            ],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: true, labels: { color: '#4b5563', boxWidth: 12, font: { size: 11 } } }, tooltip: { backgroundColor: '#33090F', titleColor: '#D4AF37', bodyColor: '#fff', padding: 10, callbacks: { title: i => i[0].label + ' ' + bulan, label: c => c.dataset.label + ': ' + rp(c.parsed.y) } } },
            scales: { x: { grid: { display: false }, ticks: { color: '#6b7280', font: { size: 10 } } }, y: { beginAtZero: true, grid: { color: 'rgba(212,175,55,.2)' }, ticks: { color: '#6b7280', font: { size: 10 }, callback: singkat } } },
        },
    });
})();
</script>
@endpush
@endif
