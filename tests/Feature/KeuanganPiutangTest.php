<?php

namespace Tests\Feature;

use App\Models\{Admin, Klien, Pemesanan};
use App\Services\KeuanganService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KeuanganPiutangTest extends TestCase
{
    use RefreshDatabase;

    private int $urut = 0;

    private function pesanan(array $o = []): Pemesanan
    {
        $this->urut++;
        $klien = Klien::create(['nama' => "Klien {$this->urut}", 'no_hp' => '628120000' . str_pad((string) $this->urut, 4, '0', STR_PAD_LEFT)]);

        return Pemesanan::create(array_merge([
            'kode' => 'IKE-UJI-' . str_pad((string) $this->urut, 6, '0', STR_PAD_LEFT),
            'klien_id' => $klien->id,
            'tanggal_acara' => now()->addMonths(3)->toDateString(),
            'lokasi_acara' => 'Gedung Uji',
            'total_biaya' => 10_000_000,
            'status_pemesanan' => 'Dikonfirmasi',
            'status_pembayaran' => 'Belum Bayar',
        ], $o));
    }

    private function bayar(Pemesanan $p, int $nominal, string $jenis = 'DP'): void
    {
        $p->pembayaran()->create([
            'jenis_pembayaran' => $jenis, 'metode_bayar' => 'Transfer', 'nominal' => $nominal, 'tanggal_bayar' => today()->toDateString(),
        ]);
        $p->refreshPembayaran();
    }

    private function admin(): Admin
    {
        return Admin::create(['nama' => 'Admin Uji', 'email' => 'uji@ikes.test', 'password' => 'rahasia123']);
    }

    public function test_piutang_hanya_menghitung_pesanan_aktif_yang_belum_lunas(): void
    {
        $sebagian = $this->pesanan();                       // sisa 6 jt
        $this->bayar($sebagian, 4_000_000);

        $lunas = $this->pesanan();                          // tidak ikut
        $this->bayar($lunas, 10_000_000, 'Pelunasan');

        $this->pesanan(['status_pemesanan' => 'Dibatalkan']); // dibatalkan: tidak ikut

        $belumBayar = $this->pesanan(['total_biaya' => 2_500_000]); // sisa 2,5 jt

        $pt = app(KeuanganService::class)->piutang();

        $this->assertSame(8_500_000, $pt['total']);
        $this->assertCount(2, $pt['baris']);
        $this->assertEqualsCanonicalizing(
            [$sebagian->id, $belumBayar->id],
            $pt['baris']->pluck('pemesanan.id')->all()
        );
    }

    public function test_pembayaran_menurunkan_piutang_sampai_nol(): void
    {
        $p = $this->pesanan();
        $svc = app(KeuanganService::class);

        $this->assertSame(10_000_000, $svc->piutang()['total']);
        $this->bayar($p, 5_000_000);
        $this->assertSame(5_000_000, $svc->piutang()['total']);
        $this->bayar($p, 5_000_000, 'Pelunasan');
        $this->assertSame(0, $svc->piutang()['total']);
    }

    public function test_piutang_jatuh_tempo_mengikuti_batas_pelunasan(): void
    {
        $hari = (int) config('ikes.pelunasan_hari');
        $this->pesanan(['tanggal_acara' => now()->addDays($hari)->toDateString(), 'total_biaya' => 3_000_000]); // tepat jatuh tempo
        $this->pesanan(['tanggal_acara' => now()->addMonths(3)->toDateString(), 'total_biaya' => 7_000_000]);   // masih lama

        $pt = app(KeuanganService::class)->piutang();

        $this->assertSame(10_000_000, $pt['total']);
        $this->assertSame(3_000_000, $pt['jatuhTempo']);
    }

    public function test_dashboard_menampilkan_piutang_dan_laba_kas(): void
    {
        $p = $this->pesanan();
        $this->bayar($p, 4_000_000);

        $this->actingAs($this->admin())->get('/admin')
            ->assertOk()
            ->assertSee('Piutang (Sisa Tagihan)')
            ->assertSee('Rp 6.000.000')
            ->assertSee('Laba Kas Bulan Ini')
            ->assertSee('Tagih via WA');
    }

    public function test_dashboard_menampilkan_pengeluaran_per_kategori_dan_laba(): void
    {
        $admin = $this->admin();
        $this->bayar($this->pesanan(), 5_000_000);
        \App\Models\Pengeluaran::create([
            'admin_id' => $admin->id, 'tanggal' => today()->toDateString(),
            'kategori' => 'Bahan Baku', 'keterangan' => 'Belanja bahan uji', 'nominal' => 1_500_000,
        ]);

        $this->actingAs($admin)->get('/admin')
            ->assertOk()
            ->assertSee('Pemasukan per Layanan')
            ->assertSee('Pengeluaran per Kategori')
            ->assertSee('Bahan Baku')
            ->assertSee('Rp 1.500.000')   // pengeluaran bulan ini
            ->assertSee('Rp 3.500.000');  // laba kas = 5 jt - 1,5 jt
    }

    public function test_laporan_semua_periode_tampil_beserta_piutang(): void
    {
        $this->bayar($this->pesanan(), 4_000_000);
        $admin = $this->admin();

        foreach (['harian', 'bulanan', 'tahunan'] as $mode) {
            $this->actingAs($admin)->get("/admin/laporan?mode=$mode")
                ->assertOk()->assertSee('Piutang (Sisa Tagihan per Hari Ini)', false)->assertSee('Rp 6.000.000');
        }
    }

    public function test_parameter_periode_tidak_valid_ditolak_bukan_error_500(): void
    {
        $admin = $this->admin();   // email admin unik: buat sekali saja per test
        $this->actingAs($admin)->get('/admin/laporan?mode=bulanan&bulan=ngawur')->assertSessionHasErrors('bulan');
        $this->actingAs($admin)->get('/admin/laporan?mode=tahunan&tahun=1800')->assertSessionHasErrors('tahun');
    }

    public function test_ekspor_excel_berhasil_diunduh(): void
    {
        if (! class_exists(\ZipArchive::class)) {
            $this->markTestSkipped('Ekstensi PHP zip belum aktif.');
        }
        $this->bayar($this->pesanan(), 4_000_000);

        $this->actingAs($this->admin())->get('/admin/laporan/ekspor?mode=bulanan')
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_ekspor_pdf_berhasil_diunduh_untuk_semua_periode(): void
    {
        $admin = $this->admin();
        $this->bayar($this->pesanan(), 4_000_000);
        \App\Models\Pengeluaran::create([
            'admin_id' => $admin->id, 'tanggal' => today()->toDateString(),
            'kategori' => 'Operasional', 'keterangan' => 'Listrik (bulan ini)', 'nominal' => 500_000,
        ]);

        foreach (['harian', 'bulanan', 'tahunan'] as $mode) {
            $res = $this->actingAs($admin)->get("/admin/laporan/pdf?mode=$mode")->assertOk()->assertHeader('content-type', 'application/pdf');
            $isi = $res->getContent();
            $this->assertStringStartsWith('%PDF-1.4', $isi);
            $this->assertStringContainsString('%%EOF', $isi);
            $this->assertStringContainsString('attachment; filename="laporan-keuangan-' . $mode, (string) $res->headers->get('content-disposition'));
        }
    }

    public function test_rekap_tahun_menjumlahkan_pemasukan_pengeluaran_dan_laba_per_bulan(): void
    {
        $admin = $this->admin();
        $this->bayar($this->pesanan(), 5_000_000);
        \App\Models\Pengeluaran::create([
            'admin_id' => $admin->id, 'tanggal' => today()->toDateString(),
            'kategori' => 'Bahan Baku', 'keterangan' => 'Belanja bahan uji', 'nominal' => 1_500_000,
        ]);

        $r = app(KeuanganService::class)->rekapTahun(now()->year);

        $this->assertCount(12, $r['baris']);
        $this->assertSame(5_000_000, $r['total']['masuk']);
        $this->assertSame(1_500_000, $r['total']['keluar']);
        $this->assertSame(3_500_000, $r['total']['laba']);
        $bulanIni = $r['baris'][now()->month - 1];
        $this->assertSame(3_500_000, $bulanIni['laba']);
        // Bulan lain kosong: seluruh pemasukan tahun ini ada di bulan berjalan.
        $this->assertSame(5_000_000, array_sum(array_column($r['baris'], 'masuk')));
        $this->assertSame(5_000_000, $bulanIni['masuk']);
    }

    public function test_dashboard_menampilkan_rekap_tahun_dengan_tautan_ekspor(): void
    {
        $this->bayar($this->pesanan(), 4_000_000);

        $this->actingAs($this->admin())->get('/admin')
            ->assertOk()
            ->assertSee('Rekap Tahun ' . now()->year)
            ->assertSee('/admin/laporan/pdf?mode=tahunan', false)
            ->assertSee('/admin/laporan/ekspor?mode=tahunan', false);
    }

    private function batalkan(Pemesanan $p, ?string $danaBatal, Admin $admin)
    {
        return $this->actingAs($admin)->patch("/admin/pemesanan/{$p->kode}/status", array_filter([
            'status_pemesanan' => 'Dibatalkan', 'dana_batal' => $danaBatal,
        ]));
    }

    public function test_pembayaran_pesanan_batal_yang_dikeluarkan_tidak_dihitung_sebagai_pemasukan(): void
    {
        $admin = $this->admin();
        $aktif = $this->pesanan();
        $this->bayar($aktif, 2_000_000);
        $batal = $this->pesanan();
        $this->bayar($batal, 3_000_000);

        $this->batalkan($batal, 'dikeluarkan', $admin)->assertSessionHasNoErrors();

        $svc = app(KeuanganService::class);
        $d = $svc->laporan('harian', today());
        $this->assertSame(2_000_000, $d['masuk']);
        $this->assertSame(2_000_000, $d['laba']);
        $this->assertSame(0, $d['danaHangus']);
        $this->assertSame(3_000_000, $d['danaDikeluarkan']);
        $this->assertSame(2_000_000, $svc->rekapTahun(now()->year)['total']['masuk']);

        // Dashboard: pemasukan bersih hari ini juga hanya Rp 2 jt.
        $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('Rp 2.000.000')->assertDontSee('Rp 5.000.000');
    }

    public function test_dp_hangus_tetap_pemasukan_dan_pengembalian_sebagian_mengurangi(): void
    {
        $admin = $this->admin();
        $batal = $this->pesanan();
        $this->bayar($batal, 3_000_000);

        $this->batalkan($batal, 'hangus', $admin)->assertSessionHasNoErrors();
        $this->actingAs($admin)->post("/admin/pemesanan/{$batal->kode}/pengembalian", [
            'nominal_refund' => 1_000_000, 'metode_refund' => 'Transfer', 'tanggal_refund' => today()->toDateString(),
        ])->assertSessionHasNoErrors();

        $d = app(KeuanganService::class)->laporan('harian', today());
        $this->assertSame(3_000_000, $d['masuk']);
        $this->assertSame(1_000_000, $d['refund']);
        $this->assertSame(2_000_000, $d['bersih']);
        $this->assertSame(2_000_000, $d['danaHangus']);
        $this->assertSame(0, $d['danaDikeluarkan']);
    }

    public function test_membatalkan_pesanan_yang_sudah_dibayar_wajib_memilih_perlakuan_dana(): void
    {
        $admin = $this->admin();
        $p = $this->pesanan();
        $this->bayar($p, 1_000_000);

        $this->batalkan($p, null, $admin)->assertSessionHasErrors('dana_batal');
        $this->assertSame('Dikonfirmasi', $p->fresh()->status_pemesanan);

        // Tanpa pembayaran, tidak perlu memilih.
        $kosong = $this->pesanan();
        $this->batalkan($kosong, null, $admin)->assertSessionHasNoErrors();
        $this->assertSame('Dibatalkan', $kosong->fresh()->status_pemesanan);
    }

    public function test_pesanan_batal_data_lama_tanpa_pilihan_dianggap_dikeluarkan_dan_aktif_kembali_dihitung(): void
    {
        $admin = $this->admin();
        $p = $this->pesanan(['status_pemesanan' => 'Dibatalkan']);   // dana_batal NULL = data lama
        $this->bayar($p, 4_000_000);
        $svc = app(KeuanganService::class);

        $this->assertSame(0, $svc->laporan('harian', today())['masuk']);

        // Pesanan diaktifkan kembali: pilihan dihapus dan pembayarannya dihitung lagi.
        $this->actingAs($admin)->patch("/admin/pemesanan/{$p->kode}/status", ['status_pemesanan' => 'Dikonfirmasi'])->assertSessionHasNoErrors();
        $this->assertNull($p->fresh()->dana_batal);
        $this->assertSame(4_000_000, $svc->laporan('harian', today())['masuk']);
    }

    public function test_pengembalian_ditolak_bila_dana_pesanan_batal_sudah_dikeluarkan(): void
    {
        $admin = $this->admin();
        $p = $this->pesanan();
        $this->bayar($p, 2_000_000);
        $this->batalkan($p, 'dikeluarkan', $admin);

        $this->actingAs($admin)->post("/admin/pemesanan/{$p->kode}/pengembalian", [
            'nominal_refund' => 500_000, 'metode_refund' => 'Tunai', 'tanggal_refund' => today()->toDateString(),
        ])->assertSessionHasErrors('pengembalian');
        $this->assertDatabaseCount('pengembalian', 0);
    }

    public function test_halaman_admin_butuh_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/laporan/ekspor')->assertRedirect('/admin/login');
        $this->get('/admin/laporan/pdf')->assertRedirect('/admin/login');
        $this->get('/admin/pengeluaran')->assertRedirect('/admin/login');
    }
}
