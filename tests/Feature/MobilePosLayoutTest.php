<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobilePosLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_create_page_positions_floating_bar_above_mobile_bottom_navigation(): void
    {
        $this->withoutVite();

        $staff = User::factory()->staff()->create();

        $response = $this->actingAs($staff)->get(route('staff.transactions.create'));

        $response->assertOk();
        $response->assertSee('bottom-[calc(4rem+env(safe-area-inset-bottom))]', false);
        $response->assertSee('pb-36 lg:pb-0', false);
        $response->assertSee('Lihat Nota & Bayar', false);
        $response->assertDontSee('fixed bottom-0 inset-x-0 z-40 lg:hidden', false);
    }

    public function test_admin_pos_create_page_also_reserves_clearance_above_bottom_navigation(): void
    {
        $this->withoutVite();

        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.transactions.create'));

        $response->assertOk();
        $response->assertSee('bottom-[calc(4rem+env(safe-area-inset-bottom))]', false);
    }
}
