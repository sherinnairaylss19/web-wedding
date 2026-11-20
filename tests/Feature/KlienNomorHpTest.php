<?php

namespace Tests\Feature;

use App\Models\{Admin, Klien, Layanan};
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Satu nomor WhatsApp = satu klien, apa pun format ketikannya. */
class KlienNomorHpTest extends TestCase
{
    use RefreshDatabase;

    private function pesan(string $noHp, string $nama = 'Pemesan Uji')
    {
        $l = Layanan::first() ?? Layanan::create(['nama' => 'Paket Uji', 'kategori' => 'Catering', 'deskripsi' => 'x', 'harga' => 50_000]);

        return $this->post(route('pemesanan.store'), [
            'nama' => $nama, 'no_hp' => $noHp, 'items' => [['layanan_id' => $l->id, 'jumlah' => 10]],
            'tanggal_acara' => now()->addMonth()->toDateString(), 'lokasi_acara' => 'Gedung Uji',
        ]);
    }

    public function test_no_hp_normal_terisi_otomatis_saat_dibuat_dan_diubah(): void
    {
        $k = Klien::create(['nama' => 'Klien Uji', 'no_hp' => '0812-3456-7890']);
        $this->assertSame('6281234567890', $k->no_hp_normal);

        $k->update(['no_hp' => '+62 813 1111 2222']);
        $this->assertSame('6281311112222', $k->fresh()->no_hp_normal);
    }

    public function test_database_menolak_dua_klien_dengan_nomor_setara(): void
    {
        Klien::create(['nama' => 'A', 'no_hp' => '081234567890']);

        $this->expectException(UniqueConstraintViolationException::class);
        Klien::create(['nama' => 'B', 'no_hp' => '+62 812-3456-7890']);
    }

    public function test_pemesanan_dengan_format_nomor_berbeda_tetap_klien_yang_sama(): void
    {
        $this->pesan('081234567890')->assertRedirect();
        $this->pesan('+62 812-3456-7890')->assertRedirect();
        $this->pesan('812 3456 7890')->assertRedirect();

        $this->assertSame(1, Klien::count());
        $this->assertSame(3, Klien::first()->pemesanan()->count());
    }

    public function test_klien_yang_dihapus_lunak_dipulihkan_saat_memesan_lagi(): void
    {
        $k = Klien::create(['nama' => 'Klien Lama', 'no_hp' => '081234567890']);
        $k->delete();

        $this->pesan('+6281234567890')->assertRedirect();

        $this->assertSame(1, Klien::withTrashed()->count());
        $this->assertNull($k->fresh()->deleted_at);
    }

    public function test_admin_tidak_bisa_memakai_nomor_setara_milik_klien_lain(): void
    {
        $admin = Admin::create(['nama' => 'Admin Uji', 'email' => 'uji@ikes.test', 'password' => 'rahasia123']);
        Klien::create(['nama' => 'Klien Pertama', 'no_hp' => '081234567890']);
        $b = Klien::create(['nama' => 'Klien Kedua', 'no_hp' => '081299998888']);

        // Nama sengaja valid (min 3 karakter) agar satu-satunya error yang mungkin muncul adalah soal nomor.
        $this->actingAs($admin)->put(route('admin.klien.update', $b), ['nama' => 'Klien Kedua', 'no_hp' => '+62 812-3456-7890'])
            ->assertSessionHasErrors(['no_hp' => 'Nomor WhatsApp ini sudah dipakai klien lain.'])
            ->assertSessionDoesntHaveErrors('nama');
        $this->assertSame('081299998888', $b->fresh()->no_hp);
    }

    public function test_admin_boleh_menyimpan_nomor_sendiri_dengan_format_lain(): void
    {
        $admin = Admin::create(['nama' => 'Admin Uji', 'email' => 'uji@ikes.test', 'password' => 'rahasia123']);
        $k = Klien::create(['nama' => 'Klien Uji', 'no_hp' => '081234567890']);

        $this->actingAs($admin)->put(route('admin.klien.update', $k), ['nama' => 'Klien Uji', 'no_hp' => '+62 812-3456-7890'])
            ->assertSessionHasNoErrors();
        $this->assertSame('+62 812-3456-7890', $k->fresh()->no_hp);
        $this->assertSame('6281234567890', $k->fresh()->no_hp_normal);
    }
}
