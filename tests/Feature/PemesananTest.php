<?php

namespace Tests\Feature;

use App\Models\Layanan;
use App\Models\Pemesanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PemesananTest extends TestCase
{
    use RefreshDatabase;

    private function layanan(array $o = []): Layanan
    {
        return Layanan::create(array_merge([
            'nama' => 'Wedding Lengkap', 'kategori' => 'Wedding', 'deskripsi' => 'Paket uji', 'harga' => 35000000,
        ], $o));
    }

    private function data(Layanan $l, array $o = []): array
    {
        return array_merge([
            'nama' => 'Ayu Pratiwi', 'no_whatsapp' => '0812-3456-7890', 'layanan_id' => $l->id,
            'tanggal_acara' => now()->addMonth()->toDateString(),
        ], $o);
    }

    public function test_katalog_membaca_layanan_dari_database(): void
    {
        $this->layanan(['nama' => 'Paket Dari DB']);
        $hapus = $this->layanan(['nama' => 'Paket Terhapus']);
        $hapus->delete();

        $this->get('/ikes_gallery')->assertOk()->assertSee('Paket Dari DB')->assertDontSee('Paket Terhapus');
    }

    public function test_pemesanan_valid_tersimpan_dan_menampilkan_ringkasan(): void
    {
        $l = $this->layanan();
        $res = $this->post('/pemesanan', $this->data($l));

        $p = Pemesanan::with('klien', 'detail')->firstOrFail();
        $res->assertRedirect("/pemesanan/{$p->kode_pemesanan}");
        $this->assertMatchesRegularExpression('/^IKE-\d{8}-[A-Z2-9]{6}$/', $p->kode_pemesanan);
        $this->assertSame('Menunggu Konfirmasi', $p->status_pemesanan);
        $this->assertSame('Belum Bayar', $p->status_pembayaran);
        $this->assertSame('6281234567890', $p->klien->no_whatsapp);
        $this->assertSame(35000000, $p->total_biaya);
        $this->assertSame(35000000, $p->detail->first()->harga_satuan);

        $this->get("/pemesanan/{$p->kode_pemesanan}")->assertOk()
            ->assertSee($p->kode_pemesanan)->assertSee('Ayu Pratiwi')->assertSee('Wedding Lengkap')
            ->assertSee('wa.me/', false);
    }

    public function test_catering_menghitung_total_dari_jumlah_dan_wedding_selalu_satu(): void
    {
        $c = $this->layanan(['nama' => 'Prasmanan', 'kategori' => 'Catering', 'harga' => 75000, 'satuan' => '/ porsi']);
        $this->post('/pemesanan', $this->data($c, ['jumlah' => 200]));
        $this->assertSame(15000000, Pemesanan::latest('id')->first()->total_biaya);

        $w = $this->layanan();
        $this->post('/pemesanan', $this->data($w, ['jumlah' => 9]));
        $this->assertSame(35000000, Pemesanan::latest('id')->first()->total_biaya);
    }

    public function test_klien_dengan_nomor_sama_tidak_dibuat_ganda(): void
    {
        $l = $this->layanan();
        $this->post('/pemesanan', $this->data($l));
        $this->post('/pemesanan', $this->data($l, ['no_whatsapp' => '+62 812 3456 7890']));
        $this->assertDatabaseCount('klien', 1);
        $this->assertDatabaseCount('pemesanan', 2);
    }

    public function test_data_tidak_valid_kembali_ke_form_dan_tidak_disimpan(): void
    {
        $l = $this->layanan();
        $this->from('/ikes_gallery')->post('/pemesanan', [
            'nama' => 'A', 'no_whatsapp' => '123', 'layanan_id' => 9999, 'tanggal_acara' => '2020-01-01',
        ])->assertRedirect('/ikes_gallery')
          ->assertSessionHasErrors(['nama', 'no_whatsapp', 'layanan_id', 'tanggal_acara']);

        $this->assertDatabaseCount('pemesanan', 0);
        $this->assertDatabaseCount('klien', 0);
    }

    public function test_layanan_yang_sudah_dihapus_tidak_bisa_dipesan(): void
    {
        $l = $this->layanan();
        $l->delete();
        $this->post('/pemesanan', $this->data($l))->assertSessionHasErrors('layanan_id');
        $this->assertDatabaseCount('pemesanan', 0);
    }

    public function test_kode_tidak_dikenal_menghasilkan_404(): void
    {
        $this->get('/pemesanan/IKE-00000000-XXXXXX')->assertNotFound();
    }
}
