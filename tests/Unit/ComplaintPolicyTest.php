<?php

namespace Tests\Unit;

use App\Models\Complaint;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\User;
use App\Policies\ComplaintPolicy;
use Tests\TestCase;

class ComplaintPolicyTest extends TestCase
{
    private ComplaintPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new ComplaintPolicy;
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
        $complaint = new Complaint;

        $this->assertTrue($this->policy->view($admin, $complaint));
        $this->assertTrue($this->policy->view($staff, $complaint));
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

        $complaintA = new Complaint;
        $complaintA->setRelation('transaction', $transactionA);

        // Pemilik boleh melihat
        $this->assertTrue($this->policy->view($customerUserA, $complaintA));

        // Bukan pemilik dilarang melihat
        $this->assertFalse($this->policy->view($customerUserB, $complaintA));
    }

    public function test_create_is_allowed_for_customers_with_profile(): void
    {
        $customerUser = new User(['role' => 'customer']);
        $customerUser->setRelation('customer', new Customer);

        $staffUser = new User(['role' => 'staff']);

        $this->assertTrue($this->policy->create($customerUser));
        $this->assertFalse($this->policy->create($staffUser));
    }

    public function test_update_is_allowed_for_staff_and_admin_only(): void
    {
        $admin = new User(['role' => 'admin']);
        $staff = new User(['role' => 'staff']);
        $customer = new User(['role' => 'customer']);
        $complaint = new Complaint;

        $this->assertTrue($this->policy->update($admin, $complaint));
        $this->assertTrue($this->policy->update($staff, $complaint));
        $this->assertFalse($this->policy->update($customer, $complaint));
    }

    public function test_delete_is_allowed_for_admin_only(): void
    {
        $admin = new User(['role' => 'admin']);
        $staff = new User(['role' => 'staff']);
        $complaint = new Complaint;

        $this->assertTrue($this->policy->delete($admin, $complaint));
        $this->assertFalse($this->policy->delete($staff, $complaint));
    }
}

