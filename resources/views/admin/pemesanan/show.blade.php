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
        @if ($p->status_pemesanan === 'Dibatalkan')@elseif ($sisa > 0)<button type="button" onclick="openModal('mPay')" class="btn btn-p btn-sm">💳 Catat Pembayaran</button>@else<span class="inline-flex items-center px-3 text-xs font-semibold text-green-700 border border-green-600 rounded-full">✓ Lunas</span>@endif
        <button type="button" onclick="openModal('mRes')" class="btn btn-o btn-sm">📅 Ubah Tanggal</button>
        @unless (in_array($p->status_pemesanan, ['Selesai', 'Dibatalkan'], true))<button type="button" onclick="openModal('mRinci')" class="btn btn-o btn-sm">🧮 Ubah Rincian</button>@endunless
        @if ($p->status_pemesanan === 'Dibatalkan' && $p->dana_batal === 'hangus' && $p->saldoRefund() > 0)<button type="button" onclick="openModal('mRefund')" class="btn btn-p btn-sm">↩️ Catat Pengembalian</button>@endif
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
            @if ($p->diskon > 0)<tr><td colspan="3" class="text-right text-charcoal/60">Diskon</td><td class="text-right text-red-700">− {{ rupiah($p->diskon) }}</td></tr>@endif
            <tr><td colspan="3" class="text-right font-semibold text-burgundy">Total</td><td class="text-right font-semibold text-burgundy">{{ rupiah($p->total_biaya) }}</td></tr>
        </tbody></table></div></div>

    <div class="card overflow-hidden"><div class="px-6 py-4 border-b border-gold/20"><h2 class="font-serif text-lg text-burgundy">Riwayat Pembayaran</h2></div>
        @if ($p->pembayaran->isEmpty())<p class="p-6 text-sm text-charcoal/50">Belum ada pembayaran.</p>@else
        <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Tanggal</th><th>Jenis</th><th>Metode</th><th class="text-right">Nominal</th><th>Bukti</th><th></th></tr></thead><tbody>
            @foreach ($p->pembayaran as $x)
            <tr>
                <td>{{ tgl($x->tanggal_bayar) }}@if ($x->admin)<div class="text-xs text-charcoal/50">oleh {{ $x->admin->nama }}</div>@endif</td><td>{{ $x->jenis_pembayaran }}</td>
                <td>{{ $x->metode_bayar }}@if ($x->catatan)<div class="text-xs text-charcoal/50 max-w-[10rem]">{{ $x->catatan }}</div>@endif</td>
                <td class="text-right">{{ rupiah($x->nominal) }}</td>
                <td>@if ($x->bukti_url)<a href="{{ $x->bukti_url }}" target="_blank" rel="noopener" title="Lihat bukti transaksi"><img src="{{ $x->bukti_url }}" alt="Bukti transaksi" class="w-10 h-10 object-cover rounded border border-gold/40"></a>@else<span class="text-xs text-charcoal/40">Belum ada</span>@endif</td>
                <td class="whitespace-nowrap text-right">
                    <button type="button" class="btn btn-o btn-sm" data-edit="{{ $x->id }}"
                        data-action="{{ route('admin.pemesanan.pembayaran.update', [$p, $x]) }}" data-jenis="{{ $x->jenis_pembayaran }}" data-metode="{{ $x->metode_bayar }}"
                        data-nominal="{{ $x->nominal }}" data-tanggal="{{ $x->tanggal_bayar->toDateString() }}" data-catatan="{{ $x->catatan }}"
                        data-bukti="{{ $x->bukti_url }}" data-max="{{ max(0, $p->total_biaya - ($bayar - $x->nominal)) }}">Edit</button>
                    <button type="button" class="btn btn-o btn-sm !text-red-700 !border-red-300" data-hapus
                        data-action="{{ route('admin.pemesanan.pembayaran.destroy', [$p, $x]) }}" data-info="{{ rupiah($x->nominal) }} · {{ $x->jenis_pembayaran }} · {{ tgl($x->tanggal_bayar) }}">Hapus</button>
                </td>
            </tr>
            @endforeach
        </tbody></table></div>@endif</div>

    @php $koreksi = $p->riwayatPembayaran->where('aksi', '!=', 'Dicatat'); @endphp
    @if ($koreksi->isNotEmpty())
    <div class="card overflow-hidden"><div class="px-6 py-4 border-b border-gold/20"><h2 class="font-serif text-lg text-burgundy">Riwayat Koreksi Pembayaran</h2></div>
        <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Waktu</th><th>Aksi</th><th>Perubahan</th><th>Alasan</th><th>Oleh</th></tr></thead><tbody>
            @foreach ($koreksi as $x)
            @php
                $lbl = ['jenis_pembayaran' => 'Jenis', 'metode_bayar' => 'Metode', 'nominal' => 'Nominal', 'tanggal_bayar' => 'Tanggal', 'bukti_transaksi' => 'Bukti', 'catatan' => 'Catatan'];
                $fmt = fn ($f, $v) => ($v === null || $v === '') ? '-' : match ($f) { 'nominal' => rupiah($v), 'tanggal_bayar' => tgl(\Carbon\Carbon::parse($v)), 'bukti_transaksi' => 'ada', default => $v };
                $lama = $x->data_lama ?? []; $baru = $x->data_baru ?? [];
                $berubah = array_keys(array_filter($baru, fn ($v, $f) => ($lama[$f] ?? null) !== $v, ARRAY_FILTER_USE_BOTH));
            @endphp
            <tr>
                <td class="whitespace-nowrap">{{ $x->created_at->format('d/m/Y H:i') }}</td>
                <td><span class="text-xs font-semibold {{ $x->aksi === 'Dihapus' ? 'text-red-700' : 'text-burgundy' }}">{{ $x->aksi }}</span></td>
                <td class="text-xs">
                    @if ($x->aksi === 'Dihapus')
                        {{ rupiah($lama['nominal'] ?? 0) }} · {{ $lama['jenis_pembayaran'] ?? '-' }} · {{ $lama['metode_bayar'] ?? '-' }}@if (! empty($lama['tanggal_bayar'])) · {{ $fmt('tanggal_bayar', $lama['tanggal_bayar']) }}@endif
                    @else
                        @forelse ($berubah as $f)<div>{{ $lbl[$f] ?? $f }}: {{ $fmt($f, $lama[$f] ?? null) }} → <strong>{{ $fmt($f, $baru[$f]) }}</strong></div>@empty <span class="text-charcoal/40">Tidak ada perubahan nilai</span>@endforelse
                    @endif
                </td>
                <td>{{ $x->alasan ?: '-' }}</td><td>{{ $x->admin->nama }}</td>
            </tr>
            @endforeach
        </tbody></table></div></div>
    @endif

    @if ($p->pengembalian->isNotEmpty())
    <div class="card overflow-hidden"><div class="px-6 py-4 border-b border-gold/20"><h2 class="font-serif text-lg text-burgundy">Riwayat Pengembalian Dana</h2></div>
        <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Tanggal</th><th>Metode</th><th>Catatan</th><th>Oleh</th><th class="text-right">Nominal</th></tr></thead><tbody>
            @foreach ($p->pengembalian as $x)<tr><td>{{ tgl($x->tanggal_pengembalian) }}</td><td>{{ $x->metode }}</td><td>{{ $x->catatan ?: '-' }}</td><td>{{ $x->admin->nama }}</td><td class="text-right">{{ rupiah($x->nominal) }}</td></tr>@endforeach
        </tbody></table></div></div>
    @endif

    <div class="card overflow-hidden"><div class="px-6 py-4 border-b border-gold/20"><h2 class="font-serif text-lg text-burgundy">Riwayat Perubahan Rincian</h2></div>
        @if ($p->riwayatRincian->isEmpty())<p class="p-6 text-sm text-charcoal/50">Rincian pesanan belum pernah diubah.</p>@else
        <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Diubah</th><th class="text-right">Total Lama</th><th class="text-right">Total Baru</th><th class="text-right">Diskon</th><th>Alasan</th><th>Oleh</th></tr></thead><tbody>
            @foreach ($p->riwayatRincian as $x)<tr><td>{{ tgl($x->created_at) }}</td><td class="text-right">{{ rupiah($x->total_lama) }}</td><td class="text-right">{{ rupiah($x->total_baru) }}</td><td class="text-right">{{ $x->diskon > 0 ? rupiah($x->diskon) : '-' }}</td><td>{{ $x->alasan }}</td><td>{{ $x->admin->nama }}</td></tr>@endforeach
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
            <select name="status_pemesanan" id="selStatus" class="inp">@foreach (['Menunggu Konfirmasi', 'Dikonfirmasi', 'Selesai', 'Dibatalkan'] as $s)<option @selected($s === $p->status_pemesanan)>{{ $s }}</option>@endforeach</select>
            @if ($bayar > 0)
            <fieldset id="boxBatal" class="{{ $p->status_pemesanan === 'Dibatalkan' ? '' : 'hidden' }} space-y-2 text-xs border border-gold/30 rounded-xl p-3 bg-ivory">
                <legend class="px-1 text-charcoal/60">Perlakuan dana {{ rupiah($bayar) }} yang sudah dibayar</legend>
                <label class="flex items-start gap-2"><input type="radio" name="dana_batal" value="hangus" class="mt-0.5" @checked($p->status_pemesanan === 'Dibatalkan' && $p->dana_batal === 'hangus')><span><strong>DP hangus</strong>: tetap dihitung sebagai pemasukan (bisa dikembalikan sebagian)</span></label>
                <label class="flex items-start gap-2"><input type="radio" name="dana_batal" value="dikeluarkan" class="mt-0.5" @checked($p->status_pemesanan === 'Dibatalkan' && $p->dana_batal !== 'hangus')><span><strong>Dikembalikan / dikeluarkan</strong>: tidak dihitung sebagai pemasukan</span></label>
            </fieldset>
            @endif
            <button class="btn btn-p w-full">Perbarui Status</button></form></div>
    <div class="card overflow-hidden"><div class="px-6 py-4 border-b border-gold/20"><h2 class="font-serif text-lg text-burgundy">Ringkasan Tagihan</h2></div>
        <dl class="p-6 text-sm space-y-3">
            <div class="flex justify-between"><dt class="text-charcoal/60">Total Biaya</dt><dd>{{ rupiah($p->total_biaya) }}</dd></div>
            <div class="flex justify-between"><dt class="text-charcoal/60">Sudah Dibayar</dt><dd class="text-green-700">{{ rupiah($bayar) }}</dd></div>
            <div class="flex justify-between pt-3 border-t border-gold/30"><dt class="font-medium text-burgundy">Sisa Tagihan</dt><dd class="font-serif text-xl text-burgundy">{{ rupiah($sisa) }}</dd></div>
            @if ($p->status_pemesanan === 'Dibatalkan')
                @if ($bayar > 0 && $p->dana_batal === 'hangus')
                <div class="flex justify-between pt-3 border-t border-gray-100 text-xs"><dt class="text-charcoal/60">Perlakuan dana</dt><dd class="font-semibold text-burgundy">DP hangus (pemasukan)</dd></div>
                <div class="flex justify-between text-xs"><dt class="text-charcoal/60">Sudah dikembalikan</dt><dd>{{ rupiah($p->dikembalikan()) }}</dd></div>
                <div class="flex justify-between text-xs"><dt class="text-charcoal/60">Tetap jadi pemasukan</dt><dd class="font-semibold text-burgundy">{{ rupiah($p->saldoRefund()) }}</dd></div>
                @elseif ($bayar > 0)
                <div class="flex justify-between pt-3 border-t border-gray-100 text-xs"><dt class="text-charcoal/60">Perlakuan dana</dt><dd class="font-semibold text-burgundy">Dikeluarkan dari pemasukan</dd></div>
                @endif
            @else
            <div class="pt-3 border-t border-gray-100 space-y-2 text-xs">
                <div class="flex justify-between"><dt class="text-charcoal/60">DP ({{ config('ikes.dp_persen') }}%)</dt><dd>{{ rupiah($p->dpTarget()) }}@if ($bayar > 0 && $bayar >= $p->dpTarget()) <span class="text-green-700">✓ terpenuhi</span>@endif</dd></div>
                @if ($sisa > 0)<div class="flex justify-between"><dt class="text-charcoal/60">Batas pelunasan</dt><dd class="{{ $p->sudahJatuhTempo() ? 'text-red-700 font-semibold' : '' }}">{{ tgl($p->batasPelunasan()) }}@if ($p->sudahJatuhTempo()) · jatuh tempo @endif</dd></div>@endif
            </div>
            @endif
        </dl></div>
</div>
</div>

<div id="mPay" class="modal {{ ! session('edit_id') && ($errors->has('nominal') || $errors->has('jenis_pembayaran') || $errors->has('bukti_transaksi') || $errors->has('catatan')) ? 'open' : '' }}"><div class="bg-white rounded-2xl w-full max-w-md p-6 sm:p-8 relative my-8">
    <button type="button" data-close class="absolute top-4 right-5 text-2xl text-charcoal/40 hover:text-burgundy">×</button>
    <h3 class="font-serif text-2xl text-burgundy mb-5">Catat Pembayaran</h3>
    <form method="POST" action="{{ route('admin.pemesanan.bayar', $p) }}" enctype="multipart/form-data" class="space-y-4">@csrf
        <div class="grid grid-cols-2 gap-4">
            <div><label class="lbl">Jenis</label><select name="jenis_pembayaran" id="jenis" class="inp"><option @selected($bayar == 0)>DP</option><option @selected($bayar > 0)>Pelunasan</option></select></div>
            <div><label class="lbl">Metode</label><select name="metode_bayar" class="inp"><option>Tunai</option><option>Transfer</option></select></div></div>
        <div><label class="lbl">Nominal (Rp)</label><input name="nominal" id="nominal" type="number" min="1" max="{{ $sisa }}" value="{{ old('nominal', $bayar > 0 ? $sisa : min($sisa, $p->dpTarget())) }}" class="inp"></div>
        <div><label class="lbl">Tanggal Bayar</label><input name="tanggal_bayar" type="date" value="{{ old('tanggal_bayar', today()->toDateString()) }}" class="inp"></div>
        <div><label class="lbl">Bukti Transaksi <span class="text-charcoal/40 font-normal">(foto, opsional · maks 4 MB)</span></label><input name="bukti_transaksi" type="file" accept="image/jpeg,image/png,image/webp" class="inp"></div>
        <div><label class="lbl">Catatan <span class="text-charcoal/40 font-normal">(opsional)</span></label><input name="catatan" maxlength="255" value="{{ old('catatan') }}" placeholder="Contoh: Transfer BCA a.n. Budi" class="inp"></div>
        <div class="rounded-xl bg-ivory border border-gold/30 p-4 text-sm space-y-1">
            <div class="flex justify-between"><span class="text-charcoal/60">Total biaya</span><span>{{ rupiah($p->total_biaya) }}</span></div>
            <div class="flex justify-between"><span class="text-charcoal/60">Sudah dibayar</span><span>{{ rupiah($bayar) }}</span></div>
            <div class="flex justify-between font-semibold text-burgundy pt-1 border-t border-gold/30"><span>Sisa tagihan</span><span>{{ rupiah($sisa) }}</span></div></div>
        <button class="btn btn-p w-full py-3">Simpan Pembayaran</button>
    </form></div></div>

<div id="mEdit" class="modal"><div class="bg-white rounded-2xl w-full max-w-md p-6 sm:p-8 relative my-8">
    <button type="button" data-close class="absolute top-4 right-5 text-2xl text-charcoal/40 hover:text-burgundy">×</button>
    <h3 class="font-serif text-2xl text-burgundy mb-1">Edit Pembayaran</h3><p class="text-xs text-charcoal/60 mb-5">Status pembayaran (Belum Bayar / DP / Lunas) dihitung ulang otomatis dari total yang tercatat.</p>
    <form id="fEdit" method="POST" action="" enctype="multipart/form-data" class="space-y-4">@csrf @method('PUT')
        <div class="grid grid-cols-2 gap-4">
            <div><label class="lbl">Jenis</label><select name="jenis_pembayaran" class="inp"><option>DP</option><option>Pelunasan</option></select></div>
            <div><label class="lbl">Metode</label><select name="metode_bayar" class="inp"><option>Tunai</option><option>Transfer</option></select></div></div>
        <div><label class="lbl">Nominal (Rp)</label><input name="nominal" type="number" min="1" class="inp"></div>
        <div><label class="lbl">Tanggal Bayar</label><input name="tanggal_bayar" type="date" class="inp"></div>
        <div><label class="lbl">Bukti Transaksi</label>
            <div id="buktiLamaWrap" class="mb-2 hidden"><a id="buktiLamaLink" href="#" target="_blank" rel="noopener"><img id="buktiLamaImg" src="" alt="Bukti saat ini" class="h-28 rounded-lg border border-gold/40 object-cover"></a>
                <label class="flex items-center gap-2 text-xs text-charcoal/60 mt-2"><input type="checkbox" name="hapus_bukti" value="1"> Hapus bukti saat ini</label></div>
            <p id="buktiKosong" class="text-xs text-charcoal/40 mb-2">Belum ada bukti.</p>
            <input name="bukti_transaksi" type="file" accept="image/jpeg,image/png,image/webp" class="inp"><p class="text-[11px] text-charcoal/40 mt-1">Pilih file baru untuk mengganti. JPG/PNG/WEBP, maks 4 MB.</p></div>
        <div><label class="lbl">Catatan <span class="text-charcoal/40 font-normal">(opsional)</span></label><input name="catatan" maxlength="255" class="inp"></div>
        <div><label class="lbl">Alasan Koreksi <span class="text-red-600">*</span></label><input name="alasan_pembayaran" maxlength="255" class="inp" placeholder="Contoh: Salah input nominal" required></div>
        <button class="btn btn-p w-full py-3">Simpan Perubahan</button>
    </form></div></div>

<div id="mHapus" class="modal"><div class="bg-white rounded-2xl w-full max-w-md p-6 sm:p-8 relative my-8">
    <button type="button" data-close class="absolute top-4 right-5 text-2xl text-charcoal/40 hover:text-burgundy">×</button>
    <h3 class="font-serif text-2xl text-burgundy mb-1">Hapus Pembayaran</h3>
    <p class="text-xs text-charcoal/60 mb-1">Pembayaran: <strong id="hapusInfo"></strong></p>
    <p class="text-xs text-charcoal/60 mb-5">Nominal ini tidak akan dihitung lagi dan status pembayaran dihitung ulang. Datanya tetap tersimpan di riwayat koreksi untuk audit.</p>
    <form id="fHapus" method="POST" action="" class="space-y-4">@csrf @method('DELETE')
        <div><label class="lbl">Alasan Penghapusan <span class="text-red-600">*</span></label><input name="alasan_hapus" maxlength="255" class="inp" placeholder="Contoh: Input ganda" required></div>
        <button class="btn btn-p w-full py-3 !bg-red-700">Hapus Pembayaran</button>
    </form></div></div>

@php $refundBuka = $errors->hasAny(['nominal_refund', 'metode_refund', 'tanggal_refund', 'catatan_refund', 'pengembalian']); @endphp
<div id="mRefund" class="modal {{ $refundBuka ? 'open' : '' }}"><div class="bg-white rounded-2xl w-full max-w-md p-6 sm:p-8 relative my-8">
    <button type="button" data-close class="absolute top-4 right-5 text-2xl text-charcoal/40 hover:text-burgundy">×</button>
    <h3 class="font-serif text-2xl text-burgundy mb-1">Catat Pengembalian Dana</h3>
    <p class="text-xs text-charcoal/60 mb-5">Pesanan ini dibatalkan. Catat dana yang dikembalikan ke klien. Dana yang tidak dikembalikan (misalnya DP hangus) tidak perlu dicatat.</p>
    <form method="POST" action="{{ route('admin.pemesanan.pengembalian', $p) }}" class="space-y-4">@csrf
        <div class="rounded-xl bg-ivory border border-gold/30 p-4 text-sm space-y-1">
            <div class="flex justify-between"><span class="text-charcoal/60">Total dibayar klien</span><span>{{ rupiah($bayar) }}</span></div>
            <div class="flex justify-between"><span class="text-charcoal/60">Sudah dikembalikan</span><span>{{ rupiah($p->dikembalikan()) }}</span></div>
            <div class="flex justify-between font-semibold text-burgundy pt-1 border-t border-gold/30"><span>Dapat dikembalikan</span><span>{{ rupiah($p->saldoRefund()) }}</span></div></div>
        <div><label class="lbl">Nominal (Rp)</label><input name="nominal_refund" type="number" min="1" max="{{ $p->saldoRefund() }}" value="{{ old('nominal_refund', $p->saldoRefund()) }}" class="inp"></div>
        <div><label class="lbl">Metode</label><select name="metode_refund" class="inp"><option @selected(old('metode_refund') === 'Tunai')>Tunai</option><option @selected(old('metode_refund') === 'Transfer')>Transfer</option></select></div>
        <div><label class="lbl">Tanggal Pengembalian</label><input name="tanggal_refund" type="date" max="{{ today()->toDateString() }}" value="{{ old('tanggal_refund', today()->toDateString()) }}" class="inp"></div>
        <div><label class="lbl">Catatan <span class="text-charcoal/40 font-normal">(opsional)</span></label><input name="catatan_refund" maxlength="255" value="{{ old('catatan_refund') }}" placeholder="Contoh: DP dikembalikan sesuai kebijakan" class="inp"></div>
        <button class="btn btn-p w-full py-3">Simpan Pengembalian</button>
    </form></div></div>

@php $rinciBuka = collect($errors->keys())->contains(fn ($k) => in_array(explode('.', $k)[0], ['item', 'baru', 'diskon', 'alasan_rincian', 'rincian'])); @endphp
<div id="mRinci" class="modal {{ $rinciBuka ? 'open' : '' }}"><div class="bg-white rounded-2xl w-full max-w-2xl p-6 sm:p-8 relative my-8">
    <button type="button" data-close class="absolute top-4 right-5 text-2xl text-charcoal/40 hover:text-burgundy">×</button>
    <h3 class="font-serif text-2xl text-burgundy mb-1">Ubah Rincian Pesanan</h3>
    <p class="text-xs text-charcoal/60 mb-5">Total baru = subtotal − diskon. Total tidak boleh lebih kecil dari pembayaran yang sudah tercatat ({{ rupiah($bayar) }}). Status pembayaran dihitung ulang otomatis dan perubahan dicatat di riwayat.</p>
    <form method="POST" action="{{ route('admin.pemesanan.rincian', $p) }}" class="space-y-5">@csrf @method('PUT')
        <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Layanan</th><th>Jumlah</th><th>Harga Satuan (Rp)</th><th>Hapus</th></tr></thead><tbody>
            @foreach ($p->detail as $d)
            <tr><td>{{ $d->layanan->nama }}</td>
                <td><input type="number" min="1" name="item[{{ $d->id }}][jumlah]" value="{{ old('item.' . $d->id . '.jumlah', $d->jumlah) }}" class="inp w-24"></td>
                <td><input type="number" min="0" name="item[{{ $d->id }}][harga_satuan]" value="{{ old('item.' . $d->id . '.harga_satuan', $d->harga_satuan) }}" class="inp w-36"></td>
                <td><input type="checkbox" name="item[{{ $d->id }}][hapus]" value="1" aria-label="Hapus {{ $d->layanan->nama }}" @checked(old('item.' . $d->id . '.hapus'))></td></tr>
            @endforeach
        </tbody></table></div>
        <div class="rounded-xl bg-ivory border border-gold/30 p-4 space-y-3">
            <p class="text-sm font-medium text-burgundy">Tambah layanan <span class="text-charcoal/40 font-normal">(opsional)</span></p>
            <select name="baru[layanan_id]" class="inp" onchange="this.form.elements['baru[harga_satuan]'].placeholder = this.selectedOptions[0].dataset.harga ? 'Harga katalog: ' + this.selectedOptions[0].dataset.harga : 'Harga katalog'">
                <option value="">— Tidak menambah layanan —</option>
                @foreach ($layananAktif->groupBy('kategori') as $kat => $items)
                    <optgroup label="{{ $kat }}">@foreach ($items as $l)<option value="{{ $l->id }}" data-harga="{{ $l->harga }}" @selected((string) old('baru.layanan_id') === (string) $l->id)>{{ $l->nama }} ({{ rupiah($l->harga) }} {{ $l->satuan }})</option>@endforeach</optgroup>
                @endforeach
            </select>
            <div class="grid grid-cols-2 gap-3">
                <input type="number" min="1" name="baru[jumlah]" value="{{ old('baru.jumlah', 1) }}" class="inp" placeholder="Jumlah" aria-label="Jumlah layanan baru">
                <input type="number" min="0" name="baru[harga_satuan]" value="{{ old('baru.harga_satuan') }}" class="inp" placeholder="Harga katalog" aria-label="Harga satuan layanan baru">
            </div>
            <p class="text-[11px] text-charcoal/50">Kosongkan harga untuk memakai harga katalog saat ini.</p>
        </div>
        <div><label class="lbl">Diskon (Rp)</label><input name="diskon" type="number" min="0" value="{{ old('diskon', $p->diskon) }}" class="inp"></div>
        <div><label class="lbl">Alasan Perubahan <span class="text-red-600">*</span></label><input name="alasan_rincian" maxlength="255" value="{{ old('alasan_rincian') }}" class="inp" placeholder="Contoh: Klien menambah 50 porsi" required></div>
        <button class="btn btn-p w-full py-3">Simpan Rincian</button>
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
const selStatus = document.getElementById('selStatus'), boxBatal = document.getElementById('boxBatal');
if (selStatus && boxBatal) selStatus.onchange = () => boxBatal.classList.toggle('hidden', selStatus.value !== 'Dibatalkan');

const jenis = document.getElementById('jenis'), nom = document.getElementById('nominal');
if (jenis && nom) jenis.onchange = () => { nom.value = jenis.value === 'DP' ? Math.min({{ $sisa }}, {{ $p->dpTarget() }}) : {{ $sisa }}; };

(() => {
    const f = document.getElementById('fEdit');
    document.querySelectorAll('[data-edit]').forEach(b => b.addEventListener('click', () => {
        const d = b.dataset, el = f.elements;
        f.action = d.action;
        el.jenis_pembayaran.value = d.jenis; el.metode_bayar.value = d.metode;
        el.nominal.value = d.nominal; el.nominal.max = d.max; el.tanggal_bayar.value = d.tanggal; el.catatan.value = d.catatan || '';
        el.bukti_transaksi.value = ''; el.hapus_bukti.checked = false; el.alasan_pembayaran.value = '';
        const ada = !!d.bukti;
        document.getElementById('buktiLamaWrap').classList.toggle('hidden', !ada);
        document.getElementById('buktiKosong').classList.toggle('hidden', ada);
        if (ada) { document.getElementById('buktiLamaImg').src = d.bukti; document.getElementById('buktiLamaLink').href = d.bukti; }
        openModal('mEdit');
    }));
    document.querySelectorAll('[data-hapus]').forEach(b => b.addEventListener('click', () => {
        const fh = document.getElementById('fHapus');
        fh.action = b.dataset.action; fh.elements.alasan_hapus.value = '';
        document.getElementById('hapusInfo').textContent = b.dataset.info;
        openModal('mHapus');
    }));
    @if (session('edit_id'))document.querySelector('[data-edit="{{ session('edit_id') }}"]')?.click();@endif
})();
</script>
@endpush
