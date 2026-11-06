<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_pemesanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemesanan_id')->constrained('pemesanan')->cascadeOnDelete();
            $table->foreignId('layanan_id')->constrained('layanan')->restrictOnDelete();
            $table->unsignedInteger('jumlah')->default(1);
            $table->unsignedBigInteger('harga_satuan'); // snapshot harga saat dipesan
            $table->unsignedBigInteger('subtotal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_pemesanan');
    }
};
