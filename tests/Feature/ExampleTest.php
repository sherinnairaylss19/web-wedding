<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    // Beranda membaca tabel layanan/testimoni, jadi database test harus dimigrasi.
    use RefreshDatabase;

    public function test_beranda_dapat_dibuka(): void
    {
        $this->get('/')->assertOk();
    }
}
