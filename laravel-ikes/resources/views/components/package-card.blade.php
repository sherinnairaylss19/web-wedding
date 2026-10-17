@props(['p'])
<article class="card-lift bg-white rounded-2xl overflow-hidden border border-gold/20 shadow-md flex flex-col fade-in">
    <div class="img-fallback relative aspect-[4/3] overflow-hidden">
        @if ($p->foto_url)<img src="{{ $p->foto_url }}" alt="{{ $p->nama }}" loading="lazy" class="w-full h-full object-cover" onerror="this.remove()">@endif
        @if ($p->label)<span class="absolute top-3 left-3 bg-gold text-burgundy-dark text-[11px] font-semibold px-3 py-1 rounded-full shadow">{{ $p->label }}</span>@endif
    </div>
    <div class="p-5 flex flex-col flex-1">
        <p class="text-[11px] uppercase tracking-wider text-gold-dark mb-1">{{ $p->kategori }}</p>
        <h4 class="font-serif text-lg text-burgundy leading-snug">{{ $p->nama }}</h4>
        <p class="text-sm text-charcoal/70 mt-2 leading-relaxed flex-1">{{ $p->deskripsi }}</p>
        <div class="mt-4 pt-4 border-t border-gold/30 flex items-end justify-between gap-2">
            <div>
                <p class="text-[11px] uppercase tracking-wider text-charcoal/50">{{ $p->kategori === 'Wedding' ? 'Mulai dari' : 'Harga' }}</p>
                <p class="font-semibold text-burgundy">{{ rupiah($p->harga) }}<span class="text-xs font-normal text-charcoal/60"> {{ $p->satuan }}</span></p>
            </div>
            <a href="{{ route('pemesanan.create', ['layanan' => $p->id]) }}" class="text-xs font-medium bg-burgundy hover:bg-burgundy-light text-white px-4 py-2 rounded-full transition">Pesan</a>
        </div>
    </div>
</article>
