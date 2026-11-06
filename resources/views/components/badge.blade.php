@props(['t'])
@php
    $c = ['Menunggu Konfirmasi' => 'amber', 'Dikonfirmasi' => 'blue', 'Selesai' => 'green', 'Dibatalkan' => 'red', 'Belum Bayar' => 'red', 'DP' => 'amber', 'Lunas' => 'green', 'Menunggu' => 'amber', 'Tampil' => 'green', 'Disembunyikan' => 'gray'][$t] ?? 'gray';
@endphp
<span class="badge b-{{ $c }}">{{ $t }}</span>
