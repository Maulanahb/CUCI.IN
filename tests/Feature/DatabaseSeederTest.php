<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_default_users_with_correct_roles_and_status(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@cuci.in')->firstOrFail();
        $staff = User::where('email', 'staff@cuci.in')->firstOrFail();
        $inactive = User::where('email', 'nonaktif@cuci.in')->firstOrFail();

        $this->assertSame('admin', $admin->role);
        $this->assertSame('staff', $staff->role);
        $this->assertTrue($staff->is_active);
        $this->assertFalse($inactive->is_active);
        $this->assertTrue(Hash::check('password', $admin->password));
    }

    public function test_it_seeds_active_and_inactive_services(): void
    {
        $this->seed();

        $this->assertSame(4, Service::where('is_active', true)->count());
        $this->assertSame(1, Service::where('is_active', false)->count());
    }

    public function test_it_seeds_guest_and_registered_customers(): void
    {
        $this->seed();

        $this->assertSame(3, Customer::whereNull('user_id')->count());

        $registeredCustomer = Customer::where('phone', '081234567804')->firstOrFail();

        $this->assertSame('customer@cuci.in', $registeredCustomer->user->email);
        $this->assertSame('customer', $registeredCustomer->user->role);
    }

    public function test_seeding_twice_does_not_duplicate_records(): void
    {
        $this->seed();
        $this->seed();

        $this->assertSame(4, User::count());
        $this->assertSame(5, Service::count());
        $this->assertSame(4, Customer::count());
    }
}
