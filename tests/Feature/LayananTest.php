<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Klien;
use App\Models\Layanan;
use App\Models\Pemesanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LayananTest extends TestCase
{
    use RefreshDatabase;

    private function valid(array $o = []): array
    {
        return array_merge([
            'nama' => 'Paket Uji', 'kategori' => 'Wedding', 'deskripsi' => 'Deskripsi uji', 'harga' => 1500000,
        ], $o);
    }

    private function admin(): Admin
    {
        return Admin::create(['nama' => 'Admin Uji', 'email' => 'a@x.test', 'password' => 'rahasia123']);
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get('/admin/layanan')->assertRedirect('/admin/login');
        $this->post('/admin/layanan', $this->valid())->assertRedirect('/admin/login');
        $this->assertDatabaseCount('layanan', 0);
    }

    public function test_login_admin_valid_dan_salah(): void
    {
        $this->admin();
        $this->post('/admin/login', ['email' => 'a@x.test', 'password' => 'salah'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/admin/login', ['email' => 'a@x.test', 'password' => 'rahasia123'])->assertRedirect('/admin');
        $this->assertAuthenticated();
    }

    public function test_admin_yang_sudah_login_diarahkan_dari_halaman_login_ke_dashboard(): void
    {
        $this->actingAs($this->admin())->get('/admin/login')->assertRedirect('/admin');
    }

    public function test_daftar_tambah_dan_ubah_tersimpan_di_database(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin());

        $this->get('/admin/layanan')->assertOk();

        // Tambah (+ foto)
        $this->from('/admin/layanan')->post('/admin/layanan', $this->valid(['foto' => UploadedFile::fake()->image('a.jpg')]))
            ->assertRedirect('/admin/layanan')->assertSessionHas('ok');
        $l = Layanan::firstOrFail();
        $this->assertSame('Paket Uji', $l->nama);
        $this->assertSame('', $l->satuan);                       // satuan kosong disimpan sebagai string kosong
        $this->assertTrue($l->aktif);                            // layanan baru langsung aktif
        Storage::disk('public')->assertExists($l->foto);
        $fotoLama = $l->foto;

        $this->get('/admin/layanan')->assertOk()->assertSee('Paket Uji');

        // Ubah tanpa foto baru: foto lama dipertahankan
        $this->from('/admin/layanan')->put("/admin/layanan/{$l->id}", $this->valid(['nama' => 'Paket Baru', 'harga' => 2000000]))
            ->assertRedirect('/admin/layanan');
        $this->assertDatabaseHas('layanan', ['id' => $l->id, 'nama' => 'Paket Baru', 'harga' => 2000000, 'foto' => $fotoLama]);

        // Ubah dengan foto baru: foto lama dihapus dari disk
        $this->from('/admin/layanan')->put("/admin/layanan/{$l->id}", $this->valid(['foto' => UploadedFile::fake()->image('b.jpg')]))
            ->assertRedirect('/admin/layanan');
        $baru = $l->fresh()->foto;
        $this->assertNotSame($fotoLama, $baru);
        Storage::disk('public')->assertMissing($fotoLama);
        Storage::disk('public')->assertExists($baru);
    }

    public function test_nonaktifkan_menyembunyikan_dari_katalog_dan_aktifkan_menampilkan_lagi(): void
    {
        $l = Layanan::create($this->valid(['nama' => 'Paket Toggle']));
        $this->actingAs($this->admin());

        // flushSession: pesan flash "Layanan \"Paket Toggle\" dinonaktifkan..." akan ikut tampil di halaman berikutnya
        // dan membuat assertDontSee/assertSee tidak lagi mengukur isi katalog.
        $this->patch("/admin/layanan/{$l->id}/aktif")->assertSessionHas('ok');
        $this->assertFalse($l->fresh()->aktif);
        $this->flushSession();
        $this->get('/katalog')->assertOk()->assertDontSee('Paket Toggle');

        $this->patch("/admin/layanan/{$l->id}/aktif");
        $this->assertTrue($l->fresh()->aktif);
        $this->flushSession();
        $this->get('/katalog')->assertOk()->assertSee('Paket Toggle');
    }

    public function test_layanan_yang_belum_pernah_dipesan_bisa_dihapus(): void
    {
        $l = Layanan::create($this->valid());
        $this->actingAs($this->admin())->delete("/admin/layanan/{$l->id}")->assertSessionHas('ok');
        $this->assertDatabaseMissing('layanan', ['id' => $l->id]);
    }

    public function test_layanan_yang_sudah_dipesan_tidak_bisa_dihapus(): void
    {
        $l = Layanan::create($this->valid());
        $klien = Klien::create(['nama' => 'Ayu Pratiwi', 'no_hp' => '6281234567890']);
        $p = Pemesanan::create([
            'kode' => 'IKG-2026-001', 'klien_id' => $klien->id, 'tanggal_acara' => now()->addMonth()->toDateString(),
            'lokasi_acara' => 'Gedung Uji', 'total_biaya' => 1500000,
        ]);
        $p->detail()->create(['layanan_id' => $l->id, 'jumlah' => 1, 'harga_satuan' => 1500000, 'subtotal' => 1500000]);

        $this->actingAs($this->admin())->delete("/admin/layanan/{$l->id}")->assertSessionHasErrors('hapus');
        $this->assertDatabaseHas('layanan', ['id' => $l->id]);
    }

    public function test_beranda_menampilkan_unggulan_aktif_berurutan_menurut_kolom_urutan(): void
    {
        Layanan::create($this->valid(['nama' => 'Paket Ketiga', 'unggulan' => true, 'urutan' => 3]));
        Layanan::create($this->valid(['nama' => 'Paket Pertama', 'unggulan' => true, 'urutan' => 1]));
        Layanan::create($this->valid(['nama' => 'Paket Kedua', 'unggulan' => true, 'urutan' => 2, 'label' => 'Aaa']));
        Layanan::create($this->valid(['nama' => 'Paket Berlabel', 'label' => 'Zzz Terlaris']));                 // label tidak menentukan apa pun
        Layanan::create($this->valid(['nama' => 'Paket Nonaktif', 'unggulan' => true, 'urutan' => 0, 'aktif' => false]));

        $this->get('/')->assertOk()
            ->assertSeeInOrder(['Paket Pertama', 'Paket Kedua', 'Paket Ketiga'])
            ->assertDontSee('Paket Berlabel')
            ->assertDontSee('Paket Nonaktif');
    }

    public function test_beranda_dibatasi_empat_unggulan_dan_urutan_sama_mengikuti_id(): void
    {
        foreach (range(1, 5) as $i) {
            Layanan::create($this->valid(['nama' => "Unggulan $i", 'unggulan' => true, 'urutan' => 7]));   // urutan sama semua
        }

        $this->get('/')->assertOk()
            ->assertSeeInOrder(['Unggulan 1', 'Unggulan 2', 'Unggulan 3', 'Unggulan 4'])
            ->assertDontSee('Unggulan 5');
    }

    public function test_beranda_tetap_tampil_tanpa_unggulan(): void
    {
        Layanan::create($this->valid(['nama' => 'Paket Biasa']));
        $this->get('/')->assertOk()->assertDontSee('Paket Pilihan Kami')->assertDontSee('Paket Biasa');
    }

    public function test_admin_menandai_unggulan_dan_mengatur_urutan(): void
    {
        $this->actingAs($this->admin());

        $this->from('/admin/layanan')->post('/admin/layanan', $this->valid(['unggulan' => '1', 'urutan' => 2]))->assertSessionHasNoErrors();
        $l = Layanan::firstOrFail();
        $this->assertTrue($l->unggulan);
        $this->assertSame(2, $l->urutan);

        // Checkbox tidak dicentang tidak terkirim: unggulan menjadi false; urutan kosong menjadi 0.
        $this->from('/admin/layanan')->put("/admin/layanan/{$l->id}", $this->valid(['urutan' => '']))->assertSessionHasNoErrors();
        $this->assertFalse($l->fresh()->unggulan);
        $this->assertSame(0, $l->fresh()->urutan);

        $this->from('/admin/layanan')->post('/admin/layanan', $this->valid(['nama' => 'Lain', 'urutan' => -1]))->assertSessionHasErrors('urutan');
        $this->assertDatabaseCount('layanan', 1);
    }

    public function test_data_tidak_valid_kembali_ke_form_dan_tidak_disimpan(): void
    {
        $this->actingAs($this->admin());
        $this->from('/admin/layanan')
            ->post('/admin/layanan', ['nama' => '', 'kategori' => 'Lain', 'harga' => 'abc'])
            ->assertRedirect('/admin/layanan')
            ->assertSessionHasErrors(['nama', 'kategori', 'deskripsi', 'harga']);
        $this->assertDatabaseCount('layanan', 0);
    }
}
