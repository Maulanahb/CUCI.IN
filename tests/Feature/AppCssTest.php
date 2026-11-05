<?php

namespace Tests\Feature;

use Tests\TestCase;

class AppCssTest extends TestCase
{
    public function test_reduced_motion_keeps_colour_transitions_and_spinners(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        preg_match('/@media \(prefers-reduced-motion: reduce\) \{(.*)\}\s*$/s', $css, $matches);
        $block = $matches[1] ?? '';

        $this->assertNotSame('', $block, 'Blok prefers-reduced-motion tidak ditemukan.');
        $this->assertStringNotContainsString('transition-duration: 0.01ms', $block);
        $this->assertStringContainsString('.animate-spin', $block);
    }

    public function test_reduced_motion_limits_transitions_to_non_spatial_properties(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        preg_match('/@media \(prefers-reduced-motion: reduce\) \{(.*)\}\s*$/s', $css, $matches);
        $block = $matches[1] ?? '';

        $this->assertMatchesRegularExpression('/transition-property:\s*color,[^;]*opacity[^;]*!important/', $block);
        $this->assertDoesNotMatchRegularExpression('/transition-property:[^;]*\b(transform|translate|scale|all)\b/', $block);
    }
}
