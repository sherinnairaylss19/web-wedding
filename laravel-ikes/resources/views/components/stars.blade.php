@props(['n' => 0])
<span class="inline-flex items-center gap-0.5">
    @for ($i = 1; $i <= 5; $i++)
        <svg class="w-4 h-4 {{ $i <= round($n) ? 'text-gold' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M10 1.5l2.6 5.5 6 .8-4.4 4.2 1.1 6L10 15l-5.3 3 1.1-6L1.4 7.8l6-.8L10 1.5z"/></svg>
    @endfor
</span>
