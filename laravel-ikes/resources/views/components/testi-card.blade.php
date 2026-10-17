@props(['t'])
<figure class="bg-white rounded-2xl p-6 shadow-lg border-t-4 border-gold flex flex-col">
    <svg class="w-8 h-8 text-gold/40 mb-3" fill="currentColor" viewBox="0 0 24 24"><path d="M9.5 6C6.5 7 4 10 4 14v4h6v-6H7c0-2 1-3.5 3-4.3L9.5 6zm10 0c-3 1-5.5 4-5.5 8v4h6v-6h-3c0-2 1-3.5 3-4.3L19.5 6z"/></svg>
    <blockquote class="text-sm text-charcoal/80 leading-relaxed flex-1">{{ $t->isi }}</blockquote>
    <figcaption class="mt-5 pt-5 border-t border-gray-100 flex items-center gap-3">
        <span class="w-12 h-12 rounded-full bg-burgundy text-gold border-2 border-gold flex items-center justify-center font-serif font-semibold shrink-0">{{ Str::of($t->pemesanan->klien->nama)->explode(' ')->take(2)->map(fn($w) => Str::upper(Str::substr($w, 0, 1)))->implode('') }}</span>
        <span class="min-w-0">
            <span class="block font-medium text-burgundy text-sm truncate">{{ $t->pemesanan->klien->nama }}</span>
            <span class="flex items-center mt-0.5"><x-stars :n="$t->rating" /></span>
            <span class="block text-[11px] text-charcoal/50 mt-0.5">Acara: {{ tgl_long($t->pemesanan->tanggal_acara) }}</span>
        </span>
    </figcaption>
</figure>
