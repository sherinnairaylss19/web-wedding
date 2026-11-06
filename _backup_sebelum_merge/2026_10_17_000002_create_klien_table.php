<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('klien', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('no_whatsapp', 20)->unique(); // format 628xxxxxxxxx, kunci pengenal klien
            $table->string('email')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('klien');
    }
};
