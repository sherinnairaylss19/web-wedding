<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Ringkasan Pemesanan – Ike's Gallery</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<script>
  tailwind.config = { theme: { extend: {
    colors: { burgundy: { DEFAULT:'#4A0E17', light:'#6B1A27', dark:'#33090F' }, gold: { DEFAULT:'#D4AF37', dark:'#B8932A' }, ivory:'#FDFBF7' },
    fontFamily: { serif:['"Playfair Display"','serif'], sans:['Poppins','sans-serif'] } } } }
</script>
</head>
<body class="bg-ivory font-sans text-gray-800 min-h-screen py-10 px-4">
<main class="max-w-xl mx-auto">
  <div class="text-center mb-8">
    <p class="text-gold-dark tracking-[0.3em] text-xs uppercase mb-2">Pemesanan Diterima</p>
    <h1 class="font-serif text-3xl text-burgundy">Terima kasih, {{ $pemesanan->klien->nama }}</h1>
  </div>

  <div class="bg-white rounded-3xl shadow-xl border border-gold/30 overflow-hidden">
    <div class="bg-burgundy text-center py-6">
      <p class="text-white/70 text-xs uppercase tracking-widest">Kode Pemesanan</p>
      <p class="font-serif text-3xl text-gold mt-1 select-all">{{ $pemesanan->kode_pemesanan }}</p>
      <p class="text-white/60 text-xs mt-2">Simpan kode ini — dibutuhkan untuk mengirim testimoni.</p>
    </div>
    <dl class="p-6 text-sm divide-y divide-gray-100">
      @foreach($pemesanan->detail as $d)
      <div class="py-3 flex justify-between gap-4">
        <dt class="text-gray-500">Layanan</dt>
        <dd class="text-right font-medium">{{ $d->layanan->nama }}<br>
          <span class="text-xs font-normal text-gray-500">{{ $d->jumlah }} × Rp {{ number_format($d->harga_satuan, 0, ',', '.') }}</span></dd>
      </div>
      @endforeach
      <div class="py-3 flex justify-between"><dt class="text-gray-500">Tanggal acara</dt><dd class="font-medium">{{ $pemesanan->tanggal_acara->translatedFormat('l, d F Y') }}</dd></div>
      @if($pemesanan->lokasi_acara)
      <div class="py-3 flex justify-between gap-4"><dt class="text-gray-500">Lokasi</dt><dd class="font-medium text-right">{{ $pemesanan->lokasi_acara }}</dd></div>
      @endif
      <div class="py-3 flex justify-between"><dt class="text-gray-500">Estimasi total</dt><dd class="font-semibold text-burgundy">Rp {{ number_format($pemesanan->total_biaya, 0, ',', '.') }}</dd></div>
      <div class="py-3 flex justify-between"><dt class="text-gray-500">Status</dt>
        <dd><span class="bg-gold/20 text-gold-dark px-3 py-1 rounded-full text-xs font-semibold">{{ $pemesanan->status_pemesanan }}</span></dd></div>
    </dl>
    <div class="p-6 pt-0 space-y-3">
      <a href="{{ $waUrl }}" target="_blank" rel="noopener"
         class="block text-center bg-burgundy hover:bg-burgundy-light text-white font-semibold tracking-wide py-3.5 rounded-xl border border-gold/40">
        Konsultasi via WhatsApp
      </a>
      <a href="{{ route('beranda') }}" class="block text-center text-sm text-burgundy hover:underline">← Kembali ke beranda</a>
    </div>
  </div>
  <p class="text-center text-xs text-gray-500 mt-5">Total di atas adalah estimasi. Tim kami akan mengonfirmasi detail dan harga final melalui WhatsApp.</p>
</main>
</body>
</html>
