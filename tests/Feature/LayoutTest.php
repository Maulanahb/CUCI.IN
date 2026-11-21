<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class LayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_layout_renders_slot_and_livewire_assets(): void
    {
        $this->withoutVite();

        $html = Blade::render('<x-layouts::guest>Konten Guest</x-layouts::guest>');

        $this->assertStringContainsString('Konten Guest', $html);
        $this->assertStringContainsString('CUCI', $html);
        $this->assertStringContainsString('livewire', $html);
    }

    public function test_app_layout_renders_title_and_authenticated_user(): void
    {
        $this->withoutVite();
        $this->actingAs(User::factory()->staff()->create(['name' => 'Sari Staff']));

        $html = Blade::render('<x-layouts::app title="Dashboard">Konten App</x-layouts::app>');

        $this->assertStringContainsString('Konten App', $html);
        $this->assertStringContainsString('Dashboard — ', $html);
        $this->assertStringContainsString('Sari Staff', $html);
        $this->assertStringContainsString('staff', $html);
    }

    public function test_app_layout_marks_closed_mobile_sidebar_inert(): void
    {
        $this->withoutVite();
        $this->actingAs(User::factory()->staff()->create());

        $html = Blade::render('<x-layouts::app title="Dashboard">Konten App</x-layouts::app>');

        $this->assertStringContainsString('isDesktop', $html);
        $this->assertStringContainsString(':inert="! sidebarOpen && ! isDesktop"', $html);
    }

    public function test_app_layout_tracks_breakpoint_in_rem_and_manages_sidebar_focus(): void
    {
        $this->withoutVite();
        $this->actingAs(User::factory()->staff()->create());

        $html = Blade::render('<x-layouts::app title="Dashboard">Konten App</x-layouts::app>');

        $this->assertStringContainsString('(min-width: 64rem)', $html);
        $this->assertStringNotContainsString('1024px', $html);
        $this->assertStringContainsString("\$watch('sidebarOpen'", $html);
        $this->assertStringContainsString('if (event.matches) { sidebarOpen = false; }', $html);
    }

    public function test_app_layout_renders_viewport_cover_and_mobile_bottom_navigation_for_customer(): void
    {
        $this->withoutVite();
        $user = User::factory()->customer()->create();

        $response = $this->actingAs($user)->get(route('customer.dashboard'));

        $response->assertOk();
        $response->assertSee('viewport-fit=cover', false);
        $response->assertSee('lg:hidden print:hidden', false);
        $response->assertSeeInOrder(['aria-label="Navigasi bawah"', 'Beranda', 'Riwayat', 'Pengaduan', 'Lacak Nota', 'Lainnya'], false);
    }

    public function test_app_layout_renders_four_core_bottom_tabs_plus_more_for_staff(): void
    {
        $this->withoutVite();
        $user = User::factory()->staff()->create();

        $response = $this->actingAs($user)->get(route('staff.dashboard'));

        $response->assertOk();
        $response->assertSeeInOrder(['aria-label="Navigasi bawah"', 'Beranda', 'Kasir POS', 'Transaksi', 'Verifikasi', 'Lainnya'], false);
        $response->assertSee('data-bottom-overflow-active="false"', false);
    }

    public function test_app_layout_renders_four_core_bottom_tabs_plus_more_for_admin(): void
    {
        $this->withoutVite();
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSeeInOrder(['aria-label="Navigasi bawah"', 'Beranda', 'Kasir POS', 'Transaksi', 'Laporan', 'Lainnya'], false);
        $response->assertSee('data-bottom-overflow-active="false"', false);
    }

    public function test_app_layout_marks_more_button_active_when_visiting_overflow_route(): void
    {
        $this->withoutVite();
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee('data-bottom-overflow-active="true"', false);
    }

    public function test_app_layout_reserves_main_bottom_clearance_on_mobile_and_tablet(): void
    {
        $this->withoutVite();

        $html = Blade::render('<x-layouts::app title="Dashboard">Konten App</x-layouts::app>');

        $this->assertStringContainsString('pb-[calc(5.5rem+env(safe-area-inset-bottom))]', $html);
        $this->assertStringContainsString('lg:pb-6', $html);
        $this->assertStringContainsString('id="sidebar-toggle"', $html);
    }
}
