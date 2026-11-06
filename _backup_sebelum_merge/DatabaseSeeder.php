<?php

namespace Database\Seeders;

use App\Models\Layanan;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Akun owner/admin. GANTI password setelah login pertama / sebelum produksi.
        User::updateOrCreate(
            ['email' => 'admin@ikesgallery.test'],
            ['name' => 'Owner Ike\'s Gallery', 'password' => 'admin12345']
        );

        $u = fn ($id) => "https://images.unsplash.com/{$id}?auto=format&fit=crop&w=800&q=70";
        $data = [
            ['Wedding Rias + Dekor', 'Wedding', 12500000, null, 'Rias pengantin, busana akad & resepsi, serta dekorasi pelaminan elegan.', 'photo-1465495976277-4387d4b0b4c6'],
            ['Wedding Lengkap', 'Wedding', 35000000, null, 'Rias, dekorasi, katering 300 porsi, MC, dan dokumentasi foto-video.', 'photo-1519741497674-611481863552'],
            ['Akad Intimate', 'Wedding', 8500000, null, 'Paket akad sederhana untuk keluarga inti dengan dekorasi minimalis.', 'photo-1511795409834-ef04bbd61622'],
            ['Wedding Premium Gedung', 'Wedding', 65000000, null, 'Paket mewah full service di gedung, lengkap dengan entertainment.', 'photo-1464366400600-7168b8af9bc3'],
            ['Nasi Box Premium', 'Catering', 45000, '/ porsi', 'Nasi, ayam/daging, lauk pendamping, sambal, dan buah segar.', 'photo-1504674900247-0877df9cc836'],
            ['Prasmanan Reguler', 'Catering', 75000, '/ porsi', 'Menu prasmanan 8 macam hidangan, minimal 100 porsi.', 'photo-1555244162-803834f70033'],
            ['Prasmanan Gold', 'Catering', 120000, '/ porsi', 'Menu premium dengan live station, dessert, dan pelayan berseragam.', 'photo-1414235077428-338989a2e8c0'],
            ['Tumpeng & Snack Box', 'Catering', 35000, '/ box', 'Cocok untuk syukuran, arisan, dan acara kantor.', 'photo-1555939594-58d7cb561ad1'],
        ];
        foreach ($data as [$nama, $kat, $harga, $satuan, $desc, $foto]) {
            Layanan::updateOrCreate(['nama' => $nama], [
                'kategori' => $kat, 'harga' => $harga, 'satuan' => $satuan, 'deskripsi' => $desc, 'foto' => $u($foto),
            ]);
        }
    }
}
