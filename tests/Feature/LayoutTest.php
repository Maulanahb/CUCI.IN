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
}
