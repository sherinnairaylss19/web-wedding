<?php

use Carbon\Carbon;

if (!function_exists('rupiah')) {
    function rupiah($n): string { return 'Rp ' . number_format((int) $n, 0, ',', '.'); }
}
if (!function_exists('tgl')) {
    function tgl($d): string { return $d ? Carbon::parse($d)->translatedFormat('j M Y') : '-'; }
}
if (!function_exists('tgl_long')) {
    function tgl_long($d): string { return $d ? Carbon::parse($d)->translatedFormat('l, j F Y') : '-'; }
}
