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

    public function test_it_generates_mathematically_decodable_qr_code_svg(): void
    {
        $service = new BarcodeService;
        $inputs = [
            '',
            'TRX-0001',
            'https://cuci.in/lookup?code=TRK-20261015-PRINT',
            'https://portal-cuciin.web.id/lookup?code=TRK-20261113-0001',
            str_repeat('A', 78),
        ];

        foreach ($inputs as $input) {
            $svg = $service->generateQrCodeSvg($input, 96);
            $decoded = $this->decodeAndVerifyQrSvg($svg);

            $this->assertSame(0, $decoded['format_syndrome_1'], "Format copy 1 BCH invalid for: {$input}");
            $this->assertSame(0, $decoded['format_syndrome_2'], "Format copy 2 BCH invalid for: {$input}");
            $this->assertTrue($decoded['rs_valid'], "Reed-Solomon syndromes non-zero for: {$input}");
            $this->assertSame($input, $decoded['payload']);
        }
    }

    public function test_it_keeps_valid_error_correction_when_input_exceeds_version_4_capacity(): void
    {
        $service = new BarcodeService;
        $longInput = str_repeat('X', 85);
        $svg = $service->generateQrCodeSvg($longInput, 96);
        $decoded = $this->decodeAndVerifyQrSvg($svg);

        $this->assertSame(0, $decoded['format_syndrome_1']);
        $this->assertSame(0, $decoded['format_syndrome_2']);
        $this->assertTrue($decoded['rs_valid']);
        $this->assertSame(substr($longInput, 0, 78), $decoded['payload']);
    }

    /**
     * Parse SVG QR code and independently verify ISO/IEC 18004 format BCH, RS syndromes, and payload.
     *
     * @return array{format_syndrome_1: int, format_syndrome_2: int, rs_valid: bool, payload: string}
     */
    private function decodeAndVerifyQrSvg(string $svg): array
    {
        preg_match('/viewBox="0 0 (\d+) (\d+)"/', $svg, $vb);
        $totalDim = (int) $vb[1];
        $quiet = 2;
        $dim = $totalDim - ($quiet * 2);

        preg_match('/<path d="([^"]+)"/', $svg, $pm);
        $matrix = array_fill(0, $dim, array_fill(0, $dim, 0));
        preg_match_all('/M(\d+),(\d+)h(\d+)v1h-\d+z/', $pm[1], $matches, PREG_SET_ORDER);

        foreach ($matches as $m) {
            $x = (int) $m[1] - $quiet;
            $y = (int) $m[2] - $quiet;
            $run = (int) $m[3];
            for ($k = 0; $k < $run; $k++) {
                $matrix[$y][$x + $k] = 1;
            }
        }

        $bchSyndrome = function (array $coords) use ($matrix): int {
            $raw = '';
            foreach ($coords as [$r, $c]) {
                $raw .= $matrix[$r][$c];
            }
            $rem = bindec($raw) ^ 0x5412;
            for ($bit = 14; $bit >= 10; $bit--) {
                if ($rem & (1 << $bit)) {
                    $rem ^= (0x537 << ($bit - 10));
                }
            }

            return $rem;
        };

        $coords1 = [
            [8, 0], [8, 1], [8, 2], [8, 3], [8, 4], [8, 5], [8, 7], [8, 8],
            [7, 8], [5, 8], [4, 8], [3, 8], [2, 8], [1, 8], [0, 8],
        ];
        $coords2 = [
            [$dim - 1, 8], [$dim - 2, 8], [$dim - 3, 8], [$dim - 4, 8], [$dim - 5, 8], [$dim - 6, 8], [$dim - 7, 8],
            [8, $dim - 8], [8, $dim - 7], [8, $dim - 6], [8, $dim - 5], [8, $dim - 4], [8, $dim - 3], [8, $dim - 2], [8, $dim - 1],
        ];

        $version = intdiv($dim - 17, 4);
        $specs = [
            2 => ['dataCap' => 34, 'ecLen' => 10, 'align' => 18],
            3 => ['dataCap' => 55, 'ecLen' => 15, 'align' => 22],
            4 => ['dataCap' => 80, 'ecLen' => 20, 'align' => 26],
        ];
        $spec = $specs[$version];

        $isFunc = array_fill(0, $dim, array_fill(0, $dim, false));
        $markRect = function (int $r0, int $c0, int $h, int $w) use (&$isFunc, $dim): void {
            for ($r = max(0, $r0); $r < min($dim, $r0 + $h); $r++) {
                for ($c = max(0, $c0); $c < min($dim, $c0 + $w); $c++) {
                    $isFunc[$r][$c] = true;
                }
            }
        };
        $markRect(0, 0, 9, 9);
        $markRect(0, $dim - 8, 9, 8);
        $markRect($dim - 8, 0, 8, 9);
        for ($i = 0; $i < $dim; $i++) {
            $isFunc[6][$i] = true;
            $isFunc[$i][6] = true;
        }
        $markRect($spec['align'] - 2, $spec['align'] - 2, 5, 5);

        $bits = '';
        $dir = -1;
        $col = $dim - 1;
        while ($col > 0) {
            if ($col === 6) {
                $col--;
            }
            $rows = ($dir === -1) ? range($dim - 1, 0) : range(0, $dim - 1);
            foreach ($rows as $r) {
                foreach ([$col, $col - 1] as $c) {
                    if (! $isFunc[$r][$c]) {
                        $b = $matrix[$r][$c];
                        if (($r + $c) % 2 === 0) {
                            $b ^= 1;
                        }
                        $bits .= $b;
                    }
                }
            }
            $col -= 2;
            $dir = -$dir;
        }

        $totalCw = $spec['dataCap'] + $spec['ecLen'];
        $codewords = [];
        for ($i = 0; $i < $totalCw; $i++) {
            $codewords[] = bindec(substr($bits, $i * 8, 8));
        }

        $exp = array_fill(0, 512, 0);
        $log = array_fill(0, 256, 0);
        $x = 1;
        for ($i = 0; $i < 255; $i++) {
            $exp[$i] = $x;
            $log[$x] = $i;
            $x <<= 1;
            if ($x & 0x100) {
                $x ^= 0x11D;
            }
        }
        for ($i = 255; $i < 512; $i++) {
            $exp[$i] = $exp[$i - 255];
        }

        $rsValid = true;
        for ($k = 0; $k < $spec['ecLen']; $k++) {
            $root = $exp[$k];
            $val = 0;
            foreach ($codewords as $cw) {
                $prod = ($val === 0 || $root === 0) ? 0 : $exp[$log[$val] + $log[$root]];
                $val = $prod ^ $cw;
            }
            if ($val !== 0) {
                $rsValid = false;
                break;
            }
        }

        $charCount = bindec(substr($bits, 4, 8));
        $payload = '';
        for ($i = 0; $i < $charCount; $i++) {
            $payload .= chr(bindec(substr($bits, 12 + $i * 8, 8)));
        }

        return [
            'format_syndrome_1' => $bchSyndrome($coords1),
            'format_syndrome_2' => $bchSyndrome($coords2),
            'rs_valid' => $rsValid,
            'payload' => $payload,
        ];
    }
}
