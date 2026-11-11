<?php

namespace Tests\Unit;

use App\Support\ShopRules;
use PHPUnit\Framework\TestCase;

class ContohWhiteBoxTest extends TestCase
{
    public function test_diskon_di_bawah_ambang_nol(): void
    {
        $hasil = ShopRules::discount(60000, false);
        $this->assertSame(0, $hasil);
    }
}
