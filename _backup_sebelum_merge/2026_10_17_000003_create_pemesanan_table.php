<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemesanan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pemesanan', 30)->unique();
            $table->foreignId('klien_id')->constrained('klien')->restrictOnDelete();
            $table->date('tanggal_acara');
            $table->string('lokasi_acara')->nullable();
            $table->text('catatan')->nullable();
            $table->unsignedBigInteger('total_biaya')->default(0);
            $table->enum('status_pemesanan', ['Menunggu Konfirmasi', 'Dikonfirmasi', 'Selesai', 'Dibatalkan'])
                ->default('Menunggu Konfirmasi');
            $table->enum('status_pembayaran', ['Belum Bayar', 'DP', 'Lunas'])->default('Belum Bayar');
            $table->timestamps();

            $table->index('tanggal_acara');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemesanan');
    }
};
