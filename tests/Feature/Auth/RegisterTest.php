<?php

namespace Tests\Feature\Auth;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $this->withoutVite();

        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Daftar Akun Pelanggan');
        $response->assertSee('CUCI');
        $response->assertSee('_hp_website');
    }

    public function test_authenticated_user_is_redirected_away_from_registration(): void
    {
        $user = User::factory()->customer()->create();

        $response = $this->actingAs($user)->get('/register');

        $response->assertRedirect(route('dashboard'));
    }

    public function test_new_customer_can_register_successfully(): void
    {
        $response = $this->post('/register', [
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'email' => 'budi@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            '_hp_website' => '',
            '_hp_timestamp' => time() - 5,
        ]);

        $response->assertRedirect(route('customer.dashboard'));
        $this->assertAuthenticated();

        $user = User::where('email', 'budi@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('Budi Santoso', $user->name);
        $this->assertSame('customer', $user->role);
        $this->assertTrue($user->is_active);

        $customer = Customer::where('user_id', $user->id)->first();
        $this->assertNotNull($customer);
        $this->assertSame('Budi Santoso', $customer->name);
        $this->assertSame('081234567890', $customer->phone);
    }

    public function test_registration_fails_if_email_is_already_registered(): void
    {
        User::factory()->create([
            'email' => 'budi@example.com',
        ]);

        $response = $this->post('/register', [
            'name' => 'Budi Baru',
            'phone' => '081299998888',
            'email' => 'budi@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            '_hp_website' => '',
            '_hp_timestamp' => time() - 5,
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_registration_fails_if_password_confirmation_does_not_match(): void
    {
        $response = $this->post('/register', [
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'email' => 'budi@example.com',
            'password' => 'password123',
            'password_confirmation' => 'beda12345',
            '_hp_website' => '',
            '_hp_timestamp' => time() - 5,
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    public function test_registration_blocked_by_honeypot_trap_if_bot_fills_hidden_field(): void
    {
        $response = $this->post('/register', [
            'name' => 'Spam Bot',
            'phone' => '081234567890',
            'email' => 'bot@spammer.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            '_hp_website' => 'http://spam-link-promo.com',
            '_hp_timestamp' => time() - 5,
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'bot@spammer.com']);
    }

    public function test_registration_blocked_if_submitted_faster_than_threshold(): void
    {
        // Bot mengirim form instan (0 detik setelah page load)
        $response = $this->post('/register', [
            'name' => 'Fast Bot',
            'phone' => '081234567890',
            'email' => 'fast@bot.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            '_hp_website' => '',
            '_hp_timestamp' => time(),
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'fast@bot.com']);
    }

    public function test_registered_customer_can_access_customer_dashboard(): void
    {
        $this->withoutVite();

        $user = User::factory()->customer()->create();
        Customer::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'phone' => '08123456789',
        ]);

        $response = $this->actingAs($user)->get('/customer/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Selamat Datang');
        $response->assertSee($user->name);
    }

    public function test_customer_cannot_access_admin_or_staff_dashboard(): void
    {
        $customerUser = User::factory()->customer()->create();

        $this->actingAs($customerUser)->get('/admin/dashboard')->assertStatus(403);
        $this->actingAs($customerUser)->get('/staff/dashboard')->assertStatus(403);
    }

    public function test_login_page_contains_link_to_customer_registration(): void
    {
        $this->withoutVite();

        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee(route('register'));
        $response->assertSee('Daftar Pelanggan');
    }

    public function test_registration_fails_if_phone_number_is_too_short_or_invalid(): void
    {
        // 1. Uji nomor pendek 4 digit (kasus BUG-06: 0681)
        $responseShort = $this->post('/register', [
            'name' => 'Pelanggan Uji',
            'phone' => '0681',
            'email' => 'short@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            '_hp_website' => '',
            '_hp_timestamp' => time() - 5,
        ]);

        $responseShort->assertSessionHasErrors('phone');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'short@example.com']);

        // 2. Uji nomor telepon rumah / bukan seluler (021...)
        $responseLandline = $this->post('/register', [
            'name' => 'Pelanggan Rumah',
            'phone' => '0217654321',
            'email' => 'landline@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            '_hp_website' => '',
            '_hp_timestamp' => time() - 5,
        ]);

        $responseLandline->assertSessionHasErrors('phone');
        $this->assertGuest();

        // 3. Uji nomor mengandung karakter non-angka
        $responseLetters = $this->post('/register', [
            'name' => 'Pelanggan Karakter',
            'phone' => '0812abcd1234',
            'email' => 'letters@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            '_hp_website' => '',
            '_hp_timestamp' => time() - 5,
        ]);

        $responseLetters->assertSessionHasErrors('phone');
        $this->assertGuest();
    }

    public function test_registration_succeeds_with_various_valid_indonesian_phone_formats(): void
    {
        $validPhones = [
            'format_lokal' => '081234567890',
            'format_plus_62' => '+6281234567891',
            'format_62' => '6281234567892',
        ];

        foreach ($validPhones as $key => $phone) {
            $email = "user_{$key}@example.com";

            $response = $this->post('/register', [
                'name' => "User {$key}",
                'phone' => $phone,
                'email' => $email,
                'password' => 'password123',
                'password_confirmation' => 'password123',
                '_hp_website' => '',
                '_hp_timestamp' => time() - 5,
            ]);

            $response->assertSessionHasNoErrors();
            $response->assertRedirect(route('customer.dashboard'));

            $this->assertDatabaseHas('users', ['email' => $email]);
            $this->assertDatabaseHas('customers', ['phone' => $phone]);

            // Logout untuk iterasi pengujian berikutnya
            auth()->logout();
        }
    }
}
