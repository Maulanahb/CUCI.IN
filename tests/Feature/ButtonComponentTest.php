<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class ButtonComponentTest extends TestCase
{
    public function test_icon_button_keeps_desktop_size_and_grows_on_touch(): void
    {
        $html = Blade::render('<x-ui.button size="icon">x</x-ui.button>');

        $this->assertStringContainsString('size-8', $html);
        $this->assertStringContainsString('pointer-coarse:size-11', $html);
    }

    public function test_xs_row_and_sm_buttons_grow_on_touch(): void
    {
        foreach (['xs' => 'h-8', 'row' => 'h-8', 'sm' => 'h-9'] as $size => $desktopHeight) {
            $html = Blade::render('<x-ui.button size="'.$size.'">x</x-ui.button>');

            $this->assertStringContainsString($desktopHeight, $html, "size {$size} kehilangan tinggi desktop");
            $this->assertStringContainsString('pointer-coarse:h-11', $html, "size {$size} belum membesar di layar sentuh");
        }
    }

    public function test_md_button_stays_44px(): void
    {
        $html = Blade::render('<x-ui.button>x</x-ui.button>');

        $this->assertStringContainsString('h-11', $html);
    }
}
