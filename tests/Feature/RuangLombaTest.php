<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RuangLombaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Test Dashboard Analitik Prestasi loads properly
     */
    public function test_dashboard_analitik_loads_successfully(): void
    {
        $response = $this->get('/analitik');

        $response->assertStatus(200);
        $response->assertSee('Dashboard Analitik');
        $response->assertSee('Prestasi Juara');
        $response->assertSee('D-IV Sistem Informasi Bisnis');
        $response->assertSee('D-IV Teknik Informatika');
    }

    /**
     * Test Dashboard root URL / loads properly
     */
    public function test_dashboard_root_url_loads(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Dashboard Analitik');
    }

    /**
     * Test Dashboard filter by year works
     */
    public function test_dashboard_filter_by_year(): void
    {
        $response = $this->get('/analitik?tahun=2026');

        $response->assertStatus(200);
        $response->assertSee('Tahun 2026');
    }
}
