<?php

namespace App\Services;

class BarcodeService
{
    /**
     * Code 128 symbol patterns (indices 0 to 106).
     *
     * @var array<int, string>
     */
    private const CODE128_PATTERNS = [
        '212222', '222122', '222221', '121223', '121322', '131222', '122213', '122312', '132212', '221213',
        '221312', '231212', '112232', '122132', '122231', '113222', '123122', '123221', '223211', '221132',
        '221231', '213212', '223112', '312131', '311222', '321122', '321221', '312212', '322112', '322211',
        '212123', '212321', '232121', '111323', '131123', '131321', '112313', '132113', '132311', '211313',
        '231113', '231311', '112133', '112331', '132131', '113123', '113321', '133121', '313121', '211331',
        '231131', '213113', '213311', '213131', '311123', '311321', '331121', '312113', '312311', '332111',
        '314111', '221411', '431111', '111224', '111422', '121124', '121421', '141122', '141221', '112214',
        '112412', '122114', '122411', '142112', '142211', '241211', '221114', '413111', '241112', '134111',
        '111242', '121142', '121241', '114212', '124112', '124211', '411212', '421112', '421211', '212141',
        '214121', '412121', '111143', '111341', '131141', '114113', '114311', '411113', '411311', '113141',
        '114131', '311141', '411131', '211412', '211214', '211232', '2331112',
    ];

    /**
     * GF(256) exponent table.
     *
     * @var array<int, int>|null
     */
    private static ?array $gfExp = null;

    /**
     * GF(256) logarithm table.
     *
     * @var array<int, int>|null
     */
    private static ?array $gfLog = null;

    /**
     * Inisialisasi tabel Galois Field 256.
     */
    private static function initGf(): void
    {
        if (self::$gfExp !== null) {
            return;
        }

        self::$gfExp = array_fill(0, 512, 0);
        self::$gfLog = array_fill(0, 256, 0);
        $x = 1;
        for ($i = 0; $i < 255; $i++) {
            self::$gfExp[$i] = $x;
            self::$gfLog[$x] = $i;
            $x <<= 1;
            if ($x & 0x100) {
                $x ^= 0x11D;
            }
        }
        for ($i = 255; $i < 512; $i++) {
            self::$gfExp[$i] = self::$gfExp[$i - 255];
        }
    }

    /**
     * Perkalian elemen Galois Field 256.
     */
    private static function gfMul(int $x, int $y): int
    {
        if ($x === 0 || $y === 0) {
            return 0;
        }

        return self::$gfExp[self::$gfLog[$x] + self::$gfLog[$y]];
    }

    /**
     * Polinomial generator Reed-Solomon.
     *
     * @return array<int, int>
     */
    private static function rsPoly(int $ecLen): array
    {
        $poly = [1];
        for ($i = 0; $i < $ecLen; $i++) {
            $factor = self::$gfExp[$i];
            $temp = [];
            foreach ($poly as $c) {
                $temp[] = self::gfMul($c, $factor);
            }
            $temp[] = 0;
            $poly = [0, ...$poly];
            for ($j = 0; $j < count($temp); $j++) {
                $poly[$j] = $poly[$j] ^ $temp[$j];
            }
        }

        return $poly;
    }

    /**
     * Hitung kode koreksi error Reed-Solomon.
     *
     * @param  array<int, int>  $data
     * @return array<int, int>
     */
    private static function rsCalc(array $data, int $ecLen): array
    {
        self::initGf();
        $poly = self::rsPoly($ecLen);
        $result = array_merge($data, array_fill(0, $ecLen, 0));
        $dataCount = count($data);

        for ($i = 0; $i < $dataCount; $i++) {
            $lead = $result[$i];
            if ($lead !== 0) {
                $polyCount = count($poly);
                for ($j = 0; $j < $polyCount; $j++) {
                    $result[$i + $j] ^= self::gfMul($poly[$j], $lead);
                }
            }
        }

        return array_slice($result, $dataCount);
    }

    /**
     * Generate SVG barcode standar Code 128B untuk nomor nota atau kode tracking.
     */
    public function generateCode128Svg(string $code, int $height = 38, int $quietZone = 8): string
    {
        $cleanCode = trim($code);
        if ($cleanCode === '') {
            $cleanCode = 'CUCIIN';
        }

        $checksum = 104; // Start Code B
        $symbols = [104];

        $codeLen = strlen($cleanCode);
        for ($i = 0; $i < $codeLen; $i++) {
            $val = ord($cleanCode[$i]) - 32;
            if ($val < 0 || $val > 95) {
                $val = ord('?') - 32;
            }
            $symbols[] = $val;
            $checksum += ($i + 1) * $val;
        }

        $symbols[] = $checksum % 103;
        $symbols[] = 106; // Stop Code

        $path = '';
        $currentX = $quietZone;

        foreach ($symbols as $sym) {
            $pattern = self::CODE128_PATTERNS[$sym];
            $patLen = strlen($pattern);
            for ($j = 0; $j < $patLen; $j++) {
                $width = (int) $pattern[$j];
                if ($j % 2 === 0) { // Garis hitam (bar)
                    $path .= "M{$currentX},0h{$width}v{$height}h-{$width}z ";
                }
                $currentX += $width;
            }
        }

        $totalWidth = $currentX + $quietZone;

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.$totalWidth.' '.$height.'" class="w-full h-auto max-h-12" shape-rendering="crispEdges">'
            .'<rect width="'.$totalWidth.'" height="'.$height.'" fill="#ffffff"/>'
            .'<path d="'.trim($path).'" fill="#000000"/>'
            .'</svg>';
    }

    /**
     * Generate SVG QR Code Level L dengan mask teroptimalisasi untuk pelacakan nota.
     */
    public function generateQrCodeSvg(string $text, int $size = 110): string
    {
        self::initGf();
        $len = strlen($text);

        if ($len <= 32) {
            $version = 2; // 25x25
            $dim = 25;
            $dataCap = 34;
            $ecLen = 10;
            $alignPos = [6, 18];
        } elseif ($len <= 53) {
            $version = 3; // 29x29
            $dim = 29;
            $dataCap = 55;
            $ecLen = 15;
            $alignPos = [6, 22];
        } else {
            $version = 4; // 33x33
            $dim = 33;
            $dataCap = 80;
            $ecLen = 20;
            $alignPos = [6, 26];
        }

        // Mode 0100 (Byte) + Count 8 bits
        $bits = '0100';
        $bits .= str_pad(decbin($len), 8, '0', STR_PAD_LEFT);
        for ($i = 0; $i < $len; $i++) {
            $bits .= str_pad(decbin(ord($text[$i])), 8, '0', STR_PAD_LEFT);
        }

        $maxBits = $dataCap * 8;
        $termLen = min(4, $maxBits - strlen($bits));
        if ($termLen > 0) {
            $bits .= str_repeat('0', $termLen);
        }
        if (strlen($bits) % 8 !== 0) {
            $bits .= str_repeat('0', 8 - (strlen($bits) % 8));
        }

        $padBytes = ['11101100', '00010001'];
        $p = 0;
        while (strlen($bits) < $maxBits) {
            $bits .= $padBytes[$p % 2];
            $p++;
        }

        $dataCodewords = [];
        for ($i = 0; $i < strlen($bits); $i += 8) {
            $dataCodewords[] = bindec(substr($bits, $i, 8));
        }

        $ecCodewords = self::rsCalc($dataCodewords, $ecLen);
        $allCodewords = array_merge($dataCodewords, $ecCodewords);

        $allBits = '';
        foreach ($allCodewords as $cw) {
            $allBits .= str_pad(decbin($cw), 8, '0', STR_PAD_LEFT);
        }

        $matrix = array_fill(0, $dim, array_fill(0, $dim, -1));

        $setFinder = function (int $r, int $c) use (&$matrix, $dim): void {
            for ($i = -1; $i <= 7; $i++) {
                for ($j = -1; $j <= 7; $j++) {
                    $row = $r + $i;
                    $col = $c + $j;
                    if ($row < 0 || $row >= $dim || $col < 0 || $col >= $dim) {
                        continue;
                    }
                    if ($i >= 0 && $i <= 6 && $j >= 0 && $j <= 6) {
                        if ($i === 0 || $i === 6 || $j === 0 || $j === 6 || ($i >= 2 && $i <= 4 && $j >= 2 && $j <= 4)) {
                            $matrix[$row][$col] = 1;
                        } else {
                            $matrix[$row][$col] = 0;
                        }
                    } else {
                        $matrix[$row][$col] = 0;
                    }
                }
            }
        };

        $setFinder(0, 0);
        $setFinder(0, $dim - 7);
        $setFinder($dim - 7, 0);

        if ($version >= 2) {
            $ar = $alignPos[1];
            $ac = $alignPos[1];
            for ($i = -2; $i <= 2; $i++) {
                for ($j = -2; $j <= 2; $j++) {
                    if (abs($i) === 2 || abs($j) === 2 || ($i === 0 && $j === 0)) {
                        $matrix[$ar + $i][$ac + $j] = 1;
                    } else {
                        $matrix[$ar + $i][$ac + $j] = 0;
                    }
                }
            }
        }

        for ($i = 8; $i < $dim - 8; $i++) {
            if ($matrix[6][$i] === -1) {
                $matrix[6][$i] = ($i % 2 === 0) ? 1 : 0;
            }
            if ($matrix[$i][6] === -1) {
                $matrix[$i][6] = ($i % 2 === 0) ? 1 : 0;
            }
        }

        $matrix[4 * $version + 9][8] = 1;

        for ($i = 0; $i < 9; $i++) {
            if ($matrix[8][$i] === -1) {
                $matrix[8][$i] = 2;
            }
            if ($matrix[$i][8] === -1) {
                $matrix[$i][8] = 2;
            }
        }
        for ($i = 0; $i < 8; $i++) {
            if ($matrix[8][$dim - 1 - $i] === -1) {
                $matrix[8][$dim - 1 - $i] = 2;
            }
            if ($matrix[$dim - 1 - $i][8] === -1) {
                $matrix[$dim - 1 - $i][8] = 2;
            }
        }

        $bitIdx = 0;
        $totalBits = strlen($allBits);
        $dir = -1;
        $col = $dim - 1;

        while ($col > 0) {
            if ($col === 6) {
                $col--;
            }
            $rows = ($dir === -1) ? range($dim - 1, 0) : range(0, $dim - 1);
            foreach ($rows as $r) {
                foreach ([$col, $col - 1] as $c) {
                    if ($matrix[$r][$c] === -1) {
                        $b = ($bitIdx < $totalBits) ? (int) $allBits[$bitIdx] : 0;
                        if (($r + $c) % 2 === 0) {
                            $b ^= 1;
                        }
                        $matrix[$r][$c] = $b;
                        $bitIdx++;
                    }
                }
            }
            $col -= 2;
            $dir = -$dir;
        }

        // Format info Level L + Mask 0
        $formatBits = '111011000110111';
        $coords1 = [
            [8, 0], [8, 1], [8, 2], [8, 3], [8, 4], [8, 5], [8, 7], [8, 8],
            [7, 8], [5, 8], [4, 8], [3, 8], [2, 8], [1, 8], [0, 8],
        ];
        $coords2 = [
            [$dim - 1, 8], [$dim - 2, 8], [$dim - 3, 8], [$dim - 4, 8], [$dim - 5, 8], [$dim - 6, 8], [$dim - 7, 8],
            [8, $dim - 8], [8, $dim - 7], [8, $dim - 6], [8, $dim - 5], [8, $dim - 4], [8, $dim - 3], [8, $dim - 2], [8, $dim - 1],
        ];

        for ($i = 0; $i < 15; $i++) {
            $val = (int) $formatBits[$i];
            $matrix[$coords1[$i][0]][$coords1[$i][1]] = $val;
            $matrix[$coords2[$i][0]][$coords2[$i][1]] = $val;
        }

        $quiet = 2;
        $totalDim = $dim + ($quiet * 2);
        $path = '';

        for ($r = 0; $r < $dim; $r++) {
            $c = 0;
            while ($c < $dim) {
                if ($matrix[$r][$c] === 1) {
                    $start = $c;
                    while ($c < $dim && $matrix[$r][$c] === 1) {
                        $c++;
                    }
                    $run = $c - $start;
                    $x = $start + $quiet;
                    $y = $r + $quiet;
                    $path .= "M{$x},{$y}h{$run}v1h-{$run}z ";
                } else {
                    $c++;
                }
            }
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.$totalDim.' '.$totalDim.'" width="'.$size.'" height="'.$size.'" class="inline-block" shape-rendering="crispEdges">'
            .'<rect width="'.$totalDim.'" height="'.$totalDim.'" fill="#ffffff"/>'
            .'<path d="'.trim($path).'" fill="#000000"/>'
            .'</svg>';
    }
}
