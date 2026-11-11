<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ApiRateLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('throttle:api')->get('/_test/throttle', function () {
            return response('OK');
        });

        $this->clearAllApiRateLimits();
    }

    protected function tearDown(): void
    {
        $this->clearAllApiRateLimits();
        parent::tearDown();
    }

    /**
     * Bersihkan cache rate limiter untuk identifier pengguna dan IP pengujian.
     */
    protected function clearAllApiRateLimits(): void
    {
        $keys = ['127.0.0.1', '192.168.1.50', '192.168.1.99', '1', '2', '3', '4', '5'];
        foreach ($keys as $key) {
            RateLimiter::clear($key);
            RateLimiter::clear(md5('api'.$key));
        }
    }

    /**
     * Cakupan 3.a: 60 permintaan pertama dalam satu menit diterima,
     * dan permintaan ke-61 menghasilkan HTTP status 429.
     */
    public function test_sixty_first_request_within_a_minute_is_throttled_with_429(): void
    {
        for ($i = 1; $i <= 60; $i++) {
            $this->getJson('/_test/throttle')->assertStatus(200);
        }

        $response = $this->getJson('/_test/throttle');
        $response->assertStatus(429);
        $response->assertHeader('Retry-After');

        // Setelah waktu 61 detik berlalu, permintaan diterima kembali
        $this->travel(61)->seconds();
        $this->getJson('/_test/throttle')->assertStatus(200);
        $this->travelBack();
    }

    /**
     * Cakupan 3.b: Halaman web 429 tampil dengan pesan ramah pengguna dan waktu Retry-After.
     */
    public function test_web_request_displays_429_page_with_message_and_retry_after(): void
    {
        for ($i = 1; $i <= 60; $i++) {
            $this->get('/_test/throttle')->assertStatus(200);
        }

        $response = $this->get('/_test/throttle');

        $response->assertStatus(429);
        $response->assertHeader('Retry-After');
        $response->assertSee('Terlalu banyak percobaan, coba lagi beberapa saat');
        $response->assertSee((string) $response->headers->get('Retry-After'));
    }

    /**
     * Cakupan 3.c: Permintaan JSON mendapat payload body JSON dengan message,
     * retry_after, dan header Retry-After.
     */
    public function test_json_request_receives_json_body_with_message_retry_after_and_header(): void
    {
        for ($i = 1; $i <= 60; $i++) {
            $this->getJson('/_test/throttle')->assertStatus(200);
        }

        $response = $this->getJson('/_test/throttle');

        $response->assertStatus(429);
        $response->assertHeader('Retry-After');
        $response->assertJsonStructure([
            'message',
            'retry_after',
        ]);
        $response->assertJsonPath('message', 'Terlalu banyak percobaan, coba lagi beberapa saat.');
        $response->assertJsonPath('retry_after', (int) $response->headers->get('Retry-After'));
    }

    /**
     * Cakupan 3.d: Pengguna terotentikasi dan tamu diisolasi menggunakan limit masing-masing.
     */
    public function test_authenticated_users_and_guests_use_isolated_rate_limits(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // Habiskan kuota User A (60 request)
        for ($i = 1; $i <= 60; $i++) {
            $this->actingAs($userA)->getJson('/_test/throttle')->assertStatus(200);
        }

        // Permintaan ke-61 User A terkena 429
        $this->actingAs($userA)->getJson('/_test/throttle')->assertStatus(429);

        // User B yang login tidak ikut terkunci (memiliki kuota sendiri berbasis user_id)
        $this->actingAs($userB)->getJson('/_test/throttle')->assertStatus(200);

        // Logout untuk menguji pengguna tamu (guest)
        auth()->logout();

        // Pengguna tamu (guest) dari IP default 127.0.0.1 memiliki kuota sendiri berbasis IP
        $this->getJson('/_test/throttle')->assertStatus(200);

        // Habiskan sisa 59 kuota tamu dari IP default
        for ($i = 2; $i <= 60; $i++) {
            $this->getJson('/_test/throttle')->assertStatus(200);
        }
        $this->getJson('/_test/throttle')->assertStatus(429);

        // Tamu dari IP lain (192.168.1.99) tidak ikut terkunci
        $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.99'])
            ->getJson('/_test/throttle')
            ->assertStatus(200);
    }
}
