@extends('layouts.admin')
@section('judul', 'Laporan Keuangan')
@section('content')
@php
    $g = $grafik;
    $hariAktif = $mode === 'harian' ? $ref->day : null;
    $adaData = collect($g['masuk'])->merge($g['refund'])->merge($g['keluar'])->contains(fn ($v) => $v != 0);
    $judulGrafik = $mode === 'tahunan' ? 'per Bulan' : 'per Hari';
    $warnaLaba = fn ($n) => $n < 0 ? 'text-red-700' : 'text-burgundy';
    $lay = $perLayanan['layanan'];
    $kat = $perLayanan['kategori'];
    $pt = $piutang;
@endphp

<form method="GET" action="{{ route('admin.laporan') }}" class="no-print card p-5 mb-6 flex flex-wrap items-end gap-4">
    <div><label class="lbl">Periode</label><select name="mode" id="mode" class="inp" onchange="toggle()"><option value="harian" @selected($mode === 'harian')>Harian</option><option value="bulanan" @selected($mode === 'bulanan')>Bulanan</option><option value="tahunan" @selected($mode === 'tahunan')>Tahunan</option></select></div>
    <div id="wd"><label class="lbl">Tanggal</label><input name="tanggal" type="date" value="{{ $tanggal }}" class="inp"></div>
    <div id="wm"><label class="lbl">Bulan</label><input name="bulan" type="month" value="{{ $bulan }}" class="inp"></div>
    <div id="wy"><label class="lbl">Tahun</label><input name="tahun" type="number" min="2000" max="2100" value="{{ $tahun }}" class="inp w-28"></div>
    <button class="btn btn-o">Tampilkan</button>
    <div class="ml-auto flex flex-wrap gap-2">
        <button type="submit" formaction="{{ route('admin.laporan.ekspor') }}" class="btn btn-o">📥 Ekspor Excel</button>
        <button type="submit" formaction="{{ route('admin.laporan.pdf') }}" class="btn btn-p">📄 Unduh PDF</button>
        <button type="button" onclick="window.print()" class="btn btn-o">🖨️ Cetak</button>
    </div>
</form>
<p class="hidden print:block font-serif text-2xl text-burgundy mb-4">Laporan Keuangan {{ ucfirst($mode) }} – {{ $label }}</p>
<p class="no-print text-sm text-charcoal/60 mb-4">Periode: <span class="font-medium text-burgundy">{{ $label }}</span></p>

<div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-3">
    @foreach ([['Pembayaran Masuk', rupiah($masuk), 'text-burgundy'], ['Pengembalian Dana', rupiah($refund), 'text-burgundy'], ['Pemasukan Bersih', rupiah($bersih), $warnaLaba($bersih)], ['Pengeluaran', rupiah($keluar), 'text-burgundy'], ['Laba Kas', rupiah($laba), $warnaLaba($laba)], ['Piutang (sisa tagihan)', rupiah($pt['total']), 'text-burgundy']] as [$a, $b, $warna])
    <div class="card p-5 {{ $a === 'Laba Kas' ? 'border-gold' : '' }}">
        <p class="font-serif text-xl sm:text-2xl {{ $warna }} break-words">{{ $b }}</p>
        <p class="text-xs text-charcoal/60 mt-1">{{ $a }}@if ($a === 'Piutang (sisa tagihan)' && $pt['jatuhTempo'] > 0) · <a href="#piutang" class="text-red-700 underline">{{ rupiah($pt['jatuhTempo']) }} jatuh tempo</a>@endif</p>
    </div>
    @endforeach
</div>
<p class="text-xs text-charcoal/60 mb-6">DP {{ rupiah($totalDp) }} · Pelunasan {{ rupiah($totalLunas) }} · {{ $pembayaran->count() }} transaksi pembayaran. Laba kas = pembayaran masuk − pengembalian − pengeluaran (basis kas: pembayaran diakui saat diterima, bukan saat acara berlangsung). Piutang adalah posisi per hari ini.</p>

@if ($danaHangus > 0 || $danaDikeluarkan > 0)
<div class="rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-sm px-4 py-3 mb-6 space-y-1">
    @if ($danaHangus > 0)<p>Pemasukan periode ini sudah termasuk <strong>{{ rupiah($danaHangus) }}</strong> dari pesanan dibatalkan yang DP-nya hangus.</p>@endif
    @if ($danaDikeluarkan > 0)<p><strong>{{ rupiah($danaDikeluarkan) }}</strong> pembayaran dari pesanan dibatalkan <strong>tidak dihitung</strong> sebagai pemasukan (dikembalikan/dikeluarkan).</p>@endif
    <p class="text-xs text-amber-800/80">Perlakuan dana dipilih saat pesanan dibatalkan; bisa diubah dari halaman detail pesanan.</p>
</div>
@endif

<div class="card p-6 mb-6">
    <div class="flex flex-wrap items-baseline justify-between gap-2 mb-4">
        <h2 class="font-serif text-lg text-burgundy">Pemasukan Bersih &amp; Pengeluaran {{ $judulGrafik }} <span class="text-sm text-charcoal/50 font-sans">· {{ $mode === 'tahunan' ? $ref->year : $ref->translatedFormat('F Y') }}</span></h2>
        <p class="text-xs text-charcoal/60">Laba kas: <span class="font-semibold {{ $warnaLaba(array_sum($g['laba'])) }}">{{ rupiah(array_sum($g['laba'])) }}</span></p>
    </div>
    @if ($adaData)
        <div class="relative h-64"><canvas id="grafik" aria-label="Grafik pemasukan bersih dan pengeluaran {{ $judulGrafik }}" role="img"></canvas></div>
        @if ($hariAktif)<p class="text-xs text-charcoal/50 mt-3">Batang pemasukan yang lebih terang menandai tanggal yang sedang dipilih.</p>@endif
    @else
        <p class="text-center text-charcoal/60 py-16">Belum ada transaksi pada {{ $mode === 'tahunan' ? 'tahun' : 'bulan' }} ini.</p>
    @endif
</div>

@if ($mode === 'tahunan')
<div class="card overflow-hidden mb-6"><div class="px-6 py-4 border-b border-gold/20"><h2 class="font-serif text-lg text-burgundy">Rekap per Bulan</h2></div>
    <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Bulan</th><th class="text-right">Pembayaran Masuk</th><th class="text-right">Pengembalian</th><th class="text-right">Pemasukan Bersih</th><th class="text-right">Pengeluaran</th><th class="text-right">Laba Kas</th></tr></thead><tbody>
        @foreach ($g['label'] as $i => $bln)
        <tr><td>{{ $bln }}</td><td class="text-right">{{ rupiah($g['masuk'][$i]) }}</td><td class="text-right">{{ rupiah($g['refund'][$i]) }}</td><td class="text-right">{{ rupiah($g['bersih'][$i]) }}</td><td class="text-right">{{ rupiah($g['keluar'][$i]) }}</td><td class="text-right font-medium {{ $warnaLaba($g['laba'][$i]) }}">{{ rupiah($g['laba'][$i]) }}</td></tr>
        @endforeach
        <tr><td class="font-semibold text-burgundy">Total {{ $ref->year }}</td><td class="text-right font-semibold">{{ rupiah($masuk) }}</td><td class="text-right font-semibold">{{ rupiah($refund) }}</td><td class="text-right font-semibold">{{ rupiah($bersih) }}</td><td class="text-right font-semibold">{{ rupiah($keluar) }}</td><td class="text-right font-semibold {{ $warnaLaba($laba) }}">{{ rupiah($laba) }}</td></tr>
    </tbody></table></div></div>
@endif

<div class="grid lg:grid-cols-2 gap-6 mb-6">
    <div class="card overflow-hidden"><div class="px-6 py-4 border-b border-gold/20"><h2 class="font-serif text-lg text-burgundy">Pemasukan Bersih per Layanan</h2></div>
        <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Layanan</th><th class="text-right">Pemasukan Bersih</th></tr></thead><tbody>
            @forelse ($kat as $namaKat => $nilaiKat)
                <tr class="bg-ivory"><td class="font-semibold text-burgundy">{{ $namaKat }}</td><td class="text-right font-semibold text-burgundy">{{ rupiah($nilaiKat) }}</td></tr>
                @foreach ($lay->where('kategori', $namaKat) as $row)<tr><td class="pl-8">{{ $row['nama'] }}</td><td class="text-right">{{ rupiah($row['nominal']) }}</td></tr>@endforeach
            @empty
                <tr><td colspan="2" class="text-center text-charcoal/60 py-10">Belum ada pemasukan pada periode ini.</td></tr>
            @endforelse
        </tbody></table></div>
        <p class="px-6 py-3 text-[11px] text-charcoal/50 border-t border-gold/10">Pembayaran dicatat per pesanan, jadi untuk pesanan berisi beberapa layanan nilainya dibagi sebanding dengan subtotal tiap layanan.</p>
    </div>

    <div class="card overflow-hidden"><div class="px-6 py-4 border-b border-gold/20"><h2 class="font-serif text-lg text-burgundy">Pengeluaran per Kategori</h2></div>
        <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Kategori</th><th class="text-right">Total</th></tr></thead><tbody>
            @forelse ($pengeluaranPerKategori as $nama => $nilai)<tr><td>{{ $nama }}</td><td class="text-right">{{ rupiah($nilai) }}</td></tr>
            @empty<tr><td colspan="2" class="text-center text-charcoal/60 py-10">Belum ada pengeluaran pada periode ini.</td></tr>@endforelse
            @if ($pengeluaranPerKategori->isNotEmpty())<tr><td class="font-semibold text-burgundy">Total</td><td class="text-right font-semibold text-burgundy">{{ rupiah($keluar) }}</td></tr>@endif
        </tbody></table></div>
        <p class="no-print px-6 py-3 text-xs border-t border-gold/10"><a href="{{ route('admin.pengeluaran.index') }}" class="text-burgundy underline">Catat atau kelola pengeluaran →</a></p>
    </div>
</div>

<div id="piutang" class="card overflow-hidden mb-6"><div class="px-6 py-4 border-b border-gold/20 flex flex-wrap items-baseline justify-between gap-2"><h2 class="font-serif text-lg text-burgundy">Piutang (Sisa Tagihan per Hari Ini)</h2>
    <p class="text-xs text-charcoal/60">Total {{ rupiah($pt['total']) }}@if ($pt['jatuhTempo'] > 0) · <span class="text-red-700 font-semibold">{{ rupiah($pt['jatuhTempo']) }} jatuh tempo</span>@endif</p></div>
    <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Kode</th><th>Klien</th><th>Tgl Acara</th><th class="text-right">Total</th><th class="text-right">Dibayar</th><th class="text-right">Sisa</th><th></th></tr></thead><tbody>
        @forelse ($pt['baris']->take(25) as $row)
            @php $p = $row['pemesanan']; @endphp
            <tr><td class="font-mono text-xs"><a href="{{ route('admin.pemesanan.show', $p) }}" class="text-burgundy hover:text-gold-dark">{{ $p->kode }}</a></td><td>{{ $p->klien->nama }}</td><td>{{ tgl($p->tanggal_acara) }}</td><td class="text-right">{{ rupiah($p->total_biaya) }}</td><td class="text-right">{{ rupiah($row['terbayar']) }}</td><td class="text-right font-medium">{{ rupiah($row['sisa']) }}</td>
                <td>@if ($row['jatuh_tempo'])<span class="inline-block text-[10px] font-semibold text-red-700 border border-red-300 rounded-full px-2 py-0.5">Jatuh tempo</span>@endif</td></tr>
        @empty<tr><td colspan="7" class="text-center text-charcoal/60 py-10">Tidak ada piutang. Semua pesanan aktif sudah lunas.</td></tr>@endforelse
    </tbody></table></div>
    @if ($pt['baris']->count() > 25)<p class="px-6 py-3 text-xs text-charcoal/60 border-t border-gold/10">Menampilkan 25 dari {{ $pt['baris']->count() }} pesanan. Daftar lengkap ada di ekspor Excel (sheet Piutang).</p>@endif
</div>

<div class="card overflow-hidden mb-6"><div class="px-6 py-4 border-b border-gold/20"><h2 class="font-serif text-lg text-burgundy">Rincian Pembayaran</h2></div>
    <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Tanggal</th><th>Kode</th><th>Klien</th><th>Jenis</th><th>Metode</th><th>Catatan</th><th>Bukti</th><th class="text-right">Nominal</th></tr></thead><tbody>
        @forelse ($pembayaran as $x)<tr><td>{{ tgl($x->tanggal_bayar) }}</td><td class="font-mono text-xs"><a href="{{ route('admin.pemesanan.show', $x->pemesanan) }}" class="text-burgundy hover:text-gold-dark">{{ $x->pemesanan->kode }}</a></td><td>{{ $x->pemesanan->klien->nama }}</td><td>{{ $x->jenis_pembayaran }}</td><td>{{ $x->metode_bayar }}</td>
            <td class="text-xs text-charcoal/70 max-w-[12rem]">{{ $x->catatan ?: '-' }}</td>
            <td>@if ($x->bukti_url)<a href="{{ $x->bukti_url }}" target="_blank" rel="noopener" title="Lihat bukti transaksi"><img src="{{ $x->bukti_url }}" alt="Bukti transaksi {{ $x->pemesanan->kode }}" class="w-10 h-10 object-cover rounded border border-gold/40"></a>@else<span class="text-xs text-amber-700">Belum ada</span>@endif</td>
            <td class="text-right">{{ rupiah($x->nominal) }}</td></tr>
        @empty<tr><td colspan="8" class="text-center text-charcoal/60 py-10">Data pembayaran tidak tersedia pada periode yang dipilih.</td></tr>@endforelse
        @if ($pembayaran->count())<tr><td colspan="7" class="text-right font-semibold text-burgundy">Total</td><td class="text-right font-semibold text-burgundy">{{ rupiah($masuk) }}</td></tr>@endif
    </tbody></table></div></div>

@if ($pengembalian->isNotEmpty())
<div class="card overflow-hidden mb-6"><div class="px-6 py-4 border-b border-gold/20"><h2 class="font-serif text-lg text-burgundy">Pengembalian Dana</h2></div>
    <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Tanggal</th><th>Kode</th><th>Klien</th><th>Metode</th><th>Catatan</th><th class="text-right">Nominal</th></tr></thead><tbody>
        @foreach ($pengembalian as $x)<tr><td>{{ tgl($x->tanggal_pengembalian) }}</td><td class="font-mono text-xs"><a href="{{ route('admin.pemesanan.show', $x->pemesanan) }}" class="text-burgundy hover:text-gold-dark">{{ $x->pemesanan->kode }}</a></td><td>{{ $x->pemesanan->klien->nama }}</td><td>{{ $x->metode }}</td><td class="text-xs text-charcoal/70 max-w-[12rem]">{{ $x->catatan ?: '-' }}</td><td class="text-right text-red-700">− {{ rupiah($x->nominal) }}</td></tr>@endforeach
        <tr><td colspan="5" class="text-right font-semibold text-burgundy">Total Pengembalian</td><td class="text-right font-semibold text-red-700">− {{ rupiah($refund) }}</td></tr>
    </tbody></table></div></div>
@endif

@if ($pengeluaran->isNotEmpty())
<div class="card overflow-hidden"><div class="px-6 py-4 border-b border-gold/20"><h2 class="font-serif text-lg text-burgundy">Rincian Pengeluaran</h2></div>
    <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Tanggal</th><th>Kategori</th><th>Keterangan</th><th class="text-right">Nominal</th></tr></thead><tbody>
        @foreach ($pengeluaran as $x)<tr><td>{{ tgl($x->tanggal) }}</td><td>{{ $x->kategori }}</td><td class="text-xs text-charcoal/70">{{ $x->keterangan }}</td><td class="text-right text-red-700">− {{ rupiah($x->nominal) }}</td></tr>@endforeach
        <tr><td colspan="3" class="text-right font-semibold text-burgundy">Total Pengeluaran</td><td class="text-right font-semibold text-red-700">− {{ rupiah($keluar) }}</td></tr>
    </tbody></table></div></div>
@endif
@endsection
@push('scripts')
<script>
function toggle() {
    const m = document.getElementById('mode').value;
    document.getElementById('wd').classList.toggle('hidden', m !== 'harian');
    document.getElementById('wm').classList.toggle('hidden', m !== 'bulanan');
    document.getElementById('wy').classList.toggle('hidden', m !== 'tahunan');
}
toggle();
</script>
@if ($adaData)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(() => {
    const G = @json($g);
    const judul = @json($mode === 'tahunan' ? (string) $ref->year : $ref->translatedFormat('F Y'));
    const tahunan = @json($mode === 'tahunan');
    const aktif = @json($hariAktif);
    const rp = n => (n < 0 ? '−' : '') + 'Rp ' + Math.round(Math.abs(n)).toLocaleString('id-ID');
    const singkat = n => {
        const a = Math.abs(n), s = n < 0 ? '−' : '';
        return a >= 1e6 ? s + 'Rp ' + (a / 1e6).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' jt'
             : a >= 1e3 ? s + 'Rp ' + (a / 1e3).toLocaleString('id-ID', { maximumFractionDigits: 0 }) + ' rb' : s + 'Rp ' + a;
    };
    new Chart(document.getElementById('grafik'), {
        type: 'bar',
        data: {
            labels: G.label,
            datasets: [
                { label: 'Pemasukan bersih', data: G.bersih, backgroundColor: G.label.map(l => (!tahunan && l === aktif) ? '#9B1B30' : '#4A0E17'), hoverBackgroundColor: '#6B1424', borderRadius: 4, maxBarThickness: 22 },
                { label: 'Pengeluaran', data: G.keluar, backgroundColor: '#D4AF37', hoverBackgroundColor: '#B8932A', borderRadius: 4, maxBarThickness: 22 },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true, labels: { color: '#4b5563', boxWidth: 12, font: { size: 11 } } },
                tooltip: {
                    backgroundColor: '#33090F', titleColor: '#D4AF37', bodyColor: '#fff', padding: 10,
                    callbacks: { title: i => i[0].label + ' ' + judul, label: c => c.dataset.label + ': ' + rp(c.parsed.y) },
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
