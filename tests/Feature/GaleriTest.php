<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Galeri;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GaleriTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create(['nama' => 'Admin Uji', 'email' => 'galeri@x.test', 'password' => 'rahasia123']);
    }

    private function foto(array $o = []): Galeri
    {
        return Galeri::create(array_merge(['judul' => 'Foto Uji', 'kategori' => 'Wedding', 'foto' => 'galeri/uji.jpg'], $o));
    }

    public function test_halaman_admin_galeri_butuh_login(): void
    {
        $this->get('/admin/galeri')->assertRedirect('/admin/login');
        $this->post('/admin/galeri', [])->assertRedirect('/admin/login');
        $this->assertDatabaseCount('galeri', 0);
    }

    public function test_gallery_publik_tanpa_foto_menampilkan_pesan_jujur_bukan_foto_stok(): void
    {
        $this->get('/gallery')->assertOk()
            ->assertSee('Galeri sedang kami siapkan')
            ->assertDontSee('unsplash', false);
    }

    public function test_gallery_publik_hanya_menampilkan_foto_aktif_berurutan(): void
    {
        $this->foto(['judul' => 'Keterangan Ketiga', 'urutan' => 3, 'foto' => 'galeri/c.jpg']);
        $this->foto(['judul' => 'Keterangan Pertama', 'urutan' => 1, 'foto' => 'galeri/a.jpg', 'kategori' => 'Catering']);
        $this->foto(['judul' => 'Keterangan Kedua', 'urutan' => 2, 'foto' => 'galeri/b.jpg']);
        $this->foto(['judul' => 'Keterangan Tersembunyi', 'urutan' => 0, 'foto' => 'galeri/d.jpg', 'aktif' => false]);

        $this->get('/gallery')->assertOk()
            ->assertSeeInOrder(['galeri/a.jpg', 'galeri/b.jpg', 'galeri/c.jpg'], false)
            ->assertSee('storage/galeri/a.jpg', false)
            ->assertDontSee('Keterangan Tersembunyi')
            ->assertDontSee('galeri/d.jpg', false)
            ->assertDontSee('unsplash', false);
    }

    public function test_admin_dapat_mengunggah_beberapa_foto_sekaligus(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin());

        $this->from('/admin/galeri')->post('/admin/galeri', [
            'foto' => [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.png')],
            'kategori' => 'Wedding', 'judul' => 'Pelaminan', 'urutan' => 5,
        ])->assertRedirect('/admin/galeri')->assertSessionHas('ok');

        $this->assertDatabaseCount('galeri', 2);
        foreach (Galeri::all() as $g) {
            $this->assertSame('Wedding', $g->kategori);
            $this->assertSame('Pelaminan', $g->judul);
            $this->assertSame(5, $g->urutan);
            $this->assertTrue($g->aktif);
            Storage::disk('public')->assertExists($g->foto);
        }

        $this->get('/admin/galeri')->assertOk()->assertSee('Pelaminan');
    }

    public function test_unggah_menolak_berkas_bukan_gambar_kategori_salah_dan_tanpa_foto(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin());

        $this->from('/admin/galeri')->post('/admin/galeri', [
            'foto' => [UploadedFile::fake()->create('dokumen.pdf', 10, 'application/pdf')], 'kategori' => 'Wedding',
        ])->assertSessionHasErrors('foto.0');

        $this->from('/admin/galeri')->post('/admin/galeri', [
            'foto' => [UploadedFile::fake()->image('a.jpg')], 'kategori' => 'Lain',
        ])->assertSessionHasErrors('kategori');

        $this->from('/admin/galeri')->post('/admin/galeri', ['kategori' => 'Wedding'])->assertSessionHasErrors('foto');

        $this->assertDatabaseCount('galeri', 0);
    }

    public function test_ubah_keterangan_dan_ganti_foto_menghapus_berkas_lama(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin());
        Storage::disk('public')->put('galeri/lama.jpg', 'x');
        $g = $this->foto(['foto' => 'galeri/lama.jpg']);

        // Tanpa foto baru: berkas lama dipertahankan.
        $this->from('/admin/galeri')->put("/admin/galeri/{$g->id}", ['judul' => 'Judul Baru', 'kategori' => 'Catering', 'urutan' => 4])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('galeri', ['id' => $g->id, 'judul' => 'Judul Baru', 'kategori' => 'Catering', 'urutan' => 4, 'foto' => 'galeri/lama.jpg']);
        Storage::disk('public')->assertExists('galeri/lama.jpg');

        // Dengan foto baru: berkas lama dihapus.
        $this->from('/admin/galeri')->put("/admin/galeri/{$g->id}", ['kategori' => 'Catering', 'foto' => UploadedFile::fake()->image('baru.jpg')])
            ->assertSessionHasNoErrors();
        $baru = $g->fresh()->foto;
        $this->assertNotSame('galeri/lama.jpg', $baru);
        Storage::disk('public')->assertMissing('galeri/lama.jpg');
        Storage::disk('public')->assertExists($baru);
    }

    public function test_sembunyikan_dan_tampilkan_foto_di_gallery_publik(): void
    {
        $g = $this->foto(['judul' => 'Foto Toggle', 'foto' => 'galeri/toggle.jpg']);
        $this->actingAs($this->admin());

        $this->patch("/admin/galeri/{$g->id}/aktif")->assertSessionHas('ok');
        $this->assertFalse($g->fresh()->aktif);
        $this->flushSession();                       // pesan flash tidak boleh ikut terhitung saat memeriksa isi halaman
        $this->get('/gallery')->assertOk()->assertDontSee('galeri/toggle.jpg', false);

        $this->patch("/admin/galeri/{$g->id}/aktif");
        $this->assertTrue($g->fresh()->aktif);
        $this->flushSession();
        $this->get('/gallery')->assertOk()->assertSee('galeri/toggle.jpg', false);
    }

    public function test_hapus_foto_menghapus_baris_dan_berkas(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('galeri/hapus.jpg', 'x');
        $g = $this->foto(['foto' => 'galeri/hapus.jpg']);

        $this->actingAs($this->admin())->delete("/admin/galeri/{$g->id}")->assertSessionHas('ok');

        $this->assertDatabaseMissing('galeri', ['id' => $g->id]);
        Storage::disk('public')->assertMissing('galeri/hapus.jpg');
    }
}
