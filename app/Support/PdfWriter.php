<?php

namespace App\Support;

/**
 * Penulis PDF sederhana tanpa dependensi Composer (A4 lanskap, font Helvetica bawaan PDF).
 * Mendukung judul, subjudul, teks, dan tabel yang otomatis pindah halaman (header tabel diulang)
 * serta nomor halaman. Teks UTF-8 dikonversi ke Windows-1252; karakter di luar itu menjadi "?".
 */
class PdfWriter
{
    private const LEBAR = 841.89;
    private const TINGGI = 595.28;
    private const TEPI = 36;
    private const PAD = 4;
    private const UKURAN = 8.5;
    private const LH = 11;
    private const BURGUNDY = [0.502, 0.0, 0.125];
    private const GOLD = [0.831, 0.686, 0.216];
    private const ABU = [0.4, 0.4, 0.4];

    /** Lebar glyph Helvetica (per 1000 em) untuk ASCII 32..126. */
    private const REGULAR = [
        278, 278, 355, 556, 556, 889, 667, 191, 333, 333, 389, 584, 278, 333, 278, 278,
        556, 556, 556, 556, 556, 556, 556, 556, 556, 556, 278, 278, 584, 584, 584, 556, 1015,
        667, 667, 722, 722, 667, 611, 778, 722, 278, 500, 667, 556, 833, 722, 778, 667, 778, 722, 667, 611, 722, 667, 944, 667, 667, 611,
        278, 278, 278, 469, 556, 333,
        556, 556, 500, 556, 556, 278, 556, 556, 222, 222, 500, 222, 833, 556, 556, 556, 556, 333, 500, 278, 556, 500, 722, 500, 500, 500,
        334, 260, 334, 584,
    ];
    private const BOLD = [
        278, 333, 474, 556, 556, 889, 722, 238, 333, 333, 389, 584, 278, 333, 278, 278,
        556, 556, 556, 556, 556, 556, 556, 556, 556, 556, 333, 333, 584, 584, 584, 611, 975,
        722, 722, 722, 722, 667, 611, 778, 722, 278, 556, 722, 611, 833, 722, 778, 667, 778, 722, 667, 611, 722, 667, 944, 667, 667, 611,
        333, 278, 333, 584, 556, 333,
        556, 611, 556, 611, 556, 333, 611, 611, 278, 278, 556, 278, 889, 611, 611, 611, 611, 389, 556, 333, 611, 556, 778, 556, 556, 500,
        389, 280, 389, 584,
    ];

    /** @var string[] isi halaman yang sudah selesai */
    private array $halaman = [];
    private string $buf = '';
    private float $y;

    public function __construct(private string $footer = '')
    {
        $this->y = self::TINGGI - self::TEPI;
    }

    public function title(string $judul, string $sub = ''): void
    {
        $this->teks(self::TEPI, $this->y - 18, $this->enc($judul), 18, true, self::BURGUNDY);
        $this->y -= 24;
        if ($sub !== '') {
            $this->teks(self::TEPI, $this->y - 10, $this->enc($sub), 10, false, self::ABU);
            $this->y -= 16;
        }
        $this->kotak(self::TEPI, $this->y - 1.5, self::LEBAR - 2 * self::TEPI, 1.5, self::GOLD);
        $this->y -= 10;
    }

    public function heading(string $teks): void
    {
        $this->pastikan(60);
        $this->y -= 8;
        $this->teks(self::TEPI, $this->y - 12, $this->enc($teks), 12, true, self::BURGUNDY);
        $this->y -= 20;
    }

    public function paragraph(string $teks, float $ukuran = 8): void
    {
        foreach ($this->bungkus($teks, self::LEBAR - 2 * self::TEPI, $ukuran, false) as $baris) {
            $this->pastikan($ukuran + 4);
            $this->teks(self::TEPI, $this->y - $ukuran, $baris, $ukuran, false, self::ABU);
            $this->y -= $ukuran + 3;
        }
        $this->y -= 6;
    }

    /**
     * @param string[]  $head   judul kolom
     * @param array[]   $rows   baris berisi teks per kolom
     * @param number[]  $ratio  perbandingan lebar kolom
     * @param string[]  $align  'L' (default) atau 'R' per kolom
     * @param int[]     $tebal  indeks baris yang dicetak tebal dengan garis emas di atasnya (subtotal/total)
     * @param float     $lebar  pecahan lebar halaman yang dipakai tabel (0..1)
     */
    public function table(array $head, array $rows, array $ratio, array $align = [], array $tebal = [], float $lebar = 1.0): void
    {
        $usable = (self::LEBAR - 2 * self::TEPI) * $lebar;
        $w = array_map(fn ($r) => $r / array_sum($ratio) * $usable, array_values($ratio));

        $this->pastikan(18 + 24);
        $this->kepalaTabel($head, $w, $align);

        if (! $rows) {
            $this->teks(self::TEPI + self::PAD, $this->y - 12, $this->enc('Tidak ada data pada periode ini.'), self::UKURAN, false, self::ABU);
            $this->y -= 22;
            return;
        }

        foreach (array_values($rows) as $i => $row) {
            $tb = in_array($i, $tebal, true);
            $sel = [];
            $n = 1;
            foreach ($w as $c => $lc) {
                $sel[$c] = $this->bungkus((string) ($row[$c] ?? ''), $lc - 2 * self::PAD, self::UKURAN, $tb);
                $n = max($n, count($sel[$c]));
            }
            $h = $n * self::LH + 6;
            if ($this->y - $h < self::TEPI + 16) {
                $this->halamanBaru();
                $this->kepalaTabel($head, $w, $align);
            }
            if ($i % 2 === 1) {
                $this->kotak(self::TEPI, $this->y - $h, $usable, $h, [0.97, 0.95, 0.93]);
            }
            if ($tb) {
                $this->kotak(self::TEPI, $this->y - 0.8, $usable, 0.8, self::GOLD);
            }
            $x = self::TEPI;
            foreach ($sel as $c => $baris) {
                foreach ($baris as $k => $ln) {
                    $tx = ($align[$c] ?? 'L') === 'R'
                        ? $x + $w[$c] - self::PAD - $this->lebarTeks($ln, self::UKURAN, $tb)
                        : $x + self::PAD;
                    $this->teks($tx, $this->y - 10.5 - $k * self::LH, $ln, self::UKURAN, $tb, [0.15, 0.15, 0.15]);
                }
                $x += $w[$c];
            }
            $this->y -= $h;
            $this->kotak(self::TEPI, $this->y, $usable, 0.4, [0.88, 0.85, 0.82]);
        }
        $this->y -= 10;
    }

    /** Hasil akhir: isi berkas PDF (string biner). */
    public function output(): string
    {
        $isi = [...$this->halaman, $this->buf];
        $n = count($isi);
        foreach ($isi as $i => &$konten) {
            $konten .= $this->tulis(self::TEPI, 20, $this->enc($this->footer), 7.5, false, self::ABU);
            $label = $this->enc('Halaman ' . ($i + 1) . ' / ' . $n);
            $konten .= $this->tulis(self::LEBAR - self::TEPI - $this->lebarTeks($label, 7.5, false), 20, $label, 7.5, false, self::ABU);
        }
        unset($konten);

        $obj = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            3 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
            4 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>',
        ];
        $kids = [];
        foreach ($isi as $i => $konten) {
            $idHal = 5 + $i * 2;
            $kids[] = $idHal . ' 0 R';
            $obj[$idHal] = sprintf('<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %.2F %.2F] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents %d 0 R >>', self::LEBAR, self::TINGGI, $idHal + 1);
            $z = function_exists('gzcompress') ? gzcompress($konten, 6) : false;
            $obj[$idHal + 1] = $z !== false
                ? '<< /Length ' . strlen($z) . " /Filter /FlateDecode >>\nstream\n" . $z . "\nendstream"
                : '<< /Length ' . strlen($konten) . " >>\nstream\n" . $konten . "\nendstream";
        }
        $obj[2] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . $n . ' >>';
        ksort($obj);

        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offset = [];
        foreach ($obj as $id => $isiObj) {
            $offset[$id] = strlen($pdf);
            $pdf .= $id . " 0 obj\n" . $isiObj . "\nendobj\n";
        }
        $xref = strlen($pdf);
        $jumlah = count($obj) + 1;
        $pdf .= "xref\n0 $jumlah\n0000000000 65535 f \n";
        foreach ($offset as $o) {
            $pdf .= sprintf("%010d 00000 n \n", $o);
        }
        return $pdf . "trailer\n<< /Size $jumlah /Root 1 0 R >>\nstartxref\n$xref\n%%EOF\n";
    }

    /* ---------- internal ---------- */

    private function kepalaTabel(array $head, array $w, array $align): void
    {
        $this->kotak(self::TEPI, $this->y - 18, array_sum($w), 18, self::BURGUNDY);
        $x = self::TEPI;
        foreach ($w as $c => $lc) {
            $t = $this->enc((string) ($head[$c] ?? ''));
            $tx = ($align[$c] ?? 'L') === 'R' ? $x + $lc - self::PAD - $this->lebarTeks($t, self::UKURAN, true) : $x + self::PAD;
            $this->teks($tx, $this->y - 12.5, $t, self::UKURAN, true, [1, 1, 1]);
            $x += $lc;
        }
        $this->y -= 18;
    }

    private function pastikan(float $tinggi): void
    {
        if ($this->y - $tinggi < self::TEPI + 16) {
            $this->halamanBaru();
        }
    }

    private function halamanBaru(): void
    {
        $this->halaman[] = $this->buf;
        $this->buf = '';
        $this->y = self::TINGGI - self::TEPI;
    }

    private function teks(float $x, float $y, string $enc, float $ukuran, bool $tebal, array $rgb): void
    {
        $this->buf .= $this->tulis($x, $y, $enc, $ukuran, $tebal, $rgb);
    }

    private function tulis(float $x, float $y, string $enc, float $ukuran, bool $tebal, array $rgb): string
    {
        $esc = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $enc);
        return sprintf("BT /F%d %.1F Tf %.3F %.3F %.3F rg %.2F %.2F Td (%s) Tj ET\n", $tebal ? 2 : 1, $ukuran, $rgb[0], $rgb[1], $rgb[2], $x, $y, $esc);
    }

    private function kotak(float $x, float $y, float $w, float $h, array $rgb): void
    {
        $this->buf .= sprintf("%.3F %.3F %.3F rg %.2F %.2F %.2F %.2F re f\n", $rgb[0], $rgb[1], $rgb[2], $x, $y, $w, $h);
    }

    /** UTF-8 -> Windows-1252 (satu byte per karakter), tanpa karakter kontrol. */
    private function enc(string $s): string
    {
        $s = str_replace(["\u{2212}", "\r", "\n", "\t"], ['-', ' ', ' ', ' '], $s);
        $s = preg_replace('/[\x00-\x1F\x7F]/', '', $s) ?? '';
        $e = function_exists('mb_convert_encoding') ? mb_convert_encoding($s, 'Windows-1252', 'UTF-8') : @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $s);
        return is_string($e) ? $e : '';
    }

    private function lebarTeks(string $enc, float $ukuran, bool $tebal): float
    {
        $tabel = $tebal ? self::BOLD : self::REGULAR;
        $total = 0;
        for ($i = 0, $n = strlen($enc); $i < $n; $i++) {
            $o = ord($enc[$i]);
            $total += ($o >= 32 && $o <= 126) ? $tabel[$o - 32] : 556;
        }
        return $total * $ukuran / 1000;
    }

    /** Pecah teks menjadi baris selebar $maks. Spasi di awal (indentasi) dipertahankan. @return string[] (sudah ter-encode) */
    private function bungkus(string $s, float $maks, float $ukuran, bool $tebal): array
    {
        $s = $this->enc($s);
        $lead = strlen($s) - strlen(ltrim($s, ' '));
        $baris = [];
        $cur = str_repeat(' ', $lead);
        $kosong = true; // belum ada kata pada baris berjalan
        foreach (explode(' ', ltrim($s, ' ')) as $kata) {
            $coba = $kosong ? $cur . $kata : $cur . ' ' . $kata;
            if ($this->lebarTeks($coba, $ukuran, $tebal) <= $maks) {
                $cur = $coba;
                $kosong = false;
                continue;
            }
            if (! $kosong) {
                $baris[] = $cur;
                $cur = '';
                $kosong = true;
            }
            // kata tunggal terlalu panjang: potong per karakter
            while ($kata !== '' && $this->lebarTeks($cur . $kata, $ukuran, $tebal) > $maks) {
                $potong = max(1, strlen($kata) - 1);
                while ($potong > 1 && $this->lebarTeks($cur . substr($kata, 0, $potong), $ukuran, $tebal) > $maks) {
                    $potong--;
                }
                $baris[] = $cur . substr($kata, 0, $potong);
                $cur = '';
                $kata = substr($kata, $potong);
            }
            $cur .= $kata;
            $kosong = false;
        }
        $baris[] = $cur;
        return $baris;
    }
}
