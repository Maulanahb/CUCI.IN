<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Super Administrator',
            'email' => 'admin@cuci.in',
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_user_management_index_page(): void
    {
        $staff = User::factory()->create([
            'name' => 'Kasir Utama',
            'role' => 'staff',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

        $response->assertOk()
            ->assertSee('Manajemen Pengguna')
            ->assertSee('Kasir Utama')
            ->assertSee('admin@cuci.in');
    }

    public function test_admin_can_filter_users_by_role_and_search_query(): void
    {
        $staff = User::factory()->create([
            'name' => 'Budi Staf',
            'email' => 'budi@cuci.in',
            'role' => 'staff',
        ]);

        $customer = User::factory()->create([
            'name' => 'Siti Pelanggan',
            'email' => 'siti@gmail.com',
            'role' => 'customer',
        ]);

        // Filter role: staff
        $response = $this->actingAs($this->admin)->get(route('admin.users.index', ['role' => 'staff']));
        $response->assertOk()
            ->assertSee('Budi Staf')
            ->assertDontSee('Siti Pelanggan');

        // Search query: siti
        $response = $this->actingAs($this->admin)->get(route('admin.users.index', ['q' => 'siti']));
        $response->assertOk()
            ->assertSee('Siti Pelanggan')
            ->assertDontSee('Budi Staf');
    }

    public function test_admin_can_create_new_staff_account_with_hashed_password(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Rian Kasir Baru',
            'email' => 'rian@cuci.in',
            'role' => 'staff',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'Rian Kasir Baru',
            'email' => 'rian@cuci.in',
            'role' => 'staff',
            'is_active' => true,
        ]);

        $newUser = User::where('email', 'rian@cuci.in')->firstOrFail();
        $this->assertTrue(Hash::check('password123', $newUser->password));
    }

    public function test_admin_can_update_user_name_email_and_role(): void
    {
        $user = User::factory()->create([
            'name' => 'Staf Lama',
            'email' => 'lama@cuci.in',
            'role' => 'staff',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $user), [
            'name' => 'Staf Diperbarui',
            'email' => 'baru@cuci.in',
            'role' => 'admin',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Staf Diperbarui',
            'email' => 'baru@cuci.in',
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_toggle_staff_active_status(): void
    {
        $staff = User::factory()->create([
            'name' => 'Staf Toggle',
            'role' => 'staff',
            'is_active' => true,
        ]);

        // Toggle nonaktif
        $response = $this->actingAs($this->admin)
            ->patch(route('admin.users.toggle-status', $staff));

        $response->assertRedirect()
            ->assertSessionHas('success');

        $this->assertFalse($staff->fresh()->is_active);

        // Toggle kembali aktif
        $this->actingAs($this->admin)
            ->patch(route('admin.users.toggle-status', $staff));

        $this->assertTrue($staff->fresh()->is_active);
    }

    public function test_admin_cannot_deactivate_own_account(): void
    {
        // Percobaan toggle status akun sendiri
        $response = $this->actingAs($this->admin)
            ->patch(route('admin.users.toggle-status', $this->admin));

        $response->assertRedirect()
            ->assertSessionHas('error');

        $this->assertTrue($this->admin->fresh()->is_active);

        // Percobaan nonaktif via form update
        $responseUpdate = $this->actingAs($this->admin)
            ->put(route('admin.users.update', $this->admin), [
                'name' => 'Super Administrator',
                'email' => 'admin@cuci.in',
                'role' => 'admin',
                'is_active' => '0',
            ]);

        $responseUpdate->assertSessionHasErrors(['is_active']);
        $this->assertTrue($this->admin->fresh()->is_active);
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $this->admin));

        $response->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
        ]);
    }

    public function test_deactivated_user_cannot_login(): void
    {
        $inactiveStaff = User::factory()->create([
            'email' => 'nonaktif@cuci.in',
            'password' => Hash::make('password123'),
            'role' => 'staff',
            'is_active' => false,
        ]);

        $response = $this->from(route('login'))->post(route('login.store'), [
            'email' => 'nonaktif@cuci.in',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email']);
    }

    public function test_non_admin_cannot_access_user_management(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'is_active' => true,
        ]);

        $customer = User::factory()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);

        // Staff dihalangi 403 Forbidden
        $responseStaff = $this->actingAs($staff)->get(route('admin.users.index'));
        $responseStaff->assertForbidden();

        // Customer dihalangi 403 Forbidden
        $responseCustomer = $this->actingAs($customer)->get(route('admin.users.index'));
        $responseCustomer->assertForbidden();
    }

    public function test_guest_cannot_access_user_management(): void
    {
        // Tamu tanpa login dialihkan ke form login
        $responseGuest = $this->get(route('admin.users.index'));
        $responseGuest->assertRedirect(route('login'));
    }
}
