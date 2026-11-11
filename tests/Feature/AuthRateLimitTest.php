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

    /**
     * Cakupan 4.b: Percobaan ke-6 menghasilkan HTTP 429 dengan header Retry-After.
     */
    public function test_sixth_login_attempt_is_throttled_with_429_and_retry_after_header(): void
    {
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post('/login', [
                'email' => 'staff@cuci.in',
                'password' => 'wrong-password',
            ])->assertStatus(302);
        }

        $response = $this->post('/login', [
            'email' => 'staff@cuci.in',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(429);
        $response->assertHeader('Retry-After');
        $this->assertNotEmpty($response->headers->get('Retry-After'));
    }

    /**
     * Cakupan 4.c: Kunci limiter diisolasi per email dan IP
     * (email berbeda atau IP berbeda tidak ikut terkunci saat satu akun terkena limit).
     */
    public function test_rate_limiter_is_isolated_per_email_and_ip_address(): void
    {
        // Kunci target@cuci.in dari IP default 127.0.0.1 (5 kali normal, ke-6 terkena 429)
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post('/login', [
                'email' => 'target@cuci.in',
                'password' => 'wrong-password',
            ])->assertStatus(302);
        }

        $this->post('/login', [
            'email' => 'target@cuci.in',
            'password' => 'wrong-password',
        ])->assertStatus(429);

        // Percobaan dengan EMAIL BERBEDA dari IP yang sama harus tetap diproses normal (302)
        $responseDifferentEmail = $this->post('/login', [
            'email' => 'other@cuci.in',
            'password' => 'wrong-password',
        ]);
        $responseDifferentEmail->assertStatus(302);
        $responseDifferentEmail->assertSessionHasErrors('email');

        // Percobaan dengan EMAIL SAMA dari IP BERBEDA harus tetap diproses normal (302)
        $responseDifferentIp = $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.100'])
            ->post('/login', [
                'email' => 'target@cuci.in',
                'password' => 'wrong-password',
            ]);
        $responseDifferentIp->assertStatus(302);
        $responseDifferentIp->assertSessionHasErrors('email');
    }

    /**
     * Cakupan 4.d: Setelah waktu decay berlalu (Carbon::setTestNow), percobaan diterima lagi.
     */
    public function test_login_attempts_are_allowed_again_after_decay_window_passes(): void
    {
        Carbon::setTestNow(Carbon::now());

        // Habiskan 5 kuota percobaan
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post('/login', [
                'email' => 'staff@cuci.in',
                'password' => 'wrong-password',
            ])->assertStatus(302);
        }

        // Percobaan ke-6 harus 429
        $this->post('/login', [
            'email' => 'staff@cuci.in',
            'password' => 'wrong-password',
        ])->assertStatus(429);

        // Majukan waktu 61 detik (melewati jendela 1 menit)
        Carbon::setTestNow(Carbon::now()->addSeconds(61));

        // Percobaan ke-7 harus diterima lagi (respons 302 login gagal biasa, bukan 429)
        $responseAfterWindow = $this->post('/login', [
            'email' => 'staff@cuci.in',
            'password' => 'wrong-password',
        ]);

        $responseAfterWindow->assertStatus(302);
        $responseAfterWindow->assertSessionHasErrors('email');
    }
}
