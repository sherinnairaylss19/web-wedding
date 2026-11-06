<?php

namespace Tests\Feature;

use App\Models\Layanan;
use App\Models\User;
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

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get('/admin/layanan')->assertRedirect('/admin/login');
    }

    public function test_login_admin_valid_dan_salah(): void
    {
        User::factory()->create(['email' => 'a@x.test', 'password' => 'rahasia123']);
        $this->post('/admin/login', ['email' => 'a@x.test', 'password' => 'salah'])->assertSessionHasErrors('email');
        $this->post('/admin/login', ['email' => 'a@x.test', 'password' => 'rahasia123'])->assertRedirect('/admin/layanan');
        $this->assertAuthenticated();
    }

    public function test_daftar_tambah_ubah_hapus_tersimpan_di_database(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $this->get('/admin/layanan')->assertOk();
        $this->get('/admin/layanan/create')->assertOk();

        // Tambah (+ foto)
        $this->post('/admin/layanan', $this->valid(['foto' => UploadedFile::fake()->image('a.jpg')]))
            ->assertRedirect('/admin/layanan');
        $l = Layanan::firstOrFail();
        $this->assertSame('Paket Uji', $l->nama);
        Storage::disk('public')->assertExists($l->foto);

        // Ubah
        $this->get("/admin/layanan/{$l->id}/edit")->assertOk()->assertSee('Paket Uji');
        $this->put("/admin/layanan/{$l->id}", $this->valid(['nama' => 'Paket Baru', 'harga' => 2000000]))
            ->assertRedirect('/admin/layanan');
        $this->assertDatabaseHas('layanan', ['id' => $l->id, 'nama' => 'Paket Baru', 'harga' => 2000000]);

        // Hapus (soft delete)
        $this->delete("/admin/layanan/{$l->id}")->assertRedirect('/admin/layanan');
        $this->assertSoftDeleted('layanan', ['id' => $l->id]);
    }

    public function test_data_tidak_valid_kembali_ke_form_dan_tidak_disimpan(): void
    {
        $this->actingAs(User::factory()->create());
        $this->from('/admin/layanan/create')
            ->post('/admin/layanan', ['nama' => '', 'kategori' => 'Lain', 'harga' => 'abc'])
            ->assertRedirect('/admin/layanan/create')
            ->assertSessionHasErrors(['nama', 'kategori', 'deskripsi', 'harga']);
        $this->assertDatabaseCount('layanan', 0);
    }
}
