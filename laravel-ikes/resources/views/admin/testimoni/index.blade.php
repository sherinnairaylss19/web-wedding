@extends('layouts.admin')
@section('judul', 'Kelola Testimoni')
@section('content')
@php $aksi = ['Menunggu' => [['Tampil', 'Setujui', 'btn-p'], ['Disembunyikan', 'Sembunyikan', 'btn-r']], 'Tampil' => [['Disembunyikan', 'Sembunyikan', 'btn-r']], 'Disembunyikan' => [['Tampil', 'Tampilkan', 'btn-p']]]; @endphp
<div class="flex flex-wrap gap-3 mb-6">
    @foreach (['Semua', 'Menunggu', 'Tampil', 'Disembunyikan'] as $s)
        <a href="{{ route('admin.testimoni.index', $s === 'Semua' ? [] : ['status' => $s]) }}" class="tab {{ $aktif === $s ? 'tab-active' : '' }}">{{ $s }} ({{ $s === 'Semua' ? $hitung->sum() : ($hitung[$s] ?? 0) }})</a>
    @endforeach
</div>
<div class="grid gap-4">
    @forelse ($daftar as $t)
        <div class="card p-5 flex flex-col sm:flex-row gap-4 sm:items-center">
            <span class="w-12 h-12 rounded-full bg-burgundy text-gold border-2 border-gold flex items-center justify-center font-serif font-semibold shrink-0">{{ Str::upper(Str::substr($t->pemesanan->klien->nama, 0, 1)) }}</span>
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1"><span class="font-medium text-burgundy">{{ $t->pemesanan->klien->nama }}</span><x-stars :n="$t->rating" /><x-badge :t="$t->status_tampil" /><span class="font-mono text-[11px] text-charcoal/50">{{ $t->pemesanan->kode }}</span></div>
                <p class="text-sm text-charcoal/75 mt-1.5 leading-relaxed">{{ $t->isi }}</p>
            </div>
            <div class="flex gap-2 shrink-0">
                @foreach ($aksi[$t->status_tampil] as [$to, $label, $cls])
                    <form method="POST" action="{{ route('admin.testimoni.update', $t) }}">@csrf @method('PATCH')<input type="hidden" name="status_tampil" value="{{ $to }}"><button class="btn {{ $cls }} btn-sm">{{ $label }}</button></form>
                @endforeach
            </div>
        </div>
    @empty<p class="text-center text-sm text-charcoal/60 py-12">Tidak ada testimoni pada status ini.</p>@endforelse
</div>
@endsection
