<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileTransactionIndexLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_transactions_index_positions_toast_and_bulk_bar_above_mobile_bottom_navigation(): void
    {
        $this->withoutVite();
        $staff = User::factory()->staff()->create();

        $response = $this->actingAs($staff)->get(route('staff.transactions.index'));

        $response->assertOk();
        $response->assertSee('bottom-[calc(5rem+env(safe-area-inset-bottom))] right-4 sm:right-6 lg:bottom-6', false);
        $response->assertSee('bottom-[calc(4.75rem+env(safe-area-inset-bottom))] inset-x-4', false);
        $response->assertSee('pb-16 lg:pb-0', false);
        $response->assertDontSee('class="fixed bottom-6 inset-x-0 mx-auto z-40 w-fit max-w-lg', false);
    }

    public function test_admin_transactions_index_also_reserves_clearance_above_bottom_navigation(): void
    {
        $this->withoutVite();
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.transactions.index'));

        $response->assertOk();
        $response->assertSee('bottom-[calc(4.75rem+env(safe-area-inset-bottom))] inset-x-4', false);
    }
}
