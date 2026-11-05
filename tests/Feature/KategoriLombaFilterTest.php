<?php

namespace Tests\Feature;

use App\Models\Lomba;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KategoriLombaFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Memastikan dropdown kategori di halaman /lomba sinkron dengan Lomba::kategoriOptions()
     */
    public function test_dropdown_kategori_menampilkan_seluruh_kategori_standar(): void
    {
        $response = $this->get(route('lomba.index'));

        $response->assertOk();
        $response->assertSee('<option value="" selected>Semua Kategori</option>', false);

        foreach (Lomba::kategoriOptions() as $kategori) {
            $escaped = e($kategori);
            $response->assertSee('value="'.$escaped.'"', false);
            $response->assertSee('>'.$escaped.'</option>', false);
        }
    }

    /**
     * Memastikan filter kategori bekerja menyaring kartu lomba sesuai kategori yang dipilih
     */
    public function test_filter_kategori_menyaring_lomba_sesuai_kategori(): void
    {
        // GEMASTIK XVIII berkategori Software Development
        // UI/UX Design Challenge National Tech Summit 2026 berkategori UI/UX Design
        $response = $this->get(route('lomba.index', ['kategori' => 'Software Development']));

        $response->assertOk();
        $response->assertSee('GEMASTIK XVIII');
        $response->assertDontSee('UI/UX Design Challenge National Tech Summit 2026');

        // Pastikan dropdown menandai 'selected' pada kategori aktif
        $response->assertSee('value="Software Development" selected', false);
    }

    /**
     * Memastikan filter kategori dengan karakter khusus seperti '&' (Data Science & AI) bekerja
     */
    public function test_filter_kategori_dengan_karakter_khusus_bekerja(): void
    {
        $response = $this->get(route('lomba.index', ['kategori' => 'Data Science & AI']));

        $response->assertOk();
        $response->assertSee('Data Science Hackathon 2026 - DataFest Asia');
        $response->assertDontSee('GEMASTIK XVIII');
    }

    /**
     * Memastikan kombinasi filter kategori dengan parameter pencarian dan tingkat berjalan baik
     */
    public function test_kombinasi_filter_kategori_dengan_filter_lain(): void
    {
        $response = $this->get(route('lomba.index', [
            'kategori' => 'Software Development',
            'q' => 'COMPFEST',
            'tingkat' => 'nasional',
        ]));

        $response->assertOk();
        $response->assertSee('COMPFEST 17 UI');
        $response->assertDontSee('GEMASTIK XVIII');
    }

    /**
     * Memastikan 'Semua Kategori' (tanpa filter kategori) menampilkan semua lomba
     */
    public function test_reset_atau_tanpa_kategori_menampilkan_semua_lomba(): void
    {
        $response = $this->get(route('lomba.index'));

        $response->assertOk();
        $response->assertSee('GEMASTIK XVIII');
        $response->assertSee('UI/UX Design Challenge National Tech Summit 2026');
    }
}
