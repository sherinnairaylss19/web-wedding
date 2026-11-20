<?php

namespace Tests\Feature;

use App\Models\Klien;
use App\Models\Pemesanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Kode pemesanan (IKG-TAHUN-NNN) harus unik dan berurutan walau ada pesanan terhapus atau pergantian tahun. */
class KodePemesananTest extends TestCase
{
    use RefreshDatabase;

    /** Meniru store(): kode dan pesanan dibuat dalam satu transaksi. */
    private function buat(): Pemesanan
    {
        return DB::transaction(function () {
            $k = Klien::firstOrCreate(['no_hp' => '081200000001'], ['nama' => 'Penguji']);
            return Pemesanan::create([
                'kode' => Pemesanan::buatKode(), 'klien_id' => $k->id,
                'tanggal_acara' => now()->addMonth(), 'lokasi_acara' => 'Uji', 'total_biaya' => 1000,
            ]);
        });
    }

    public function test_kode_berurutan_dalam_satu_tahun(): void
    {
        $y = now()->year;
        $this->assertSame("IKG-$y-001", $this->buat()->kode);
        $this->assertSame("IKG-$y-002", $this->buat()->kode);
        $this->assertSame("IKG-$y-003", $this->buat()->kode);
    }

    public function test_kode_pesanan_yang_dihapus_tidak_dipakai_ulang(): void
    {
        $y = now()->year;
        $this->buat();
        $b = $this->buat();
        $b->delete();   // soft delete: kodenya tetap tercatat

        $this->assertSame("IKG-$y-003", $this->buat()->kode);
    }

    public function test_kode_tidak_dipakai_ulang_walau_pesanan_dihapus_permanen(): void
    {
        $y = now()->year;
        $this->buat();
        $b = $this->buat();
        $b->forceDelete();   // dulu count() mundur ke 1 lalu menghasilkan 002 lagi (bentrok); sekarang tetap 003

        $this->assertSame("IKG-$y-003", $this->buat()->kode);
    }

    public function test_tahun_baru_mulai_lagi_dari_satu(): void
    {
        $this->travelTo(now()->setDate(2030, 12, 31));
        $this->buat();
        $this->assertSame('IKG-2030-002', $this->buat()->kode);

        $this->travelTo(now()->setDate(2031, 1, 1));
        $this->assertSame('IKG-2031-001', $this->buat()->kode);
    }

    public function test_transaksi_gagal_tidak_menghabiskan_nomor(): void
    {
        $y = now()->year;
        try {
            DB::transaction(function () {
                Pemesanan::buatKode();
                throw new \RuntimeException('gagal setelah mengambil nomor');
            });
        } catch (\RuntimeException) {
        }

        $this->assertSame("IKG-$y-001", $this->buat()->kode);   // nomor 001 tidak bolong
    }
}
