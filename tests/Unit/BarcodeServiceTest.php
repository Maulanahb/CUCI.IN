<?php

namespace Tests\Unit;

use App\Services\BarcodeService;
use PHPUnit\Framework\TestCase;

class BarcodeServiceTest extends TestCase
{
    public function test_it_generates_valid_code128_svg(): void
    {
        $service = new BarcodeService;
        $svg = $service->generateCode128Svg('TRK-20261015-PRINT');

        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('viewBox="0 0', $svg);
        $this->assertStringContainsString('<path d="M', $svg);
        $this->assertStringContainsString('fill="#000000"', $svg);
    }

    public function test_it_generates_valid_qr_code_svg(): void
    {
        $service = new BarcodeService;
        $svg = $service->generateQrCodeSvg('https://cuci.in/lookup?code=TRK-20261015-PRINT');

        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('shape-rendering="crispEdges"', $svg);
        $this->assertStringContainsString('<path d="M', $svg);
        $this->assertStringContainsString('fill="#000000"', $svg);
    }
}
