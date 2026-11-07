<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Transaction;
use App\Models\User;
use App\Policies\TransactionPolicy;
use Tests\TestCase;

class TransactionPolicyTest extends TestCase
{
    private TransactionPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new TransactionPolicy;
    }

    public function test_view_any_is_allowed_for_authenticated_roles(): void
    {
        $admin = new User(['role' => 'admin']);
        $staff = new User(['role' => 'staff']);
        $customer = new User(['role' => 'customer']);

        $this->assertTrue($this->policy->viewAny($admin));
        $this->assertTrue($this->policy->viewAny($staff));
        $this->assertTrue($this->policy->viewAny($customer));
    }

    public function test_view_allows_staff_and_admin(): void
    {
        $admin = new User(['role' => 'admin']);
        $staff = new User(['role' => 'staff']);
        $transaction = new Transaction;

        $this->assertTrue($this->policy->view($admin, $transaction));
        $this->assertTrue($this->policy->view($staff, $transaction));
    }

    public function test_view_allows_owner_customer_and_forbids_other_customer(): void
    {
        $customerUserA = new User(['role' => 'customer']);
        $customerUserA->id = 1;
        $customerA = new Customer;
        $customerA->id = 10;
        $customerUserA->setRelation('customer', $customerA);

        $customerUserB = new User(['role' => 'customer']);
        $customerUserB->id = 2;
        $customerB = new Customer;
        $customerB->id = 20;
        $customerUserB->setRelation('customer', $customerB);

        $transactionA = new Transaction;
        $transactionA->customer_id = 10;

        // Pemilik boleh melihat nota miliknya
        $this->assertTrue($this->policy->view($customerUserA, $transactionA));

        // Pelanggan lain dilarang melihat nota A
        $this->assertFalse($this->policy->view($customerUserB, $transactionA));
    }

    public function test_create_and_update_allowed_for_staff_and_admin_only(): void
    {
        $admin = new User(['role' => 'admin']);
        $staff = new User(['role' => 'staff']);
        $customer = new User(['role' => 'customer']);
        $transaction = new Transaction;

        $this->assertTrue($this->policy->create($admin));
        $this->assertTrue($this->policy->create($staff));
        $this->assertFalse($this->policy->create($customer));

        $this->assertTrue($this->policy->update($admin, $transaction));
        $this->assertTrue($this->policy->update($staff, $transaction));
        $this->assertFalse($this->policy->update($customer, $transaction));
    }

    public function test_delete_is_allowed_for_admin_only(): void
    {
        $admin = new User(['role' => 'admin']);
        $staff = new User(['role' => 'staff']);
        $transaction = new Transaction;

        $this->assertTrue($this->policy->delete($admin, $transaction));
        $this->assertFalse($this->policy->delete($staff, $transaction));
    }
}

