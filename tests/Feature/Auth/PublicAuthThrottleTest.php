<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class PublicAuthThrottleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('127.0.0.1');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        RateLimiter::clear('127.0.0.1');
        parent::tearDown();
    }

    public function test_registration_route_is_throttled_after_six_requests(): void
    {
        for ($i = 1; $i <= 6; $i++) {
            $this->post('/register', [])->assertStatus(302);
        }

        $response = $this->post('/register', []);
        $response->assertStatus(429);
        $response->assertHeader('Retry-After');
    }

    public function test_forgot_password_route_is_throttled_after_six_requests(): void
    {
        for ($i = 1; $i <= 6; $i++) {
            $this->post('/forgot-password', [])->assertStatus(302);
        }

        $response = $this->post('/forgot-password', []);
        $response->assertStatus(429);
        $response->assertHeader('Retry-After');
    }

    public function test_reset_password_route_is_throttled_after_six_requests(): void
    {
        for ($i = 1; $i <= 6; $i++) {
            $this->post('/reset-password', [])->assertStatus(302);
        }

        $response = $this->post('/reset-password', []);
        $response->assertStatus(429);
        $response->assertHeader('Retry-After');
    }
}

