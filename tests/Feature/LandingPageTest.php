<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_renders_successfully(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('CUCI');
        $response->assertSee('Lacak Cucian');
    }

    public function test_landing_page_loads_livewire_and_alpine_assets(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $content = $response->getContent();

        $this->assertStringContainsString('livewire', $content, 'Landing page harus memuat bundle script Livewire/Alpine.');
    }

    public function test_landing_page_renders_accessible_mobile_navigation(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $content = $response->getContent();

        $this->assertStringContainsString('aria-label="Toggle Navigation Menu"', $content);
        $this->assertStringContainsString(':aria-expanded="mobileMenuOpen ? \'true\' : \'false\'"', $content);
        $this->assertStringContainsString('aria-controls="mobile-nav"', $content);
        $this->assertStringContainsString('id="mobile-nav"', $content);
        $this->assertStringContainsString('@click.outside="mobileMenuOpen = false"', $content);
        $this->assertStringContainsString('@keydown.escape.window="mobileMenuOpen = false"', $content);
    }

    public function test_landing_page_renders_interactive_pricing_calculator(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $content = $response->getContent();

        $this->assertStringContainsString('x-model="weight"', $content);
        $this->assertStringContainsString('x-text="formatMoney(total)"', $content);
    }
}
