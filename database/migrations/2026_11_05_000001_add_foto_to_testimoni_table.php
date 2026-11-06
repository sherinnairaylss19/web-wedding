<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Foto bukti opsional yang diunggah klien bersama testimoni. */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('testimoni', function (Blueprint $t) {
            $t->string('foto')->nullable()->after('isi');
        });
    }

    public function down(): void
    {
        Schema::table('testimoni', function (Blueprint $t) {
            $t->dropColumn('foto');
        });
    }
};
