<?php

namespace Database\Seeders;

use App\Models\{Admin, Klien, Layanan, Pemesanan, Testimoni};
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Admin::updateOrCreate(['email' => 'admin@ikesgallery.id'], ['nama' => "Owner Ike's Gallery", 'password' => 'admin123']);

        $data = [
            ['Wedding', 'Wedding Rias + Dekor', 12500000, '', null, 'Rias pengantin, busana akad & resepsi, serta dekorasi pelaminan elegan.', 'photo-1465495976277-4387d4b0b4c6'],
            ['Wedding', 'Wedding Lengkap', 35000000, '', 'Terlaris', 'Rias, dekorasi, katering 300 porsi, MC, dan dokumentasi foto-video.', 'photo-1519741497674-611481863552'],
            ['Wedding', 'Akad Intimate', 8500000, '', null, 'Paket akad sederhana untuk keluarga inti dengan dekorasi minimalis.', 'photo-1511795409834-ef04bbd61622'],
            ['Wedding', 'Wedding Premium Gedung', 65000000, '', 'Premium', 'Paket mewah full service di gedung, lengkap dengan entertainment.', 'photo-1464366400600-7168b8af9bc3'],
            ['Catering', 'Nasi Box Premium', 45000, '/ porsi', null, 'Nasi, ayam/daging, lauk pendamping, sambal, dan buah segar.', 'photo-1504674900247-0877df9cc836'],
            ['Catering', 'Prasmanan Reguler', 75000, '/ porsi', 'Favorit', 'Menu prasmanan 8 macam hidangan, minimal 100 porsi.', 'photo-1555244162-803834f70033'],
            ['Catering', 'Prasmanan Gold', 120000, '/ porsi', null, 'Menu premium dengan live station, dessert, dan pelayan berseragam.', 'photo-1414235077428-338989a2e8c0'],
            ['Catering', 'Tumpeng & Snack Box', 35000, '/ box', null, 'Cocok untuk syukuran, arisan, dan acara kantor.', 'photo-1555939594-58d7cb561ad1'],
        ];
        foreach ($data as [$kat, $nama, $harga, $sat, $label, $desk, $foto]) {
            Layanan::updateOrCreate(['nama' => $nama], ['kategori' => $kat, 'harga' => $harga, 'satuan' => $sat, 'label' => $label, 'deskripsi' => $desk, 'foto' => $foto]);
        }

        // Testimoni contoh berstatus Tampil (setiap testimoni terhubung ke satu pemesanan)
        $contoh = [
            ['Ayu & Rizky', 5, 'Rias dan dekorasinya cantik sekali, persis seperti yang kami impikan. Tim sangat ramah dan profesional dari awal sampai acara selesai.'],
            ['Siti Nurhaliza', 5, 'Katering untuk acara lamaran anak kami enak semua, tamu banyak yang tambah. Penyajian rapi dan tepat waktu.'],
            ['Dewi & Fajar', 5, 'Paket Wedding Lengkap benar-benar membantu, semua urusan beres dalam satu tempat.'],
            ['Rina Marlina', 4, 'Dekorasi akad intimate-nya elegan dan harganya masuk akal. Komunikasinya cepat lewat WhatsApp.'],
        ];
        $l = Layanan::first();
        foreach ($contoh as $i => [$nama, $rating, $isi]) {
            $k = Klien::updateOrCreate(['no_hp' => '0812000000' . $i], ['nama' => $nama, 'alamat' => 'Indramayu']);
            $p = Pemesanan::firstOrCreate(['kode' => 'IKG-2025-00' . ($i + 1)], [
                'klien_id' => $k->id, 'tanggal_acara' => now()->subMonths($i + 2), 'lokasi_acara' => 'Indramayu',
                'total_biaya' => $l->harga, 'status_pemesanan' => 'Selesai', 'status_pembayaran' => 'Lunas',
            ]);
            if (!$p->detail()->exists()) $p->detail()->create(['layanan_id' => $l->id, 'jumlah' => 1, 'harga_satuan' => $l->harga, 'subtotal' => $l->harga]);
            if (!$p->pembayaran()->exists()) $p->pembayaran()->create(['jenis_pembayaran' => 'Pelunasan', 'metode_bayar' => 'Transfer', 'nominal' => $l->harga, 'tanggal_bayar' => now()->subMonths($i + 2)]);
            Testimoni::firstOrCreate(['pemesanan_id' => $p->id], ['rating' => $rating, 'isi' => $isi, 'status_tampil' => 'Tampil']);
        }
    }
}
