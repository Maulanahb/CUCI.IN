<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_screen_can_be_rendered(): void
    {
        $this->withoutVite();

        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
        $response->assertSee('Lupa Kata Sandi?');
        $response->assertSee('CUCI');
    }

    public function test_reset_password_link_can_be_requested_for_registered_user(): void
    {
        Notification::fake();

        $user = User::factory()->customer()->create([
            'email' => 'customer@cuci.in',
        ]);

        $response = $this->post('/forgot-password', [
            'email' => 'customer@cuci.in',
        ]);

        $response->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_password_link_fails_for_unregistered_email(): void
    {
        $response = $this->post('/forgot-password', [
            'email' => 'tidakada@example.com',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        $this->withoutVite();

        $user = User::factory()->customer()->create();
        $token = Password::createToken($user);

        $response = $this->get('/reset-password/'.$token.'?email='.$user->email);

        $response->assertStatus(200);
        $response->assertSee('Atur Ulang Kata Sandi');
        $response->assertSee($user->email);
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $user = User::factory()->customer()->create([
            'email' => 'customer@cuci.in',
            'password' => 'oldpassword123',
        ]);

        $token = Password::createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => 'customer@cuci.in',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status');

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    public function test_password_cannot_be_reset_with_invalid_token(): void
    {
        $user = User::factory()->customer()->create([
            'email' => 'customer@cuci.in',
            'password' => 'oldpassword123',
        ]);

        $response = $this->post('/reset-password', [
            'token' => 'invalid-token-string',
            'email' => 'customer@cuci.in',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors('email');

        $user->refresh();
        $this->assertTrue(Hash::check('oldpassword123', $user->password));
    }

    public function test_password_cannot_be_reset_with_mismatched_confirmation(): void
    {
        $user = User::factory()->customer()->create([
            'email' => 'customer@cuci.in',
        ]);

        $token = Password::createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => 'customer@cuci.in',
            'password' => 'newpassword123',
            'password_confirmation' => 'differentpassword',
        ]);

        $response->assertSessionHasErrors('password');
    }
}
