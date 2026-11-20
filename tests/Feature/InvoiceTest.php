<?php

namespace Tests\Feature;

use App\Models\{Admin, Invoice, Klien, Layanan, Pemesanan};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Invoice membeku saat terbit; perubahan tagihan ditangani lewat revisi, bukan menimpa invoice lama. */
class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    private ?Admin $adm = null;

    /** Satu admin per tes (email unik), dipakai ulang di setiap permintaan. */
    private function admin(): Admin
    {
        return $this->adm ??= Admin::create(['nama' => 'Admin Uji', 'email' => 'uji@ikes.test', 'password' => 'rahasia123']);
    }

    private function pesanan(): Pemesanan
    {
        $l = Layanan::create(['nama' => 'Paket Lama', 'kategori' => 'Wedding', 'deskripsi' => 'x', 'harga' => 10_000_000]);
        $k = Klien::create(['nama' => 'Klien Uji', 'no_hp' => '6281200000001', 'alamat' => 'Indramayu']);
        $p = Pemesanan::create([
            'kode' => 'IKG-2026-001', 'klien_id' => $k->id, 'tanggal_acara' => now()->addMonths(2)->toDateString(),
            'lokasi_acara' => 'Gedung Uji', 'total_biaya' => 10_000_000,
        ]);
        $p->detail()->create(['layanan_id' => $l->id, 'jumlah' => 1, 'harga_satuan' => 10_000_000, 'subtotal' => 10_000_000]);

        return $p;
    }

    /** Meniru ubahRincian: harga naik ke 12,5 jt. */
    private function naikkanHarga(Pemesanan $p): void
    {
        $p->detail()->first()->update(['harga_satuan' => 12_500_000, 'subtotal' => 12_500_000]);
        $p->update(['total_biaya' => 12_500_000]);
    }

    private function bukaInvoice(Pemesanan $p, array $query = [])
    {
        return $this->actingAs($this->admin())->get(route('admin.pemesanan.invoice', ['pemesanan' => $p] + $query));
    }

    public function test_invoice_pertama_diterbitkan_dengan_snapshot_tagihan(): void
    {
        $p = $this->pesanan();

        $res = $this->bukaInvoice($p)->assertOk()->assertSee('INV-2026-001')->assertSee('Paket Lama');

        $inv = Invoice::firstOrFail();
        $this->assertSame(0, $inv->revisi);
        $this->assertSame(10_000_000, $inv->rincian['total']);
        $this->assertSame('Paket Lama', $inv->rincian['items'][0]['nama']);
        $this->assertSame('Klien Uji', $inv->rincian['klien']['nama']);
        $res->assertViewHas('usang', false);
    }

    public function test_mengubah_rincian_dan_nama_layanan_tidak_mengubah_invoice_yang_sudah_terbit(): void
    {
        $p = $this->pesanan();
        $this->bukaInvoice($p);   // terbit dan membeku

        $this->naikkanHarga($p);
        Layanan::firstOrFail()->update(['nama' => 'Paket Baru']);

        $res = $this->bukaInvoice($p)->assertOk()->assertSee('Paket Lama')->assertDontSee('Paket Baru');
        $this->assertSame(10_000_000, $res->viewData('inv')->rincian['total']);   // tetap angka saat terbit
        $res->assertViewHas('usang', true);                                       // dan admin diberi tahu ada perubahan
        $this->assertDatabaseCount('invoice', 1);
    }

    public function test_ganti_nama_layanan_saja_bukan_perubahan_tagihan(): void
    {
        $p = $this->pesanan();
        $this->bukaInvoice($p);

        Layanan::firstOrFail()->update(['nama' => 'Paket Baru']);

        $this->bukaInvoice($p)->assertViewHas('usang', false);
    }

    public function test_revisi_menerbitkan_invoice_baru_dan_invoice_lama_tetap_utuh(): void
    {
        $p = $this->pesanan();
        $this->bukaInvoice($p);
        $this->naikkanHarga($p);

        $this->actingAs($this->admin())
            ->post(route('admin.pemesanan.invoice.revisi', $p), ['alasan_revisi' => 'Harga paket naik'])
            ->assertRedirect(route('admin.pemesanan.invoice', $p));

        $this->assertDatabaseCount('invoice', 2);
        $rev = Invoice::where('revisi', 1)->firstOrFail();
        $this->assertSame('INV-2026-001-R1', $rev->nomor_invoice);
        $this->assertSame(12_500_000, $rev->rincian['total']);
        $this->assertSame('Harga paket naik', $rev->alasan);

        $terbaru = $this->bukaInvoice($p);   // tanpa parameter: revisi terbaru
        $this->assertSame(1, $terbaru->viewData('inv')->revisi);
        $terbaru->assertViewHas('usang', false);

        $lama = $this->bukaInvoice($p, ['revisi' => 0])->assertSee('Digantikan oleh INV-2026-001-R1');
        $this->assertSame(10_000_000, $lama->viewData('inv')->rincian['total']);   // invoice awal tidak tersentuh
    }

    public function test_revisi_ditolak_bila_tagihan_tidak_berubah_atau_alasan_kosong(): void
    {
        $p = $this->pesanan();
        $this->bukaInvoice($p);
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.pemesanan.invoice.revisi', $p), ['alasan_revisi' => 'Iseng'])
            ->assertSessionHasErrors('invoice');

        $this->naikkanHarga($p);
        $this->actingAs($admin)->post(route('admin.pemesanan.invoice.revisi', $p), ['alasan_revisi' => ''])
            ->assertSessionHasErrors('alasan_revisi');

        $this->assertDatabaseCount('invoice', 1);
    }

    public function test_revisi_yang_tidak_ada_memberi_404(): void
    {
        $p = $this->pesanan();
        $this->bukaInvoice($p)->assertOk();

        $this->bukaInvoice($p, ['revisi' => 9])->assertNotFound();
    }
}
