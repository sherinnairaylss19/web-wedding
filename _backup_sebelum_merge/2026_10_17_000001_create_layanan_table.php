<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('layanan', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->enum('kategori', ['Wedding', 'Catering']);
            $table->text('deskripsi');
            $table->unsignedBigInteger('harga');
            $table->string('satuan', 30)->nullable(); // mis. "/ porsi", "/ box"
            $table->string('foto')->nullable();        // path di disk public atau URL
            $table->timestamps();
            $table->softDeletes();                     // hapus aman: riwayat pemesanan tetap utuh
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('layanan');
    }
};
