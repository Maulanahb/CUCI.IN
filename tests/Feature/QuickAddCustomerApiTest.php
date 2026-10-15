<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuickAddCustomerApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_create_customer_via_json_request(): void
    {
        $staff = User::factory()->staff()->create();

        $response = $this->actingAs($staff)
            ->postJson(route('staff.customers.store'), [
                'name' => 'Budi Santoso',
                'phone' => '081234567890',
                'address' => 'Jl. Mawar No. 12',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Data pelanggan berhasil ditambahkan.',
                'customer' => [
                    'name' => 'Budi Santoso',
                    'phone' => '081234567890',
                ],
            ]);

        $this->assertDatabaseHas('customers', [
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'address' => 'Jl. Mawar No. 12',
        ]);
    }

    public function test_admin_can_create_customer_via_json_request(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
            ->postJson(route('admin.customers.store'), [
                'name' => 'Siti Rahma',
                'phone' => '+628987654321',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'customer' => [
                    'name' => 'Siti Rahma',
                    'phone' => '08987654321', // Auto-normalized by CustomerRequest
                ],
            ]);
    }

    public function test_validation_fails_returns_422_json(): void
    {
        $staff = User::factory()->staff()->create();

        $response = $this->actingAs($staff)
            ->postJson(route('staff.customers.store'), [
                'name' => '',
                'phone' => '',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'phone']);
    }

    public function test_unauthorized_users_cannot_create_customer(): void
    {
        $customerUser = User::factory()->customer()->create();

        $responseGuest = $this->postJson(route('staff.customers.store'), [
            'name' => 'Test',
            'phone' => '08123456789',
        ]);
        $responseGuest->assertStatus(401);

        $responseCustomer = $this->actingAs($customerUser)
            ->postJson(route('staff.customers.store'), [
                'name' => 'Test',
                'phone' => '08123456789',
            ]);
        $responseCustomer->assertStatus(403);
    }
}
