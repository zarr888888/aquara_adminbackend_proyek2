<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SemuaHalamanBisaDiAksesTest extends TestCase
{

    use RefreshDatabase;
    /**
     * TEST 1: Pastikan kalau ada yang buka web utama, langsung dilempar (302).
     */
    public function test_halaman_utama_redirect_ke_admin(): void
    {
        $response = $this->get('/');
        $response->assertStatus(302); 
    }

    /**
     * TEST 2: Pastikan Halaman LOGIN FILAMENT berhasil terbuka dengan sempurna (200 OK).
     */
    public function test_halaman_login_filament_bisa_dibuka(): void
    {
        $response = $this->get('/admin/login');
        
        $response->assertStatus(200);
    }

    /**
     * TEST 3: Pastikan semua API publik (GET) merespon dengan baik (200 OK).
     */
    public function test_semua_api_publik_bisa_diakses(): void
    {
        $daftarApi = [
            '/api/fish-types',
            '/api/stocks',
            '/api/posts',
            '/api/forum',
            '/api/pasars',
        ];

        foreach ($daftarApi as $url) {
            $response = $this->get($url);
            $response->assertStatus(200);
        }
    }
}