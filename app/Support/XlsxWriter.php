<?php

namespace App\Support;

use RuntimeException;
use ZipArchive;

/**
 * Penulis berkas .xlsx minimal tanpa dependensi Composer (hanya butuh ekstensi PHP zip).
 * Mendukung banyak sheet, angka, teks, format Rupiah, header berwarna, lebar kolom, dan freeze baris pertama.
 *
 * Sel: nilai biasa (int/float = angka, string = teks) atau ['v' => nilai, 's' => gaya].
 * Gaya: 'head' (header burgundy), 'bold', 'money', 'moneyb' (Rupiah tebal), 'title'.
 */
class XlsxWriter
{
    private const STYLE = ['default' => 0, 'head' => 1, 'money' => 2, 'bold' => 3, 'moneyb' => 4, 'title' => 5];

    /** @var array<int, array{name: string, rows: array, widths: array, freeze: bool}> */
    private array $sheets = [];

    public static function head(string $t): array { return ['v' => $t, 's' => 'head']; }
    public static function bold(mixed $v): array { return ['v' => $v, 's' => 'bold']; }
    public static function money(int|float $n): array { return ['v' => $n, 's' => 'money']; }
    public static function moneyBold(int|float $n): array { return ['v' => $n, 's' => 'moneyb']; }
    public static function title(string $t): array { return ['v' => $t, 's' => 'title']; }

    public function sheet(string $name, array $rows, array $widths = [], bool $freezeHeader = false): static
    {
        $this->sheets[] = ['name' => $this->namaSheet($name), 'rows' => $rows, 'widths' => $widths, 'freeze' => $freezeHeader];
        return $this;
    }

    /** Tulis ke $path. Melempar RuntimeException bila ekstensi zip tidak aktif atau berkas gagal dibuat. */
    public function save(string $path): void
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('Ekstensi PHP "zip" belum aktif, sehingga berkas Excel tidak dapat dibuat.');
        }
        if (! $this->sheets) {
            $this->sheet('Sheet1', []);
        }
        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Berkas Excel tidak dapat dibuat.');
        }
        $n = count($this->sheets);
        $ct = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';
        $wb = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets>';
        $rel = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
        foreach ($this->sheets as $i => $s) {
            $id = $i + 1;
            $ct .= '<Override PartName="/xl/worksheets/sheet' . $id . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
            $wb .= '<sheet name="' . $this->esc($s['name']) . '" sheetId="' . $id . '" r:id="rId' . $id . '"/>';
            $rel .= '<Relationship Id="rId' . $id . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . $id . '.xml"/>';
            $zip->addFromString('xl/worksheets/sheet' . $id . '.xml', $this->sheetXml($s));
        }
        $rel .= '<Relationship Id="rId' . ($n + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>';
        $zip->addFromString('[Content_Types].xml', $ct . '</Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $zip->addFromString('xl/workbook.xml', $wb . '</sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', $rel);
        $zip->addFromString('xl/styles.xml', $this->stylesXml());
        if (! $zip->close()) {
            throw new RuntimeException('Berkas Excel gagal ditulis.');
        }
    }

    private function sheetXml(array $s): string
    {
        $x = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
        if ($s['freeze']) {
            $x .= '<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>';
        }
        if ($s['widths']) {
            $x .= '<cols>';
            foreach (array_values($s['widths']) as $i => $w) {
                $x .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . (float) $w . '" customWidth="1"/>';
            }
            $x .= '</cols>';
        }
        $x .= '<sheetData>';
        foreach (array_values($s['rows']) as $r => $row) {
            $x .= '<row r="' . ($r + 1) . '">';
            foreach (array_values($row) as $c => $cell) {
                $x .= $this->cellXml($this->kolom($c) . ($r + 1), $cell);
            }
            $x .= '</row>';
        }
        return $x . '</sheetData></worksheet>';
    }

    private function cellXml(string $ref, mixed $cell): string
    {
        $style = 'default';
        if (is_array($cell)) {
            $style = $cell['s'] ?? 'default';
            $cell = $cell['v'] ?? null;
        }
        if ($cell === null || $cell === '') {
            return self::STYLE[$style] ? '<c r="' . $ref . '" s="' . self::STYLE[$style] . '"/>' : '';
        }
        $s = self::STYLE[$style] ?? 0;
        $sAttr = $s ? ' s="' . $s . '"' : '';
        if (is_int($cell) || is_float($cell)) {
            return '<c r="' . $ref . '"' . $sAttr . '><v>' . (is_float($cell) ? rtrim(rtrim(number_format($cell, 6, '.', ''), '0'), '.') : $cell) . '</v></c>';
        }
        // Teks disimpan sebagai inlineStr sehingga tidak pernah ditafsirkan Excel sebagai rumus.
        return '<c r="' . $ref . '"' . $sAttr . ' t="inlineStr"><is><t xml:space="preserve">' . $this->esc((string) $cell) . '</t></is></c>';
    }

    private function stylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<numFmts count="1"><numFmt numFmtId="164" formatCode="&quot;Rp &quot;#,##0;[Red]\-&quot;Rp &quot;#,##0"/></numFmts>'
            . '<fonts count="4"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="14"/><color rgb="FF800020"/><name val="Calibri"/></font></fonts>'
            . '<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF800020"/><bgColor indexed="64"/></patternFill></fill></fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="6">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>'
            . '<xf numFmtId="164" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
            . '<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
            . '<xf numFmtId="164" fontId="2" fillId="0" borderId="0" xfId="0" applyNumberFormat="1" applyFont="1"/>'
            . '<xf numFmtId="0" fontId="3" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
            . '</cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>';
    }

    /** 0 -> A, 25 -> Z, 26 -> AA. */
    private function kolom(int $i): string
    {
        $s = '';
        for ($i++; $i > 0; $i = intdiv($i - 1, 26)) {
            $s = chr(65 + ($i - 1) % 26) . $s;
        }
        return $s;
    }

    private function esc(string $v): string
    {
        $v = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $v) ?? '';
        return htmlspecialchars($v, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    /** Nama sheet Excel: maks. 31 karakter, tanpa []:*?/\ dan unik. */
    private function namaSheet(string $n): string
    {
        $n = trim(preg_replace('/[\[\]:*?\/\\\\]/', ' ', $n) ?? '') ?: 'Sheet';
        $n = mb_substr($n, 0, 31);
        $asli = $n;
        for ($i = 2; in_array(mb_strtolower($n), array_map(fn ($s) => mb_strtolower($s['name']), $this->sheets), true); $i++) {
            $n = mb_substr($asli, 0, 28) . ' ' . $i;
        }
        return $n;
    }
}
