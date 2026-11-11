<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthRateLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clearAllTestRateLimits();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        $this->clearAllTestRateLimits();
        parent::tearDown();
    }

    /**
     * Bersihkan cache rate limiter untuk email dan IP pengujian.
     */
    protected function clearAllTestRateLimits(): void
    {
        $emails = ['staff@cuci.in', 'target@cuci.in', 'other@cuci.in'];
        $ips = ['127.0.0.1', '192.168.1.100'];

        foreach ($emails as $email) {
            foreach ($ips as $ip) {
                $rawKey = Str::transliterate(Str::lower($email)).'|'.$ip;
                RateLimiter::clear($rawKey);
                RateLimiter::clear(md5('login'.$rawKey));
            }
        }
    }

    /**
     * Cakupan 4.a: Lima percobaan pertama diproses normal
     * (kredensial salah menghasilkan respons kesalahan login biasa / redirect 302, bukan 429).
     */
    public function test_first_five_login_attempts_are_allowed_and_processed_normally(): void
    {
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $response = $this->post('/login', [
                'email' => 'staff@cuci.in',
                'password' => 'wrong-password',
            ]);

            $response->assertStatus(302);
            $response->assertSessionHasErrors('email');
            $this->assertGuest();
        }
    }
}
