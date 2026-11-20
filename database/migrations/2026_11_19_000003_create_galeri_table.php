<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Foto karya Ike's Gallery untuk halaman Gallery publik, dikelola admin.
 * Sebelumnya gallery di-hardcode di controller dengan foto stok; sekarang berasal dari tabel ini.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('galeri', function (Blueprint $t) {
            $t->id();
            $t->string('judul', 100)->nullable();             // keterangan foto (opsional)
            $t->enum('kategori', ['Wedding', 'Catering']);
            $t->string('foto');                               // path di disk 'public'
            $t->unsignedSmallInteger('urutan')->default(0);   // angka kecil tampil lebih dulu
            $t->boolean('aktif')->default(true)->index();     // nonaktif = disembunyikan dari publik
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('galeri');
    }
};
