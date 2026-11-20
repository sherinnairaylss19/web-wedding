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
if (!function_exists('normalisasi_hp')) {
    /** 0812-xxx / +62 812 xxx / 812xxx -> 62812xxx, agar nomor yang sama dengan format berbeda bisa dicocokkan. */
    function normalisasi_hp($n): string
    {
        $d = preg_replace('/\D+/', '', (string) $n);
        if (str_starts_with($d, '0')) return '62' . substr($d, 1);
        if (str_starts_with($d, '8')) return '62' . $d;
        return $d;
    }
}
