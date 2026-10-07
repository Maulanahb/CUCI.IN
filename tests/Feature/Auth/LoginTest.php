<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_can_be_rendered(): void
    {
        $this->withoutVite();

        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Masuk ke CUCI.IN');
        $response->assertSee('CUCI.IN');
        $response->assertSee('Sistem Manajemen Operasional Laundry');
    }

    public function test_admin_can_authenticate_and_redirects_to_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@cuci.in',
            'password' => 'password',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@cuci.in',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_staff_can_authenticate_and_redirects_to_staff_dashboard(): void
    {
        $staff = User::factory()->staff()->create([
            'email' => 'staff@cuci.in',
            'password' => 'password',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'staff@cuci.in',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($staff);
        $response->assertRedirect(route('staff.dashboard'));
    }

    public function test_inactive_user_cannot_authenticate(): void
    {
        $inactive = User::factory()->inactive()->create([
            'email' => 'nonaktif@cuci.in',
            'password' => 'password',
        ]);

        $response = $this->post('/login', [
            'email' => 'nonaktif@cuci.in',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_user_cannot_authenticate_with_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'staff@cuci.in',
            'password' => 'password',
        ]);

        $response = $this->post('/login', [
            'email' => 'staff@cuci.in',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->staff()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status');
    }

    public function test_guest_cannot_access_protected_dashboard(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect(route('login'));
    }

    public function test_staff_cannot_access_admin_dashboard(): void
    {
        $staff = User::factory()->staff()->create();

        $response = $this->actingAs($staff)->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_admin_cannot_access_staff_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/staff/dashboard');

        $response->assertStatus(403);
    }

    public function test_active_user_disabled_mid_session_is_logged_out(): void
    {
        $staff = User::factory()->staff()->create(['is_active' => true]);

        // Login as active staff
        $response = $this->actingAs($staff)->get('/staff/dashboard');
        $response->assertStatus(200);

        // Deactivate user in DB
        $staff->update(['is_active' => false]);

        // Next request should log them out
        $response = $this->get('/staff/dashboard');
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
