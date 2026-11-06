<?php

return [
    'wa_number' => env('IKES_WA', '6282249346097'),   // format internasional tanpa +
    // Opsional: token Fonnte (fonnte.com) agar owner dapat pesan WA otomatis tiap ada pemesanan baru.
    // Kosongkan untuk menonaktifkan; alur click-to-chat tetap berjalan.
    'fonnte_token' => env('FONNTE_TOKEN'),
    'phone'     => env('IKES_PHONE', '+62-822-4934-6097'),
    'email'     => env('IKES_EMAIL', 'ikesunaniwedding@gmail.com'),
    'address'   => env('IKES_ADDRESS', "Alamat Ike's Gallery (isi di .env)"),
];
