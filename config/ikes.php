<?php

return [
    'wa_number' => env('IKES_WA', '6282249346097'),   // format internasional tanpa +
    // Opsional: token Fonnte (fonnte.com) agar owner dapat pesan WA otomatis tiap ada pemesanan baru.
    // Kosongkan untuk menonaktifkan; alur click-to-chat tetap berjalan.
    'fonnte_token' => env('FONNTE_TOKEN'),
    'phone'     => env('IKES_PHONE', '+62-822-4934-6097'),
    'email'     => env('IKES_EMAIL', 'ikesunaniwedding@gmail.com'),

    // Kebijakan pembayaran. Ubah lewat .env (IKES_DP_PERSEN, IKES_PELUNASAN_HARI) tanpa menyentuh kode.
    // DP: persentase total biaya. Pembayaran yang mencapai nilai ini otomatis mengonfirmasi pesanan.
    'dp_persen' => (int) env('IKES_DP_PERSEN', 50),
    // Pelunasan paling lambat N hari sebelum tanggal acara; setelah itu pesanan ditandai jatuh tempo.
    'pelunasan_hari' => (int) env('IKES_PELUNASAN_HARI', 7),
    'address'   => env('IKES_ADDRESS', "Alamat Ike's Gallery (isi di .env)"),
];
