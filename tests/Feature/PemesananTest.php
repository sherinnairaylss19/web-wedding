<?php

namespace Tests\Feature;

use App\Models\Layanan;
use App\Models\Pemesanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PemesananTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Notifikasi Fonnte opsional; matikan agar test tidak pernah memanggil API sungguhan (token bisa terisi dari .env).
        config(['ikes.fonnte_token' => null]);
    }

    private function layanan(array $o = []): Layanan
    {
        return Layanan::create(array_merge([
            'nama' => 'Wedding Lengkap', 'kategori' => 'Wedding', 'deskripsi' => 'Paket uji', 'harga' => 35000000,
        ], $o));
    }

    /** Payload form pemesanan: satu baris layanan (items[0]). */
    private function data(Layanan $l, int $jumlah = 1, array $o = []): array
    {
        return array_merge([
            'nama' => 'Ayu Pratiwi', 'no_hp' => '0812-3456-7890',
            'items' => [['layanan_id' => $l->id, 'jumlah' => $jumlah]],
            'tanggal_acara' => now()->addMonth()->toDateString(),
            'lokasi_acara' => 'Gedung Serbaguna Indramayu',
        ], $o);
    }

    private function ringkasanUrl(Pemesanan $p): string
    {
        return URL::signedRoute('pemesanan.ringkasan', ['pemesanan' => $p->kode]);
    }

    public function test_katalog_hanya_menampilkan_layanan_aktif(): void
    {
        $this->layanan(['nama' => 'Paket Dari DB']);
        $this->layanan(['nama' => 'Paket Nonaktif', 'aktif' => false]);

        $this->get('/katalog')->assertOk()->assertSee('Paket Dari DB')->assertDontSee('Paket Nonaktif');
        $this->get('/ikes_gallery')->assertRedirect('/');
    }

    public function test_pemesanan_valid_tersimpan_dan_menampilkan_ringkasan(): void
    {
        $l = $this->layanan();
        $res = $this->post('/pemesanan', $this->data($l));

        $p = Pemesanan::with('klien', 'detail')->firstOrFail();
        $res->assertRedirect($this->ringkasanUrl($p));
        $this->assertMatchesRegularExpression('/^IKG-' . now()->year . '-\d{3}$/', $p->kode);
        $this->assertSame('Menunggu Konfirmasi', $p->status_pemesanan);
        $this->assertSame('Belum Bayar', $p->status_pembayaran);
        $this->assertSame('Ayu Pratiwi', $p->klien->nama);
        $this->assertSame(35000000, (int) $p->total_biaya);
        $this->assertSame(35000000, (int) $p->detail->first()->harga_satuan);

        $this->get($this->ringkasanUrl($p))->assertOk()
            ->assertSee($p->kode)->assertSee('Ayu Pratiwi')->assertSee('Wedding Lengkap')->assertSee('Gedung Serbaguna Indramayu')
            ->assertSee('wa.me/', false);
    }

    public function test_kode_pemesanan_berurutan_dalam_satu_tahun(): void
    {
        $l = $this->layanan();
        $this->post('/pemesanan', $this->data($l));
        $this->post('/pemesanan', $this->data($l));

        $this->assertSame(
            [sprintf('IKG-%d-001', now()->year), sprintf('IKG-%d-002', now()->year)],
            Pemesanan::orderBy('id')->pluck('kode')->all()
        );
    }

    public function test_catering_menghitung_total_dari_jumlah_dan_wedding_selalu_satu(): void
    {
        $c = $this->layanan(['nama' => 'Prasmanan', 'kategori' => 'Catering', 'harga' => 75000, 'satuan' => '/ porsi']);
        $this->post('/pemesanan', $this->data($c, 200));
        $this->assertSame(15000000, (int) Pemesanan::latest('id')->first()->total_biaya);

        $w = $this->layanan();
        $this->post('/pemesanan', $this->data($w, 9));
        $this->assertSame(35000000, (int) Pemesanan::latest('id')->first()->total_biaya);
    }

    public function test_baris_layanan_yang_sama_digabung_dan_beberapa_layanan_dijumlahkan(): void
    {
        $nasi = $this->layanan(['nama' => 'Nasi Box', 'kategori' => 'Catering', 'harga' => 30000]);
        $wedding = $this->layanan();

        $this->post('/pemesanan', $this->data($nasi, 1, ['items' => [
            ['layanan_id' => $nasi->id, 'jumlah' => 50],
            ['layanan_id' => $nasi->id, 'jumlah' => 50],
            ['layanan_id' => $wedding->id, 'jumlah' => 1],
        ]]));

        $p = Pemesanan::with('detail')->firstOrFail();
        $this->assertCount(2, $p->detail);                              // nasi box digabung jadi satu baris
        $this->assertSame(100, (int) $p->detail->firstWhere('layanan_id', $nasi->id)->jumlah);
        $this->assertSame(3000000 + 35000000, (int) $p->total_biaya);
    }

    public function test_klien_dengan_nomor_sama_tidak_dibuat_ganda(): void
    {
        $l = $this->layanan();
        $this->post('/pemesanan', $this->data($l));
        $this->post('/pemesanan', $this->data($l, 1, ['no_hp' => '+62 812 3456 7890']));   // format berbeda, nomor sama
        $this->post('/pemesanan', $this->data($l, 1, ['no_hp' => '081234567890']));
        $this->assertDatabaseCount('klien', 1);
        $this->assertDatabaseCount('pemesanan', 3);

        $this->post('/pemesanan', $this->data($l, 1, ['no_hp' => '0813-9999-0000']));       // nomor lain = klien baru
        $this->assertDatabaseCount('klien', 2);
    }

    public function test_data_tidak_valid_kembali_ke_form_dan_tidak_disimpan(): void
    {
        $this->from('/pemesanan')->post('/pemesanan', [
            'nama' => 'A', 'no_hp' => '123', 'items' => [['layanan_id' => 9999, 'jumlah' => 1]], 'tanggal_acara' => '2020-01-01',
        ])->assertRedirect('/pemesanan')
          ->assertSessionHasErrors(['nama', 'no_hp', 'items.0.layanan_id', 'tanggal_acara', 'lokasi_acara']);

        $this->assertDatabaseCount('pemesanan', 0);
        $this->assertDatabaseCount('klien', 0);
    }

    public function test_layanan_nonaktif_tidak_bisa_dipesan(): void
    {
        $l = $this->layanan(['aktif' => false]);
        $this->post('/pemesanan', $this->data($l))->assertSessionHasErrors('items.0.layanan_id');
        $this->assertDatabaseCount('pemesanan', 0);
    }

    public function test_ringkasan_butuh_tautan_bertanda_tangan(): void
    {
        $l = $this->layanan();
        $this->post('/pemesanan', $this->data($l));
        $p = Pemesanan::firstOrFail();

        $this->get('/pemesanan/' . $p->kode)->assertForbidden();   // menebak kode saja tidak cukup
        $this->get(URL::signedRoute('pemesanan.ringkasan', ['pemesanan' => 'IKG-2000-999']))->assertNotFound();
    }

    public function test_cek_pesanan_dengan_kode_dan_nomor_yang_cocok(): void
    {
        $this->post('/pemesanan', $this->data($this->layanan()));
        $p = Pemesanan::firstOrFail();

        // Nomor ditulis dengan format berbeda dari saat memesan; tetap harus cocok.
        $res = $this->post('/cek-pesanan', ['kode' => strtolower($p->kode), 'no_hp' => '+62 812 3456 7890']);
        $res->assertSessionHasNoErrors()->assertRedirectContains('/cek-pesanan/' . $p->kode);

        $this->get($res->headers->get('Location'))->assertOk()->assertSee($p->kode);
    }

    public function test_cek_pesanan_dengan_data_salah_ditolak_dengan_pesan_generik(): void
    {
        $this->post('/pemesanan', $this->data($this->layanan()));
        $p = Pemesanan::firstOrFail();

        $this->post('/cek-pesanan', ['kode' => $p->kode, 'no_hp' => '0899-0000-1111'])->assertSessionHasErrors('kode');
        $this->post('/cek-pesanan', ['kode' => 'IKG-2000-999', 'no_hp' => '0812-3456-7890'])->assertSessionHasErrors('kode');
    }

    public function test_owner_dinotifikasi_lewat_fonnte_bila_token_diisi(): void
    {
        config(['ikes.fonnte_token' => 'token-uji']);
        Http::fake();

        $this->post('/pemesanan', $this->data($this->layanan()));

        Http::assertSent(fn ($r) => str_contains($r->url(), 'api.fonnte.com/send') && str_contains($r['message'], 'Ayu Pratiwi'));
        $this->assertDatabaseCount('pemesanan', 1);
    }

    public function test_kegagalan_fonnte_tidak_menggagalkan_pemesanan(): void
    {
        config(['ikes.fonnte_token' => 'token-uji']);
        Http::fake(fn () => throw new \RuntimeException('jaringan putus'));

        $this->post('/pemesanan', $this->data($this->layanan()))->assertRedirect();
        $this->assertDatabaseCount('pemesanan', 1);
    }
}
