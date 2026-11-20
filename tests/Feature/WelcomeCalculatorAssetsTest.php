<?php

namespace Tests\Feature;

use Tests\TestCase;

class WelcomeCalculatorAssetsTest extends TestCase
{
    /**
     * Landing page memakai Alpine.js untuk kalkulator estimasi dan menu mobile,
     * sehingga harus memuat asset Livewire (pembawa Alpine) seperti layout guest.
     */
    public function test_landing_page_loads_livewire_assets_so_alpine_calculator_works(): void
    {
        $this->withoutVite();

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('id="estimasi"', false);
        $response->assertSee('livewire.js', false);
        $response->assertSee('<!-- Livewire Styles -->', false);
    }
}