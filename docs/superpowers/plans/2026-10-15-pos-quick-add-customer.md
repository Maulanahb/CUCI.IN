# POS Quick-Add Customer Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Enable staff and administrator to register new customers directly from the POS Transaction page (`staff.transactions.create`) via an AJAX Quick-Add Modal without reloading the page or losing current form inputs.

**Architecture:** Support dual-response (web redirect and JSON response) in `CustomerController::store`, then integrate a responsive Tailwind modal with Vanilla JS AJAX submission on the POS Transaction creation page that dynamically injects and selects the newly created customer.

**Tech Stack:** Laravel 11 (PHP 8.4), Blade, Vanilla JavaScript (`fetch` API), Tailwind CSS, PHPUnit.

**Spec:** In-chat bounded design approved in conversation session `ba48aee0-c715-43e6-a245-b4b99081d9d6`.

## Global Constraints

- PHP version floor: PHP 8.4.
- Must format all modified PHP files with `vendor/bin/pint --dirty --format agent`.
- No new external JS dependencies (use pure Vanilla JavaScript for modal and fetch).
- Phone number normalizer in `CustomerRequest` must remain active and functional for both web and JSON requests.
- All existing tests (134 tests) must continue to pass without regression.

## Review Focus

- Validation error handling via AJAX: invalid input (e.g. empty name, invalid phone) must show error text inside the modal without closing it or reloading the page.
- Existing POS state preservation: any services or weights already inputted in the POS form must remain intact when adding a customer.
- Route prefix agility: modal submission must dynamically post to `staff.customers.store` or `admin.customers.store` based on `$currentRoutePrefix`.
- CSRF Token inclusion: AJAX POST must properly include the `X-CSRF-TOKEN` header.
- Cross-role authorization: Admin and Staff must both be authorized to use the quick-add feature.

---

### Task 1: Backend JSON Support for Customer Creation

**Files:**
- Modify: `app/Http/Controllers/Staff/CustomerController.php:123-135`
- Test: `tests/Feature/QuickAddCustomerApiTest.php`

**Interfaces:**
- Consumes: `App\Http\Requests\CustomerRequest`
- Produces: `CustomerController::store(CustomerRequest $request): RedirectResponse|JsonResponse`
  - When `$request->expectsJson()`: returns HTTP 201 with `{ success: true, message: string, customer: { id, name, phone, address } }`
  - When standard web request: preserves existing `redirect()->route(...)` behavior

- [ ] **Step 1: Write failing feature test for JSON customer store**

Create `tests/Feature/QuickAddCustomerApiTest.php`:
```php
<?php

namespace Tests\Feature;

use App\Models\Customer;
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
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php vendor/phpunit/phpunit tests/Feature/QuickAddCustomerApiTest.php`
Expected: FAIL with status 302 instead of 201 (because controller currently redirects unconditionally).

- [ ] **Step 3: Update `CustomerController::store` to handle JSON requests**

In `app/Http/Controllers/Staff/CustomerController.php`:
```php
use Illuminate\Http\JsonResponse;

public function store(CustomerRequest $request): RedirectResponse|JsonResponse
{
    $customer = Customer::create($request->validated());

    if ($request->expectsJson()) {
        return response()->json([
            'success' => true,
            'message' => 'Data pelanggan berhasil ditambahkan.',
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'address' => $customer->address,
            ],
        ], 201);
    }

    $routePrefix = $request->routeIs('admin.*') ? 'admin' : 'staff';

    return redirect()->route("{$routePrefix}.customers.index", ['customer_id' => $customer->id])
        ->with('status', 'Data pelanggan berhasil ditambahkan.');
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php vendor/phpunit/phpunit tests/Feature/QuickAddCustomerApiTest.php`
Expected: PASS with 4 tests, 4 passed.

- [ ] **Step 5: Run existing customer tests to ensure zero regression**

Run: `php vendor/phpunit/phpunit --filter=StaffCustomerManagementTest`
Expected: PASS.

- [ ] **Step 6: Commit Task 1**

```bash
git add app/Http/Controllers/Staff/CustomerController.php tests/Feature/QuickAddCustomerApiTest.php
git commit -m "feat(customer): support JSON response in customer store for quick-add POS"
```

---

### Task 2: Quick-Add Customer Modal in POS Transaction View

**Files:**
- Modify: `resources/views/staff/transactions/create.blade.php:80-115`
- Test: `tests/Feature/StaffTransactionPageTest.php`

**Interfaces:**
- Consumes: `route("{$currentRoutePrefix}.customers.store")`
- Produces: UI modal `#modal-quick-customer`, trigger button `#btn-open-quick-customer`, and JS helper for AJAX submission & select injection

- [ ] **Step 1: Write test assertion for quick-add customer button and modal**

In `tests/Feature/StaffTransactionPageTest.php`, add test `test_pos_page_renders_quick_add_customer_button_and_modal`:
```php
public function test_pos_page_renders_quick_add_customer_button_and_modal(): void
{
    $this->withoutVite();
    $staff = User::factory()->staff()->create();

    $response = $this->actingAs($staff)->get(route('staff.transactions.create'));

    $response->assertOk();
    $response->assertSee('Pelanggan Baru');
    $response->assertSee('modal-quick-customer');
    $response->assertSee('quick_customer_name');
    $response->assertSee('quick_customer_phone');
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php vendor/phpunit/phpunit --filter=test_pos_page_renders_quick_add_customer_button_and_modal`
Expected: FAIL with "Pelanggan Baru" or "modal-quick-customer" not found.

- [ ] **Step 3: Implement Quick-Add Button, Modal, and JavaScript in `create.blade.php`**

In `resources/views/staff/transactions/create.blade.php`:
1. Beside the customer label, add a modern button:
   ```html
   <button
       type="button"
       id="btn-open-quick-customer"
       onclick="openQuickCustomerModal()"
       class="inline-flex items-center gap-1 rounded-lg border border-brand-200 bg-brand-50 px-2.5 py-1 text-[11px] font-semibold text-brand-700 transition hover:bg-brand-100 hover:text-brand-800"
   >
       <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
           <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
       </svg>
       <span>+ Pelanggan Baru</span>
   </button>
   ```
2. Add modal markup `#modal-quick-customer` with:
   - Backdrop overlay
   - Title "Tambah Pelanggan Cepat"
   - Inputs: `quick_customer_name` (wajib), `quick_customer_phone` (wajib), `quick_customer_address` (opsional)
   - Error container `#quick-customer-errors`
   - Action buttons: "Batal" (`closeQuickCustomerModal()`) and "Simpan & Pilih" (`submitQuickCustomer()`)
3. Add Vanilla JavaScript logic:
   - `openQuickCustomerModal()`: shows modal, focuses name input.
   - `closeQuickCustomerModal()`: hides modal, clears input and error text.
   - `submitQuickCustomer()`: sends `fetch()` POST to `{{ route("{$currentRoutePrefix}.customers.store") }}` with CSRF header.
   - On success:
     - Injects `<option value="${data.customer.id}">${data.customer.name} (${data.customer.phone})</option>` into `#customer_id`.
     - Sets `#customer_id.value = data.customer.id`.
     - Closes modal.
     - Displays brief green notification badge "✓ Pelanggan baru berhasil dipilih".
   - On 422 error: formats and renders validation messages inside `#quick-customer-errors`.

- [ ] **Step 4: Run test to verify it passes**

Run: `php vendor/phpunit/phpunit --filter=test_pos_page_renders_quick_add_customer_button_and_modal`
Expected: PASS.

- [ ] **Step 5: Run all POS & Transaction tests**

Run: `php vendor/phpunit/phpunit --filter=StaffTransactionPageTest`
Expected: PASS.

- [ ] **Step 6: Commit Task 2**

```bash
git add resources/views/staff/transactions/create.blade.php tests/Feature/StaffTransactionPageTest.php
git commit -m "feat(pos): add quick-add customer modal and auto-select to POS create page"
```

---

### Task 3: Full Suite Verification & Code Quality

**Files:**
- All modified files

- [ ] **Step 1: Run Pint code formatter**

Run: `vendor/bin/pint --dirty --format agent`
Expected: All files properly formatted according to project conventions.

- [ ] **Step 2: Run full PHPUnit test suite**

Run: `php vendor/phpunit/phpunit`
Expected: All tests pass (>= 139 tests, 0 failures).

- [ ] **Step 3: Push changes to remote branch**

Run: `git push origin feature/status-history-and-receipt-print`
Expected: Successfully pushed to GitHub.
