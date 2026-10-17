{{-- Tailwind CDN + font + CSS bersama (sama dengan prototipe HTML) --}}
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = { theme: { extend: {
  colors: { burgundy: { DEFAULT: '#4A0E17', light: '#6B1A27', dark: '#33090F' }, gold: { DEFAULT: '#D4AF37', light: '#E6C964', dark: '#B8932A' }, ivory: '#FDFBF7', charcoal: '#222222' },
  fontFamily: { serif: ['"Playfair Display"', 'serif'], display: ['"Cormorant Garamond"', 'serif'], sans: ['Poppins', 'sans-serif'] }
} } };
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Playfair+Display:wght@500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/ikes.css') }}">
